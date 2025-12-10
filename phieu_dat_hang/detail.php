<?php
include '../config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM phieu_dat_hang WHERE ma_phieu_dat_hang = " . intval($id);
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$po = $result->fetch_assoc();

// Lấy chi tiết sản phẩm
$sql_ct = "SELECT ct.*, sp.ten_san_pham, sp.don_vi 
          FROM chi_tiet_phieu_dat_hang ct
          JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
          WHERE ct.ma_phieu_dat_hang = " . intval($id);
$result_ct = $conn->query($sql_ct);

// Lấy thông tin khách hàng
$sql_kh = "SELECT * FROM khach_hang WHERE ma_khach_hang = " . intval($po['ma_khach_hang']);
$result_kh = $conn->query($sql_kh);
$khach_hang = $result_kh->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phiếu Đặt Hàng #<?php echo $id; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Chi Tiết Phiếu Đặt Hàng #<?php echo $id; ?></h1>

        <main>
            <div class="detail-section">
                <h3>Thông Tin Chung</h3>
                <div class="detail-row">
                    <label>Mã Phiếu Đặt Hàng:</label>
                    <p><?php echo $po['ma_phieu_dat_hang']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Khách Hàng:</label>
                    <p><?php echo htmlspecialchars($khach_hang['ten_khach_hang']); ?></p>
                </div>
                <div class="detail-row">
                    <label>Điện Thoại:</label>
                    <p><?php echo htmlspecialchars($khach_hang['dien_thoai'] ?? 'N/A'); ?></p>
                </div>
                <div class="detail-row">
                    <label>Địa Chỉ:</label>
                    <p><?php echo htmlspecialchars($khach_hang['dia_chi'] ?? 'N/A'); ?></p>
                </div>
                <div class="detail-row">
                    <label>Ngày Đặt:</label>
                    <p><?php echo date('d/m/Y', strtotime($po['ngay_dat'])); ?></p>
                </div>
                <div class="detail-row">
                    <label>Trạng Thái:</label>
                    <p><span class='status-<?php echo strtolower(str_replace(' ', '-', $po['trang_thai'])); ?>'><?php echo $po['trang_thai']; ?></span></p>
                </div>
                <?php if ($po['ghi_chu']): ?>
                <div class="detail-row">
                    <label>Ghi Chú:</label>
                    <p><?php echo htmlspecialchars($po['ghi_chu']); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <div class="detail-section">
                <h3>Chi Tiết Sản Phẩm</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản Phẩm</th>
                            <th>Đơn Vị</th>
                            <th style="text-align: right;">Số Lượng</th>
                            <th style="text-align: right;">Giá Đặt</th>
                            <th style="text-align: right;">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        while ($row = $result_ct->fetch_assoc()) {
                            $thanh_tien = $row['so_luong'] * $row['gia_dat'];
                            $total += $thanh_tien;
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['ten_san_pham']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['don_vi']) . "</td>";
                            echo "<td style='text-align: right;'>" . $row['so_luong'] . "</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($row['gia_dat']) . " VNĐ</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($thanh_tien) . " VNĐ</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; border-top: 2px solid #e5e7eb;">
                            <td colspan="4" style="text-align: right;">Tổng Cộng:</td>
                            <td style="text-align: right; padding: 15px;"><?php echo formatMoney($total); ?> VNĐ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="form-actions">
                <?php if ($po['trang_thai'] == 'Chờ duyệt'): ?>
                    <a href="edit.php?id=<?php echo $id; ?>" class="btn-warning">Sửa</a>
                    <a href="delete.php?id=<?php echo $id; ?>" class="btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</a>
                    <a href="approve.php?id=<?php echo $id; ?>" class="btn-primary" onclick="return confirm('Bạn chắc chắn muốn duyệt đơn hàng này?')">Duyệt Đơn</a>
                <?php endif; ?>
                <a href="list.php" class="btn-secondary">Quay Lại Danh Sách</a>
            </div>
        </main>
    </div>
</body>
</html>