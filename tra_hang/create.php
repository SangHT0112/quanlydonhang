<?php
include '../config.php';
checkLogin();
requirePermission('create_return');

$ma_hd = $_GET['ma_hd'] ?? 0;
if (!$ma_hd) {
    header('Location: ../hoa_don/list.php');
    exit;
}

// Lấy sản phẩm từ hóa đơn
$sql = "
    SELECT ct.ma_san_pham, sp.ten_san_pham, ct.so_luong, ct.don_gia
    FROM chi_tiet_hoa_don ct
    JOIN san_pham sp ON sp.ma_san_pham = ct.ma_san_pham
    WHERE ct.ma_hoa_don = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ma_hd);
$stmt->execute();
$san_pham = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo phiếu trả hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="container">
<?php include '../header.php'; ?>

<h1>Tạo phiếu trả hàng – Hóa đơn #<?= $ma_hd ?></h1>

<form method="POST" action="store.php">
    <input type="hidden" name="ma_hoa_don" value="<?= $ma_hd ?>">

    <label>Lý do trả hàng</label>
    <textarea name="ly_do"></textarea>

    <table class="table">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>SL đã bán</th>
                <th>SL trả</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $san_pham->fetch_assoc()): ?>
            <tr>
                <td><?= $row['ten_san_pham'] ?></td>
                <td><?= $row['so_luong'] ?></td>
                <td>
                    <input type="number"
                           name="so_luong_tra[<?= $row['ma_san_pham'] ?>]"
                           min="0"
                           max="<?= $row['so_luong'] ?>"
                           value="0">
                    <input type="hidden"
                           name="don_gia[<?= $row['ma_san_pham'] ?>]"
                           value="<?= $row['don_gia'] ?>">
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <button type="submit" class="btn-primary">Tạo phiếu trả hàng</button>
    <a href="../hoa_don/list.php" class="btn-secondary">Hủy</a>
</form>

</div>
</body>
</html>
