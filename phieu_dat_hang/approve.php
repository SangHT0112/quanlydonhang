<?php
include '../config.php';
checkLogin();
requirePermission('approve_po');

$id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);  // Set approved_by

$stmt = $conn->prepare("
    UPDATE phieu_dat_hang
    SET trang_thai = 'Đã duyệt', approved_by = ?
    WHERE ma_phieu_dat_hang = ?
    AND trang_thai = 'Chờ duyệt'
");
$stmt->bind_param("ii", $user_id, $id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    die('PO không hợp lệ để duyệt');
}

logActivity('APPROVE_PO', "Duyệt PO #$id");
header("Location: detail.php?id=$id");
?>