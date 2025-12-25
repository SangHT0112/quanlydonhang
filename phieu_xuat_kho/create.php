<?php
// ===============================
// phieu_xuat_kho/create.php
// Nhân viên kho tạo phiếu xuất kho từ hóa đơn đã lập
// ===============================
include '../config.php';
checkLogin();
requirePermission('create_pxk');

// ===== LẤY MA HÓA ĐƠN =====
$ma_hd = isset($_GET['ma_hd']) ? (int)$_GET['ma_hd'] : 0;

if ($ma_hd <= 0) {
    header('Location: ../hoa_don/list.php');
    exit;
}

// ===== KIỂM TRA HÓA ĐƠN ĐÃ CÓ PHIẾU XUẤT KHO CHƯA =====
$check_pxk = $conn->prepare(
    "SELECT * FROM phieu_xuat_kho pxk 
     JOIN hoa_don hd ON pxk.ma_hoa_don = hd.ma_hoa_don 
     WHERE hd.ma_hoa_don = ?"
);
$check_pxk->bind_param("i", $ma_hd);
$check_pxk->execute();

if ($check_pxk->get_result()->num_rows > 0) {
    $_SESSION['error'] = 'Hóa đơn này đã được lập phiếu xuất kho';
    header('Location: ../hoa_don/list.php');
    exit;
}

// ===== LẤY THÔNG TIN HÓA ĐƠN (VÀ LIÊN KẾT PO) =====
$sql_hd = "
    SELECT hd.*, p.ma_phieu_dat_hang as ma_po, k.ten_khach_hang
    FROM hoa_don hd
    JOIN phieu_dat_hang p ON hd.ma_phieu_dat_hang = p.ma_phieu_dat_hang
    JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang
    WHERE hd.ma_hoa_don = ?
";
$stmt_hd = $conn->prepare($sql_hd);
$stmt_hd->bind_param("i", $ma_hd);
$stmt_hd->execute();
$hd = $stmt_hd->get_result()->fetch_assoc();

if (!$hd) {
    $_SESSION['error'] = 'Hóa đơn không tồn tại';
    header('Location: ../hoa_don/list.php');
    exit;
}

// ===== BỎ LOGIC TẠO PBH (KHÔNG CẦN THIẾT TRONG FLOW HD → PXK) =====
// PXK trực tiếp ứng với HD (ma_hoa_don)

// ===== LẤY CHI TIẾT HÓA ĐƠN (VÀ TÊN SP) =====
$sql_ct = "
    SELECT 
        cthd.ma_san_pham,
        cthd.so_luong,
        cthd.don_gia,
        cthd.thanh_tien,
        sp.ten_san_pham
    FROM chi_tiet_hoa_don cthd
    JOIN san_pham sp ON cthd.ma_san_pham = sp.ma_san_pham
    WHERE cthd.ma_hoa_don = ?
";
$stmt_ct = $conn->prepare($sql_ct);
$stmt_ct->bind_param("i", $ma_hd);
$stmt_ct->execute();
$chi_tiet = $stmt_ct->get_result()->fetch_all(MYSQLI_ASSOC);

