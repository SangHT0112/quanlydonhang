<?php
include '../config.php';
checkLogin();
// Allow either create_picklist, execute_pxk, or role 'kho'
if (!(hasPermission('create_picklist') || hasPermission('execute_pxk') || hasRole('kho'))) {
    http_response_code(403);
    echo '<html><head><meta charset="UTF-8"><style>body{font-family:Arial;text-align:center;margin-top:50px}h2{color:#ef4444}</style></head>';
    echo '<body><h2>403 - Không có quyền</h2><p>Bạn không có quyền để thực hiện hành động này.</p>';
    echo '<p><a href="javascript:history.back()">← Quay lại</a></p></body></html>';
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if (!$id) {
    header('Location: list.php');
    exit;
}

// Lấy phiếu trả hàng
$stmt = $conn->prepare("SELECT trang_thai FROM tra_hang WHERE ma_tra_hang = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$th = $stmt->get_result()->fetch_assoc();

if (!$th) {
    $_SESSION['error'] = 'Phiếu trả hàng không tồn tại.';
    header("Location: detail.php?id=$id");
    exit;
}

if ($th['trang_thai'] !== 'Kế toán duyệt') {
    $_SESSION['error'] = 'Chỉ xử lý những yêu cầu đã được kế toán duyệt.';
    header("Location: detail.php?id=$id");
    exit;
}

$conn->begin_transaction();
try {
    // Lấy chi tiết trả hàng
    $stmt_ct = $conn->prepare("SELECT ma_san_pham, so_luong_tra FROM chi_tiet_tra_hang WHERE ma_tra_hang = ?");
    $stmt_ct->bind_param('i', $id);
    $stmt_ct->execute();
    $res = $stmt_ct->get_result();

    while ($row = $res->fetch_assoc()) {
        $ma_sp = $row['ma_san_pham'];
        $sl = intval($row['so_luong_tra']);

        // Tạo record tồn kho nếu chưa có
        $chk = $conn->prepare("SELECT ma_ton_kho FROM ton_kho WHERE ma_san_pham = ?");
        $chk->bind_param('i', $ma_sp);
        $chk->execute();
        $exists = $chk->get_result()->num_rows > 0;

        if (!$exists) {
            $ins = $conn->prepare("INSERT INTO ton_kho (ma_san_pham, so_luong_ton, ngay_cap_nhat) VALUES (?, 0, NOW())");
            $ins->bind_param('i', $ma_sp);
            $ins->execute();
        }

        // Cộng tồn kho lên
        $up = $conn->prepare("UPDATE ton_kho SET so_luong_ton = so_luong_ton + ?, ngay_cap_nhat = CURRENT_TIMESTAMP WHERE ma_san_pham = ?");
        $up->bind_param('ii', $sl, $ma_sp);
        $up->execute();

        // Ghi lịch sử tồn kho nếu có bảng
        $log = $conn->prepare("INSERT INTO lich_su_ton_kho (ma_san_pham, thay_doi, loai, ref_table, ref_id, nguoi_thuc_hien) VALUES (?, ?, 'RETURN', 'tra_hang', ?, ?)");
        $userId = $_SESSION['user_id'] ?? null;
        $log->bind_param('iiii', $ma_sp, $sl, $id, $userId);
        $log->execute();
    }

    // Cập nhật trạng thái phiếu trả hàng
    $stmt_up = $conn->prepare("UPDATE tra_hang SET trang_thai = 'Hoàn thành' WHERE ma_tra_hang = ?");
    $stmt_up->bind_param('i', $id);
    $stmt_up->execute();

    $conn->commit();

    logActivity('PROCESS_RETURN', "Kho hoàn thành trả hàng #$id");
    $_SESSION['success'] = 'Phiếu trả hàng đã được xử lý, tồn kho đã được cập nhật.';
    header("Location: detail.php?id=$id");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'Lỗi xử lý trả hàng: ' . $e->getMessage();
    header("Location: detail.php?id=$id");
    exit;
}

?>
