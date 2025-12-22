<?php
$current_file = basename($_SERVER['PHP_SELF']);
$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>


<header>
    <div class="header-inner">
         <div class="header-top">
            <h1>📊 Hệ Thống Quản Lý Đơn Hàng</h1>
            
            <div class="user-info">
                <!-- Tên người dùng nổi bật -->
                <div class="user-greeting">
                    <i class="fas fa-user-circle user-icon"></i>
                    <div class="user-text">
                        <span>Xin chào</span>
                        <strong class="username">
                         <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Guest'); ?></span>
                           
                        </strong>
                    </div>
                </div>

                <!-- Nút Đăng Xuất siêu đẹp -->
                <a href="/logout.php" class="btn-logout-premium">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng Xuất</span>
                </a>
            </div>
        </div>

        <nav class="navbar">
            <ul>
                <li><a href="/index.php" data-url="/index.php" class="<?= $current_file == 'index.php' ? 'active' : '' ?>">Trang Chủ</a></li>
                
                <?php if (hasPermission('manage_users')): ?>
                    <li><a href="/khach_hang/list.php" data-url="/khach_hang/list.php" class="<?= strpos($current_path, '/khach_hang') !== false ? 'active' : '' ?>">Khách Hàng</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('create_po')): ?>
                    <li><a href="/san_pham/list.php" data-url="/san_pham/list.php" class="<?= strpos($current_path, '/san_pham') !== false ? 'active' : '' ?>">Sản Phẩm</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('view_po')): ?>
                    <li><a href="/phieu_dat_hang/list.php" data-url="/phieu_dat_hang/list.php" class="<?= strpos($current_path, '/phieu_dat_hang') !== false ? 'active' : '' ?>">Phiếu Đặt Hàng</a></li>
                <?php endif; ?>
            
                
                <?php if (hasPermission('execute_pxk') || hasRole('kho')): ?>
                    <li><a href="/phieu_xuat_kho/list.php" data-url="/phieu_xuat_kho/list.php" class="<?= strpos($current_path, '/phieu_xuat_kho') !== false ? 'active' : '' ?>">Phiếu Xuất Kho</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('create_invoice') || hasPermission('issue_invoice') || hasPermission('view_invoice')): ?>
                    <li><a href="/hoa_don/list.php" data-url="/hoa_don/list.php" class="<?= strpos($current_path, '/hoa_don') !== false ? 'active' : '' ?>">Hóa Đơn</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('record_payment')): ?>
                    <li><a href="/thanh_toan/list.php" data-url="/thanh_toan/list.php" class="<?= strpos($current_path, '/thanh_toan') !== false ? 'active' : '' ?>">Thanh Toán</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('create_return') || hasPermission('approve_return') || hasRole('kho') || hasRole('ketoan')): ?>
                    <li><a href="/tra_hang/list.php" data-url="/tra_hang/list.php" class="<?= strpos($current_path, '/tra_hang') !== false ? 'active' : '' ?>">Trả Hàng</a></li>
                <?php endif; ?>
                
                <?php if (hasRole('kho')): ?>
                    <li><a href="/ton_kho/list.php" data-url="/ton_kho/list.php" class="<?= strpos($current_path, '/ton_kho') !== false ? 'active' : '' ?>">Tồn Kho</a></li>
                <?php endif; ?>
                
                <?php if (hasPermission('manage_users')): ?>
                    <li><a href="/admin/users.php" data-url="/admin/users.php" class="<?= strpos($current_path, '/admin') !== false ? 'active' : '' ?>">Quản Trị</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- CSS NÂNG CẤP CHO USER INFO & BTN LOGOUT -->
<style>
.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 24px;
}

/* Tên người dùng - Nổi bật, sang trọng */
.user-greeting {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.18);
    padding: 10px 18px;
    border-radius: 16px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.user-greeting:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
}

.user-icon {
    font-size: 32px;
    color: #ffffff;
    opacity: 0.9;
}

.user-text {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
}

.user-text span {
    font-size: 13px;
    opacity: 0.8;
    color: #f0f0f0;
}

.username {
    font-size: 18px;
    font-weight: 700;
    color: #ffffff;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

/* Nút Đăng Xuất - Siêu đẹp, hiện đại */
.btn-logout-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white !important;
    padding: 12px 22px;
    border-radius: 16px;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    transition: all 0.35s ease;
    border: none;
}

.btn-logout-premium i {
    font-size: 18px;
}

.btn-logout-premium:hover {
    background: linear-gradient(135deg, #c82333, #bd2130);
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(220, 53, 69, 0.5);
}

.btn-logout-premium:active {
    transform: translateY(-1px);
}

/* Responsive - Mobile */
@media (max-width: 768px) {
    .header-top {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }
    
    .user-info {
        flex-direction: column;
        gap: 16px;
    }
    
    .user-greeting {
        padding: 12px 20px;
    }
    
    .username {
        font-size: 20px;
    }
}

/* THÊM MỚI: Smooth Transition cho Main Content */
#main-content {
    opacity: 1;
    transition: opacity 0.3s ease-in-out;
}

#main-content.loading {
    opacity: 0.6;
    pointer-events: none;
}

