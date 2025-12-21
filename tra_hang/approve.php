<?php
include '../config.php';
checkLogin();


$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$action = $_POST['action'] ?? '';
$note = trim($_POST['note'] ?? '');

if (!$id) {
    header('Location: list.php');
    exit;
}

try {
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE tra_hang SET trang_thai = 'Kế toán duyệt' WHERE ma_tra_hang = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if ($note !== '') {
            $stmt2 = $conn->prepare("UPDATE tra_hang SET ly_do = CONCAT(IFNULL(ly_do,''), ? ) WHERE ma_tra_hang = ?");
            $append = "\n[KT duyệt] " . $note;
            $stmt2->bind_param('si', $append, $id);
            $stmt2->execute();
        }

        logActivity('APPROVE_RETURN', "Kế toán duyệt trả hàng #$id");
        $_SESSION['success'] = 'Kế toán đã duyệt yêu cầu trả hàng.';

    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE tra_hang SET trang_thai = 'Từ chối' WHERE ma_tra_hang = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if ($note !== '') {
            $stmt2 = $conn->prepare("UPDATE tra_hang SET ly_do = CONCAT(IFNULL(ly_do,''), ? ) WHERE ma_tra_hang = ?");
            $append = "\n[KT từ chối] " . $note;
            $stmt2->bind_param('si', $append, $id);
            $stmt2->execute();
        }

        logActivity('REJECT_RETURN', "Kế toán từ chối trả hàng #$id");
        $_SESSION['error'] = 'Kế toán đã từ chối yêu cầu trả hàng.';
    }

    header("Location: detail.php?id=$id");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = 'Lỗi xử lý yêu cầu: ' . $e->getMessage();
    header("Location: detail.php?id=$id");
    exit;
}

?>
