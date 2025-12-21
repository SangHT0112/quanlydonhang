<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

$search = $_GET['search'] ?? '';
$trang_thai = $_GET['trang_thai'] ?? '';

// Xử lý AJAX update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_customer') {
    header('Content-Type: application/json');
    $id = intval($_POST['ma_khach_hang']);
    $ten_kh = trim($_POST['ten_khach_hang']);
    $dia_chi = trim($_POST['dia_chi'] ?? '');
    $dien_thoai = trim($_POST['dien_thoai'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $trang_thai = $_POST['trang_thai'];

    if (empty($ten_kh)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên khách hàng']);
        exit;
    }

    $sql = "UPDATE khach_hang SET ten_khach_hang = ?, dia_chi = ?, dien_thoai = ?, email = ?, trang_thai = ? WHERE ma_khach_hang = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $ten_kh, $dia_chi, $dien_thoai, $email, $trang_thai, $id);
    
    if ($stmt->execute()) {
        logActivity('UPDATE_CUSTOMER', 'Cập nhật khách hàng: ' . $ten_kh);
        echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật: ' . $stmt->error]);
    }
    exit;
}

// Lấy danh sách khách hàng
$sql = "SELECT * FROM khach_hang WHERE 1=1";
if ($search) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (ten_khach_hang LIKE '%$search%' OR dien_thoai LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($trang_thai) {
    $trang_thai = $conn->real_escape_string($trang_thai);
    $sql .= " AND trang_thai = '$trang_thai'";
}
$sql .= " ORDER BY ngay_tao DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Khách Hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        /* Giữ nguyên CSS bạn đã có, chỉ thêm phần modal */
        
        /* Modal Overlay */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        /* Modal Content */
        .modal {
            background: white;
            width: 90%;
            max-width: 600px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: slideUp 0.4s ease;
        }

        .modal-header {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 24px;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-body .form-group {
            margin-bottom: 20px;
        }

        .modal-body label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        .modal-body input,
        .modal-body textarea,
        .modal-body select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .modal-body input:focus,
        .modal-body textarea:focus,
        .modal-body select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
        }

        .modal-body .info-item {
            margin-bottom: 16px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #6366f1;
        }

        .modal-body .info-item label {
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
            color: #4b5563;
            font-size: 14px;
        }

        .modal-body .info-item span {
            color: #1f2937;
            font-size: 16px;
        }

        .modal-footer {
            padding: 20px 30px;
            background: #f8fafc;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>
        <?php include '../chat/chat.php'; ?>
    <div class="container">
        <h1>Danh Sách Khách Hàng</h1>

        <main>
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Tìm kiếm tên, SĐT, email..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="trang_thai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Hoạt động" <?php if ($trang_thai == 'Hoạt động') echo 'selected'; ?>>Hoạt động</option>
                        <option value="Ngừng hoạt động" <?php if ($trang_thai == 'Ngừng hoạt động') echo 'selected'; ?>>Ngừng hoạt động</option>
                    </select>
                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <div class="actions-section">
                <a href="create.php" class="btn-primary">+ Thêm Khách Hàng Mới</a>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Tên Khách Hàng</th>
                        <th>Điện Thoại</th>
                        <th>Email</th>
                        <th>Địa Chỉ</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="customer-row"
                                data-id="<?php echo $row['ma_khach_hang']; ?>"
                                data-ten="<?php echo htmlspecialchars($row['ten_khach_hang']); ?>"
                                data-dienthoai="<?php echo htmlspecialchars($row['dien_thoai'] ?? ''); ?>"
                                data-email="<?php echo htmlspecialchars($row['email'] ?? ''); ?>"
                                data-diachi="<?php echo htmlspecialchars($row['dia_chi'] ?? ''); ?>"
                                data-trangthai="<?php echo htmlspecialchars($row['trang_thai']); ?>"
                                data-ngaytao="<?php echo date('d/m/Y H:i:s', strtotime($row['ngay_tao'])); ?>">
                                <td><strong><?php echo htmlspecialchars($row['ten_khach_hang']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['dien_thoai'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['dia_chi'] ?? ''); ?></td>
                                <td><span class="status-<?php echo strtolower(str_replace(' ', '-', $row['trang_thai'])); ?>">
                                    <?php echo $row['trang_thai']; ?>
                                </span></td>
                                <td><?php echo date('d/m/Y', strtotime($row['ngay_tao'])); ?></td>
                                <td>
                                    <button class="btn-warning edit-btn" 
                                            data-id="<?php echo $row['ma_khach_hang']; ?>"
                                            data-ten="<?php echo htmlspecialchars($row['ten_khach_hang']); ?>"
                                            data-dienthoai="<?php echo htmlspecialchars($row['dien_thoai'] ?? ''); ?>"
                                            data-email="<?php echo htmlspecialchars($row['email'] ?? ''); ?>"
                                            data-diachi="<?php echo htmlspecialchars($row['dia_chi'] ?? ''); ?>"
                                            data-trangthai="<?php echo htmlspecialchars($row['trang_thai']); ?>">
                                        Sửa
                                    </button>
                                    <a href="delete.php?id=<?php echo $row['ma_khach_hang']; ?>" 
                                       class="btn-danger" 
                                       onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">Không có khách hàng</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal Sửa Khách Hàng -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">Sửa Thông Tin Khách Hàng</div>
            <div class="modal-body">
                <div id="modalAlert"></div>
                <form id="editForm">
                    <input type="hidden" name="ma_khach_hang" id="editId">
                    <input type="hidden" name="action" value="update_customer">

                    <div class="form-group">
                        <label>Tên Khách Hàng</label>
                        <input type="text" name="ten_khach_hang" id="editTen" required>
                    </div>
                    <div class="form-group">
                        <label>Điện Thoại</label>
                        <input type="tel" name="dien_thoai" id="editDienThoai">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="editEmail">
                    </div>
                    <div class="form-group">
                        <label>Địa Chỉ</label>
                        <textarea name="dia_chi" id="editDiaChi" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Trạng Thái</label>
                        <select name="trang_thai" id="editTrangThai">
                            <option value="Hoạt động">Hoạt động</option>
                            <option value="Ngừng hoạt động">Ngừng hoạt động</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="closeModal">Hủy</button>
                <button type="button" class="btn-primary" id="saveEdit">Cập Nhật</button>
            </div>
        </div>
    </div>

    <!-- Modal Xem Thông Tin Khách Hàng -->
    <div class="modal-overlay" id="viewModal">
        <div class="modal">
            <div class="modal-header">Thông Tin Khách Hàng</div>
            <div class="modal-body">
                <div id="viewContent">
                    <!-- Nội dung sẽ được điền bằng JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="closeViewModal">Đóng</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('editModal');
        const viewModal = document.getElementById('viewModal');
        const alertBox = document.getElementById('modalAlert');
        const viewContent = document.getElementById('viewContent');

        // Mở modal xem thông tin khi click vào hàng (trừ nút hành động)
        document.querySelectorAll('.customer-row').forEach(row => {
            row.addEventListener('click', (e) => {
                // Bỏ qua nếu click vào nút Sửa hoặc liên kết Xóa
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.closest('BUTTON') || e.target.closest('A')) {
                    return;
                }

                const id = row.dataset.id;
                const ten = row.dataset.ten;
                const dienThoai = row.dataset.dienthoai;
                const email = row.dataset.email;
                const diaChi = row.dataset.diachi;
                const trangThai = row.dataset.trangthai;
                const ngayTao = row.dataset.ngaytao;

                const html = `
                    <div class="info-item">
                        <label>Mã Khách Hàng</label>
                        <span>${id}</span>
                    </div>
                    <div class="info-item">
                        <label>Tên Khách Hàng</label>
                        <span>${ten}</span>
                    </div>
                    <div class="info-item">
                        <label>Điện Thoại</label>
                        <span>${dienThoai || 'Chưa cập nhật'}</span>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <span>${email || 'Chưa cập nhật'}</span>
                    </div>
                    <div class="info-item">
                        <label>Địa Chỉ</label>
                        <span>${diaChi || 'Chưa cập nhật'}</span>
                    </div>
                    <div class="info-item">
                        <label>Trạng Thái</label>
                        <span class="status-${trangThai.toLowerCase().replace(' ', '-')}">${trangThai}</span>
                    </div>
                    <div class="info-item">
                        <label>Ngày Tạo</label>
                        <span>${ngayTao}</span>
                    </div>
                `;

                viewContent.innerHTML = html;
                viewModal.style.display = 'flex';
            });
        });

        // Mở modal sửa
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('editId').value = btn.dataset.id;
                document.getElementById('editTen').value = btn.dataset.ten;
                document.getElementById('editDienThoai').value = btn.dataset.dienthoai;
                document.getElementById('editEmail').value = btn.dataset.email;
                document.getElementById('editDiaChi').value = btn.dataset.diachi;
                document.getElementById('editTrangThai').value = btn.dataset.trangthai;

                alertBox.innerHTML = '';
                modal.style.display = 'flex';
            });
        });

        // Đóng modal sửa
        document.getElementById('closeModal').addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Đóng modal xem
        document.getElementById('closeViewModal').addEventListener('click', () => {
            viewModal.style.display = 'none';
        });

        // Click ngoài modal để đóng (cả hai modal)
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.style.display = 'none';
        });

        viewModal.addEventListener('click', (e) => {
            if (e.target === viewModal) viewModal.style.display = 'none';
        });

        // Lưu sửa
        document.getElementById('saveEdit').addEventListener('click', async () => {
            const formData = new FormData(document.getElementById('editForm'));

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alertBox.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alertBox.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
                }
            } catch (err) {
                alertBox.innerHTML = `<div class="alert alert-error">Lỗi kết nối</div>`;
            }
        });
    </script>
</body>
</html>