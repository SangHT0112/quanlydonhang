<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

$id = $_GET['id'] ?? 0;

if ($id == 0) {
    header('Location: list.php');
    exit;
}

try {
    // Kiểm tra xem khách hàng có đơn hàng không
    $sql = "SELECT COUNT(*) as count FROM phieu_dat_hang WHERE ma_khach_hang = " . intval($id);
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        header('Location: list.php?error=' . urlencode('Không thể xóa khách hàng đã có đơn hàng'));
        exit;
    }

    // Xóa khách hàng
    $sql = "DELETE FROM khach_hang WHERE ma_khach_hang = " . intval($id);
    $conn->query($sql);
    
    logActivity('DELETE_CUSTOMER', 'Xóa khách hàng #' . $id);
    header('Location: list.php');
} catch (Exception $e) {
    header('Location: list.php?error=' . urlencode($e->getMessage()));
}
?>