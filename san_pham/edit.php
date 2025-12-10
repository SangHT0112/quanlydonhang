<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

$error = '';
$success = '';
$id = $_GET['id'] ?? 0;

// Lấy dữ liệu sản phẩm
$sql = "SELECT * FROM san_pham WHERE ma_san_pham = " . intval($id);
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$san_pham = $result->fetch_assoc();

if ($_POST) {
    try {
        $ten_sp = $_POST['ten_san_pham'];
        $gia_ban = $_POST['gia_ban'];
        $don_vi = $_POST['don_vi'] ?? 'Cái';
        $mo_ta = $_POST['mo_ta'];

        if (empty($ten_sp) || $gia_ban < 0) {
            throw new Exception('Vui lòng nhập đầy đủ thông tin sản phẩm');
        }

        $sql = "UPDATE san_pham SET ten_san_pham = ?, gia_ban = ?, don_vi = ?, mo_ta = ? 
               WHERE ma_san_pham = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Lỗi prepare: ' . $conn->error);
        }
        $stmt->bind_param("sdssi", $ten_sp, $gia_ban, $don_vi, $mo_ta, $id);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi cập nhật: ' . $stmt->error);
        }

        logActivity('UPDATE_PRODUCT', 'Cập nhật sản phẩm: ' . $ten_sp);
        
        $success = 'Cập nhật sản phẩm thành công!';
        header('Refresh: 2; url=list.php');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Sản Phẩm</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
            <h1>Sửa Thông Tin Sản Phẩm</h1>
          

        <main>
            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" class="form-main">
                    <div class="form-group">
                        <label for="ten_san_pham">Tên Sản Phẩm:</label>
                        <input type="text" name="ten_san_pham" id="ten_san_pham" required value="<?php echo htmlspecialchars($san_pham['ten_san_pham']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="gia_ban">Giá Bán (VNĐ):</label>
                        <input type="number" name="gia_ban" id="gia_ban" step="0.01" min="0" required value="<?php echo $san_pham['gia_ban']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="don_vi">Đơn Vị:</label>
                        <input type="text" name="don_vi" id="don_vi" value="<?php echo htmlspecialchars($san_pham['don_vi']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="mo_ta">Mô Tả:</label>
                        <textarea name="mo_ta" id="mo_ta" rows="4"><?php echo htmlspecialchars($san_pham['mo_ta'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Cập Nhật</button>
                        <a href="list.php" class="btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>