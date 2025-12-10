<?php
include '../config.php';
checkLogin();
requirePermission('edit_po');

$id = $_GET['id'] ?? 0;

if ($id == 0) {
    header('Location: list.php');
    exit;
}

// Kiểm tra quyền xóa
$sql = "SELECT trang_thai FROM phieu_dat_hang WHERE ma_phieu_dat_hang = " . intval($id);
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['trang_thai'] != 'Chờ duyệt') {
        header('Location: list.php');
        exit;
    }
}

try {
    $conn->begin_transaction();

    // Xóa chi tiết
    $sql = "DELETE FROM chi_tiet_phieu_dat_hang WHERE ma_phieu_dat_hang = " . intval($id);
    $conn->query($sql);

    // Xóa phiếu
    $sql = "DELETE FROM phieu_dat_hang WHERE ma_phieu_dat_hang = " . intval($id);
    $conn->query($sql);

    $conn->commit();
    logActivity('DELETE_PO', 'Xóa phiếu đặt hàng #' . $id);
    
    header('Location: list.php');
} catch (Exception $e) {
    $conn->rollback();
    header('Location: list.php?error=' . urlencode($e->getMessage()));
}
?>