<?php
include '../config.php';
checkLogin();
requirePermission('approve_po');

$id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

$conn->begin_transaction();

$stmt = $conn->prepare("
    UPDATE phieu_dat_hang
    SET trang_thai = 'Đã duyệt', approved_by = ?
    WHERE ma_phieu_dat_hang = ?
    AND trang_thai = 'Chờ duyệt'
");
$stmt->bind_param("ii", $user_id, $id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    $conn->rollback();  // ← ĐÂY ĐÃ AN TOÀN, KHÔNG CẦN CHECK
    die('PO không hợp lệ để duyệt');
}

// ==============================
// SYSTEM MESSAGE CHO CHAT
// ==============================
$message = "PO #$id đã được duyệt. Nhấn để lập hóa đơn.";
$link = "/hoa_don/create.php?ma_po=$id";

$stmt_msg = $conn->prepare("
    INSERT INTO system_messages (sender_role, receiver_role, message, action_link)
    VALUES ('sale', 'ketoan', ?, ?)
");
$stmt_msg->bind_param("ss", $message, $link);
$stmt_msg->execute();

// ==============================
// REALTIME: EMIT CẢ TOAST + CHAT
// ==============================
$payload_toast = [
    'event' => 'po_approved',
    'room'  => 'ketoan',
    'data'  => [
        'ma_phieu' => $id,
        'message'  => "PO #$id đã được duyệt bởi {$_SESSION['full_name']}. Sẵn sàng lập hóa đơn!",
        'ma_po'    => $id
    ]
];
emitSocket($payload_toast);

$payload_chat = [
    'event' => 'system_message',
    'room'  => 'ketoan',
    'data'  => [
        'sender'  => 'sale',
        'message' => $message,
        'link'    => $link,
        'time'    => date('Y-m-d H:i:s')
    ]
];
emitSocket($payload_chat);

$conn->commit();

logActivity('APPROVE_PO', "Duyệt PO #$id");

header("Location: detail.php?id=$id");
exit;

// HELPER: Emit socket (giữ nguyên)
function emitSocket($payload) {
    $ch = curl_init('http://localhost:4000/emit');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 1,
        CURLOPT_TIMEOUT => 2
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Socket emit failed: HTTP $httpCode, Payload: " . json_encode($payload));
    }
}
?>