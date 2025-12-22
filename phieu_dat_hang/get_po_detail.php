<?php
// phieu_dat_hang/get_po_detail.php
include '../config.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT p.*, u.full_name AS nguoi_tao, a.full_name AS nguoi_duyet, k.ten_khach_hang
    FROM phieu_dat_hang p
    LEFT JOIN users u ON p.created_by = u.id
    LEFT JOIN users a ON p.approved_by = a.id
    LEFT JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang
    WHERE p.ma_phieu_dat_hang = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$po = $result->fetch_assoc();

if (!$po) {
    echo json_encode(['error' => 'PO không tồn tại']);
    exit;
}

// Chi tiết sản phẩm
$stmt_items = $conn->prepare("
    SELECT 
        ct.ma_san_pham,
        sp.ten_san_pham,
        sp.don_vi,
        ct.so_luong,
        ct.gia_dat,
        (ct.so_luong * ct.gia_dat) AS thanh_tien
    FROM chi_tiet_phieu_dat_hang ct
    JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
    WHERE ct.ma_phieu_dat_hang = ?
");
$stmt_items->bind_param("i", $id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode([
    'po' => $po,
    'items' => $items
]);
?>