// ===============================
// XỬ LÝ SUBMIT
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $errors = [];
    $nguoi_xuat = $_SESSION['full_name'] ?? 'N/A';  // Lấy tên user hiện tại

    // ===== KIỂM TRA TỒN KHO TRƯỚC (NGĂN CHẶN NẾU THIẾU) =====
    foreach ($chi_tiet as $item) {
        $stmt_ton = $conn->prepare(
            "SELECT so_luong_ton FROM ton_kho WHERE ma_san_pham = ?"
        );
        $stmt_ton->bind_param("i", $item['ma_san_pham']);
        $stmt_ton->execute();
        $ton = $stmt_ton->get_result()->fetch_assoc()['so_luong_ton'] ?? 0;

        if ($ton < $item['so_luong']) {
            $_SESSION['error'] = "Tồn kho sản phẩm {$item['ten_san_pham']} không đủ! Chỉ còn {$ton}, cần {$item['so_luong']}.";  // ← SỬA: Set session error, không throw để tránh loop
            header("Location: create.php?ma_hd=$ma_hd");
            exit;
        }
    }

    // ===== TRANSACTION =====
    $conn->begin_transaction();

    try {
        // ===== TẠO PHIẾU XUẤT KHO (ỨNG VỚI HD) =====
        $sql_pxk = "
            INSERT INTO phieu_xuat_kho
            (ma_hoa_don, ngay_xuat, nguoi_xuat, trang_thai)
            VALUES (?, CURDATE(), ?, 'Đang xuất')
        ";
        $stmt_pxk = $conn->prepare($sql_pxk);
        $stmt_pxk->bind_param("is", $ma_hd, $nguoi_xuat);  // ← SỬA: Bind ma_hd (ma_hoa_don)
        $stmt_pxk->execute();

        $ma_pxk = $conn->insert_id;

        // ===== CHI TIẾT PHIẾU XUẤT KHO =====
        foreach ($chi_tiet as $item) {
            $thanh_tien_pxk = $item['so_luong'] * $item['don_gia'];  // Tính lại
            $stmt_ct_pxk = $conn->prepare("
                INSERT INTO chi_tiet_phieu_xuat_kho
                (ma_phieu_xuat_kho, ma_san_pham, so_luong_xuat, thanh_tien)
                VALUES (?, ?, ?, ?)
            ");
            $stmt_ct_pxk->bind_param(
                "iiid",
                $ma_pxk,
                $item['ma_san_pham'],
                $item['so_luong'],
                $thanh_tien_pxk
            );
            $stmt_ct_pxk->execute();
        }

        // ===== TRỪ TỒN KHO NGAY (SAU KHI INSERT CHI TIẾT PXK) =====
        foreach ($chi_tiet as $item) {
            // Kiểm tra nếu chưa có entry ton_kho, tạo mới (so_luong_ton = 0)
            $check_ton_exist = $conn->prepare("SELECT ma_ton_kho FROM ton_kho WHERE ma_san_pham = ?");
            $check_ton_exist->bind_param("i", $item['ma_san_pham']);
            $check_ton_exist->execute();
            if ($check_ton_exist->get_result()->num_rows == 0) {
                $stmt_create_ton = $conn->prepare("INSERT INTO ton_kho (ma_san_pham, so_luong_ton) VALUES (?, 0)");
                $stmt_create_ton->bind_param("i", $item['ma_san_pham']);
                $stmt_create_ton->execute();
            }

            // Trừ tồn kho
            $stmt_tru_ton = $conn->prepare("
                UPDATE ton_kho SET so_luong_ton = so_luong_ton - ?, ngay_cap_nhat = CURRENT_TIMESTAMP 
                WHERE ma_san_pham = ?
            ");
            $stmt_tru_ton->bind_param("ii", $item['so_luong'], $item['ma_san_pham']);
            $stmt_tru_ton->execute();

            // Kiểm tra tồn kho sau trừ (nếu âm, rollback)
            $check_ton_after = $conn->prepare("SELECT so_luong_ton FROM ton_kho WHERE ma_san_pham = ?");
            $check_ton_after->bind_param("i", $item['ma_san_pham']);
            $check_ton_after->execute();
            $ton_after = $check_ton_after->get_result()->fetch_assoc()['so_luong_ton'] ?? 0;
            if ($ton_after < 0) {
                throw new Exception("Lỗi: Tồn kho sản phẩm {$item['ten_san_pham']} âm sau trừ! Rollback.");
            }
        }

        // ===== CẬP NHẬT HÓA ĐƠN VỚI MA_PXK =====
        $stmt_up_hd = $conn->prepare("
            UPDATE hoa_don SET ma_phieu_xuat_kho = ? WHERE ma_hoa_don = ?
        ");
        $stmt_up_hd->bind_param("ii", $ma_pxk, $ma_hd);
        $stmt_up_hd->execute();

        // ===== BỎ UPDATE PBH (KHÔNG CẦN THIẾT) =====

        $conn->commit();

        // Log activity
        logActivity('CREATE_PXK', "Tạo phiếu xuất kho #$ma_pxk từ HD #$ma_hd");

        // THÊM MỚI: EMIT SOCKET CHO KẾ TOÁN (UPDATE TRẠNG THÁI TIN NHẮN → HOÀN THÀNH)
        $payload_pxk_complete = [
            'event' => 'pxk_created',  // Event mới để update chat
            'room'  => 'ketoan',
            'data'  => [
                'ma_po' => $hd['ma_po'],
                'ma_hoa_don' => $ma_hd,
                'ma_pxk' => $ma_pxk,
                'message' => "Phiếu xuất kho #$ma_pxk từ HD #$ma_hd đã tạo. Sẵn sàng thanh toán!"
            ]
        ];
        emitSocket($payload_pxk_complete);  // Gọi hàm helper ở dưới

        $_SESSION['success'] = "Tạo phiếu xuất kho thành công (PXK #$ma_pxk). Tồn kho đã được cập nhật.";
        header('Location: list.php');
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Lỗi tạo phiếu xuất kho: ' . $e->getMessage();
        header("Location: create.php?ma_hd=$ma_hd");
        exit;
    }
}

// HELPER: Emit socket (copy từ hoa_don/create.php nếu chưa có)
if (!function_exists('emitSocket')) {
    function emitSocket($payload) {
        $ch = curl_init('http://localhost:4000/emit');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT => 2
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Socket emit failed: HTTP $httpCode, Payload: " . json_encode($payload));
        }
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo phiếu xuất kho - HD #<?php echo $ma_hd; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="container">
    <?php include '../header.php'; ?>

    <h1>Tạo Phiếu Xuất Kho – HD #<?php echo $ma_hd; ?></h1>

    <?php
    if (!empty($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    ?>

    <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($hd['ten_khach_hang']); ?></p>
    <p><strong>Tổng tiền:</strong> <?php echo formatMoney($hd['tong_tien']); ?> VNĐ</p>
    <p><strong>Người xuất:</strong> <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'N/A'); ?></p>

    <table class="table">
        <thead>
        <tr>
            <th>Sản phẩm</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
            <th>Thành tiền</th>
            <th>Tồn kho</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($chi_tiet as $item): 
            $sql_ton = "SELECT so_luong_ton FROM ton_kho WHERE ma_san_pham = ?";
            $stmt = $conn->prepare($sql_ton);
            $stmt->bind_param("i", $item['ma_san_pham']);
            $stmt->execute();
            $ton = $stmt->get_result()->fetch_assoc()['so_luong_ton'] ?? 0;
        ?>
            <tr>
                <td><?php echo htmlspecialchars($item['ten_san_pham']); ?></td>
                <td><?php echo $item['so_luong']; ?></td>
                <td><?php echo formatMoney($item['don_gia']); ?></td>
                <td><?php echo formatMoney($item['thanh_tien']); ?></td>
                <td><?php echo $ton; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <form method="POST">
        <button class="btn-primary"
                onclick="return confirm('Xác nhận tạo phiếu xuất kho và trừ tồn kho?')">
            Tạo phiếu xuất kho
        </button>
        <a href="../hoa_don/list.php" class="btn-secondary">Quay lại</a>
    </form>
</div>
</body>
</html>