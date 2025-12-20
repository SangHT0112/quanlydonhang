<?php
include '../config.php';
checkLogin();

$search = $_GET['search'] ?? '';

// XỬ LÝ AJAX: Cập nhật sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_product') {
    header('Content-Type: application/json');

    $id         = intval($_POST['ma_san_pham']);
    $ten_sp     = trim($_POST['ten_san_pham']);
    $gia_ban    = floatval($_POST['gia_ban']);
    $don_vi     = trim($_POST['don_vi'] ?? 'Cái');
    $mo_ta      = trim($_POST['mo_ta'] ?? '');

    if ($ten_sp === '' || $gia_ban < 0) {
        echo json_encode(['success' => false, 'message' => 'Tên sản phẩm và giá bán không hợp lệ']);
        exit;
    }

    try {
        $conn->begin_transaction();

        $sql = "UPDATE san_pham SET ten_san_pham = ?, gia_ban = ?, don_vi = ?, mo_ta = ? WHERE ma_san_pham = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssi", $ten_sp, $gia_ban, $don_vi, $mo_ta, $id);

        if (!$stmt->execute()) {
            throw new Exception('Lỗi cập nhật sản phẩm');
        }

        logActivity('UPDATE_PRODUCT', "Cập nhật sản phẩm #$id - $ten_sp");

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Lấy danh sách sản phẩm
$sql = "SELECT * FROM san_pham WHERE 1=1";
if ($search) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND ten_san_pham LIKE '%$search%'";
}
$sql .= " ORDER BY ngay_tao DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Sản Phẩm</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        /* ==================== CHUNG & ĐẸP HƠN ==================== */
        main {
            flex: 1;
            padding: 40px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            min-height: 100vh;
        }

        main > h1 {
            font-size: 32px;
            font-weight: 800;
            color: #4338ca;
            margin-bottom: 10px;
            text-align: center;
        }

        /* Filter & Actions */
        .filter-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border: 1px solid #e0e7ff;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .filter-form input {
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
        }

        .filter-form input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.15);
        }

        .actions-section {
            text-align: right;
            margin-bottom: 30px;
        }

        .actions-section .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 16px 32px;
            font-size: 18px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(102,126,234,0.3);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        /* Table */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            border: 1px solid #e0e7ff;
        }

        .table thead {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .table th {
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
            padding: 20px 24px;
            text-align: left;
        }

        .table th:last-child, .table td:last-child { text-align: center; }

        .table tbody tr:hover {
            background: linear-gradient(to right, #f8f9ff, #f0f4ff);
            transform: scale(1.01);
            box-shadow: 0 8px 20px rgba(102,126,234,0.1);
            transition: all 0.3s ease;
        }

        .table td {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .table td strong { font-size: 18px; color: #1e293b; }

        .table .price {
            text-align: right;
            font-weight: 700;
            color: #dc2626;
        }

        .table .btn-warning, .table .btn-danger {
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            margin: 0 5px;
            transition: all 0.3s;
        }

        .table .btn-warning {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            box-shadow: 0 4px 15px rgba(251,191,36,0.3);
        }

        .table .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 15px rgba(239,68,68,0.3);
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal {
            background: white;
            width: 90%;
            max-width: 650px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
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

        .modal-body input, .modal-body textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
        }

        .modal-body input:focus, .modal-body textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.15);
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

        @media (max-width: 768px) {
            .filter-form { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>
    <div class="container">
        <h1>Danh Sách Sản Phẩm</h1>

        <main>
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Tìm kiếm tên sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <div class="actions-section">
                <a href="create.php" class="btn-primary">+ Thêm Sản Phẩm Mới</a>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Tên Sản Phẩm</th>
                        <th>Đơn Vị</th>
                        <th style="text-align: right;">Giá Bán</th>
                        <th>Mô Tả</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['ten_san_pham']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['don_vi']); ?></td>
                                <td class="price"><?php echo formatMoney($row['gia_ban']); ?> VNĐ</td>
                                <td><?php echo htmlspecialchars(substr($row['mo_ta'] ?? '', 0, 50)); ?>...</td>
                                <td><?php echo date('d/m/Y', strtotime($row['ngay_tao'])); ?></td>
                                <td>
                                    <button class="btn-warning edit-btn"
                                        data-id="<?php echo $row['ma_san_pham']; ?>"
                                        data-ten="<?php echo htmlspecialchars($row['ten_san_pham']); ?>"
                                        data-gia="<?php echo $row['gia_ban']; ?>"
                                        data-donvi="<?php echo htmlspecialchars($row['don_vi']); ?>"
                                        data-mota="<?php echo htmlspecialchars($row['mo_ta'] ?? ''); ?>">
                                        Sửa
                                    </button>
                                    <a href="delete.php?id=<?php echo $row['ma_san_pham']; ?>"
                                       class="btn-danger"
                                       onclick="return confirm('Bạn chắc chắn muốn xóa sản phẩm này?')">
                                        Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 60px;">Không có sản phẩm nào</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal Sửa Sản Phẩm -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">Sửa Thông Tin Sản Phẩm</div>
            <div class="modal-body">
                <div id="modalAlert"></div>
                <form id="editForm">
                    <input type="hidden" name="ma_san_pham" id="editId">
                    <input type="hidden" name="action" value="update_product">

                    <div class="form-group">
                        <label>Tên Sản Phẩm</label>
                        <input type="text" name="ten_san_pham" id="editTen" required>
                    </div>

                    <div class="form-group">
                        <label>Giá Bán (VNĐ)</label>
                        <input type="number" name="gia_ban" id="editGia" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Đơn Vị</label>
                        <input type="text" name="don_vi" id="editDonVi" value="Cái">
                    </div>

                    <div class="form-group">
                        <label>Mô Tả</label>
                        <textarea name="mo_ta" id="editMoTa" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="closeModal">Hủy</button>
                <button type="button" class="btn-primary" id="saveEdit">Cập Nhật</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('editModal');
        const alertBox = document.getElementById('modalAlert');

        // Mở modal sửa
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('editId').value = btn.dataset.id;
                document.getElementById('editTen').value = btn.dataset.ten;
                document.getElementById('editGia').value = btn.dataset.gia;
                document.getElementById('editDonVi').value = btn.dataset.donvi;
                document.getElementById('editMoTa').value = btn.dataset.mota;

                alertBox.innerHTML = '';
                modal.style.display = 'flex';
            });
        });

        // Đóng modal
        document.getElementById('closeModal').addEventListener('click', () => {
            modal.style.display = 'none';
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.style.display = 'none';
        });

        // Lưu thay đổi
        document.getElementById('saveEdit').addEventListener('click', async () => {
            const formData = new FormData(document.getElementById('editForm'));

            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    alertBox.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alertBox.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
                }
            } catch (err) {
                alertBox.innerHTML = `<div class="alert alert-error">Lỗi kết nối mạng</div>`;
            }
        });
    </script>
</body>
</html>