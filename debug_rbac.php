<?php
include 'config.php';
checkLogin();

$user = currentUser();
$roles = getUserRoles();
$perms = getUserPermissions();

header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>RBAC Debug - Thông tin người dùng</title>
    <style>body{font-family:Arial,Helvetica,sans-serif;margin:20px}pre{background:#f5f5f7;padding:15px;border-radius:6px}</style>
</head>
<body>
    <h2>RBAC Debug</h2>
    <p><strong>Người dùng:</strong> <?php echo htmlspecialchars($user['full_name'] ?? 'Guest'); ?> (<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>)</p>
    <p><strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id'] ?? ''); ?></p>

    <h3>Vai trò (roles)</h3>
    <pre><?php echo htmlspecialchars(print_r($roles, true)); ?></pre>

    <h3>Quyền hạn (permissions)</h3>
    <pre><?php echo htmlspecialchars(print_r($perms, true)); ?></pre>

    <h3>Gợi ý kiểm tra trong DB</h3>
    <pre>
-- Tìm user id
SELECT id, username FROM users WHERE username = 'ketoan1' OR username = 'ketoan';

-- Danh sách vai trò của user
SELECT r.* FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = &lt;USER_ID&gt;;

-- Quyền hạn của vai trò
SELECT p.* FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = &lt;ROLE_ID&gt;;

-- Quyền hạn trực tiếp của user (qua roles)
SELECT DISTINCT p.* FROM permissions p
JOIN role_permissions rp ON p.id = rp.permission_id
JOIN user_roles ur ON rp.role_id = ur.role_id
WHERE ur.user_id = &lt;USER_ID&gt;;
    </pre>

    <p><a href="index.php">Quay lại</a></p>
</body>
</html>