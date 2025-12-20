<?php
include '../config.php';
checkLogin();
requirePermission('view_invoice');

$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM hoa_don WHERE ma_hoa_don = " . intval($id);
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$hoa_don = $result->fetch_assoc();

// Lấy chi tiết hóa đơn
$sql_ct = "SELECT ct.*, sp.ten_san_pham, sp.don_vi 
          FROM chi_tiet_hoa_don ct
          JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
          WHERE ct.ma_hoa_don = " . intval($id);
$result_ct = $conn->query($sql_ct);

// Lấy thông tin thanh toán
$sql_tt = "SELECT * FROM thanh_toan WHERE ma_hoa_don = " . intval($id) . " ORDER BY ngay_tra DESC";
$result_tt = $conn->query($sql_tt);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Hóa Đơn #<?php echo $id; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Chi Tiết Hóa Đơn #<?php echo $id; ?></h1>

        <main>
            <div class="detail-section">
                <h3>Thông Tin Hóa Đơn</h3>
                <div class="detail-row">
                    <label>Mã Hóa Đơn:</label>
                    <p><?php echo $hoa_don['ma_hoa_don']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Ngày Xuất:</label>
                    <p><?php echo date('d/m/Y', strtotime($hoa_don['ngay_xuat_hd'])); ?></p>
                </div>
                <div class="detail-row">
                    <label>Tổng Tiền:</label>
                    <p style="font-weight: bold; color: #10b981; font-size: 16px;"><?php echo formatMoney($hoa_don['tong_tien']); ?> VNĐ</p>
                </div>
                <div class="detail-row">
                    <label>Khuyến Mại:</label>
                    <p><?php echo formatMoney($hoa_don['khuyen_mai_tong']); ?> VNĐ</p>
                </div>
                <div class="detail-row">
                    <label>Trạng Thái:</label>
                    <p><span class='status-<?php echo strtolower(str_replace(' ', '-', $hoa_don['trang_thai'])); ?>'><?php echo $hoa_don['trang_thai']; ?></span></p>
                </div>
            </div>

            <div class="detail-section">
                <h3>Chi Tiết Sản Phẩm</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản Phẩm</th>
                            <th>Đơn Vị</th>
                            <th style="text-align: right;">Số Lượng</th>
                            <th style="text-align: right;">Đơn Giá</th>
                            <th style="text-align: right;">Chiết Khấu</th>
                            <th style="text-align: right;">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result_ct->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['ten_san_pham']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['don_vi']) . "</td>";
                            echo "<td style='text-align: right;'>" . $row['so_luong'] . "</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($row['don_gia']) . " VNĐ</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($row['chiet_khau']) . " VNĐ</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($row['thanh_tien']) . " VNĐ</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="detail-section">
                <h3>Lịch Sử Thanh Toán</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ngày Trả</th>
                            <th style="text-align: right;">Số Tiền</th>
                            <th>Loại Thanh Toán</th>
                            <th>Ghi Chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tong_da_tra = 0;
                        if ($result_tt->num_rows > 0) {
                            while ($row = $result_tt->fetch_assoc()) {
                                $tong_da_tra += $row['so_tien_tra'];
                                echo "<tr>";
                                echo "<td>" . date('d/m/Y', strtotime($row['ngay_tra'])) . "</td>";
                                echo "<td style='text-align: right;'>" . formatMoney($row['so_tien_tra']) . " VNĐ</td>";
                                echo "<td>" . $row['loai_thanh_toan'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['ghi_chu'] ?? '') . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center;'>Chưa có thanh toán</td></tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; border-top: 2px solid #e5e7eb;">
                            <td colspan="1" style="text-align: right;">Tổng Đã Trả:</td>
                            <td style="text-align: right; padding: 15px;" colspan="3"><?php echo formatMoney($tong_da_tra); ?> VNĐ</td>
                        </tr>
                        <tr style="font-weight: bold;">
                            <td colspan="1" style="text-align: right;">Còn Nợ:</td>
                            <td style="text-align: right; padding: 15px; color: #ef4444;" colspan="3"><?php echo formatMoney($hoa_don['tong_tien'] - $tong_da_tra); ?> VNĐ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="form-actions">
                <a href="list.php" class="btn-secondary">Quay Lại Danh Sách</a>
            </div>
        </main>
    </div>
</body>
</html>