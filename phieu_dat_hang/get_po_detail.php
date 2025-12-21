<!-- phieu_dat_hang/get_po_detail.php (TÊN FILE MỚI: TẠO FILE NÀY ĐỂ AJAX HOẠT ĐỘNG) -->
<?php
include '../config.php';
checkLogin();

$id = intval($_GET['id']);

if (!$id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID không hợp lệ']);
    exit;
}

// Lấy chi tiết PO
$stmt = $conn->prepare("
    SELECT p.*, u.full_name AS nguoi_tao, a.full_name AS nguoi_duyet
    FROM phieu_dat_hang p
    LEFT JOIN users u ON p.created_by = u.id
    LEFT JOIN users a ON p.approved_by = a.id
    WHERE p.ma_phieu_dat_hang = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$po = $result->fetch_assoc();

if (!$po) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Phiếu đặt hàng không tồn tại']);
    exit;
}

// Tên khách hàng (sửa: dùng prepared statement)
$kh_query = $conn->prepare("SELECT ten_khach_hang FROM khach_hang WHERE ma_khach_hang = ?");
$kh_query->bind_param("i", $po['ma_khach_hang']);
$kh_query->execute();
$kh_result = $kh_query->get_result();
$kh = $kh_result->fetch_assoc();
$po['ten_khach_hang'] = $kh['ten_khach_hang'] ?? 'N/A';

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
$po['items'] = [];
while ($item = $items_result->fetch_assoc()) {
    $po['items'][] = $item;
}

// Trả về JSON
header('Content-Type: application/json');
echo json_encode($po, JSON_UNESCAPED_UNICODE);
?>