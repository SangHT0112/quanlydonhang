<?php
include '../config.php';
checkLogin();
requirePermission('delete_po');  // Thay edit_po

$id = intval($_GET['id'] ?? 0);

if ($id == 0) {
    header('Location: list.php');
    exit;
}

// Kiểm tra trạng thái (prepared)
$stmt = $conn->prepare("SELECT trang_thai FROM phieu_dat_hang WHERE ma_phieu_dat_hang = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['trang_thai'] != 'Chờ duyệt') {
        header('Location: list.php');
        exit;
    }
}

try {
    $conn->begin_transaction();

    // Xóa chi tiết (prepared)
    $stmt_del_ct = $conn->prepare("DELETE FROM chi_tiet_phieu_dat_hang WHERE ma_phieu_dat_hang = ?");
    $stmt_del_ct->bind_param("i", $id);
    $stmt_del_ct->execute();

    // Xóa phiếu
    $stmt_del = $conn->prepare("DELETE FROM phieu_dat_hang WHERE ma_phieu_dat_hang = ?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();

    $conn->commit();
    logActivity('DELETE_PO', 'Xóa phiếu đặt hàng #' . $id);
    
    header('Location: list.php');
} catch (Exception $e) {
    $conn->rollback();
    header('Location: list.php?error=' . urlencode($e->getMessage()));
}
?>