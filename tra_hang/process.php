<?php
include '../config.php';
checkLogin();

// Cho phép: kho / tạo picklist / thực hiện PXK
if (
    !(
        hasPermission('create_picklist') ||
        hasPermission('execute_pxk') ||
        hasRole('kho')
    )
) {
    http_response_code(403);
    echo '<html><head><meta charset="UTF-8">
    <style>body{font-family:Arial;text-align:center;margin-top:50px}h2{color:#ef4444}</style>
    </head><body>';
    echo '<h2>403 - Không có quyền</h2>';
    echo '<p>Bạn không có quyền để thực hiện hành động này.</p>';
    echo '<p><a href="javascript:history.back()">← Quay lại</a></p>';
    echo '</body></html>';
    exit;
}

$id = intval($_POST['id'] ?? 0);
if (!$id) {
    header('Location: list.php');
    exit;
}

$conn->begin_transaction();

try {
    // ============================
    // 1. KHÓA PHIẾU TRẢ HÀNG
    // ============================
    $stmt = $conn->prepare("
        SELECT trang_thai 
        FROM tra_hang 
        WHERE ma_tra_hang = ? 
        FOR UPDATE
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $tra_hang = $stmt->get_result()->fetch_assoc();

    if (!$tra_hang) {
        throw new Exception('Phiếu trả hàng không tồn tại.');
    }

    if ($tra_hang['trang_thai'] !== 'Kế toán duyệt') {
        throw new Exception('Phiếu này đã được xử lý trước đó.');
    }

    // ĐÁNH DẤU ĐANG XỬ LÝ → KHÓA DOUBLE SUBMIT
    $stmt_lock = $conn->prepare("
        UPDATE tra_hang 
        SET trang_thai = 'Đang xử lý' 
        WHERE ma_tra_hang = ?
    ");
    $stmt_lock->bind_param('i', $id);
    $stmt_lock->execute();

    // ============================
    // 2. LẤY CHI TIẾT TRẢ HÀNG
    // ============================
    $stmt_ct = $conn->prepare("
        SELECT ma_san_pham, so_luong_tra 
        FROM chi_tiet_tra_hang 
        WHERE ma_tra_hang = ?
    ");
    $stmt_ct->bind_param('i', $id);
    $stmt_ct->execute();
    $chi_tiet = $stmt_ct->get_result();

    // ============================
    // 3. CẬP NHẬT TỒN KHO
    // ============================
    while ($row = $chi_tiet->fetch_assoc()) {
        $ma_sp = (int)$row['ma_san_pham'];
        $sl = (int)$row['so_luong_tra'];

        // Lock tồn kho sản phẩm
        $chk = $conn->prepare("
            SELECT ma_ton_kho 
            FROM ton_kho 
            WHERE ma_san_pham = ? 
            FOR UPDATE
        ");
        $chk->bind_param('i', $ma_sp);
        $chk->execute();
        $ton = $chk->get_result()->fetch_assoc();

        // Nếu chưa có → tạo
        if (!$ton) {
            $ins = $conn->prepare("
                INSERT INTO ton_kho (ma_san_pham, so_luong_ton, ngay_cap_nhat)
                VALUES (?, 0, NOW())
            ");
            $ins->bind_param('i', $ma_sp);
            $ins->execute();
        }

        // CỘNG TỒN KHO
        $up = $conn->prepare("
            UPDATE ton_kho 
            SET so_luong_ton = so_luong_ton + ?, 
                ngay_cap_nhat = CURRENT_TIMESTAMP 
            WHERE ma_san_pham = ?
        ");
        $up->bind_param('ii', $sl, $ma_sp);
        $up->execute();
       
    }

    // ============================
    // 5. HOÀN THÀNH PHIẾU
    // ============================
    $stmt_done = $conn->prepare("
        UPDATE tra_hang 
        SET trang_thai = 'Hoàn thành'
        WHERE ma_tra_hang = ?
    ");
    $stmt_done->bind_param('i', $id);
    $stmt_done->execute();

    $conn->commit();

    logActivity('PROCESS_RETURN', "Kho hoàn thành trả hàng #$id");

    $_SESSION['success'] = 'Phiếu trả hàng đã được xử lý. Tồn kho đã cập nhật.';
    header("Location: detail.php?id=$id");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'Lỗi xử lý trả hàng: ' . $e->getMessage();
    header("Location: detail.php?id=$id");
    exit;
}
