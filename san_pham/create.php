<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

$error = '';
$success = '';

if ($_POST) {
    try {
        $ten_sp = $_POST['ten_san_pham'];
        $gia_ban = $_POST['gia_ban'];
        $don_vi = $_POST['don_vi'] ?? 'Cái';
        $mo_ta = $_POST['mo_ta'];

        if (empty($ten_sp) || $gia_ban < 0) {
            throw new Exception('Vui lòng nhập đầy đủ thông tin sản phẩm');
        }

        $sql = "INSERT INTO san_pham (ten_san_pham, gia_ban, don_vi, mo_ta) 
               VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Lỗi prepare: ' . $conn->error);
        }
        $stmt->bind_param("sdss", $ten_sp, $gia_ban, $don_vi, $mo_ta);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi thêm sản phẩm: ' . $stmt->error);
        }

        logActivity('CREATE_PRODUCT', 'Thêm sản phẩm: ' . $ten_sp);
        
        $success = 'Thêm sản phẩm thành công!';
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
    <title>Thêm Sản Phẩm</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Thêm Sản Phẩm Mới</h1>

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
                        <input type="text" name="ten_san_pham" id="ten_san_pham" required>
                    </div>

                    <div class="form-group">
                        <label for="gia_ban">Giá Bán (VNĐ):</label>
                        <input type="number" name="gia_ban" id="gia_ban" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="don_vi">Đơn Vị:</label>
                        <input type="text" name="don_vi" id="don_vi" value="Cái">
                    </div>

                    <div class="form-group">
                        <label for="mo_ta">Mô Tả:</label>
                        <textarea name="mo_ta" id="mo_ta" rows="4"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Thêm Sản Phẩm</button>
                        <a href="list.php" class="btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>