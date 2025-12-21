<?php
include '../config.php';
checkLogin();
requirePermission('create_return');

$ma_hd = $_POST['ma_hoa_don'];
$ly_do = $_POST['ly_do'];
$so_luong_tra = $_POST['so_luong_tra'];
$don_gia = $_POST['don_gia'];

$conn->begin_transaction();

try {
    // 1. Tạo phiếu trả hàng (Sales gửi yêu cầu trả)
    $stmt = $conn->prepare("
        INSERT INTO tra_hang (ma_hoa_don, ngay_tra, ly_do, trang_thai)
        VALUES (?, CURDATE(), ?, 'Yêu cầu')
    ");
    $stmt->bind_param("is", $ma_hd, $ly_do);
    $stmt->execute();
    $ma_tra_hang = $conn->insert_id;

    // 2. Chi tiết trả hàng
    foreach ($so_luong_tra as $ma_sp => $sl) {
        if ($sl > 0) {
            $gia = $don_gia[$ma_sp];
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
    }

    $conn->commit();

    // Log hoạt động và thông báo
    logActivity('CREATE_RETURN', "Tạo yêu cầu trả hàng #$ma_tra_hang từ HD #$ma_hd");
    $_SESSION['success'] = 'Yêu cầu trả hàng đã được gửi cho kế toán.';
    header("Location: list.php");
} catch (Exception $e) {
    $conn->rollback();
    die("Lỗi tạo phiếu trả hàng");
}
