<?php
include '../config.php';
checkLogin();
requirePermission('create_return');

$sql = "SELECT t.ma_tra_hang, h.ma_hoa_don, t.ngay_tra, t.ly_do, t.trang_thai
        FROM tra_hang t
        JOIN hoa_don h ON t.ma_hoa_don = h.ma_hoa_don
        ORDER BY t.ngay_tra DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Trả Hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Danh Sách Trả Hàng</h1>

        <main>
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã TH</th>
                        <th>Mã Hóa Đơn</th>
                        <th>Ngày Trả</th>
                        <th>Lý Do</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>#" . $row['ma_tra_hang'] . "</strong></td>";
                            echo "<td>#" . $row['ma_hoa_don'] . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_tra'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ly_do'] ?? '') . "</td>";
                            echo "<td><span class='status-" . strtolower(str_replace(' ', '-', $row['trang_thai'])) . "'>" . $row['trang_thai'] . "</span></td>";
                            echo "<td>";
                            echo "<a href='detail.php?id=" . $row['ma_tra_hang'] . "' class='btn-info'>Xem</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center;'>Không có yêu cầu trả hàng</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>