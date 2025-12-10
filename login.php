<?php
include 'config.php';

$error = '';
$success = '';

if ($_POST) {
    try {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $action = $_POST['action'] ?? 'login';

        if (empty($username) || empty($password)) {
            throw new Exception('Vui lòng nhập tên đăng nhập và mật khẩu');
        }

        if ($action == 'login') {

            $sql = "SELECT id, username, password, full_name, status 
                    FROM users 
                    WHERE username = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if ($user['status'] == 0) {
                    throw new Exception('Tài khoản này đã bị khóa. Liên hệ quản trị viên.');
                }

                // ❗ KIỂM TRA MẬT KHẨU THƯỜNG
                if ($password === $user['password']) {

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['username'] = $user['username'];

                    logActivity("LOGIN", "Đăng nhập thành công");
                    header("Location: index.php");
                    exit;

                } else {
                    throw new Exception("Tên đăng nhập hoặc mật khẩu không chính xác");
                }
            } else {
                throw new Exception("Tên đăng nhập hoặc mật khẩu không chính xác");
            }
        }

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
    <title>Đăng Nhập - Hệ Thống Quản Lý Đơn Hàng</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-login:hover {
            background-color: #764ba2;
        }

        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .demo-info {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 12px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid #93c5fd;
        }

        .demo-info strong {
            display: block;
            margin-bottom: 5px;
        }

        .footer {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>📊 Quản Lý Đơn Hàng</h1>
            <p>Hệ Thống Quản Lý Bán Hàng Toàn Diện</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="demo-info">
            <strong>Demo Account:</strong>
            Tài khoản: <strong>admin</strong><br>
            Mật khẩu: <strong>admin123</strong>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="username">Tên Đăng Nhập:</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mật Khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <input type="hidden" name="action" value="login">

            <button type="submit" class="btn-login">Đăng Nhập</button>
        </form>

        <div class="footer">
            <p>&copy; 2025 Hệ Thống Quản Lý Đơn Hàng</p>
        </div>
    </div>
</body>
</html>