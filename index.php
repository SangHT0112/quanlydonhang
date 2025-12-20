<?php
include 'config.php';
checkLogin();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Lý Đơn Hàng</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    
           <?= include 'header.php'; ?>
    <div class="container">

        <main>
            <div class="dashboard">
                <h2>Bảng Điều Khiển</h2>
                
                <div class="stats-grid">
                    <?php
                    // Tổng khách hàng
                    $result = $conn->query("SELECT COUNT(*) as count FROM khach_hang WHERE trang_thai = 'Hoạt động'");
                    $row = $result->fetch_assoc();
                    $total_customers = $row['count'];

                    // Tổng sản phẩm
                    $result = $conn->query("SELECT COUNT(*) as count FROM san_pham");
                    $row = $result->fetch_assoc();
                    $total_products = $row['count'];

                    // Tổng đơn hàng chưa duyệt
                    $result = $conn->query("SELECT COUNT(*) as count FROM phieu_dat_hang WHERE trang_thai = 'Chờ duyệt'");
                    $row = $result->fetch_assoc();
                    $pending_po = $row['count'];

                    // Tổng hóa đơn chưa thanh toán
                    $result = $conn->query("SELECT COUNT(*) as count FROM hoa_don WHERE trang_thai != 'Đã thanh toán'");
                    $row = $result->fetch_assoc();
                    $unpaid_invoices = $row['count'];

                    // Doanh thu trong tháng
                    $result = $conn->query("SELECT SUM(tong_tien) as total FROM hoa_don WHERE MONTH(ngay_xuat_hd) = MONTH(NOW()) AND YEAR(ngay_xuat_hd) = YEAR(NOW())");
                    $row = $result->fetch_assoc();
                    $monthly_revenue = $row['total'] ?? 0;
                    ?>
                    
                    <div class="stat-card">
                        <h3>Khách Hàng</h3>
                        <p class="stat-number"><?php echo $total_customers; ?></p>
                        <a href="khach_hang/list.php">Xem Chi Tiết →</a>
                    </div>

                    <div class="stat-card">
                        <h3>Sản Phẩm</h3>
                        <p class="stat-number"><?php echo $total_products; ?></p>
                        <a href="san_pham/list.php">Xem Chi Tiết →</a>
                    </div>

                    <div class="stat-card warning">
                        <h3>Đơn Chờ Duyệt</h3>
                        <p class="stat-number"><?php echo $pending_po; ?></p>
                        <a href="phieu_dat_hang/list.php">Xem Chi Tiết →</a>
                    </div>

                    <div class="stat-card alert">
                        <h3>Hóa Đơn Chưa Thanh Toán</h3>
                        <p class="stat-number"><?php echo $unpaid_invoices; ?></p>
                        <a href="hoa_don/list.php">Xem Chi Tiết →</a>
                    </div>

                    <div class="stat-card success">
                        <h3>Doanh Thu Tháng</h3>
                        <p class="stat-number"><?php echo formatMoney($monthly_revenue); ?></p>
                        <a href="hoa_don/list.php">Xem Chi Tiết →</a>
                    </div>

                    <div class="stat-card">
                        <h3>Hành Động Nhanh</h3>
                        <p style="margin: 10px 0;">
                            <a href="phieu_dat_hang/create.php" class="btn-primary">Tạo Đơn Hàng Mới</a>
                        </p>
                        <p>
                            <a href="khach_hang/create.php" class="btn-primary">Thêm Khách Hàng</a>
                        </p>
                    </div>
                </div>

                <div class="recent-section">
                    <h3>Đơn Hàng Gần Đây</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã PO</th>
                                <th>Khách Hàng</th>
                                <th>Ngày Đặt</th>
                                <th>Tổng Tiền</th>
                                <th>Trạng Thái</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.ma_phieu_dat_hang, k.ten_khach_hang, p.ngay_dat, p.tong_tien, p.trang_thai 
                                    FROM phieu_dat_hang p 
                                    JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang 
                                    ORDER BY p.ngay_dat DESC LIMIT 5";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>#" . $row['ma_phieu_dat_hang'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['ten_khach_hang']) . "</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['ngay_dat'])) . "</td>";
                                    echo "<td>" . formatMoney($row['tong_tien']) . " VNĐ</td>";
                                    echo "<td><span class='status-" . strtolower(str_replace(' ', '-', $row['trang_thai'])) . "'>" . $row['trang_thai'] . "</span></td>";
                                    echo "<td><a href='phieu_dat_hang/detail.php?id=" . $row['ma_phieu_dat_hang'] . "'>Xem</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align: center;'>Chưa có đơn hàng</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Hệ Thống Quản Lý Đơn Hàng. All rights reserved.</p>
        </footer>
    </div>


    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

    <script>
    // Kết nối đến NodeJS server
    const socket = io("http://localhost:3001");

    // Khi kết nối thành công
    socket.on("connect", () => {
        console.log("Đã kết nối đến socket server với ID:", socket.id);
    });

    // Nhận thông báo từ server
    socket.on("receiveMessage", (msg) => {
        console.log("Tin nhắn realtime:", msg);
    });
    </script>

</body>
</html>
