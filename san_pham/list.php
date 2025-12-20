<?php
include '../config.php';
checkLogin();

$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM san_pham WHERE 1=1";

if ($search) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND ten_san_pham LIKE '%$search%'";
}

$sql .= " ORDER BY ngay_tao DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Sản Phẩm</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    <div class="container">
        
        <h1>Danh Sách Sản Phẩm</h1>
        <main>
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Tìm kiếm tên sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <div class="actions-section">
                <a href="create.php" class="btn-primary">+ Thêm Sản Phẩm Mới</a>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Tên Sản Phẩm</th>
                        <th>Đơn Vị</th>
                        <th style="text-align: right;">Giá Bán</th>
                        <th>Mô Tả</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>" . htmlspecialchars($row['ten_san_pham']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row['don_vi']) . "</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($row['gia_ban']) . " VNĐ</td>";
                            echo "<td>" . htmlspecialchars(substr($row['mo_ta'] ?? '', 0, 50)) . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_tao'])) . "</td>";
                            echo "<td>";
                            echo "<a href='edit.php?id=" . $row['ma_san_pham'] . "' class='btn-warning'>Sửa</a> ";
                            echo "<a href='delete.php?id=" . $row['ma_san_pham'] . "' class='btn-danger' onclick='return confirm(\"Bạn chắc chắn muốn xóa?\")'>Xóa</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center;'>Không có sản phẩm</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>