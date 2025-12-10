<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

$error = '';
$success = '';

if ($_POST) {
    try {
        $ten_kh = $_POST['ten_khach_hang'];
        $dia_chi = $_POST['dia_chi'];
        $dien_thoai = $_POST['dien_thoai'];
        $email = $_POST['email'];

        if (empty($ten_kh)) {
            throw new Exception('Vui lòng nhập tên khách hàng');
        }

        $sql = "INSERT INTO khach_hang (ten_khach_hang, dia_chi, dien_thoai, email, trang_thai) 
               VALUES (?, ?, ?, ?, 'Hoạt động')";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Lỗi prepare: ' . $conn->error);
        }
        $stmt->bind_param("ssss", $ten_kh, $dia_chi, $dien_thoai, $email);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi thêm khách hàng: ' . $stmt->error);
        }

        $ma_kh = $conn->insert_id;
        logActivity('CREATE_CUSTOMER', 'Thêm khách hàng: ' . $ten_kh);
        
        $success = 'Thêm khách hàng thành công!';
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
    <title>Thêm Khách Hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Thêm Khách Hàng Mới</h1>

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
                        <label for="ten_khach_hang">Tên Khách Hàng:</label>
                        <input type="text" name="ten_khach_hang" id="ten_khach_hang" required>
                    </div>

                    <div class="form-group">
                        <label for="dien_thoai">Điện Thoại:</label>
                        <input type="tel" name="dien_thoai" id="dien_thoai">
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email">
                    </div>

                    <div class="form-group">
                        <label for="dia_chi">Địa Chỉ:</label>
                        <textarea name="dia_chi" id="dia_chi" rows="3"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Thêm Khách Hàng</button>
                        <a href="list.php" class="btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>