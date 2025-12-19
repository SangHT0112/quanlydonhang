<?php
// File: /hoa_don/create.php
// Quy trình: Kế toán tạo hóa đơn từ PO đã duyệt, kiểm tra tồn kho trước

include '../config.php';
checkLogin();
requirePermission('create_invoice'); // Permission cho kế toán tạo hóa đơn

$ma_po = $_GET['ma_po'] ?? ''; // Lấy từ query khi click từ list PO

if (!$ma_po) {
    header('Location: /phieu_dat_hang/list.php');
    exit;
}

// Lấy dữ liệu PO
$sql_po = "SELECT p.*, k.ten_khach_hang FROM phieu_dat_hang p JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang WHERE p.ma_phieu_dat_hang = ? AND p.trang_thai = 'Đã duyệt'";
$stmt_po = $conn->prepare($sql_po);
$stmt_po->bind_param("i", $ma_po);
$stmt_po->execute();
$po = $stmt_po->get_result()->fetch_assoc();

if (!$po) {
    $_SESSION['error'] = "PO không tồn tại hoặc chưa duyệt!";
    header('Location: list.php');
    exit;
}

// Lấy chi tiết PO
$sql_chi_tiet = "SELECT ct.*, sp.ten_san_pham FROM chi_tiet_phieu_dat_hang ct JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham WHERE ma_phieu_dat_hang = ?";
$stmt_chi_tiet = $conn->prepare($sql_chi_tiet);
$stmt_chi_tiet->bind_param("i", $ma_po);
$stmt_chi_tiet->execute();
$chi_tiet = $stmt_chi_tiet->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kiểm tra tồn kho trước khi tạo
    $has_error = false;
    $errors = [];
    foreach ($chi_tiet as $item) {
        $sql_ton = "SELECT ton_kho FROM san_pham WHERE ma_san_pham = ?";
        $stmt_ton = $conn->prepare($sql_ton);
        $stmt_ton->bind_param("i", $item['ma_san_pham']);
        $stmt_ton->execute();
        $ton = $stmt_ton->get_result()->fetch_assoc()['ton_kho'] ?? 0;

        if ($ton < $item['so_luong']) {
            $has_error = true;
            $errors[] = "Sản phẩm {$item['ten_san_pham']}: Tồn kho chỉ {$ton}, cần {$item['so_luong']}!";
        }
    }

    if ($has_error) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: create.php?ma_po=' . $ma_po);
        exit;
    }

    // Bắt đầu transaction
    $conn->begin_transaction();

    try {
        // Tạo hóa đơn (giả sử hóa đơn điện tử, lưu bản nháp)
        $ngay_lap = date('Y-m-d H:i:s');
        $ma_hoa_don = 'HD' . time(); // Tự sinh mã
        $trang_thai_hd = 'Bản nháp'; // Chờ phát hành

        $sql_hd = "INSERT INTO hoa_don (ma_hoa_don, ma_phieu_dat_hang, ma_khach_hang, ngay_lap, tong_tien, trang_thai) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_hd = $conn->prepare($sql_hd);
        $stmt_hd->bind_param("siisss", $ma_hoa_don, $ma_po, $po['ma_khach_hang'], $ngay_lap, $po['tong_tien'], $trang_thai_hd);
        $stmt_hd->execute();

        // Thêm chi tiết hóa đơn (tương tự PO)
        foreach ($chi_tiet as $item) {
            $sql_ct_hd = "INSERT INTO chi_tiet_hoa_don (ma_hoa_don, ma_san_pham, so_luong, don_gia, thanh_tien) VALUES (?, ?, ?, ?, ?)";
            $stmt_ct_hd = $conn->prepare($sql_ct_hd);
            $stmt_ct_hd->bind_param("siidd", $ma_hoa_don, $item['ma_san_pham'], $item['so_luong'], $item['don_gia'], $item['thanh_tien']);
            $stmt_ct_hd->execute();
        }

        // Trừ tồn kho
        foreach ($chi_tiet as $item) {
            $sql_tru = "UPDATE san_pham SET ton_kho = ton_kho - ? WHERE ma_san_pham = ?";
            $stmt_tru = $conn->prepare($sql_tru);
            $stmt_tru->bind_param("ii", $item['so_luong'], $item['ma_san_pham']);
            $stmt_tru->execute();
        }

        $conn->commit();

        // Gửi notify cho kho (hóa đơn mới, cần xuất kho)
        $notify_data = [
            'ma_hd' => $ma_hoa_don,
            'message' => "Hóa đơn mới từ PO #{$ma_po}, cần xuất kho!",
            'ma_po' => $ma_po
        ];
        file_put_contents('/tmp/hd_notify.json', json_encode($notify_data)); // Queue cho socket

        $_SESSION['success'] = "Tạo hóa đơn thành công! Mã: {$ma_hoa_don}. Đã trừ tồn kho.";
        header('Location: list.php');
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Lỗi tạo hóa đơn: " . $e->getMessage();
        header('Location: create.php?ma_po=' . $ma_po);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Hóa Đơn Từ PO #<?php echo $ma_po; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Tạo Hóa Đơn (Kiểm Tra Tồn Kho) - PO #<?php echo $ma_po; ?></h1>
        <?php if (isset($_SESSION['error'])) { echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>"; unset($_SESSION['error']); } ?>
        
        <div class="po-info">
            <p><strong>Khách Hàng:</strong> <?php echo htmlspecialchars($po['ten_khach_hang']); ?></p>
            <p><strong>Tổng Tiền:</strong> <?php echo formatMoney($po['tong_tien']); ?> VNĐ</p>
        </div>

        <h3>Chi Tiết Sản Phẩm (Từ PO)</h3>
        <table class="table">
            <thead><tr><th>Sản Phẩm</th><th>Số Lượng</th><th>Đơn Giá</th><th>Thành Tiền</th><th>Tồn Kho Hiện Tại</th></tr></thead>
            <tbody>
                <?php foreach ($chi_tiet as $item): 
                    // Lấy tồn kho hiện tại
                    $sql_ton = "SELECT ton_kho FROM san_pham WHERE ma_san_pham = ?";
                    $stmt_ton = $conn->prepare($sql_ton);
                    $stmt_ton->bind_param("i", $item['ma_san_pham']);
                    $stmt_ton->execute();
                    $ton = $stmt_ton->get_result()->fetch_assoc()['ton_kho'] ?? 0;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['ten_san_pham']); ?></td>
                        <td><?php echo $item['so_luong']; ?></td>
                        <td><?php echo formatMoney($item['don_gia']); ?></td>
                        <td><?php echo formatMoney($item['thanh_tien']); ?></td>
                        <td><?php echo $ton; ?> (<?php echo $ton >= $item['so_luong'] ? 'OK' : '<span class="error">Không đủ!</span>'; ?>)</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form method="POST">
            <button type="submit" class="btn-primary" onclick="return confirm('Xác nhận tạo hóa đơn và trừ tồn kho?')">Tạo Hóa Đơn Bản Nháp</button>
            <a href="list.php" class="btn-secondary">Quay Lại</a>
        </form>
    </div>
</body>
</html>