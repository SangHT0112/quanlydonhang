<?php
// ===============================
// hoa_don/create.php
// Kế toán tạo hóa đơn từ PO đã duyệt
// ===============================

include '../config.php';
checkLogin();
requirePermission('create_invoice');

// ===== LẤY PO =====
$ma_po = isset($_GET['ma_po']) ? (int)$_GET['ma_po'] : 0;

if ($ma_po <= 0) {
    header('Location: ../phieu_dat_hang/list.php');
    exit;
}

// ===== KIỂM TRA PO ĐÃ CÓ HÓA ĐƠN CHƯA =====
$check_hd = $conn->prepare(
    "SELECT 1 FROM hoa_don WHERE ma_phieu_dat_hang = ?"
);
$check_hd->bind_param("i", $ma_po);
$check_hd->execute();

if ($check_hd->get_result()->num_rows > 0) {
    $_SESSION['error'] = 'PO này đã được lập hóa đơn';
    header('Location: ../phieu_dat_hang/list.php');
    exit;
}

// ===== LẤY THÔNG TIN PO (PHẢI ĐÃ DUYỆT) =====
$sql_po = "
    SELECT p.*, k.ten_khach_hang
    FROM phieu_dat_hang p
    JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang
    WHERE p.ma_phieu_dat_hang = ? 
      AND p.trang_thai = 'Đã duyệt'
";
$stmt_po = $conn->prepare($sql_po);
$stmt_po->bind_param("i", $ma_po);
$stmt_po->execute();
$po = $stmt_po->get_result()->fetch_assoc();

if (!$po) {
    $_SESSION['error'] = 'PO không tồn tại hoặc chưa được duyệt';
    header('Location: ../phieu_dat_hang/list.php');
    exit;
}

// ===== LẤY CHI TIẾT PO =====
$sql_ct = "
    SELECT 
        ct.ma_san_pham,
        ct.so_luong,
        ct.gia_dat,
        ct.thanh_tien,
        sp.ten_san_pham
    FROM chi_tiet_phieu_dat_hang ct
    JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
    WHERE ct.ma_phieu_dat_hang = ?
";
$stmt_ct = $conn->prepare($sql_ct);
$stmt_ct->bind_param("i", $ma_po);
$stmt_ct->execute();
$chi_tiet = $stmt_ct->get_result()->fetch_all(MYSQLI_ASSOC);

// ===============================
// XỬ LÝ SUBMIT
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $errors = [];

    // ===== KIỂM TRA TỒN KHO (CHỈ ĐỂ CẢNH BÁO) =====
    foreach ($chi_tiet as $item) {
        $stmt = $conn->prepare(
            "SELECT so_luong_ton FROM ton_kho WHERE ma_san_pham = ?"
        );
        $stmt->bind_param("i", $item['ma_san_pham']);
        $stmt->execute();
        $ton = $stmt->get_result()->fetch_assoc()['so_luong_ton'] ?? 0;

        if ($ton < $item['so_luong']) {
            $errors[] = "Sản phẩm {$item['ten_san_pham']} chỉ còn {$ton}";
        }
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header("Location: create.php?ma_po=$ma_po");
        exit;
    }

    // ===== TRANSACTION =====
    $conn->begin_transaction();

    try {
        // ===== TẠO HÓA ĐƠN =====
        $sql_hd = "
            INSERT INTO hoa_don
            (ma_phieu_dat_hang, ngay_xuat_hd, tong_tien, trang_thai)
            VALUES (?, CURDATE(), ?, 'Chưa thanh toán')
        ";
        $stmt_hd = $conn->prepare($sql_hd);
        $stmt_hd->bind_param("id", $ma_po, $po['tong_tien']);
        $stmt_hd->execute();

        $ma_hoa_don = $conn->insert_id;

        // ===== CHI TIẾT HÓA ĐƠN =====
        foreach ($chi_tiet as $item) {
            $stmt_ct_hd = $conn->prepare("
                INSERT INTO chi_tiet_hoa_don
                (ma_hoa_don, ma_san_pham, so_luong, don_gia, thanh_tien)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt_ct_hd->bind_param(
                "iiidd",
                $ma_hoa_don,
                $item['ma_san_pham'],
                $item['so_luong'],
                $item['gia_dat'],
                $item['thanh_tien']
            );
            $stmt_ct_hd->execute();
        }

        // ===== CẬP NHẬT TRẠNG THÁI PO =====
        $stmt_up = $conn->prepare("
            UPDATE phieu_dat_hang
            SET trang_thai = 'Đã lập hóa đơn'
            WHERE ma_phieu_dat_hang = ?
        ");
        $stmt_up->bind_param("i", $ma_po);
        $stmt_up->execute();

        $conn->commit();

        $_SESSION['success'] = "Tạo hóa đơn thành công (HD #$ma_hoa_don)";
        header('Location: list.php');
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Lỗi tạo hóa đơn: ' . $e->getMessage();
        header("Location: create.php?ma_po=$ma_po");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo hóa đơn - PO #<?php echo $ma_po; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="container">
    <?php include '../header.php'; ?>

    <h1>Tạo Hóa Đơn – PO #<?php echo $ma_po; ?></h1>

    <?php
    if (!empty($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    ?>

    <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($po['ten_khach_hang']); ?></p>
    <p><strong>Tổng tiền:</strong> <?php echo formatMoney($po['tong_tien']); ?> VNĐ</p>

    <table class="table">
        <thead>
        <tr>
            <th>Sản phẩm</th>
            <th>Số lượng</th>
            <th>Giá đặt</th>
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
                <td><?php echo formatMoney($item['gia_dat']); ?></td>
                <td><?php echo formatMoney($item['thanh_tien']); ?></td>
                <td><?php echo $ton; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <form method="POST">
        <button class="btn-primary"
                onclick="return confirm('Xác nhận tạo hóa đơn')">
            Tạo hóa đơn
        </button>
        <a href="../phieu_dat_hang/list.php" class="btn-secondary">Quay lại</a>
    </form>
</div>
</body>
</html>
