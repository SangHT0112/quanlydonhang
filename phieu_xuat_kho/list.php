<?php
include '../config.php';
checkLogin();
requirePermission('execute_pxk');

$search = '';
$trang_thai = '';
if ($_GET) {
    $search = $_GET['search'] ?? '';
    $trang_thai = $_GET['trang_thai'] ?? '';
}

// Xây dựng câu SQL tìm kiếm
$sql = "SELECT pxk.ma_phieu_xuat_kho, pxk.ma_phieu_ban_hang, pxk.ngay_xuat, pxk.nguoi_xuat, pxk.trang_thai,
               pbh.ngay_lap, pbh.tong_tien
        FROM phieu_xuat_kho pxk 
        JOIN phieu_ban_hang pbh ON pxk.ma_phieu_ban_hang = pbh.ma_phieu_ban_hang
        WHERE 1=1";

if ($search) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (pxk.ma_phieu_xuat_kho LIKE '%$search%' OR pxk.ma_phieu_ban_hang LIKE '%$search%')";
}
if ($trang_thai) {
    $trang_thai = $conn->real_escape_string($trang_thai);
    $sql .= " AND pxk.trang_thai = '$trang_thai'";
}

$sql .= " ORDER BY pxk.ngay_xuat DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Phiếu Xuất Kho</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Danh Sách Phiếu Xuất Kho</h1>
        <main>
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Tìm kiếm mã phiếu..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="trang_thai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Đang xuất" <?php if ($trang_thai == 'Đang xuất') echo 'selected'; ?>>Đang xuất</option>
                        <option value="Hoàn thành" <?php if ($trang_thai == 'Hoàn thành') echo 'selected'; ?>>Hoàn thành</option>
                    </select>

                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Mã PXK</th>
                        <th>Mã PBH</th>
                        <th>Ngày Xuất</th>
                        <th>Người Xuất</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>#" . $row['ma_phieu_xuat_kho'] . "</strong></td>";
                            echo "<td>#" . $row['ma_phieu_ban_hang'] . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_xuat'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nguoi_xuat'] ?? 'N/A') . "</td>";
                            echo "<td><span class='status-" . strtolower(str_replace(' ', '-', $row['trang_thai'])) . "'>" . $row['trang_thai'] . "</span></td>";
                            echo "<td>";
                            echo "<a href='detail.php?id=" . $row['ma_phieu_xuat_kho'] . "' class='btn-info'>Xem</a> ";
                            echo "<a href='print.php?id=" . $row['ma_phieu_xuat_kho'] . "' class='btn-secondary' target='_blank'>In</a>";
                            if ($row['trang_thai'] == 'Đang xuất') {
                                echo " <a href='edit.php?id=" . $row['ma_phieu_xuat_kho'] . "' class='btn-warning'>Sửa</a> ";
                                echo "<a href='delete.php?id=" . $row['ma_phieu_xuat_kho'] . "' class='btn-danger' onclick='return confirm(\"Bạn chắc chắn muốn xóa phiếu xuất kho này?\")'>Xóa</a> ";
                                echo "<a href='complete.php?id=" . $row['ma_phieu_xuat_kho'] . "' class='btn-primary' onclick='return confirm(\"Bạn chắc chắn muốn hoàn thành xuất kho này?\")'>Hoàn Thành</a>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center;'>Không có phiếu xuất kho</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
