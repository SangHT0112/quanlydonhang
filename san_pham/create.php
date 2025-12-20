<?php
include '../config.php';
checkLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $ten_sp   = trim($_POST['ten_san_pham']);
        $gia_ban  = floatval($_POST['gia_ban']);
        $don_vi   = $_POST['don_vi'] ?? 'Cái';
        $mo_ta    = $_POST['mo_ta'] ?? '';
        $ton_dau  = intval($_POST['so_luong_ton']); // tồn kho ban đầu

        if ($ten_sp === '' || $gia_ban < 0 || $ton_dau < 0) {
            throw new Exception('Dữ liệu không hợp lệ');
        }

        // ===== TRANSACTION =====
        $conn->begin_transaction();

        // 1️⃣ Thêm sản phẩm
        $sql_sp = "
            INSERT INTO san_pham (ten_san_pham, gia_ban, don_vi, mo_ta)
            VALUES (?, ?, ?, ?)
        ";
        $stmt_sp = $conn->prepare($sql_sp);
        $stmt_sp->bind_param("sdss", $ten_sp, $gia_ban, $don_vi, $mo_ta);
        $stmt_sp->execute();

        $ma_sp = $conn->insert_id;

        // 2️⃣ Tạo tồn kho ban đầu
        $sql_tk = "
            INSERT INTO ton_kho (ma_san_pham, so_luong_ton, ngay_cap_nhat)
            VALUES (?, ?, NOW())
        ";
        $stmt_tk = $conn->prepare($sql_tk);
        $stmt_tk->bind_param("ii", $ma_sp, $ton_dau);
        $stmt_tk->execute();

        // 3️⃣ Ghi log
        logActivity(
            'CREATE_PRODUCT',
            "Thêm sản phẩm #$ma_sp - $ten_sp (Tồn đầu: $ton_dau)"
        );

        $conn->commit();

        $success = 'Thêm sản phẩm và tồn kho thành công!';
        header('Refresh: 2; url=list.php');

    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
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
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" class="form-main">
                <div class="form-group">
                    <label>Tên Sản Phẩm</label>
                    <input type="text" name="ten_san_pham" required>
                </div>

                <div class="form-group">
                    <label>Giá Bán (VNĐ)</label>
                    <input type="number" name="gia_ban" min="0" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Đơn Vị</label>
                    <input type="text" name="don_vi" value="Cái">
                </div>

                <div class="form-group">
                    <label>Số Lượng Tồn Ban Đầu</label>
                    <input type="number" name="so_luong_ton" min="0" value="0" required>
                </div>

                <div class="form-group">
                    <label>Mô Tả</label>
                    <textarea name="mo_ta" rows="3"></textarea>
                </div>

                <div class="form-actions">
                    <button class="btn-primary">Thêm Sản Phẩm</button>
                    <a href="list.php" class="btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
