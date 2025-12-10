<?php
include 'config.php';

if (isset($_SESSION['user_id'])) {
    logActivity('LOGOUT', 'Đăng xuất');
    session_destroy();
}

header('Location: login.php');
exit;
?>