<?php
include '../config.php';
checkLogin();
requirePermission('record_payment');

$sql = "SELECT t.ma_thanh_toan, h.ma_hoa_don, t.so_tien_tra, t.ngay_tra, t.loai_thanh_toan, t.ghi_chu
        FROM thanh_toan t
        JOIN hoa_don h ON t.ma_hoa_don = h.ma_hoa_don
        ORDER BY t.ngay_tra DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Thanh Toán</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>


        <main>
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã TT</th>
                        <th>Mã Hóa Đơn</th>
                        <th>Ngày Trả</th>
                        <th style="text-align: right;">Số Tiền</th>
                        <th>Loại Thanh Toán</th>
                        <th>Ghi Chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>#" . $row['ma_thanh_toan'] . "</strong></td>";
                            echo "<td>#" . $row['ma_hoa_don'] . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_tra'])) . "</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($row['so_tien_tra']) . " VNĐ</td>";
                            echo "<td>";
                            if ($row['loai_thanh_toan'] == 'Tiền mặt') {
                                echo "<span style='color: #10b981;'>💵 " . $row['loai_thanh_toan'] . "</span>";
                            } elseif ($row['loai_thanh_toan'] == 'Chuyển khoản') {
                                echo "<span style='color: #3b82f6;'>🏦 " . $row['loai_thanh_toan'] . "</span>";
                            } else {
                                echo "<span style='color: #f59e0b;'>📝 " . $row['loai_thanh_toan'] . "</span>";
                            }
                            echo "</td>";
                            echo "<td>" . htmlspecialchars($row['ghi_chu'] ?? '') . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center;'>Không có ghi nhận thanh toán</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>