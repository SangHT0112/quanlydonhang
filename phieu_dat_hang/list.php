<?php
include '../config.php';
checkLogin();

$search = '';
$trang_thai = '';
if ($_GET) {
    $search = $_GET['search'] ?? '';
    $trang_thai = $_GET['trang_thai'] ?? '';
}

// Xây dựng câu SQL tìm kiếm
$sql = "SELECT p.ma_phieu_dat_hang, k.ten_khach_hang, p.ngay_dat, p.tong_tien, p.trang_thai 
        FROM phieu_dat_hang p 
        JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang 
        WHERE 1=1";

if ($search) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (k.ten_khach_hang LIKE '%$search%' OR p.ma_phieu_dat_hang LIKE '%$search%')";
}
if ($trang_thai) {
    $trang_thai = $conn->real_escape_string($trang_thai);
    $sql .= " AND p.trang_thai = '$trang_thai'";
}

$sql .= " ORDER BY p.ngay_dat DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Phiếu Đặt Hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Danh Sách Phiếu Đặt Hàng</h1>
        <main>
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Tìm kiếm khách hàng..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="trang_thai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Chờ duyệt" <?php if ($trang_thai == 'Chờ duyệt') echo 'selected'; ?>>Chờ duyệt</option>
                        <option value="Đã duyệt" <?php if ($trang_thai == 'Đã duyệt') echo 'selected'; ?>>Đã duyệt</option>
                        <option value="Hủy" <?php if ($trang_thai == 'Hủy') echo 'selected'; ?>>Hủy</option>
                    </select>

                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <div class="actions-section">
                <a href="create.php" class="btn-primary">+ Tạo Phiếu Đặt Hàng Mới</a>
            </div>

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
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>#" . $row['ma_phieu_dat_hang'] . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row['ten_khach_hang']) . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_dat'])) . "</td>";
                            echo "<td>" . formatMoney($row['tong_tien']) . " VNĐ</td>";
                            echo "<td><span class='status-" . strtolower(str_replace(' ', '-', $row['trang_thai'])) . "'>" . $row['trang_thai'] . "</span></td>";
                            echo "<td>";
                            echo "<a href='detail.php?id=" . $row['ma_phieu_dat_hang'] . "' class='btn-info'>Xem</a> ";
                            if ($row['trang_thai'] == 'Chờ duyệt') {
                                if (hasPermission('edit_po')) {
                                    echo "<a href='edit.php?id=" . $row['ma_phieu_dat_hang'] . "' class='btn-warning'>Sửa</a> ";
                                }
                                if (hasPermission('approve_po')) {
                                    echo "<a href='delete.php?id=" . $row['ma_phieu_dat_hang'] . "' class='btn-danger' onclick='return confirm(\"Bạn chắc chắn muốn xóa?\")'>Xóa</a>";
                                }
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center;'>Không có phiếu đặt hàng</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>