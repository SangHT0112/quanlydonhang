<?php
include '../config.php';
checkLogin();
requirePermission('create_bh');

$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM phieu_ban_hang WHERE ma_phieu_ban_hang = " . intval($id);
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$pbh = $result->fetch_assoc();

// Kiểm tra đã có phiếu xuất kho chưa
$sql_check_pxk = "SELECT ma_phieu_xuat_kho FROM phieu_xuat_kho WHERE ma_phieu_ban_hang = " . intval($id);
$result_check_pxk = $conn->query($sql_check_pxk);
$has_pxk = $result_check_pxk->num_rows > 0;
$ma_pxk = $has_pxk ? $result_check_pxk->fetch_assoc()['ma_phieu_xuat_kho'] : null;

// Lấy chi tiết sản phẩm
$sql_ct = "SELECT ct.*, sp.ten_san_pham, sp.don_vi 
          FROM chi_tiet_phieu_ban_hang ct
          JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
          WHERE ct.ma_phieu_ban_hang = " . intval($id);
$result_ct = $conn->query($sql_ct);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phiếu Bán Hàng #<?php echo $id; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Chi Tiết Phiếu Bán Hàng #<?php echo $id; ?></h1>

        <main>
            <div class="detail-section">
                <h3>Thông Tin Chung</h3>
                <div class="detail-row">
                    <label>Mã Phiếu Bán Hàng:</label>
                    <p><?php echo $pbh['ma_phieu_ban_hang']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Mã Phiếu Đặt Hàng:</label>
                    <p><?php echo $pbh['ma_phieu_dat_hang']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Ngày Lập:</label>
                    <p><?php echo date('d/m/Y', strtotime($pbh['ngay_lap'])); ?></p>
                </div>
                <div class="detail-row">
                    <label>Tổng Tiền:</label>
                    <p><?php echo formatMoney($pbh['tong_tien']); ?> VNĐ</p>
                </div>
                <div class="detail-row">
                    <label>Trạng Thái:</label>
                    <p><span class='status-<?php echo strtolower(str_replace(' ', '-', $pbh['trang_thai'])); ?>'><?php echo $pbh['trang_thai']; ?></span></p>
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
                            <th style="text-align: right;">Giá Bán</th>
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
                            echo "<td style='text-align: right;'>" . formatMoney($row['gia_ban']) . " VNĐ</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($row['chiet_khau']) . " VNĐ</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($row['thanh_tien']) . " VNĐ</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <?php if (hasPermission('execute_pxk') || hasRole('kho')): ?>
                    <?php if ($has_pxk): ?>
                        <a href="../phieu_xuat_kho/detail.php?id=<?php echo $ma_pxk; ?>" class="btn-info">Xem Phiếu Xuất Kho</a>
                    <?php else: ?>
                        <a href="../phieu_xuat_kho/create.php?ma_phieu_ban_hang=<?php echo $id; ?>" class="btn-primary">Tạo Phiếu Xuất Kho</a>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="list.php" class="btn-secondary">Quay Lại Danh Sách</a>
            </div>
        </main>
    </div>
</body>
</html>