<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

$search = $_GET['search'] ?? '';
$trang_thai = $_GET['trang_thai'] ?? '';

$sql = "SELECT * FROM khach_hang WHERE 1=1";

if ($search) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (ten_khach_hang LIKE '%$search%' OR dien_thoai LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($trang_thai) {
    $trang_thai = $conn->real_escape_string($trang_thai);
    $sql .= " AND trang_thai = '$trang_thai'";
}

$sql .= " ORDER BY ngay_tao DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Khách Hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Danh Sách Khách Hàng</h1>
           

        <main>
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Tìm kiếm tên, SĐT, email..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="trang_thai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Hoạt động" <?php if ($trang_thai == 'Hoạt động') echo 'selected'; ?>>Hoạt động</option>
                        <option value="Ngừng hoạt động" <?php if ($trang_thai == 'Ngừng hoạt động') echo 'selected'; ?>>Ngừng hoạt động</option>
                    </select>

                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <div class="actions-section">
                <a href="create.php" class="btn-primary">+ Thêm Khách Hàng Mới</a>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Tên Khách Hàng</th>
                        <th>Điện Thoại</th>
                        <th>Email</th>
                        <th>Địa Chỉ</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>" . htmlspecialchars($row['ten_khach_hang']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row['dien_thoai'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($row['dia_chi'] ?? '') . "</td>";
                            echo "<td><span class='status-" . strtolower(str_replace(' ', '-', $row['trang_thai'])) . "'>" . $row['trang_thai'] . "</span></td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_tao'])) . "</td>";
                            echo "<td>";
                            echo "<a href='edit.php?id=" . $row['ma_khach_hang'] . "' class='btn-warning'>Sửa</a> ";
                            echo "<a href='delete.php?id=" . $row['ma_khach_hang'] . "' class='btn-danger' onclick='return confirm(\"Bạn chắc chắn muốn xóa?\")'>Xóa</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center;'>Không có khách hàng</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>