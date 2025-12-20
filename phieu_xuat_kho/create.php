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
    "SELECT 1 FROM hoa_don hd JOIN phieu_xuat_kho pxk ON hd.ma_phieu_xuat_kho = pxk.ma_phieu_xuat_kho WHERE hd.ma_hoa_don = ?"
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

// ===== KIỂM TRA ĐÃ CÓ PHIẾU BÁN HÀNG (PBH) CHO PO NÀY CHƯA =====
$check_pbh = $conn->prepare(
    "SELECT ma_phieu_ban_hang FROM phieu_ban_hang WHERE ma_phieu_dat_hang = ?"
);
$check_pbh->bind_param("i", $hd['ma_po']);
$check_pbh->execute();
$pbh_result = $check_pbh->get_result()->fetch_assoc();

if (!$pbh_result) {
    // Nếu chưa có PBH, tạo PBH tạm thời từ PO
    $sql_create_pbh = "
        INSERT INTO phieu_ban_hang (ma_phieu_dat_hang, ngay_lap, tong_tien, trang_thai)
        VALUES (?, CURDATE(), ?, 'Duyệt tồn kho')
    ";
    $stmt_pbh = $conn->prepare($sql_create_pbh);
    $stmt_pbh->bind_param("id", $hd['ma_po'], $hd['tong_tien']);
    $stmt_pbh->execute();
    $ma_pbh = $conn->insert_id;

    // Cập nhật chi tiết PBH từ chi tiết HD (giả sử giá bán = giá đặt)
    $sql_ct_hd = "
        SELECT cthd.ma_san_pham, cthd.so_luong, cthd.don_gia as gia_ban
        FROM chi_tiet_hoa_don cthd
        WHERE cthd.ma_hoa_don = ?
    ";
    $stmt_ct_hd = $conn->prepare($sql_ct_hd);
    $stmt_ct_hd->bind_param("i", $ma_hd);
    $stmt_ct_hd->execute();
    $chi_tiet_hd = $stmt_ct_hd->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($chi_tiet_hd as $item) {
        $sql_ct_pbh = "
            INSERT INTO chi_tiet_phieu_ban_hang
            (ma_phieu_ban_hang, ma_san_pham, so_luong, gia_ban)
            VALUES (?, ?, ?, ?)
        ";
        $stmt_ct_pbh = $conn->prepare($sql_ct_pbh);
        $stmt_ct_pbh->bind_param("iiid", $ma_pbh, $item['ma_san_pham'], $item['so_luong'], $item['gia_ban']);
        $stmt_ct_pbh->execute();
    }
} else {
    $ma_pbh = $pbh_result['ma_phieu_ban_hang'];
}

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
            throw new Exception("Tồn kho sản phẩm {$item['ten_san_pham']} không đủ! Chỉ còn {$ton}, cần {$item['so_luong']}.");
        }
    }

    // ===== TRANSACTION =====
    $conn->begin_transaction();

    try {
        // ===== TẠO PHIẾU XUẤT KHO =====
        $sql_pxk = "
            INSERT INTO phieu_xuat_kho
            (ma_phieu_ban_hang, ngay_xuat, nguoi_xuat, trang_thai)
            VALUES (?, CURDATE(), ?, 'Đang xuất')
        ";
        $stmt_pxk = $conn->prepare($sql_pxk);
        $stmt_pxk->bind_param("is", $ma_pbh, $nguoi_xuat);
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

        // ===== CẬP NHẬT TRẠNG THÁI PBH =====
        $stmt_up_pbh = $conn->prepare("
            UPDATE phieu_ban_hang SET trang_thai = 'Gửi kế toán' WHERE ma_phieu_ban_hang = ?
        ");
        $stmt_up_pbh->bind_param("i", $ma_pbh);
        $stmt_up_pbh->execute();

        $conn->commit();

        // Log activity
        logActivity('CREATE_PXK', "Tạo phiếu xuất kho #$ma_pxk từ HD #$ma_hd");

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