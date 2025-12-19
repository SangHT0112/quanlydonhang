<?php
include '../config.php';
header('Content-Type: application/json');

$id = intval($_GET['id']);
if (!$id) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$stmt = $conn->prepare("
    SELECT p.ma_phieu_dat_hang, p.ngay_dat, p.tong_tien, p.trang_thai,
           k.ten_khach_hang
    FROM phieu_dat_hang p 
    JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang 
    WHERE p.ma_phieu_dat_hang = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'PO not found']);
}
?>