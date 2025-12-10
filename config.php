    <?php
    session_start();

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
        $sql = "SELECT id, username, full_name FROM users WHERE id = $id AND status = 1 LIMIT 1";
        $res = $conn->query($sql);
        return $res && $res->num_rows ? $res->fetch_assoc() : null;
    }

    // Kiểm tra user có role không
    function hasRole($roleName) {
        global $conn;
        if (!isset($_SESSION['user_id'])) return false;
        $userId = intval($_SESSION['user_id']);
        $roleNameEsc = $conn->real_escape_string($roleName);
        $sql = "SELECT 1 FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = $userId AND r.name = '$roleNameEsc' LIMIT 1";
        $res = $conn->query($sql);
        return ($res && $res->num_rows > 0);
    }

    // Kiểm tra user có permission không
    function hasPermission($permissionName) {
        global $conn;
        if (!isset($_SESSION['user_id'])) return false;
        $userId = intval($_SESSION['user_id']);
        $permissionNameEsc = $conn->real_escape_string($permissionName);
        $sql = "SELECT 1 FROM user_roles ur
                JOIN role_permissions rp ON ur.role_id = rp.role_id
                JOIN permissions p ON rp.permission_id = p.id
                WHERE ur.user_id = $userId AND p.name = '$permissionNameEsc' LIMIT 1";
        $res = $conn->query($sql);
        return ($res && $res->num_rows > 0);
    }

    // Yêu cầu permission, nếu không có thì phản hồi 403
    function requirePermission($permissionName) {
        if (!hasPermission($permissionName)) {
            http_response_code(403);
            echo '<html><head><meta charset="UTF-8"><style>body{font-family:Arial;text-align:center;margin-top:50px}h2{color:#ef4444}</style></head>';
            echo '<body><h2>403 - Không có quyền</h2><p>Bạn không có quyền để thực hiện hành động này.</p>';
            echo '<p><a href="javascript:history.back()">← Quay lại</a></p></body></html>';
            exit;
        }
    }

    // Lấy danh sách roles của user hiện tại
    function getUserRoles() {
        global $conn;
        if (!isset($_SESSION['user_id'])) return [];
        $userId = intval($_SESSION['user_id']);
        $sql = "SELECT r.id, r.name, r.description FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = $userId ORDER BY r.name";
        $res = $conn->query($sql);
        $roles = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $roles[] = $row;
            }
        }
        return $roles;
    }

    // Lấy danh sách permissions của user hiện tại
    function getUserPermissions() {
        global $conn;
        if (!isset($_SESSION['user_id'])) return [];
        $userId = intval($_SESSION['user_id']);
        $sql = "SELECT DISTINCT p.id, p.name, p.description FROM user_roles ur
                JOIN role_permissions rp ON ur.role_id = rp.role_id
                JOIN permissions p ON rp.permission_id = p.id
                WHERE ur.user_id = $userId ORDER BY p.name";
        $res = $conn->query($sql);
        $perms = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $perms[] = $row;
            }
        }
        return $perms;
    }
    ?>