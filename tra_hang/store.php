<?php
include '../config.php';
checkLogin();
requirePermission('create_return');

$ma_hd = intval($_POST['ma_hoa_don'] ?? 0);
$ly_do = trim($_POST['ly_do'] ?? '');
$so_luong_tra = $_POST['so_luong_tra'] ?? [];
$don_gia = $_POST['don_gia'] ?? [];

if (!$ma_hd) {
    die('Hóa đơn không hợp lệ');
}

// CHẶN SUBMIT TRỐNG
$co_san_pham = false;
foreach ($so_luong_tra as $sl) {
    if ((int)$sl > 0) {
        $co_san_pham = true;
        break;
    }
}
if (!$co_san_pham) {
    $_SESSION['error'] = 'Phải chọn ít nhất 1 sản phẩm để trả.';
    header("Location: create.php?ma_hd=$ma_hd");
    exit;
}

$conn->begin_transaction();

try {
    // 1. Tạo phiếu trả hàng
    $stmt = $conn->prepare("
        INSERT INTO tra_hang (ma_hoa_don, ngay_tra, ly_do, trang_thai)
        VALUES (?, CURDATE(), ?, 'Yêu cầu')
    ");
    $stmt->bind_param("is", $ma_hd, $ly_do);
    $stmt->execute();
    $ma_tra_hang = $conn->insert_id;

    // 2. Insert chi tiết (KHÔNG TRÙNG)
    foreach ($so_luong_tra as $ma_sp => $sl) {
        $sl = (int)$sl;
        if ($sl <= 0) continue;

        $gia = (float)$don_gia[$ma_sp];
        $thanh_tien = $sl * $gia;

        $stmt = $conn->prepare("
            INSERT INTO chi_tiet_tra_hang
            (ma_tra_hang, ma_san_pham, so_luong_tra, gia_tra, thanh_tien_tra)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiidd",
            $ma_tra_hang,
            $ma_sp,
            $sl,
            $gia,
            $thanh_tien
        );
        $stmt->execute();
    }

    $conn->commit();

    logActivity('CREATE_RETURN', "Tạo yêu cầu trả hàng #$ma_tra_hang từ HD #$ma_hd");
    $_SESSION['success'] = 'Yêu cầu trả hàng đã được gửi cho kế toán.';
    header("Location: list.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Lỗi tạo phiếu trả hàng: " . $e->getMessage());
}
