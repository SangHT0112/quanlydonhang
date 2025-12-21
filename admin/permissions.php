<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

/* ===============================
   THÊM QUYỀN MỚI
=============================== */
if (isset($_POST['add_permission'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name) {
        $stmt = $conn->prepare(
            "INSERT INTO permissions (name, description) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
        logActivity('ADD_PERMISSION', "Thêm quyền $name");
        $success = "Thêm quyền thành công!";
    } else {
        $error = "Tên quyền không được để trống!";
    }
}

/* ===============================
   GÁN / GỠ QUYỀN CHO ROLE
=============================== */
if (isset($_POST['assign_permission'])) {
    $role_id = intval($_POST['role_id']);
    $permission_id = intval($_POST['permission_id']);

    $check = $conn->query(
        "SELECT * FROM role_permissions 
         WHERE role_id=$role_id AND permission_id=$permission_id"
    );

    if ($check->num_rows == 0) {
        $conn->query(
            "INSERT INTO role_permissions (role_id, permission_id)
             VALUES ($role_id, $permission_id)"
        );
        $success = "Gán quyền thành công!";
    } else {
        $error = "Vai trò đã có quyền này!";
    }
}

/* ===============================
   LẤY DỮ LIỆU
=============================== */
$roles = $conn->query("SELECT * FROM roles ORDER BY name");
$permissions = $conn->query("SELECT * FROM permissions ORDER BY name");

$role_permissions = [];
$rs = $conn->query(
    "SELECT rp.role_id, p.name 
     FROM role_permissions rp
     JOIN permissions p ON rp.permission_id = p.id"
);
while ($r = $rs->fetch_assoc()) {
    $role_permissions[$r['role_id']][] = $r['name'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản Lý Quyền Hạn</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.box {background:#f8f9fa;padding:20px;border-radius:8px;margin-bottom:30px}
.badge {background:#667eea;color:#fff;padding:4px 10px;border-radius:12px;font-size:12px;margin:2px;display:inline-block}
</style>
</head>

<body>
<div class="container">
<?php include '../header.php'; ?>

<h1>🔐 Quản Lý Quyền Hạn</h1>

<?php if (isset($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<!-- ================== THÊM QUYỀN ================== -->
<div class="box">
<h2>➕ Thêm Quyền Mới</h2>

<form method="POST">
    <label>Tên quyền (key)</label>
    <input type="text" name="name" required placeholder="vd: create_invoice">

    <label>Mô tả quyền</label>
    <textarea name="description" rows="3"
        placeholder="Giải thích quyền dùng để làm gì"></textarea>

    <button name="add_permission">Thêm quyền</button>
</form>
</div>

<!-- ================== DANH SÁCH QUYỀN ================== -->
<div class="box">
<h2>📋 Danh Sách Quyền</h2>
<table class="table">
<tr>
    <th>Tên quyền</th>
    <th>Mô tả</th>
</tr>
<?php
$permissions->data_seek(0);
while ($p = $permissions->fetch_assoc()):
?>
<tr>
    <td><b><?= $p['name'] ?></b></td>
    <td><?= $p['description'] ?: '-' ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<!-- ================== GÁN QUYỀN CHO BỘ PHẬN ================== -->
<div class="box">
<h2>🏢 Gán Quyền Cho Bộ Phận</h2>

<?php while ($role = $roles->fetch_assoc()): ?>
<h3><?= $role['name'] ?></h3>

<p>
<?php
if (!empty($role_permissions[$role['id']])) {
    foreach ($role_permissions[$role['id']] as $perm) {
        echo "<span class='badge'>$perm</span>";
    }
} else {
    echo "<i>Chưa có quyền</i>";
}
?>
</p>

<form method="POST" style="display:flex;gap:10px;max-width:400px">
    <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
    <select name="permission_id" required>
        <option value="">-- Chọn quyền --</option>
        <?php
        $permissions->data_seek(0);
        while ($p = $permissions->fetch_assoc()):
        ?>
        <option value="<?= $p['id'] ?>">
            <?= $p['name'] ?> – <?= $p['description'] ?>
        </option>
        <?php endwhile; ?>
    </select>
    <button name="assign_permission">Gán</button>
</form>
<hr>
<?php endwhile; ?>
</div>

</div>
</body>
</html>
