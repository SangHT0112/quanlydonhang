<?php
include '../config.php';
checkLogin();
requirePermission('create_bh');

$search = '';

$sql = "SELECT p.ma_phieu_ban_hang, p.ma_phieu_dat_hang, p.ngay_lap, p.tong_tien, p.trang_thai 
        FROM phieu_ban_hang p
        ORDER BY p.ngay_lap DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Phiếu Bán Hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>

        <main>
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã PBH</th>
                        <th>Mã PO</th>
                        <th>Ngày Lập</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>#" . $row['ma_phieu_ban_hang'] . "</strong></td>";
                            echo "<td>#" . $row['ma_phieu_dat_hang'] . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_lap'])) . "</td>";
                            echo "<td>" . formatMoney($row['tong_tien']) . " VNĐ</td>";
                            echo "<td><span class='status-" . strtolower(str_replace(' ', '-', $row['trang_thai'])) . "'>" . $row['trang_thai'] . "</span></td>";
                            echo "<td>";
                            echo "<a href='detail.php?id=" . $row['ma_phieu_ban_hang'] . "' class='btn-info'>Xem</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center;'>Không có phiếu bán hàng</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>