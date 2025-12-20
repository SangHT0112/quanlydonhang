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

// 🔔 REALTIME: PO đã duyệt (cho kế toán)
$payload = [
    'event' => 'po_approved',
    'room'  => 'ketoan',
    'data'  => [
        'ma_phieu' => $id,
        'message'  => "PO #$id đã được duyệt, sẵn sàng lập hóa đơn"
    ]
];

$ch = curl_init('http://localhost:4000/emit');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true
]);
curl_exec($ch);
curl_close($ch);
header("Location: detail.php?id=$id");
?>