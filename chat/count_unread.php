<?php
// count_unread.php - ĐẾM SỐ TIN NHẮN CHƯA ĐỌC
include '../config.php';
checkLogin();

$role = $_SESSION['role'];

try {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM system_messages 
        WHERE receiver_role = ? AND is_read = 0
    ");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    echo $result['unread_count'];  // Trả về số nguyên (0, 1, 2, ...)
    
} catch (Exception $e) {
    echo '0';  // Fallback nếu lỗi
}
?>