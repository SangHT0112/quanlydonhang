<?php
session_start();

// $servername = "sql107.infinityfree.com";
// $username = "if0_39047055";
// $password = "htsang112";
// $dbname = "quanlydonhang";


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quanlydonhang";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Hàm format tiền
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.');
}

// Hàm kiểm tra đăng nhập
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Hàm log hoạt động
function logActivity($action, $details = "") {
    global $conn;
    $user = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
    $timestamp = date('Y-m-d H:i:s');
    $sql = "INSERT INTO activity_log (user, action, details, timestamp) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $user, $action, $details, $timestamp);
        $stmt->execute();
    }
}

// ===== RBAC Functions =====

// Lấy thông tin user hiện tại
function currentUser() {
    global $conn;
    if (!isset($_SESSION['user_id'])) return null;
    $id = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT id, username, full_name FROM users WHERE id = ? AND status = 1 LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows ? $res->fetch_assoc() : null;
}

// Kiểm tra user có role không (sửa: prepared)
function hasRole($roleName) {
    global $conn;
    if (!isset($_SESSION['user_id'])) return false;
    $userId = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT 1 FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ? AND r.name = ? LIMIT 1");
    $stmt->bind_param("is", $userId, $roleName);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows > 0;
}

// Kiểm tra user có permission không (sửa: prepared)
function hasPermission($permissionName) {
    global $conn;
    if (!isset($_SESSION['user_id'])) return false;
    $userId = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT 1 FROM user_roles ur JOIN role_permissions rp ON ur.role_id = rp.role_id JOIN permissions p ON rp.permission_id = p.id WHERE ur.user_id = ? AND p.name = ? LIMIT 1");
    $stmt->bind_param("is", $userId, $permissionName);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows > 0;
}

// Yêu cầu permission (sửa: dùng hasPermission)
function requirePermission($permissionName) {
    if (!hasPermission($permissionName)) {
        http_response_code(403);
        echo '<html><head><meta charset="UTF-8"><style>body{font-family:Arial;text-align:center;margin-top:50px}h2{color:#ef4444}</style></head>';
        echo '<body><h2>403 - Không có quyền</h2><p>Bạn không có quyền để thực hiện hành động này.</p>';
        echo '<p><a href="javascript:history.back()">← Quay lại</a></p></body></html>';
        exit;
    }
}

// Lấy danh sách roles của user hiện tại (giữ nguyên)
function getUserRoles() {
    global $conn;
    if (!isset($_SESSION['user_id'])) return [];
    $userId = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT r.id, r.name, r.description FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ? ORDER BY r.name");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $roles = [];
    while ($row = $res->fetch_assoc()) {
        $roles[] = $row;
    }
    return $roles;
}

// Lấy danh sách permissions của user hiện tại (sửa: prepared)
function getUserPermissions() {
    global $conn;
    if (!isset($_SESSION['user_id'])) return [];
    $userId = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT DISTINCT p.id, p.name, p.description FROM user_roles ur JOIN role_permissions rp ON ur.role_id = rp.role_id JOIN permissions p ON rp.permission_id = p.id WHERE ur.user_id = ? ORDER BY p.name");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $perms = [];
    while ($row = $res->fetch_assoc()) {
        $perms[] = $row;
    }
    return $perms;
}
?>