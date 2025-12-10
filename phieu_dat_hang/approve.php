<?php
include '../config.php';
checkLogin();
requirePermission('approve_po');

$id = $_GET['id'] ?? 0;

if ($id == 0) {
    header('Location: list.php');
    exit;
}

try {
    $sql = "UPDATE phieu_dat_hang SET trang_thai = 'Đã duyệt' WHERE ma_phieu_dat_hang = " . intval($id);
    $conn->query($sql);
    logActivity('APPROVE_PO', 'Duyệt phiếu đặt hàng #' . $id);
    header('Location: detail.php?id=' . $id);
} catch (Exception $e) {
    header('Location: detail.php?id=' . $id . '&error=' . urlencode($e->getMessage()));
}
?>