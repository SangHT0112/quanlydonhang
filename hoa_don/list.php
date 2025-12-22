<?php
include '../config.php';
checkLogin();

$search = $_GET['search'] ?? '';
$trang_thai = $_GET['trang_thai'] ?? '';

// Xây dựng câu SQL tìm kiếm (prepared cho an toàn)
$sql = "
SELECT 
    hd.*,
    k.ten_khach_hang,
    CASE WHEN th.ma_tra_hang IS NOT NULL THEN 1 ELSE 0 END as has_return
FROM hoa_don hd
JOIN phieu_dat_hang p ON hd.ma_phieu_dat_hang = p.ma_phieu_dat_hang
JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang
LEFT JOIN tra_hang th ON hd.ma_hoa_don = th.ma_hoa_don 
WHERE 1=1
";

$params = [];
$types = "";

if ($search) {
    $sql .= " AND (k.ten_khach_hang LIKE ? OR hd.ma_hoa_don LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if ($trang_thai) {
    $sql .= " AND hd.trang_thai = ?";
    $params[] = $trang_thai;
    $types .= "s";
}

$sql .= " ORDER BY hd.ngay_xuat_hd DESC";
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
    <title>Danh Sách Hóa Đơn</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <?php include '../chat/chat.php'; ?>
        <h1>Danh Sách Hóa Đơn</h1>

        <main>
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <!-- THÊM: Input search cho KH/mã HD -->
                    <input type="text" name="search" placeholder="Tìm mã HD hoặc tên khách hàng..." value="<?= htmlspecialchars($search) ?>">
                    
                    <select name="trang_thai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Chưa thanh toán" <?= $trang_thai == 'Chưa thanh toán' ? 'selected' : '' ?>>Chưa thanh toán</option>
                        <option value="Đã thanh toán" <?= $trang_thai == 'Đã thanh toán' ? 'selected' : '' ?>>Đã thanh toán</option>
                        <option value="Công nợ" <?= $trang_thai == 'Công nợ' ? 'selected' : '' ?>>Công nợ</option>
                    </select>

                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Mã Hóa Đơn</th>
                        <th>Khách Hàng</th>  <!-- ← THÊM: Cột khách hàng từ JOIN -->
                        <th>Ngày Xuất</th>
                        <th>Tổng Tiền</th>
                        <th>Khuyến Mại</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
               <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): 
                            $status_class = strtolower(str_replace(' ', '-', $row['trang_thai']));
                            $has_pxk = !empty($row['ma_phieu_xuat_kho']);
                            $has_return = $row['has_return'];
                        ?>
                            <tr>
                                <td><strong>#<?= $row['ma_hoa_don'] ?></strong></td>
                                <td><?= htmlspecialchars($row['ten_khach_hang']) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['ngay_xuat_hd'])) ?></td>
                                <td><?= formatMoney($row['tong_tien']) ?> VNĐ</td>
                                <td><?= formatMoney($row['khuyen_mai_tong'] ?? 0) ?> VNĐ</td>
                                <td><span class="status-<?= $status_class ?>"><?= $row['trang_thai'] ?></span></td>
                                <td class="actions">
                                    <a href="detail.php?id=<?= $row['ma_hoa_don'] ?>" class="btn btn-info btn-sm">Xem</a>

                                    <?php if (hasPermission('create_pxk')): ?>
                                        <?php if ($has_pxk): ?>
                                            <button class="btn btn-success btn-sm" disabled>Đã lập PXK</button>
                                        <?php else: ?>
                                            <a href="../phieu_xuat_kho/create.php?ma_hd=<?= $row['ma_hoa_don'] ?>" 
                                            class="btn btn-primary btn-sm">Lập PXK</a>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (hasPermission('create_return')): ?>
                                        <?php if ($has_return): ?>
                                            <button class="btn btn-secondary btn-sm" disabled>Đã trả hàng</button>
                                        <?php else: ?>
                                            <a href="../tra_hang/create.php?ma_hd=<?= $row['ma_hoa_don'] ?>" 
                                            class="btn btn-warning btn-sm">Trả Hàng</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">Không có hóa đơn</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>