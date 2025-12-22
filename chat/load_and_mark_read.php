<?php
// load_and_mark_read.php - LOAD + MARK READ + CHECK TRẠNG THÁI (FIX: NO TRANSACTION, BETTER ERROR HANDLING)
include '../config.php';
checkLogin();

$role = $_SESSION['role'];

// Không dùng transaction để tránh rollback nếu 1 phần fail

try {
    // 1. UPDATE is_read = 1 cho tin chưa đọc (riêng biệt, không rollback)
    $stmt_update = $conn->prepare("
        UPDATE system_messages 
        SET is_read = 1 
        WHERE receiver_role = ? AND is_read = 0
    ");
    if (!$stmt_update) {
        throw new Exception("Prepare UPDATE fail: " . $conn->error);
    }
    $stmt_update->bind_param("s", $role);
    if (!$stmt_update->execute()) {
        throw new Exception("Execute UPDATE fail: " . $stmt_update->error);
    }
    $updated_rows = $stmt_update->affected_rows;
    $stmt_update->close();

    error_log("DEBUG: Role=$role, Marked unread: $updated_rows");  // Log để kiểm tra

    // 2. LOAD: Ưu tiên unread (nhưng sau update thì toàn read), ORDER BY created_at DESC, LIMIT 20
    // Dynamic JOIN cho kho (an toàn hơn: check table tồn tại nếu cần, nhưng tạm giữ)
    $join_pxk = ($role === 'kho') ? "LEFT JOIN phieu_xuat_kho pxk ON hd.ma_hoa_don = pxk.ma_hoa_don" : "";
    $has_pxk = ($role === 'kho') ? "CASE WHEN pxk.ma_phieu_xuat_kho IS NOT NULL THEN 1 ELSE 0 END as has_pxk" : "0 as has_pxk";

    $sql = "
        SELECT 
            sm.id,
            sm.sender_role,
            sm.receiver_role,
            sm.message,
            sm.action_link,
            sm.is_read,
            sm.created_at,
            sm.ma_phieu_dat_hang,
            p.trang_thai as po_trang_thai,
            CASE WHEN hd.ma_hoa_don IS NOT NULL THEN 1 ELSE 0 END as has_hoa_don,
            $has_pxk
        FROM system_messages sm
        LEFT JOIN phieu_dat_hang p ON sm.ma_phieu_dat_hang = p.ma_phieu_dat_hang
        LEFT JOIN hoa_don hd ON p.ma_phieu_dat_hang = hd.ma_phieu_dat_hang
        $join_pxk
        WHERE sm.receiver_role = ?
        ORDER BY sm.is_read ASC, sm.created_at DESC
        LIMIT 20
    ";

    error_log("DEBUG: SQL for role=$role: " . $sql);  // Log SQL để check syntax nếu lỗi

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare SELECT fail: " . $conn->error . " | SQL: " . substr($sql, 0, 200));  // Truncate log
    }
    $stmt->bind_param("s", $role);
    if (!$stmt->execute()) {
        throw new Exception("Execute SELECT fail: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    error_log("DEBUG: Loaded messages for role=$role: " . count($messages));  // Log count

    // 3. Flag is_completed động (an toàn: check key tồn tại)
    foreach ($messages as &$m) {
        $m['is_completed'] = false;  // Default
        if ($role === 'ketoan') {
            $m['is_completed'] = (isset($m['has_hoa_don']) && $m['has_hoa_don'] == 1);
        } elseif ($role === 'kho') {
            $has_hd = (isset($m['has_hoa_don']) && $m['has_hoa_don'] == 1);
            $has_pxk_val = (isset($m['has_pxk']) ? $m['has_pxk'] : 0);
            $m['is_completed'] = ($has_hd && $has_pxk_val == 1);
        } else {
            $m['is_completed'] = (isset($m['po_trang_thai']) && $m['po_trang_thai'] === 'Đã lập hóa đơn');
        }
    }

    // Echo success JSON
    echo json_encode([
        'messages' => $messages,
        'updated_count' => $updated_rows
    ]);

} catch (Exception $e) {
    // Không rollback (không có transaction)
    error_log("Chat load EXCEPTION: " . $e->getMessage() . " | Role: $role");  // Log đầy đủ
    echo json_encode(['error' => 'Lỗi tải tin nhắn: ' . $e->getMessage()]);
}
?>