#main-content.fade-out {
    opacity: 0;
}
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> <!-- Global jQuery nếu chưa có -->
<script src="http://localhost:4000/socket.io/socket.io.js"></script>
<script>
(function() {
    // Chỉ chạy nếu đã login. Join room theo role để mỗi role nhận được event riêng
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    const userRole = '<?php echo $_SESSION["role"] ?? "guest"; ?>';

    if (!isLoggedIn) {
        return; // Không cần socket nếu chưa login
    }

    const socket = io('http://localhost:4000');

    // Join room theo role hiện tại (ví dụ: 'ketoan', 'kho', 'sale'...)
    const room = userRole || 'global';
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
        }, 10000);
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
                    <a href="/hoa_don/create.php?ma_po=${data.ma_phieu}"
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
        }, 10000);

        // Click để đóng nhanh
        toast.on('click', () => {
            toast.fadeOut(300, () => toast.remove());
        });
    });

    // 🔔 THÊM MỚI: Hóa đơn đã tạo → kho lập phiếu xuất kho (ORANGE/YELLOW)
    if (userRole === 'kho') {
        socket.on('hd_created', function(data) {
            console.log('HD created:', data);

            const toast = $(`
                <div style="
                    position:fixed;
                    top:20px;
                    right:20px;
                    z-index:9999;
                    padding:15px 18px;
                    border-radius:8px;
                    max-width:320px;
                    background:linear-gradient(135deg,#fd7e14,#ffc107);
                    color:#ffffff;
                    box-shadow:0 6px 16px rgba(0,0,0,0.25);
                    font-weight:500;
                    cursor:pointer;
                ">
                    <strong style="font-size:16px;">📦 HÓA ĐƠN MỚI</strong><br>
                    <span style="display:block;margin-top:4px;">${data.message}</span>
                    <small style="display:block;margin-top:8px;">
                        <a href="/phieu_xuat_kho/create.php?ma_hoa_don=${data.ma_hoa_don}"
                        style="color:#ffffff;text-decoration:underline;font-weight:600;">
                            Lập phiếu xuất kho ngay →
                        </a>
                    </small>
                </div>
            `);

            $('body').append(toast);

            // Auto hide sau 6s
            setTimeout(() => {
                toast.fadeOut(500, () => toast.remove());
            }, 10000);

            // Click để đóng nhanh
            toast.on('click', () => {
                toast.fadeOut(300, () => toast.remove());
            });
        });
    }

    // socket.on('system_message', function(data) {
    //     // Always show a lightweight global toast in addition to chat widget updates.
    //     // This ensures roles like `kho` see a visible notification even when the chat box exists.
    //     try {
    //         const toast = $(`
    //             <div style="position:fixed;top:20px;right:20px;z-index:9999;padding:12px;border-radius:8px;background:#fff;border:1px solid #ddd;box-shadow:0 6px 16px rgba(0,0,0,0.12);max-width:360px;">
    //                 <strong>Thông báo:</strong><br>${data.message}
    //                 ${data.link ? `<div style="margin-top:8px"><a href="${data.link}" style="text-decoration:underline;color:#0d6efd">👉 Mở</a></div>` : ''}
    //             </div>
    //         `);
    //         $('body').append(toast);
    //         setTimeout(() => { toast.fadeOut(300, () => toast.remove()); }, 7000);
    //         toast.on('click', () => { toast.fadeOut(200, () => toast.remove()); });
    //     } catch (e) {
    //         console.log('Error showing system_message toast', e, data);
    //     }
    // });


})();

// THÊM MỚI: Smooth Page Transition với AJAX + History API (Sử dụng jQuery vì đã có)
$(document).ready(function() {
    // Chặn click trên nav links và load động
    $('nav a[data-url]').on('click', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        loadPage(url, $(this));
    });

    // Handle browser back/forward
    $(window).on('popstate', function(e) {
        if (e.originalEvent.state) {
            loadPage(location.pathname + location.search, null, true); // Không update active
        }
    });

    // Function load page mượt mà
    function loadPage(url, clickedLink = null, noHistory = false) {
        const $main = $('#main-content');
        if (!$main.length) {
            // Fallback nếu chưa có #main-content (trang đầu)
            window.location.href = url;
            return;
        }

        // Add loading state
        $main.addClass('loading fade-out');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                // Parse HTML response để lấy chỉ phần <main>
                const $temp = $('<div>').html(data);
                const $newMain = $temp.find('main').first(); // Giả sử nội dung chính trong <main>

                if ($newMain.length) {
                    // Update active class cho menu
                    if (clickedLink) {
                        $('nav a').removeClass('active');
                        clickedLink.addClass('active');
                    }

                    // Replace content với transition
                    $main.fadeOut(200, function() {
                        $main.html($newMain.html()).fadeIn(300);
                        $main.removeClass('loading fade-out');
                    });

                    // Update URL history (nếu không phải popstate)
                    if (!noHistory) {
                        history.pushState({url: url}, '', url);
                    }

                    // Re-init scripts nếu cần (ví dụ: socket events, tooltips...)
                    // $(document).trigger('pageLoaded'); // Có thể dùng event custom nếu cần
                } else {
                    // Fallback nếu không parse được
                    window.location.href = url;
                }
            },
            error: function() {
                $main.removeClass('loading fade-out');
                alert('Lỗi tải trang. Đang reload...');
                window.location.href = url;
            }
        });
    }

    // Đăng ký event cho các link khác (nếu cần, ví dụ trong content)
    $(document).on('click', 'a:not([data-url]):not(.btn-logout-premium):not([target])', function(e) {
        const href = $(this).attr('href');
        if (href && href.startsWith('/') && !href.includes('#')) {
            e.preventDefault();
            loadPage(href);
        }
    });
});
</script>
