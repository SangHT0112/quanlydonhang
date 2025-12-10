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
    // Kiểm tra xem sản phẩm có trong chi tiết nào không
    $sql = "SELECT COUNT(*) as count FROM chi_tiet_phieu_dat_hang WHERE ma_san_pham = " . intval($id);
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        header('Location: list.php?error=' . urlencode('Không thể xóa sản phẩm đã được sử dụng'));
        exit;
    }

    // Xóa từ tồn kho
    $sql = "DELETE FROM ton_kho WHERE ma_san_pham = " . intval($id);
    $conn->query($sql);

    // Xóa sản phẩm
    $sql = "DELETE FROM san_pham WHERE ma_san_pham = " . intval($id);
    $conn->query($sql);
    
    logActivity('DELETE_PRODUCT', 'Xóa sản phẩm #' . $id);
    header('Location: list.php');
} catch (Exception $e) {
    header('Location: list.php?error=' . urlencode($e->getMessage()));
}
?>