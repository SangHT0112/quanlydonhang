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
        !
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="http://localhost:4000/socket.io/socket.io.js"></script>

<script>
const socket = io('http://localhost:4000');
const role = '<?= $_SESSION["role"] ?? "guest" ?>';

// join room theo role
socket.emit('join-room', role);

// LOAD TIN TỪ DB
function loadChat() {
    $.get('/chat/load.php', function(res) {
        const data = JSON.parse(res);
        $('#chat-body').html('');

        data.forEach(m => {
            $('#chat-body').append(`
                <div class="bg-gray-100 p-3 rounded-lg">
                    <strong>${m.sender_role.toUpperCase()}:</strong> ${m.message}
                    ${m.action_link ? `
                        <a href="${m.action_link}"
                           class="block mt-2 text-blue-600 font-semibold hover:underline">
                           👉 Thực hiện
                        </a>` : ''}
                    <div class="text-xs text-gray-500 mt-1">${m.created_at}</div>
                </div>
            `);
        });
    });
}

// REALTIME MESSAGE
socket.on('system_message', function(data) {
    $('#chat-body').prepend(`
        <div class="bg-green-100 p-3 rounded-lg">
            <strong>${data.sender.toUpperCase()}:</strong> ${data.message}
            ${data.link ? `
                <a href="${data.link}"
                   class="block mt-2 text-green-700 font-semibold hover:underline">
                   👉 Thực hiện
                </a>` : ''}
            <div class="text-xs text-gray-500 mt-1">${data.time}</div>
        </div>
    `);
    $('#chat-badge').removeClass('hidden');
});

// TOGGLE
$('#chat-toggle').on('click', function () {
    $('#chat-box').toggleClass('hidden');
    $('#chat-badge').addClass('hidden');
    loadChat();
});

$('#chat-close').on('click', function () {
    $('#chat-box').addClass('hidden');
});
</script>
