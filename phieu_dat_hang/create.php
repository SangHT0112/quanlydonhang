<?php
// create.php - HỖ TRỢ AJAX + TOAST + CHAT MESSAGE (SỬA LỖI TRANSACTION)
include '../config.php';

// Kiểm tra AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');
    if (!hasPermission('create_po')) {
        echo json_encode(['success' => false, 'error' => 'Bạn không có quyền tạo PO']);
        exit;
    }
}

requirePermission('create_po');
checkLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        exit;
    }
    die('Invalid request');
}

try {
    $ma_kh     = intval($_POST['ma_khach_hang']);
    $ngay_dat  = $_POST['ngay_dat'];
    $ghi_chu   = $_POST['ghi_chu'] ?? '';
    $tong_tien = floatval($_POST['tong_tien']);
    $user_id   = intval($_SESSION['user_id']);

    if (!$ma_kh || !$ngay_dat) {
        throw new Exception('Thiếu thông tin bắt buộc');
    }

    $conn->begin_transaction();

    // 1️⃣ INSERT PHIẾU ĐẶT HÀNG
    $sql_po = "
        INSERT INTO phieu_dat_hang
        (ma_khach_hang, ngay_dat, tong_tien, ghi_chu, trang_thai, created_by)
        VALUES (?, ?, ?, ?, 'Chờ duyệt', ?)
    ";
    $stmt_po = $conn->prepare($sql_po);
    $stmt_po->bind_param("isdsi", $ma_kh, $ngay_dat, $tong_tien, $ghi_chu, $user_id);
    $stmt_po->execute();
    $ma_po = $conn->insert_id;

    // 2️⃣ INSERT CHI TIẾT SẢN PHẨM
    $i = 1;
    while (isset($_POST["ma_san_pham_$i"])) {
        $ma_sp = intval($_POST["ma_san_pham_$i"]);
        $sl    = intval($_POST["so_luong_$i"]);
        $gia   = floatval($_POST["gia_dat_$i"]);

        if ($ma_sp && $sl > 0 && $gia >= 0) {
            $stmt_ct = $conn->prepare("
                INSERT INTO chi_tiet_phieu_dat_hang
                (ma_phieu_dat_hang, ma_san_pham, so_luong, gia_dat)
                VALUES (?, ?, ?, ?)
            ");
            $stmt_ct->bind_param("iiid", $ma_po, $ma_sp, $sl, $gia);
            $stmt_ct->execute();
        }
        $i++;
    }

    $conn->commit();  // Commit DB trước khi emit socket

    logActivity('CREATE_PO', "Tạo phiếu đặt hàng #$ma_po");


    // ==============================
    // REALTIME: EMIT CẢ TOAST + CHAT
    // ==============================
    // 1. Toast: po_created
    $payload_toast = [
        'event' => 'po_created',
        'room'  => 'ketoan',
        'data'  => [
            'ma_phieu' => $ma_po,
            'message'  => "Có đơn hàng mới #$ma_po từ phòng kinh doanh"
        ]
    ];
    emitSocket($payload_toast);

    // Trả response
    if ($isAjax) {
        $sql_detail = "
            SELECT 
                p.ma_phieu_dat_hang,
                k.ten_khach_hang,
                p.ngay_dat,
                p.tong_tien,
                p.trang_thai
            FROM phieu_dat_hang p
            JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang
            WHERE p.ma_phieu_dat_hang = ?
        ";
        $stmt_detail = $conn->prepare($sql_detail);
        $stmt_detail->bind_param("i", $ma_po);
        $stmt_detail->execute();
        $poData = $stmt_detail->get_result()->fetch_assoc();

        echo json_encode([
            'success' => true,
            'data' => $poData
        ]);
        exit;
    } else {
        header("Location: detail.php?id=$ma_po");
        exit;
    }

} catch (Exception $e) {
    $conn->rollback();  // ← SỬA: Bỏ if check, gọi trực tiếp (an toàn cho mysqli)
    
    if ($isAjax) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    } else {
        $error = $e->getMessage();
    }
}

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