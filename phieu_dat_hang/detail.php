<?php
include '../config.php';
checkLogin();

$id = intval($_GET['id']);

// Sửa query: Đồng bộ trạng thái, dùng prepared, assume thêm created_by/approved_by
$stmt = $conn->prepare("
    SELECT p.*, u.full_name AS nguoi_tao, a.full_name AS nguoi_duyet
    FROM phieu_dat_hang p
    LEFT JOIN users u ON p.created_by = u.id
    LEFT JOIN users a ON p.approved_by = a.id
    WHERE p.ma_phieu_dat_hang = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$po = $result->fetch_assoc();
if (!$po) {
    header('Location: list.php?error=PO không tồn tại');
    exit;
}

// Chi tiết items
$stmt_items = $conn->prepare("
    SELECT 
        ct.ma_san_pham,
        sp.ten_san_pham,
        sp.don_vi,
        ct.so_luong,
        ct.gia_dat,
        (ct.so_luong * ct.gia_dat) AS thanh_tien
    FROM chi_tiet_phieu_dat_hang ct
    JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
    WHERE ct.ma_phieu_dat_hang = ?
");
$stmt_items->bind_param("i", $id);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết PO #<?= $id ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Chi Tiết Phiếu Đặt Hàng #<?= $id ?></h1>
        <main>
            <div class="detail-card">
                <p><strong>Trạng thái:</strong> <span class="status-<?= strtolower(str_replace(' ', '-', $po['trang_thai'])) ?>"><?= htmlspecialchars($po['trang_thai']) ?></span></p>
                <p><strong>Khách hàng:</strong> <?= $conn->query("SELECT ten_khach_hang FROM khach_hang WHERE ma_khach_hang = " . $po['ma_khach_hang'])->fetch_assoc()['ten_khach_hang'] ?? 'N/A' ?></p>
                <p><strong>Ngày đặt:</strong> <?= date('d/m/Y', strtotime($po['ngay_dat'])) ?></p>
                <p><strong>Tổng tiền:</strong> <?= formatMoney($po['tong_tien']) ?> VNĐ</p>
                <p><strong>Người tạo:</strong> <?= htmlspecialchars($po['nguoi_tao'] ?? 'N/A') ?></p>
                <p><strong>Người duyệt:</strong> <?= htmlspecialchars($po['nguoi_duyet'] ?? 'N/A') ?></p>
                <p><strong>Ghi chú:</strong> <?= htmlspecialchars($po['ghi_chu'] ?? 'Không có') ?></p>
            </div>

            <h3>Chi Tiết Sản Phẩm</h3>
            <table class="table">
                <thead>
                    <tr><th>Sản Phẩm</th><th>Số Lượng</th><th>Giá Đặt</th><th>Thành Tiền</th></tr>
                </thead>
                <tbody>
                    <?php while($r = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['ten_san_pham']) ?> (<?= $r['don_vi'] ?>)</td>
                        <td><?= $r['so_luong'] ?></td>
                        <td><?= formatMoney($r['gia_dat']) ?> VNĐ</td>
                        <td><?= formatMoney($r['thanh_tien']) ?> VNĐ</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="actions">
                <a href="list.php" class="btn-secondary">← Quay lại danh sách</a>
                <?php if ($po['trang_thai'] == 'Chờ duyệt' && hasPermission('approve_po')): ?>
                    <a href="approve.php?id=<?= $id ?>" class="btn-primary">Duyệt</a>
                <?php endif; ?>
                <?php if ($po['trang_thai'] == 'Chờ duyệt' && hasPermission('edit_po')): ?>
                    <a href="edit.php?id=<?= $id ?>" class="btn-info">Sửa</a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>