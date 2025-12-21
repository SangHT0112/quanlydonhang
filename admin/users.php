<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

// ================================
// THÊM QUYỀN MỚI
// ================================
if (isset($_POST['add_permission'])) {
    try {
        $perm_name = trim($_POST['perm_name']);
        $perm_desc = trim($_POST['perm_desc']);

        if ($perm_name == '') {
            $error = "Tên quyền không được để trống!";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO permissions (name, description) VALUES (?, ?)"
            );
            $stmt->bind_param("ss", $perm_name, $perm_desc);
            $stmt->execute();

            logActivity('ADD_PERMISSION', "Thêm quyền $perm_name");
            $success = "Thêm quyền thành công!";
        }
    } catch (Exception $e) {
        $error = "Lỗi thêm quyền: " . $e->getMessage();
    }
}



// Xử lý cập nhật vai trò người dùng
if ($_POST && isset($_POST['action'])) {
    try {
        $user_id = intval($_POST['user_id']);
        $role_id = intval($_POST['role_id']);
        
        if ($_POST['action'] === 'assign_role') {
            // Kiểm tra role đã tồn tại
            $sql = "SELECT * FROM user_roles WHERE user_id = $user_id AND role_id = $role_id";
            $result = $conn->query($sql);
            
            if ($result->num_rows == 0) {
                $sql = "INSERT INTO user_roles (user_id, role_id) VALUES ($user_id, $role_id)";
                $conn->query($sql);
                logActivity('ASSIGN_ROLE', "Gán vai trò $role_id cho người dùng $user_id");
                $success = "Gán vai trò thành công!";
            } else {
                $error = "Người dùng đã có vai trò này!";
            }
        } elseif ($_POST['action'] === 'remove_role') {
            $sql = "DELETE FROM user_roles WHERE user_id = $user_id AND role_id = $role_id";
            $conn->query($sql);
            logActivity('REMOVE_ROLE', "Gỡ vai trò $role_id của người dùng $user_id");
            $success = "Gỡ vai trò thành công!";
        }
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách người dùng
$sql = "SELECT u.id, u.username, u.full_name, u.status, GROUP_CONCAT(r.name SEPARATOR ', ') as roles
        FROM users u
        LEFT JOIN user_roles ur ON u.id = ur.user_id
        LEFT JOIN roles r ON ur.role_id = r.id
        GROUP BY u.id
        ORDER BY u.full_name";
$result = $conn->query($sql);

// Lấy danh sách vai trò
$sql_roles = "SELECT * FROM roles ORDER BY name";
$result_roles = $conn->query($sql_roles);
$roles_list = array();
while ($role = $result_roles->fetch_assoc()) {
    $roles_list[$role['id']] = $role;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .user-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .user-header h4 {
            margin: 0;
            color: #333;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .user-info {
            margin: 10px 0;
            font-size: 14px;
            color: #555;
        }
        
        .user-roles {
            margin: 10px 0;
            padding: 8px;
            background: white;
            border-radius: 4px;
            min-height: 30px;
        }
        
        .role-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .role-select {
            margin-top: 10px;
        }
        
        .role-select select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .role-actions {
            display: flex;
            gap: 5px;
        }
        
        .role-actions button {
            flex: 1;
            padding: 8px 10px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-assign {
            background: #28a745;
            color: white;
        }
        
        .btn-assign:hover {
            background: #218838;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Quản Lý Người Dùng & Vai Trò</h1>

        <main>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <h2>👥 Danh Sách Người Dùng</h2>
            
            <div class="admin-container">
                <?php
                if ($result->num_rows > 0) {
                    while ($user = $result->fetch_assoc()) {
                        $status_class = $user['status'] == 1 ? 'status-active' : 'status-inactive';
                        $status_text = $user['status'] == 1 ? 'Kích hoạt' : 'Bị khóa';
                        $roles_display = $user['roles'] ? $user['roles'] : 'Không có vai trò';
                        
                        echo "<div class='user-card'>";
                        echo "<div class='user-header'>";
                        echo "<h4>{$user['full_name']}</h4>";
                        echo "<span class='status-badge $status_class'>$status_text</span>";
                        echo "</div>";
                        
                        echo "<div class='user-info'>";
                        echo "<strong>Tên đăng nhập:</strong> {$user['username']}<br>";
                        echo "</div>";
                        
                        echo "<div class='user-roles'>";
                        echo "<strong>Vai trò hiện tại:</strong><br>";
                        foreach (explode(', ', $roles_display) as $role) {
                            if ($role !== 'Không có vai trò') {
                                echo "<span class='role-badge'>$role</span>";
                            }
                        }
                        if ($roles_display === 'Không có vai trò') {
                            echo "<span style='color: #999;'>Chưa có vai trò</span>";
                        }
                        echo "</div>";
                        
                        echo "<form method='POST' class='role-select'>";
                        echo "<input type='hidden' name='user_id' value='{$user['id']}'>";
                        echo "<select name='role_id' required>";
                        echo "<option value=''>-- Chọn vai trò --</option>";
                        foreach ($roles_list as $role_id => $role) {
                            echo "<option value='$role_id'>{$role['name']}</option>";
                        }
                        echo "</select>";
                        echo "<div class='role-actions'>";
                        echo "<button type='submit' name='action' value='assign_role' class='btn-assign'>Gán Vai Trò</button>";
                        echo "</div>";
                        echo "</form>";
                        
                        echo "</div>";
                    }
                } else {
                    echo "<p style='grid-column: 1/-1; text-align: center;'>Không có người dùng</p>";
                }
                ?>
            </div>

            <hr style="margin: 40px 0;">

            <h2>🔑 Danh Sách Vai Trò & Quyền Hạn</h2>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Vai Trò</th>
                        <th>Mô Tả</th>
                        <th>Quyền Hạn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_role_perms = "SELECT r.id, r.name, r.description, GROUP_CONCAT(p.name SEPARATOR ', ') as permissions
                                       FROM roles r
                                       LEFT JOIN role_permissions rp ON r.id = rp.role_id
                                       LEFT JOIN permissions p ON rp.permission_id = p.id
                                       GROUP BY r.id
                                       ORDER BY r.name";
                    $result_role_perms = $conn->query($sql_role_perms);
                    
                    while ($role_perm = $result_role_perms->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><strong>" . $role_perm['name'] . "</strong></td>";
                        echo "<td>" . ($role_perm['description'] ? $role_perm['description'] : '-') . "</td>";
                        echo "<td>";
                        if ($role_perm['permissions']) {
                            $perms = explode(', ', $role_perm['permissions']);
                            foreach ($perms as $perm) {
                                echo "<span class='role-badge' style='background: #764ba2;'>$perm</span>";
                            }
                        } else {
                            echo "Không có quyền hạn";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <div style="margin-top: 40px; padding: 20px; background: #f0f4ff; border-radius: 8px;">
                <h3>💡 Hướng Dẫn Sử Dụng</h3>
                <ul>
                    <li><strong>Gán Vai Trò:</strong> Chọn vai trò từ dropdown và nhấn "Gán Vai Trò"</li>
                    <li><strong>Xóa Vai Trò:</strong> Không có nút xóa ở đây, bạn cần vào phpMyAdmin để xóa từ bảng user_roles</li>
                    <li><strong>Thêm Quyền Hạn Mới:</strong> Sử dụng phpMyAdmin để thêm vào bảng permissions, sau đó gán cho vai trò qua role_permissions</li>
                </ul>
                
                <h3>📊 Vai Trò & Quyền Hạn Mặc Định</h3>
                <ul>
                    <li><strong>Admin:</strong> manage_users (quản lý toàn bộ hệ thống)</li>
                    <li><strong>Sale:</strong> create_po, edit_po, approve_po (tạo/duyệt đơn)</li>
                    <li><strong>Kho:</strong> execute_pxk, create_pxk (xuất kho)</li>
                    <li><strong>Kế Toán:</strong> create_bh, create_invoice, issue_invoice, record_payment, create_return (PBH, HĐ, thanh toán, trả hàng)</li>
                </ul>
            </div>
        </main>
    </div>
</body>
</html>
