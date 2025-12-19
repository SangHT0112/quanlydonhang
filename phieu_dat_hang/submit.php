<?php
include '../config.php';
checkLogin();


$id = intval($_GET['id']);

$stmt = $conn->prepare("
    UPDATE phieu_dat_hang
    SET trang_thai = 'Chờ duyệt'  
    WHERE ma_phieu_dat_hang = ?
    AND trang_thai = 'Chờ duyệt'  
");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    die('Không thể gửi duyệt');
}

/* Gửi socket cho kế toán */
$payload = [
    'event' => 'po_submitted',
    'room' => 'ketoan',
    'data' => [
        'ma_phieu' => $id,
        'message' => "Có PO mới cần duyệt #$id"
    ]
];

$ch = curl_init('http://localhost:4000/emit');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);
curl_exec($ch);
curl_close($ch);

logActivity('SUBMIT_PO', "Gửi duyệt PO #$id");
header("Location: detail.php?id=$id");
?>