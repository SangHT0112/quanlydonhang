<?php
if (!isset($_SESSION)) session_start();
?>
<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- CHAT BUTTON -->
<div id="chat-toggle"
     class="fixed bottom-6 right-6 z-[9999] w-14 h-14 rounded-full bg-blue-600
            text-white flex items-center justify-center text-2xl cursor-pointer shadow-lg">
    💬
    <span id="chat-badge"
          class="hidden absolute -top-1 -right-1 bg-red-600 text-xs px-2 py-0.5 rounded-full">
        
    </span>
</div>

<!-- CHAT BOX -->
<div id="chat-box"
     class="fixed bottom-24 right-6 z-[9999] w-80 bg-white rounded-xl shadow-2xl hidden flex-col">

    <div class="flex items-center justify-between px-4 py-3 bg-blue-600 text-white rounded-t-xl">
        <span class="font-semibold">📨 Thông báo nghiệp vụ</span>
        <button id="chat-close">✖</button>
    </div>

    <div id="chat-body"
         class="p-3 max-h-80 overflow-y-auto space-y-3 text-sm">
    </div>
</div>

<!-- CHAT SCRIPT - SỬA ĐỂ TRÁNH DUPLICATE VÀ FORMAT GỐC TỪ DB (GIỮ CÁI ĐẦU, BỎ CÁI 2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="http://localhost:4000/socket.io/socket.io.js"></script>

<script>
const socket = io('http://localhost:4000');
const role = '<?= $_SESSION["role"] ?? "guest" ?>';

// ← THÊM MỚI: Set để track message IDs đã load (tránh duplicate khi prepend)
let loadedMessageIds = new Set();

// Join room theo role
socket.emit('join-room', role);

// LOAD TIN NHẮN VÀ MARK READ + PHÂN BIỆT TRẠNG THÁI (CLEAR SET KHI LOAD)
function loadChat() {
    loadedMessageIds.clear();  // ← THÊM: Clear set khi load từ DB

    $.get('/chat/load_and_mark_read.php', function(res) {
        try {
            const data = JSON.parse(res);
            if (data.error) {
                console.error('Load chat error:', data.error);
                return;
            }

            $('#chat-body').html('');  // Clear cũ

            data.messages.forEach(m => {
                loadedMessageIds.add(m.id);  // ← THÊM: Add ID vào set

                // Phân biệt hiển thị dựa trên is_completed
                const statusClass = m.is_completed ? 'bg-green-100 border-l-4 border-green-500' : 'bg-yellow-100 border-l-4 border-yellow-500';
                const statusIcon = m.is_completed ? '✔️ Đã tạo hóa đơn' : '⏳ Chờ tạo hóa đơn';
                const statusText = m.is_completed ? ' (Hoàn thành)' : ' (Chưa hoàn thành)';

                $('#chat-body').append(`
                    <div class="${statusClass} p-3 rounded-lg ${m.is_read ? '' : 'animate-pulse'}" 
                        data-msg-id="${m.id}" 
                        data-ma-po="${m.ma_phieu_dat_hang || ''}">  <!-- ← THÊM: data-ma-po -->
                        <strong>${m.sender_role.toUpperCase()}:</strong> ${m.message}${statusText}
                        ${m.action_link ? `
                            <a href="${m.action_link}" class="block mt-2 text-blue-600 font-semibold hover:underline">
                                👉 Thực hiện
                            </a>` : ''}
                        <div class="text-xs text-gray-500 mt-1 flex justify-between">
                            <span>${m.created_at}</span>
                            <span class="font-medium status-icon ${m.is_completed ? 'text-green-600' : 'text-yellow-600'}">${statusIcon}</span>  <!-- ← THÊM: class cho icon dễ update -->
                            <span class="status-text">${statusText}</span>  <!-- ← THÊM: span cho text dễ update -->
                        </div>
                    </div>
                `);
            });

            if (data.updated_count > 0) {
                console.log(`Marked ${data.updated_count} messages as read.`);
            }

            updateBadge();

        } catch (e) {
            console.error('Parse chat response error:', e);
        }
    }).fail(function() {
        console.error('AJAX load chat failed');
    });
}

// LOAD UNREAD COUNT CHO BADGE
function updateBadge() {
    $.get('/chat/count_unread.php', function(res) {
        const count = parseInt(res) || 0;
        const $badge = $('#chat-badge');
        if (count > 0) {
            $badge.removeClass('hidden').text(count > 99 ? '99+' : count);
        } else {
            $badge.addClass('hidden');
        }
    }).fail(function() {
        console.error('Load unread count failed');
    });
}

// SOCKET LISTENER - KHI NHẬN MESSAGE MỚI (SỬA: CHECK ID TRONG SET/DOM, VÀ FORMAT GIỐNG DB - GIỮ CÁI ĐẦU)
socket.on('system_message', function(data) {
    console.log('New system message:', data);
    
    // ← SỬA: Check duplicate bằng ID (từ set hoặc DOM) - Nếu đã load từ DB, skip prepend
    if (data.id && (loadedMessageIds.has(data.id) || $(`[data-msg-id="${data.id}"]`).length > 0)) {
        console.log('Message already loaded from DB, skipping prepend:', data.id);
        updateBadge();  // Vẫn update badge
        return;
    }

    // Nếu chưa load, add vào set tạm (để tránh duplicate nếu loadChat() sau)
    if (data.id) {
        loadedMessageIds.add(data.id);
    }

    // ← SỬA: Sử dụng data.is_completed nếu có, mặc định false cho message mới
    const isCompleted = data.is_completed || false;
    const statusClass = isCompleted ? 'bg-green-100 border-l-4 border-green-500' : 'bg-yellow-100 border-l-4 border-yellow-500';
    const statusIcon = isCompleted ? '✔️ Đã tạo hóa đơn' : '⏳ Chờ tạo hóa đơn';
    const statusText = isCompleted ? ' (Hoàn thành)' : ' (Chưa hoàn thành)';

    // Chỉ prepend nếu chat đang mở
    if (!$('#chat-box').hasClass('hidden')) {
        $('#chat-body').prepend(`
            <div class="${statusClass} p-3 rounded-lg ${data.is_read ? '' : 'animate-pulse'}" data-msg-id="${data.id || ''}">
                <strong>${data.sender.toUpperCase()}:</strong> ${data.message}${statusText}
                ${data.link ? `
                    <a href="${data.link}"
                       class="block mt-2 text-blue-600 font-semibold hover:underline">
                       👉 Thực hiện  <!-- ← SỬA: Thống nhất text link với DB -->
                    </a>` : ''}
                <div class="text-xs text-gray-500 mt-1 flex justify-between">
                    <span>${data.time}</span>
                    <span class="font-medium">${statusIcon}</span>
                </div>
            </div>
        `);
        $('#chat-body').scrollTop(0);
    }

    updateBadge();  // Tăng badge cho unread mới
});

socket.on('hd_created', function(data) {
    console.log('HD created - Update chat status:', data);
    
    if (role !== 'ketoan') return;  // Chỉ ketoan update
    
    // Tìm tất cả div tin nhắn liên quan đến ma_po này (thêm data-ma-po vào div khi append)
    const $relatedMessages = $(`[data-ma-po="${data.ma_po}"]`);
    if ($relatedMessages.length > 0) {
        $relatedMessages.each(function() {
            const $msg = $(this);
            // Đổi class: Vàng → Xanh, icon/text
            $msg.removeClass('bg-yellow-100 border-l-4 border-yellow-500')
               .addClass('bg-green-100 border-l-4 border-green-500');
            $msg.find('.status-icon').text('✔️ Đã tạo hóa đơn').removeClass('text-yellow-600').addClass('text-green-600');
            $msg.find('.status-text').text(' (Hoàn thành)');
            
            // Optional: Remove animate-pulse nếu read
            $msg.removeClass('animate-pulse');
        });
        console.log(`Updated ${$relatedMessages.length} messages to completed for PO #${data.ma_po}`);
        
        // Update badge (giảm unread nếu có)
        updateBadge();
    }
});

// INIT: Load badge khi trang load
$(document).ready(function() {
    updateBadge();
});

// TOGGLE CHAT
$('#chat-toggle').on('click', function () {
    const isHidden = $('#chat-box').hasClass('hidden');
    $('#chat-box').toggleClass('hidden');
    
    if (isHidden) {
        loadChat();  // Load từ DB → Ưu tiên format đầy đủ (giữ cái đầu)
        $('#chat-badge').addClass('hidden');
    }
});

$('#chat-close').on('click', function () {
    $('#chat-box').addClass('hidden');
});
</script>