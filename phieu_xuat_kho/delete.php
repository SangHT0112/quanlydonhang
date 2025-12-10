<?php
include '../config.php';
checkLogin();
requirePermission('execute_pxk');

$id = $_GET['id'] ?? 0;

if ($id == 0) {
    header('Location: list.php');
    exit;
}

// Kiểm tra phiếu xuất kho có tồn tại không
$sql = "SELECT trang_thai FROM phieu_xuat_kho WHERE ma_phieu_xuat_kho = " . intval($id);
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$pxk = $result->fetch_assoc();

// Chỉ cho phép xóa khi còn ở trạng thái "Đang xuất"
if ($pxk['trang_thai'] != 'Đang xuất') {
    header('Location: detail.php?id=' . $id . '&error=' . urlencode('Chỉ có thể xóa phiếu xuất kho khi đang ở trạng thái "Đang xuất"'));
    exit;
}

try {
    $conn->begin_transaction();

    // Xóa chi tiết phiếu xuất kho
    $sql_delete_ct = "DELETE FROM chi_tiet_phieu_xuat_kho WHERE ma_phieu_xuat_kho = " . intval($id);
    if (!$conn->query($sql_delete_ct)) {
        throw new Exception('Lỗi xóa chi tiết: ' . $conn->error);
    }

    // Xóa phiếu xuất kho
    $sql_delete = "DELETE FROM phieu_xuat_kho WHERE ma_phieu_xuat_kho = " . intval($id);
    if (!$conn->query($sql_delete)) {
        throw new Exception('Lỗi xóa phiếu: ' . $conn->error);
    }

    $conn->commit();
    logActivity('DELETE_PXK', 'Xóa phiếu xuất kho #' . $id);
    
    header('Location: list.php');
} catch (Exception $e) {
    $conn->rollback();
    header('Location: list.php?error=' . urlencode($e->getMessage()));
}
?>
