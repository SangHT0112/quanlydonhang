<?php
include '../config.php';
checkLogin();
requirePermission('view_po');  // Thêm permission này cho list

$search = $_GET['search'] ?? '';
$trang_thai = $_GET['trang_thai'] ?? '';

// Xây dựng câu SQL tìm kiếm (sửa: prepared)
$sql = "SELECT p.ma_phieu_dat_hang, k.ten_khach_hang, p.ngay_dat, p.tong_tien, p.trang_thai 
        FROM phieu_dat_hang p 
        JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang 
        WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $sql .= " AND (k.ten_khach_hang LIKE ? OR p.ma_phieu_dat_hang LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}
if ($trang_thai) {
    $sql .= " AND p.trang_thai = ?";
    $params[] = $trang_thai;
    $types .= "s";
}

$sql .= " ORDER BY p.ngay_dat DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Phiếu Đặt Hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Danh Sách Phiếu Đặt Hàng</h1>
        <main>
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Tìm kiếm khách hàng..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="trang_thai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Chờ duyệt" <?php if ($trang_thai == 'Chờ duyệt') echo 'selected'; ?>>Chờ duyệt</option>
                        <option value="Đã duyệt" <?php if ($trang_thai == 'Đã duyệt') echo 'selected'; ?>>Đã duyệt</option>
                        <option value="Hủy" <?php if ($trang_thai == 'Hủy') echo 'selected'; ?>>Hủy</option>
                    </select>

                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <div class="actions-section">
                <?php if (hasPermission('create_po')): ?>
                    <a href="create.php" class="btn-primary">+ Tạo Phiếu Đặt Hàng Mới</a>
                <?php endif; ?>
            </div>

            <!-- THÊM ID="poTable" VÀO ĐÂY -->
            <table id="poTable" class="table">
                <thead>
                    <tr>
                        <th>Mã PO</th>
                        <th>Khách Hàng</th>
                        <th>Ngày Đặt</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $status_class = strtolower(str_replace([' ', 'ă', 'â', 'đ'], ['-', 'a', 'a', 'd'], $row['trang_thai']));  // Fix class cho VN chars
                            echo "<tr>";
                            echo "<td><strong>#" . $row['ma_phieu_dat_hang'] . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row['ten_khach_hang']) . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_dat'])) . "</td>";
                            echo "<td>" . formatMoney($row['tong_tien']) . " VNĐ</td>";
                            echo "<td><span class='status-" . $status_class . "'>" . $row['trang_thai'] . "</span></td>";
                            echo "<td>";
                            echo "<a href='detail.php?id=" . $row['ma_phieu_dat_hang'] . "' class='btn-info'>Xem</a> ";
                            if ($row['trang_thai'] == 'Chờ duyệt') {
                                if (hasPermission('edit_po')) {
                                    echo "<a href='edit.php?id=" . $row['ma_phieu_dat_hang'] . "' class='btn-warning'>Sửa</a> ";
                                }
                                if (hasPermission('delete_po')) {  // Fix: Dùng delete_po
                                    echo "<a href='delete.php?id=" . $row['ma_phieu_dat_hang'] . "' class='btn-danger' onclick='return confirm(\"Bạn chắc chắn muốn xóa?\")'>Xóa</a>";
                                }
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center;'>Không có phiếu đặt hàng</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- XÓA DUPLICATE: CHỈ GIỮ 1 SCRIPT SOCKET.IO -->
    <script src="http://localhost:4000/socket.io/socket.io.js"></script>
    <script>
    (function() {
        const userRole = '<?php echo $_SESSION["role"] ?? "guest"; ?>';
        const socket = io('http://localhost:4000');
        
        // FIX: Log room name đúng (không phải userRole)
        const room = (userRole === 'ketoan') ? 'ketoan' : 'sale';
        socket.emit('join-room', room);
        console.log('User role:', userRole, '→ Joined room:', room);

        // Listen event PO created (từ sale tạo)
        socket.on('po_created', function(data) {
            console.log('Received PO new:', data);
            

            // Fetch chi tiết PO mới qua AJAX (THÊM DEBUG LOG)
            console.log('Fetching PO detail for ID:', data.ma_phieu);
            $.get('get_po_detail.php?id=' + data.ma_phieu, function(poData) {
                console.log('AJAX success - PO data:', poData);  // DEBUG: Check poData
                
                if (poData.error) {
                    console.error('Error fetching PO:', poData.error);
                    return;
                }

                // Tạo row mới (dựa trên cấu trúc table của bạn)
                const statusClass = poData.trang_thai.toLowerCase().replace(/ /g, '-').replace(/[^a-z0-9-]/g, '');  // Fix class VN chars
                const newRow = `
                    <tr>
                        <td><strong>#${poData.ma_phieu_dat_hang}</strong></td>
                        <td>${poData.ten_khach_hang || 'N/A'}</td>
                        <td>${new Date(poData.ngay_dat).toLocaleDateString('vi-VN')}</td>
                        <td>${formatMoney(poData.tong_tien)} VNĐ</td>
                        <td><span class="status-$$ {statusClass}"> $${poData.trang_thai}</span></td>
                        <td>
                            <a href="detail.php?id=${poData.ma_phieu_dat_hang}" class="btn-info">Xem</a>
                            ${userRole === 'ketoan' && poData.trang_thai === 'Chờ duyệt' ? '<a href="approve.php?id=' + poData.ma_phieu_dat_hang + '" class="btn-primary ketoan-only">Duyệt</a>' : ''}
                        </td>
                    </tr>
                `;
                
                // Append vào tbody (prepend để mới nhất ở đầu) - GIỜ SẼ HOẠT ĐỘNG VÌ CÓ ID
                $('#poTable tbody').prepend(newRow);
                console.log('Row appended to table');  // DEBUG: Xác nhận append

            }).fail(function(xhr, status, error) {
                console.error('AJAX error fetching PO detail:', status, error, xhr.responseText);  // DEBUG CHI TIẾT
                location.reload();  // Fallback reload nếu lỗi
            });
        });

        // Listen thêm event submit (nếu cần)
        socket.on('po_submitted', function(data) {
            console.log('Received PO submitted:', data);
            // Tương tự: Fetch và update row trạng thái thành 'Chờ duyệt'
            const row = $(`#poTable tbody tr td strong:contains(#${data.ma_phieu})`).closest('tr');
            if (row.length) {
                row.find('.status').text('Chờ duyệt').removeClass().addClass('status-cho-duyet');
                console.log('Updated row status for PO:', data.ma_phieu);
            }
            // Notify tương tự...
        });

        // Helper function formatMoney (nếu chưa có)
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount);
        }
    })();
    </script>
</body>
</html>