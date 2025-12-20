<?php
$current_file = basename($_SERVER['PHP_SELF']);
$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<header>
    <div class="header-top">
        <h1>📊 Hệ Thống Quản Lý Đơn Hàng</h1>
        <div class="user-info">
            <span>Xin chào: <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Khách'); ?></span>
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
            <?php if (hasPermission('view_po')): ?>
                <li><a href="/phieu_dat_hang/list.php" class="<?= strpos($current_path,'/phieu_dat_hang')!==false ? 'active' : '' ?>">Phiếu Đặt Hàng</a></li>
            <?php endif; ?>
            <?php if (hasPermission('create_bh')): ?>
                <li><a href="/phieu_ban_hang/list.php" class="<?= strpos($current_path,'/phieu_ban_hang')!==false ? 'active' : '' ?>">Phiếu Bán Hàng</a></li>
            <?php endif; ?>
            <?php if (hasPermission('execute_pxk') || hasRole('kho')): ?>
                <li><a href="/phieu_xuat_kho/list.php" class="<?= strpos($current_path,'/phieu_xuat_kho')!==false ? 'active' : '' ?>">Phiếu Xuất Kho</a></li>
            <?php endif; ?>
            <?php if (hasPermission('create_invoice') || hasPermission('issue_invoice') || hasPermission('view_invoice' )): ?>
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
<!-- THÊM MỚI: Global Socket.IO cho notify real-time (chỉ kế toán, ở tất cả trang có header) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> <!-- Global jQuery nếu chưa có -->
<script src="http://localhost:4000/socket.io/socket.io.js"></script>
<script>
(function() {
    // Chỉ chạy nếu đã login và role là 'ketoan'
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    const userRole = '<?php echo $_SESSION["role"] ?? "guest"; ?>';
   
    if (!isLoggedIn || userRole !== 'ketoan') {
        return; // Không cần socket nếu chưa login hoặc không phải kế toán
    }
   
    const socket = io('http://localhost:4000');
   
    // Join room 'ketoan' để nhận PO mới
    const room = 'ketoan';
    socket.emit('join-room', room);
    console.log('Global socket joined room:', room, 'for role:', userRole);
    
    // Listen event PO created (từ sale tạo) - GLOBAL NOTIFY
    socket.on('po_created', function(data) {
        console.log('Global notify: Received PO new:', data);
       
        // Hiển thị toast notify (global, không phụ thuộc trang)
        const toast = $('<div id="global-toast" class="alert alert-success" style="position:fixed;top:20px;right:20px;z-index:9999;padding:15px;border-radius:5px;box-shadow:0 4px 12px rgba(0,0,0,0.15);max-width:300px;background:#d4edda;color:#155724;border:1px solid #c3e6cb;">' +
                       '<strong>PO Mới!</strong><br>' + data.message +
                       '<br><small><a href="/phieu_dat_hang/list.php" style="color:#155724;text-decoration:underline;">Xem danh sách ngay</a></small>' +
                       '</div>');
        $('body').append(toast);
       
        // Auto hide sau 5s, hoặc click để close
        setTimeout(() => {
            toast.fadeOut(500, () => toast.remove());
        }, 5000);
        toast.on('click', () => {
            toast.fadeOut(500, () => toast.remove());
        });
       
        // Optional: Play sound notify (nếu muốn thêm âm thanh)
        // const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO/n4rY8sNqJQUsgc4='); // Base64 cho sound ngắn
        // audio.play().catch(e => console.log('Could not play audio:', e));
    });
    // 🔔 PO đã duyệt → kế toán lập hóa đơn
   // 🔔 PO đã duyệt → kế toán lập hóa đơn (GREEN)
    socket.on('po_approved', function(data) {
        console.log('PO approved:', data);

        const toast = $(`
            <div style="
                position:fixed;
                top:20px;
                right:20px;
                z-index:9999;
                padding:15px 18px;
                border-radius:8px;
                max-width:320px;
                background:linear-gradient(135deg,#28a745,#5dd879);
                color:#ffffff;
                box-shadow:0 6px 16px rgba(0,0,0,0.25);
                font-weight:500;
                cursor:pointer;
            ">
                <strong style="font-size:16px;">✔ PO ĐÃ DUYỆT</strong><br>
                <span style="display:block;margin-top:4px;">${data.message}</span>
                <small style="display:block;margin-top:8px;">
                    <a href="/hoa_don/create.php?po_id=${data.ma_phieu}"
                    style="color:#ffffff;text-decoration:underline;font-weight:600;">
                        Lập hóa đơn ngay →
                    </a>
                </small>
            </div>
        `);

        $('body').append(toast);

        // Auto hide sau 6s
        setTimeout(() => {
            toast.fadeOut(500, () => toast.remove());
        }, 6000);

        // Click để đóng nhanh
        toast.on('click', () => {
            toast.fadeOut(300, () => toast.remove());
        });
    });


})();
</script>