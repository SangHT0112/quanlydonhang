<?php
include '../config.php';
checkLogin();
requirePermission('execute_pxk');

$id = $_GET['id'] ?? 0;

// Prepared statement cho an toàn
$stmt = $conn->prepare("
    SELECT pxk.*, hd.ma_phieu_dat_hang, hd.ngay_xuat_hd as ngay_lap, hd.tong_tien
    FROM phieu_xuat_kho pxk
    JOIN hoa_don hd ON pxk.ma_hoa_don = hd.ma_hoa_don 
    WHERE pxk.ma_phieu_xuat_kho = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$pxk = $result->fetch_assoc();

// Lấy chi tiết sản phẩm
$stmt_ct = $conn->prepare("
    SELECT ct.*, sp.ten_san_pham, sp.don_vi 
    FROM chi_tiet_phieu_xuat_kho ct
    JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
    WHERE ct.ma_phieu_xuat_kho = ?
");
$stmt_ct->bind_param("i", $id);
$stmt_ct->execute();
$result_ct = $stmt_ct->get_result();

// Lấy thông tin khách hàng
$stmt_kh = $conn->prepare("
    SELECT pdh.ma_khach_hang, kh.ten_khach_hang, kh.dien_thoai, kh.dia_chi
    FROM phieu_dat_hang pdh
    JOIN khach_hang kh ON pdh.ma_khach_hang = kh.ma_khach_hang
    WHERE pdh.ma_phieu_dat_hang = ?
");
$stmt_kh->bind_param("i", $pxk['ma_phieu_dat_hang']);
$stmt_kh->execute();
$result_kh = $stmt_kh->get_result();
$khach_hang = $result_kh->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Xuất Kho #<?php echo $id; ?></title>
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none !important; }
            @page { margin: 1cm; }
        }
        body {
            font-family: 'Times New Roman', serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        .print-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .print-header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .print-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-section {
            border: 1px solid #ddd;
            padding: 15px;
        }
        .info-section h3 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-row {
            margin: 8px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .print-footer {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        .signature {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #667eea; color: white; border: none; cursor: pointer; border-radius: 5px;">
            🖨️ In Phiếu
        </button>
        <a href="detail.php?id=<?php echo $id; ?>" style="padding: 10px 20px; background: #6b7280; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-left: 10px;">
            ← Quay Lại
        </a>
    </div>

    <div class="print-header">
        <h1>PHIẾU XUẤT KHO</h1>
        <p>Số: PXK-<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></p>
    </div>

    <div class="print-info">
        <div class="info-section">
            <h3>Thông Tin Phiếu</h3>
            <div class="info-row">
                <span class="info-label">Mã Phiếu:</span>
                <span>#<?php echo $pxk['ma_phieu_xuat_kho']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Mã Hóa Đơn:</span>  <!-- ← SỬA: Thay vì Mã PBH -->
                <span>#<?php echo $pxk['ma_hoa_don']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Ngày Xuất:</span>
                <span><?php echo date('d/m/Y', strtotime($pxk['ngay_xuat'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Người Xuất:</span>
                <span><?php echo htmlspecialchars($pxk['nguoi_xuat'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Trạng Thái:</span>
                <span><?php echo $pxk['trang_thai']; ?></span>
            </div>
        </div>

        <div class="info-section">
            <h3>Thông Tin Khách Hàng</h3>
            <div class="info-row">
                <span class="info-label">Tên KH:</span>
                <span><?php echo htmlspecialchars($khach_hang['ten_khach_hang'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Điện Thoại:</span>
                <span><?php echo htmlspecialchars($khach_hang['dien_thoai'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Địa Chỉ:</span>
                <span><?php echo htmlspecialchars($khach_hang['dia_chi'] ?? 'N/A'); ?></span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Sản Phẩm</th>
                <th>Đơn Vị</th>
                <th class="text-right">Số Lượng</th>
                <th class="text-right">Thành Tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stt = 1;
            $total = 0;
            $result_ct->data_seek(0);  // ← SỬA: Reset pointer nếu cần (từ fetch trước)
            while ($row = $result_ct->fetch_assoc()):
                $total += $row['thanh_tien'];
            ?>
                <tr>
                    <td><?php echo $stt++; ?></td>
                    <td><?php echo htmlspecialchars($row['ten_san_pham']); ?></td>
                    <td><?php echo htmlspecialchars($row['don_vi'] ?? 'Cái'); ?></td>
                    <td class="text-right"><?php echo $row['so_luong_xuat']; ?></td>
                    <td class="text-right"><?php echo formatMoney($row['thanh_tien']); ?> VNĐ</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">TỔNG CỘNG:</td>
                <td class="text-right"><?php echo formatMoney($total); ?> VNĐ</td>
            </tr>
        </tfoot>
    </table>

    <div class="print-footer">
        <div class="signature">
            <div class="signature-line">
                <strong>Người Xuất Kho</strong>
            </div>
            <p style="margin-top: 40px;">
                <?php echo htmlspecialchars($pxk['nguoi_xuat'] ?? '........................'); ?>
            </p>
        </div>
        <div class="signature">
            <div class="signature-line">
                <strong>Người Nhận</strong>
            </div>
            <p style="margin-top: 40px;">
                ........................
            </p>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
        <p>In ngày: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
</body>
</html>