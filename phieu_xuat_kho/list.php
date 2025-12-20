<?php
// ===============================
// phieu_xuat_kho/list.php
// Danh sách phiếu xuất kho
// ===============================

include '../config.php';
checkLogin();
requirePermission('view_inventory');  // Hoặc permission phù hợp cho xem PXK

$search = $_GET['search'] ?? '';
$trang_thai = $_GET['trang_thai'] ?? '';

// Xây dựng câu SQL tìm kiếm (prepared)
$sql = "
    SELECT 
        pxk.ma_phieu_xuat_kho,
        pxk.ngay_xuat,
        pxk.nguoi_xuat,
        pxk.trang_thai,
        pbh.tong_tien,
        pdh.ma_phieu_dat_hang,
        k.ten_khach_hang
    FROM phieu_xuat_kho pxk
    JOIN phieu_ban_hang pbh ON pxk.ma_phieu_ban_hang = pbh.ma_phieu_ban_hang
    JOIN phieu_dat_hang pdh ON pbh.ma_phieu_dat_hang = pdh.ma_phieu_dat_hang
    JOIN khach_hang k ON pdh.ma_khach_hang = k.ma_khach_hang
    WHERE 1=1
";

$params = [];
$types = "";

if ($search) {
    $sql .= " AND (k.ten_khach_hang LIKE ? OR pxk.ma_phieu_xuat_kho LIKE ? OR pxk.nguoi_xuat LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($trang_thai) {
    $sql .= " AND pxk.trang_thai = ?";
    $params[] = $trang_thai;
    $types .= "s";
}

$sql .= " ORDER BY pxk.ngay_xuat DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
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
                    <input type="text" name="search" placeholder="Tìm kiếm khách hàng, người xuất..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="trang_thai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Đang xuất" <?php if ($trang_thai == 'Đang xuất') echo 'selected'; ?>>Đang xuất</option>
                        <option value="Hoàn thành" <?php if ($trang_thai == 'Hoàn thành') echo 'selected'; ?>>Hoàn thành</option>
                    </select>

                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <div class="actions-section">
                <?php if (hasPermission('create_pxk')): ?>
                    <a href="../hoa_don/list.php" class="btn-primary">+ Tạo Phiếu Xuất Kho Mới (từ HD)</a>
                <?php endif; ?>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Mã PXK</th>
                        <th>Ngày Xuất</th>
                        <th>Người Xuất</th>
                        <th>Khách Hàng</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $status_class = strtolower(str_replace(' ', '-', $row['trang_thai']));
                            echo "<tr>";
                            echo "<td><strong>#" . $row['ma_phieu_xuat_kho'] . "</strong></td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_xuat'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nguoi_xuat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ten_khach_hang']) . "</td>";
                            echo "<td>" . formatMoney($row['tong_tien']) . " VNĐ</td>";
                            echo "<td><span class='status-" . $status_class . "'>" . $row['trang_thai'] . "</span></td>";
                            echo "<td>";
                            echo "<a href='detail.php?id=" . $row['ma_phieu_xuat_kho'] . "' class='btn-info'>Xem</a> ";
                            
                            if ($row['trang_thai'] == 'Đang xuất' && hasPermission('execute_pxk')) {
                                echo "<a href='execute.php?id=" . $row['ma_phieu_xuat_kho'] . "' class='btn-success' onclick='return confirm(\"Xác nhận hoàn thành xuất kho?\")'>Thực xuất</a>";
                            }
                            
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center;'>Không có phiếu xuất kho</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>