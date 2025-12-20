<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

header('Content-Type: application/json');

if (!$_POST || !isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    $id = intval($_POST['id']);
    $ten = trim($_POST['ten_khach_hang']);
    $dt = trim($_POST['dien_thoai']);
    $email = trim($_POST['email']);
    $diachi = trim($_POST['dia_chi']);
    $trangthai = $_POST['trang_thai'];

    if (empty($ten)) {
        throw new Exception('Tên khách hàng không được để trống');
    }

    $stmt = $conn->prepare("UPDATE khach_hang SET ten_khach_hang=?, dien_thoai=?, email=?, dia_chi=?, trang_thai=? WHERE ma_khach_hang=?");
    $stmt->bind_param("sssssi", $ten, $dt, $email, $diachi, $trangthai, $id);
    
    if ($stmt->execute()) {
        logActivity('UPDATE_CUSTOMER', "Cập nhật KH ID $id: $ten");
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Cập nhật thất bại');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}