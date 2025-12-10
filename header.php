<?php

$current_file = basename($_SERVER['PHP_SELF']);
$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

?>

<header>
    <div class="header-top">
        <h1>📊 Hệ Thống Quản Lý Đơn Hàng</h1>
        <div class="user-info">
            <span>Xin chào: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="/logout.php" class="btn-logout">Đăng Xuất</a>
        </div>
    </div>

    <nav class="navbar">
        <ul>
            <li><a href="/index.php" class="<?= $current_file=='index.php' ? 'active' : '' ?>">Trang Chủ</a></li>

            <?php if (hasPermission('manage_users')): ?>
                <li><a href="/khach_hang/list.php" class="<?= strpos($current_path,'/khach_hang')!==false ? 'active' : '' ?>">Khách Hàng</a></li>
            <?php endif; ?>

            <?php if (hasPermission('manage_users')): ?>
                <li><a href="/san_pham/list.php" class="<?= strpos($current_path,'/san_pham')!==false ? 'active' : '' ?>">Sản Phẩm</a></li>
            <?php endif; ?>

            <?php if (hasPermission('create_po') || hasPermission('approve_po')): ?>
                <li><a href="/phieu_dat_hang/list.php" class="<?= strpos($current_path,'/phieu_dat_hang')!==false ? 'active' : '' ?>">Phiếu Đặt Hàng</a></li>
            <?php endif; ?>

            <?php if (hasPermission('create_bh')): ?>
                <li><a href="/phieu_ban_hang/list.php" class="<?= strpos($current_path,'/phieu_ban_hang')!==false ? 'active' : '' ?>">Phiếu Bán Hàng</a></li>
            <?php endif; ?>

            <?php if (hasPermission('create_invoice') || hasPermission('issue_invoice')): ?>
                <li><a href="/hoa_don/list.php" class="<?= strpos($current_path,'/hoa_don')!==false ? 'active' : '' ?>">Hóa Đơn</a></li>
            <?php endif; ?>

            <?php if (hasPermission('record_payment')): ?>
                <li><a href="/thanh_toan/list.php" class="<?= strpos($current_path,'/thanh_toan')!==false ? 'active' : '' ?>">Thanh Toán</a></li>
            <?php endif; ?>

            <?php if (hasPermission('create_return') || hasPermission('approve_return') || hasRole('kho')): ?>
                <li><a href="/tra_hang/list.php" class="<?= strpos($current_path,'/tra_hang')!==false ? 'active' : '' ?>">Trả Hàng</a></li>
            <?php endif; ?>

            <?php if (hasRole('kho')): ?>
                <li><a href="/ton_kho/list.php" class="<?= strpos($current_path,'/ton_kho')!==false ? 'active' : '' ?>">Tồn Kho</a></li>
            <?php endif; ?>

            <?php if (hasPermission('manage_users')): ?>
                <li><a href="/admin/users.php" class="<?= strpos($current_path,'/admin')!==false ? 'active' : '' ?>">Quản Trị</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
