<?php
include '../config.php';
checkLogin();

$search = $_GET['search'] ?? '';
$trang_thai = $_GET['trang_thai'] ?? '';

$sql = "SELECT * FROM hoa_don WHERE 1=1";

if ($trang_thai) {
    $trang_thai = $conn->real_escape_string($trang_thai);
    $sql .= " AND trang_thai = '$trang_thai'";
}

$sql .= " ORDER BY ngay_xuat_hd DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Hóa Đơn</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">

        
        <?php include '../header.php'; ?>
        <h1>Danh Sách Hóa Đơn</h1>

        <main>
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <select name="trang_thai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Chưa thanh toán" <?php if ($trang_thai == 'Chưa thanh toán') echo 'selected'; ?>>Chưa thanh toán</option>
                        <option value="Đã thanh toán" <?php if ($trang_thai == 'Đã thanh toán') echo 'selected'; ?>>Đã thanh toán</option>
                        <option value="Công nợ" <?php if ($trang_thai == 'Công nợ') echo 'selected'; ?>>Công nợ</option>
                    </select>

                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Mã Hóa Đơn</th>
                        <th>Ngày Xuất</th>
                        <th>Tổng Tiền</th>
                        <th>Khuyến Mại</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>#" . $row['ma_hoa_don'] . "</strong></td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_xuat_hd'])) . "</td>";
                            echo "<td>" . formatMoney($row['tong_tien']) . " VNĐ</td>";
                            echo "<td>" . formatMoney($row['khuyen_mai_tong']) . " VNĐ</td>";
                            echo "<td><span class='status-" . strtolower(str_replace(' ', '-', $row['trang_thai'])) . "'>" . $row['trang_thai'] . "</span></td>";
                            echo "<td>";
                            echo "<a href='detail.php?id=" . $row['ma_hoa_don'] . "' class='btn-info'>Xem</a>";

                        
                            if (hasPermission('create_pxk')) {
                                echo "<a href='../phieu_xuat_kho/create.php?ma_hd=" . $row['ma_hoa_don'] . "'
                                    class='btn-primary'>
                                    Lập phiếu xuất kho
                                </a>";
                            }


                            if (hasPermission('create_return')) {
                                echo "<a href='../tra_hang/create.php?ma_hd=" . $row['ma_hoa_don'] . "'
                                    class='btn-warning'>
                                    Trả hàng
                                </a>";
                            }

                             
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center;'>Không có hóa đơn</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>