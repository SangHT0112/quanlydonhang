<?php
include '../config.php';
checkLogin();
requirePermission('execute_pxk');

$sql = "SELECT tk.ma_ton_kho, sp.ten_san_pham, sp.don_vi, tk.so_luong_ton, tk.ngay_cap_nhat 
        FROM ton_kho tk
        JOIN san_pham sp ON tk.ma_san_pham = sp.ma_san_pham
        ORDER BY sp.ten_san_pham";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tồn Kho</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
       <h1>Danh sách tồn kho</h1> 

        <main>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản Phẩm</th>
                        <th>Đơn Vị</th>
                        <th style="text-align: right;">Số Lượng Tồn</th>
                        <th>Ngày Cập Nhật</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>" . htmlspecialchars($row['ten_san_pham']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row['don_vi']) . "</td>";
                            echo "<td style='text-align: right;'>";
                            if ($row['so_luong_ton'] <= 5) {
                                echo "<span style='color: #ef4444; font-weight: bold;'>" . $row['so_luong_ton'] . "</span>";
                            } else {
                                echo $row['so_luong_ton'];
                            }
                            echo "</td>";
                            echo "<td>" . date('d/m/Y H:i', strtotime($row['ngay_cap_nhat'])) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align: center;'>Không có dữ liệu tồn kho</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <p style="margin-top: 15px; color: #666; font-size: 13px;">
                <span style="color: #ef4444;">■</span> Màu đỏ chỉ ra sản phẩm có số lượng tồn ≤ 5
            </p>
        </main>
    </div>
</body>
</html>