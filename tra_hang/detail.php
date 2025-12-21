<?php
include '../config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM tra_hang WHERE ma_tra_hang = " . intval($id);
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$tra_hang = $result->fetch_assoc();

// Lấy chi tiết sản phẩm trả
$sql_ct = "SELECT ct.*, sp.ten_san_pham, sp.don_vi 
          FROM chi_tiet_tra_hang ct
          JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
          WHERE ct.ma_tra_hang = " . intval($id);
$result_ct = $conn->query($sql_ct);

// Lấy thông tin hóa đơn
$sql_hd = "SELECT * FROM hoa_don WHERE ma_hoa_don = " . intval($tra_hang['ma_hoa_don']);
$result_hd = $conn->query($sql_hd);
$hoa_don = $result_hd->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Trả Hàng #<?php echo $id; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Chi Tiết Trả Hàng #<?php echo $id; ?></h1>

        <main>
            <?php
            if (!empty($_SESSION['success'])) {
                echo "<div class='alert alert-success'>{$_SESSION['success']}</div>";
                unset($_SESSION['success']);
            }
            if (!empty($_SESSION['error'])) {
                echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
                unset($_SESSION['error']);
            }
            ?>
            <div class="detail-section">
                <h3>Thông Tin Trả Hàng</h3>
                <div class="detail-row">
                    <label>Mã Trả Hàng:</label>
                    <p><?php echo $tra_hang['ma_tra_hang']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Mã Hóa Đơn:</label>
                    <p>#<?php echo $tra_hang['ma_hoa_don']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Ngày Trả:</label>
                    <p><?php echo date('d/m/Y', strtotime($tra_hang['ngay_tra'])); ?></p>
                </div>
                <div class="detail-row">
                    <label>Lý Do:</label>
                    <p><?php echo htmlspecialchars($tra_hang['ly_do'] ?? ''); ?></p>
                </div>
                <div class="detail-row">
                    <label>Trạng Thái:</label>
                    <p><span class='status-<?php echo strtolower(str_replace(' ', '-', $tra_hang['trang_thai'])); ?>'><?php echo $tra_hang['trang_thai']; ?></span></p>
                </div>
            </div>

            <div class="detail-section">
                <h3>Chi Tiết Sản Phẩm Trả</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản Phẩm</th>
                            <th>Đơn Vị</th>
                            <th style="text-align: right;">Số Lượng Trả</th>
                            <th style="text-align: right;">Giá Trả</th>
                            <th style="text-align: right;">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tong_tra = 0;
                        while ($row = $result_ct->fetch_assoc()) {
                            $thanh_tien = $row['so_luong_tra'] * $row['gia_tra'];
                            $tong_tra += $thanh_tien;
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['ten_san_pham']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['don_vi']) . "</td>";
                            echo "<td style='text-align: right;'>" . $row['so_luong_tra'] . "</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($row['gia_tra']) . " VNĐ</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($thanh_tien) . " VNĐ</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; border-top: 2px solid #e5e7eb;">
                            <td colspan="4" style="text-align: right;">Tổng Trả:</td>
                            <td style="text-align: right; padding: 15px;"><?php echo formatMoney($tong_tra); ?> VNĐ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="form-actions">
                <?php if ($tra_hang['trang_thai'] === 'Yêu cầu' && (hasPermission('approve_return') || hasPermission('approve_return') || hasRole('ketoan'))): ?>
                    <form method="POST" action="approve.php" style="display:inline-block; margin-right:8px;">
                        <input type="hidden" name="id" value="<?php echo $tra_hang['ma_tra_hang']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <input type="text" name="note" placeholder="Ghi chú (tùy chọn)">
                        <button class="btn-primary" type="submit" onclick="return confirm('Duyệt yêu cầu trả hàng?')">Duyệt (Kế toán)</button>
                    </form>
                    <form method="POST" action="approve.php" style="display:inline-block;">
                        <input type="hidden" name="id" value="<?php echo $tra_hang['ma_tra_hang']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <input type="text" name="note" placeholder="Lý do từ chối">
                        <button class="btn-danger" type="submit" onclick="return confirm('Từ chối yêu cầu trả hàng?')">Từ chối</button>
                    </form>
                <?php endif; ?>

                <?php if ($tra_hang['trang_thai'] === 'Kế toán duyệt' && (hasPermission('create_picklist') || hasPermission('execute_pxk') || hasRole('kho'))): ?>
                    <form method="POST" action="process.php" style="display:inline-block; margin-left:8px;">
                        <input type="hidden" name="id" value="<?php echo $tra_hang['ma_tra_hang']; ?>">
                        <button class="btn-primary" type="submit" onclick="return confirm('Xác nhận kho nhận hàng và cập nhật tồn kho?')">Hoàn thành (Kho)</button>
                    </form>
                <?php endif; ?>

                <a href="list.php" class="btn-secondary">Quay Lại Danh Sách</a>
            </div>
        </main>
    </div>
</body>
</html>