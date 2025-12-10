<?php
include '../config.php';
checkLogin();
requirePermission('execute_pxk');

$id = $_GET['id'] ?? 0;

if ($id == 0) {
    header('Location: list.php');
    exit;
}

// Kiểm tra phiếu xuất kho có tồn tại và đang ở trạng thái "Đang xuất"
$sql = "SELECT * FROM phieu_xuat_kho WHERE ma_phieu_xuat_kho = " . intval($id);
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$pxk = $result->fetch_assoc();

if ($pxk['trang_thai'] != 'Đang xuất') {
    header('Location: detail.php?id=' . $id . '&error=' . urlencode('Phiếu xuất kho đã hoàn thành hoặc không thể thay đổi'));
    exit;
}

// Kiểm tra tồn kho trước khi hoàn thành
$sql_check = "SELECT ct.ma_san_pham, ct.so_luong_xuat, sp.ten_san_pham, tk.so_luong_ton
              FROM chi_tiet_phieu_xuat_kho ct
              JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
              LEFT JOIN ton_kho tk ON ct.ma_san_pham = tk.ma_san_pham
              WHERE ct.ma_phieu_xuat_kho = " . intval($id);
$result_check = $conn->query($sql_check);

$insufficient = [];
while ($row = $result_check->fetch_assoc()) {
    $so_luong_ton = $row['so_luong_ton'] ?? 0;
    if ($so_luong_ton < $row['so_luong_xuat']) {
        $insufficient[] = $row['ten_san_pham'] . ' (Tồn: ' . $so_luong_ton . ', Cần: ' . $row['so_luong_xuat'] . ')';
    }
}

if (!empty($insufficient)) {
    $error_msg = 'Không đủ tồn kho cho các sản phẩm: ' . implode(', ', $insufficient);
    header('Location: detail.php?id=' . $id . '&error=' . urlencode($error_msg));
    exit;
}

try {
    // Cập nhật trạng thái thành "Hoàn thành"
    // Trigger sẽ tự động cập nhật tồn kho
    $sql_update = "UPDATE phieu_xuat_kho SET trang_thai = 'Hoàn thành' WHERE ma_phieu_xuat_kho = " . intval($id);
    if (!$conn->query($sql_update)) {
        throw new Exception('Lỗi cập nhật: ' . $conn->error);
    }
    
    logActivity('COMPLETE_PXK', 'Hoàn thành phiếu xuất kho #' . $id);
    header('Location: detail.php?id=' . $id);
} catch (Exception $e) {
    header('Location: detail.php?id=' . $id . '&error=' . urlencode($e->getMessage()));
}
?>
