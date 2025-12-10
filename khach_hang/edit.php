<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

$error = '';
$success = '';
$id = $_GET['id'] ?? 0;

// Lấy dữ liệu khách hàng
$sql = "SELECT * FROM khach_hang WHERE ma_khach_hang = " . intval($id);
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$khach_hang = $result->fetch_assoc();

if ($_POST) {
    try {
        $ten_kh = $_POST['ten_khach_hang'];
        $dia_chi = $_POST['dia_chi'];
        $dien_thoai = $_POST['dien_thoai'];
        $email = $_POST['email'];
        $trang_thai = $_POST['trang_thai'];

        if (empty($ten_kh)) {
            throw new Exception('Vui lòng nhập tên khách hàng');
        }

        $sql = "UPDATE khach_hang SET ten_khach_hang = ?, dia_chi = ?, dien_thoai = ?, email = ?, trang_thai = ? 
               WHERE ma_khach_hang = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Lỗi prepare: ' . $conn->error);
        }
        $stmt->bind_param("sssssi", $ten_kh, $dia_chi, $dien_thoai, $email, $trang_thai, $id);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi cập nhật: ' . $stmt->error);
        }

        logActivity('UPDATE_CUSTOMER', 'Cập nhật khách hàng: ' . $ten_kh);
        
        $success = 'Cập nhật khách hàng thành công!';
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
    <title>Sửa Khách Hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
            <h1>Sửa Thông Tin Khách Hàng</h1>

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
                        <input type="text" name="ten_khach_hang" id="ten_khach_hang" required value="<?php echo htmlspecialchars($khach_hang['ten_khach_hang']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="dien_thoai">Điện Thoại:</label>
                        <input type="tel" name="dien_thoai" id="dien_thoai" value="<?php echo htmlspecialchars($khach_hang['dien_thoai'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($khach_hang['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="dia_chi">Địa Chỉ:</label>
                        <textarea name="dia_chi" id="dia_chi" rows="3"><?php echo htmlspecialchars($khach_hang['dia_chi'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="trang_thai">Trạng Thái:</label>
                        <select name="trang_thai" id="trang_thai">
                            <option value="Hoạt động" <?php if ($khach_hang['trang_thai'] == 'Hoạt động') echo 'selected'; ?>>Hoạt động</option>
                            <option value="Ngừng hoạt động" <?php if ($khach_hang['trang_thai'] == 'Ngừng hoạt động') echo 'selected'; ?>>Ngừng hoạt động</option>
                        </select>
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