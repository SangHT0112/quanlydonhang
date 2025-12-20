<?php
include '../config.php';
checkLogin();

// ==================== XỬ LÝ AJAX CHO LIVE SEARCH ====================
// Nếu là request AJAX từ live search, chỉ trả về phần bảng
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $search = $_GET['search'] ?? '';

    $sql = "SELECT * FROM san_pham WHERE 1=1";
    if ($search) {
        $search = $conn->real_escape_string($search);
        $sql .= " AND ten_san_pham LIKE '%$search%'";
    }
    $sql .= " ORDER BY ngay_tao DESC";
    $result = $conn->query($sql);

    // Chỉ output phần bảng
    echo '<div class="table-container">';
    echo '<table class="table">';
    echo '<thead><tr>';
    echo '<th>Tên Sản Phẩm</th>';
    echo '<th>Đơn Vị</th>';
    echo '<th style="text-align: right;">Giá Bán</th>';
    echo '<th>Mô Tả</th>';
    echo '<th>Ngày Tạo</th>';
    echo '<th style="text-align: center;">Hành Động</th>';
    echo '</tr></thead><tbody>';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr data-id="' . $row['ma_san_pham'] . '">';
            echo '<td><strong>' . htmlspecialchars($row['ten_san_pham']) . '</strong></td>';
            echo '<td>' . htmlspecialchars($row['don_vi']) . '</td>';
            echo '<td class="price">' . formatMoney($row['gia_ban']) . ' VNĐ</td>';
            echo '<td>' . htmlspecialchars(substr($row['mo_ta'] ?? '', 0, 60)) . '...</td>';
            echo '<td>' . date('d/m/Y', strtotime($row['ngay_tao'])) . '</td>';
            echo '<td>';
            echo '<button class="btn-warning edit-btn" data-id="' . $row['ma_san_pham'] . '" data-ten="' . htmlspecialchars($row['ten_san_pham']) . '" data-gia="' . $row['gia_ban'] . '" data-donvi="' . htmlspecialchars($row['don_vi']) . '" data-mota="' . htmlspecialchars($row['mo_ta'] ?? '') . '">Sửa</button> ';
            echo '<button class="btn-danger delete-btn" data-id="' . $row['ma_san_pham'] . '" data-ten="' . htmlspecialchars($row['ten_san_pham']) . '">Xóa</button>';
            echo '</td></tr>';
        }
    } else {
        echo '<tr><td colspan="6" style="text-align: center; padding: 80px 20px; color: #94a3b8; font-size: 18px;">';
        echo '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M9 3v18"></path><path d="M15 3v18"></path><path d="M3 9h18"></path><path d="M3 15h18"></path></svg>';
        echo '<p style="margin-top: 16px;">Không tìm thấy sản phẩm nào</p></td></tr>';
    }

    echo '</tbody></table></div>';
    exit;
}

// ==================== XỬ LÝ AJAX CHO CRUD ====================

$search = $_GET['search'] ?? '';

// Thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_product') {
    header('Content-Type: application/json');
    $ten_sp   = trim($_POST['ten_san_pham']);
    $gia_ban  = floatval($_POST['gia_ban']);
    $don_vi   = trim($_POST['don_vi'] ?? 'Cái');
    $mo_ta    = trim($_POST['mo_ta'] ?? '');
    $ton_dau  = intval($_POST['so_luong_ton']);

    if ($ten_sp === '' || $gia_ban < 0 || $ton_dau < 0) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    try {
        $conn->begin_transaction();
        $sql_sp = "INSERT INTO san_pham (ten_san_pham, gia_ban, don_vi, mo_ta) VALUES (?, ?, ?, ?)";
        $stmt_sp = $conn->prepare($sql_sp);
        $stmt_sp->bind_param("sdss", $ten_sp, $gia_ban, $don_vi, $mo_ta);
        $stmt_sp->execute();
        $ma_sp = $conn->insert_id;

        $sql_tk = "INSERT INTO ton_kho (ma_san_pham, so_luong_ton, ngay_cap_nhat) VALUES (?, ?, NOW())";
        $stmt_tk = $conn->prepare($sql_tk);
        $stmt_tk->bind_param("ii", $ma_sp, $ton_dau);
        $stmt_tk->execute();

        logActivity('CREATE_PRODUCT', "Thêm sản phẩm #$ma_sp - $ten_sp (Tồn đầu: $ton_dau)");
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}

// Cập nhật sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_product') {
    header('Content-Type: application/json');
    $id      = intval($_POST['ma_san_pham']);
    $ten_sp  = trim($_POST['ten_san_pham']);
    $gia_ban = floatval($_POST['gia_ban']);
    $don_vi  = trim($_POST['don_vi'] ?? 'Cái');
    $mo_ta   = trim($_POST['mo_ta'] ?? '');

    if ($ten_sp === '' || $gia_ban < 0) {
        echo json_encode(['success' => false, 'message' => 'Tên sản phẩm và giá bán không hợp lệ']);
        exit;
    }

    try {
        $conn->begin_transaction();
        $sql = "UPDATE san_pham SET ten_san_pham = ?, gia_ban = ?, don_vi = ?, mo_ta = ? WHERE ma_san_pham = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssi", $ten_sp, $gia_ban, $don_vi, $mo_ta, $id);
        $stmt->execute();

        logActivity('UPDATE_PRODUCT', "Cập nhật sản phẩm #$id - $ten_sp");
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Xóa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    header('Content-Type: application/json');
    $id = intval($_POST['ma_san_pham']);

    try {
        $conn->begin_transaction();
        $sql_tk = "DELETE FROM ton_kho WHERE ma_san_pham = ?";
        $stmt_tk = $conn->prepare($sql_tk);
        $stmt_tk->bind_param("i", $id);
        $stmt_tk->execute();

        $sql_sp = "DELETE FROM san_pham WHERE ma_san_pham = ?";
        $stmt_sp = $conn->prepare($sql_sp);
        $stmt_sp->bind_param("i", $id);
        $stmt_sp->execute();

        if ($stmt_sp->affected_rows === 0) {
            throw new Exception('Không tìm thấy sản phẩm để xóa');
        }

        logActivity('DELETE_PRODUCT', "Xóa sản phẩm #$id");
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Lấy danh sách bình thường
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
        main { flex: 1; padding: 40px 20px; background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); min-height: 100vh; }

        .page-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;
        }
        .page-header h1 { margin: 0; font-size: 32px; font-weight: 800; color: #4338ca; }

        .btn-add-new {
            background: linear-gradient(135deg, #667eea, #764ba2); padding: 14px 28px; font-size: 17px; border-radius: 16px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3); display: inline-flex; align-items: center; gap: 10px; color: white;
            border: none; cursor: pointer; transition: all 0.3s ease;
        }
        .btn-add-new:hover { transform: translateY(-3px); box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4); }

        .search-section { margin-bottom: 40px; display: flex; justify-content: center; }

        .live-search-wrapper { position: relative; width: 100%; max-width: 700px; margin: 0 auto; }

        .compact-search-input-group { position: relative; }

        .compact-search-input {
            width: 100%; padding: 16px 60px 16px 20px; border: 2px solid #e2e8f0; border-radius: 16px;
            font-size: 16px; background: white; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .compact-search-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.15); }

        .compact-search-btn {
            position: absolute; right: 8px; top: 8px; background: transparent; border: none; width: 44px; height: 44px;
            border-radius: 12px; cursor: default; display: flex; align-items: center; justify-content: center; pointer-events: none;
        }
        .compact-search-btn svg { stroke: #64748b; }

        .compact-clear-btn {
            position: absolute; right: 8px; top: 8px; background: #fef2f2; color: #dc2626; border: none;
            width: 44px; height: 44px; border-radius: 12px; cursor: pointer; display: flex; align-items: center;
            justify-content: center; transition: all 0.3s ease; opacity: 0; visibility: hidden;
        }
        .compact-clear-btn.show { opacity: 1; visibility: visible; pointer-events: all; }
        .compact-clear-btn:hover { background: #fecaca; transform: scale(1.1); }

        .search-loading {
            position: absolute; right: 60px; top: 50%; transform: translateY(-50%);
            width: 20px; height: 20px; border: 2px solid #e2e8f0; border-top: 2px solid #6366f1;
            border-radius: 50%; animation: spin 1s linear infinite; display: none;
        }
        @keyframes spin { 0% { transform: translateY(-50%) rotate(0deg); } 100% { transform: translateY(-50%) rotate(360deg); } }

        .table-container { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.1); border: 1px solid #e0e7ff; }
        .table thead { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .table th { color: white; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; font-size: 14px; padding: 20px 24px; text-align: left; }
        .table th:last-child, .table td:last-child { text-align: center; }
        .table tbody tr:hover { background: linear-gradient(to right, #f8f9ff, #f0f4ff); transition: all 0.3s ease; }
        .table td { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .table td strong { font-size: 18px; color: #1e293b; }
        .table .price { text-align: right; font-weight: 700; color: #dc2626; }

        .table .btn-warning, .table .btn-danger {
            padding: 10px 20px; border-radius: 12px; font-weight: 600; margin: 0 5px; cursor: pointer;
        }
        .table .btn-warning { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; }
        .table .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }

        /* Modal styles giữ nguyên... (giữ như cũ) */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 1000; }
        .modal { background: white; width: 90%; max-width: 600px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; animation: slideUp 0.4s ease; }
        .modal-header { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 24px; font-size: 24px; font-weight: 700; text-align: center; }
        .modal-header.danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .modal-body { padding: 30px; }
        .modal-body .form-group { margin-bottom: 20px; }
        .modal-body label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; }
        .modal-body input, .modal-body textarea { width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px; }
        .modal-body input:focus, .modal-body textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.15); }
        .modal-footer { padding: 20px 30px; background: #f8fafc; display: flex; justify-content: flex-end; gap: 12px; }
        .modal-footer.center { justify-content: center; }
        .alert { padding: 12px 16px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: stretch; text-align: center; }
            .btn-add-new { justify-content: center; margin: 0 auto; width: fit-content; }
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>
<div class="container">
        <main>
            <!-- Header -->
            <div class="page-header">
                <h1>Danh Sách Sản Phẩm</h1>
                <button id="openCreateModal" class="btn-add-new">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Thêm Sản Phẩm Mới
                </button>
            </div>

            <!-- Live Search -->
            <div class="search-section">
                <div class="live-search-wrapper">
                    <div class="compact-search-input-group">
                        <input type="text" id="liveSearchInput" placeholder="Tìm kiếm tên sản phẩm..." value="<?php echo htmlspecialchars($search); ?>" class="compact-search-input" autocomplete="off">
                        <div class="search-loading" id="searchLoading"></div>
                        <button type="button" class="compact-search-btn" id="searchIcon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                        <button type="button" class="compact-clear-btn <?php if ($search) echo 'show'; ?>" id="clearSearch">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bảng -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tên Sản Phẩm</th>
                            <th>Đơn Vị</th>
                            <th style="text-align: right;">Giá Bán</th>
                            <th>Mô Tả</th>
                            <th>Ngày Tạo</th>
                            <th style="text-align: center;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr data-id="<?php echo $row['ma_san_pham']; ?>">
                                    <td><strong><?php echo htmlspecialchars($row['ten_san_pham']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['don_vi']); ?></td>
                                    <td class="price"><?php echo formatMoney($row['gia_ban']); ?> VNĐ</td>
                                    <td><?php echo htmlspecialchars(substr($row['mo_ta'] ?? '', 0, 60)); ?>...</td>
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
                                        <button class="btn-danger delete-btn"
                                            data-id="<?php echo $row['ma_san_pham']; ?>"
                                            data-ten="<?php echo htmlspecialchars($row['ten_san_pham']); ?>">
                                            Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 80px 20px; color: #94a3b8; font-size: 18px;">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">...</svg>
                                    <p style="margin-top: 16px;">Không tìm thấy sản phẩm nào</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>>

    <!-- Modal THÊM -->
    <div class="modal-overlay" id="createModal">
        <div class="modal">
            <div class="modal-header">Thêm Sản Phẩm Mới</div>
            <div class="modal-body">
                <div id="createAlert"></div>
                <form id="createForm">
                    <input type="hidden" name="action" value="create_product">
                    <div class="form-group"><label>Tên Sản Phẩm <span style="color:red">*</span></label><input type="text" name="ten_san_pham" required></div>
                    <div class="form-group"><label>Giá Bán (VNĐ) <span style="color:red">*</span></label><input type="number" name="gia_ban" min="0" step="0.01" required></div>
                    <div class="form-group"><label>Đơn Vị</label><input type="text" name="don_vi" value="Cái"></div>
                    <div class="form-group"><label>Số Lượng Tồn Ban Đầu</label><input type="number" name="so_luong_ton" min="0" value="0" required></div>
                    <div class="form-group"><label>Mô Tả</label><textarea name="mo_ta" rows="3"></textarea></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="closeCreateModal">Hủy</button>
                <button type="button" class="btn-primary" id="saveCreate">Thêm Sản Phẩm</button>
            </div>
        </div>
    </div>

    <!-- Modal SỬA -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">Sửa Thông Tin Sản Phẩm</div>
            <div class="modal-body">
                <div id="editAlert"></div>
                <form id="editForm">
                    <input type="hidden" name="ma_san_pham" id="editId">
                    <input type="hidden" name="action" value="update_product">
                    <div class="form-group"><label>Tên Sản Phẩm</label><input type="text" name="ten_san_pham" id="editTen" required></div>
                    <div class="form-group"><label>Giá Bán (VNĐ)</label><input type="number" name="gia_ban" id="editGia" min="0" step="0.01" required></div>
                    <div class="form-group"><label>Đơn Vị</label><input type="text" name="don_vi" id="editDonVi"></div>
                    <div class="form-group"><label>Mô Tả</label><textarea name="mo_ta" id="editMoTa" rows="3"></textarea></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="closeEditModal">Hủy</button>
                <button type="button" class="btn-primary" id="saveEdit">Cập Nhật</button>
            </div>
        </div>
    </div>

    <!-- Modal XÓA - CHUYÊN NGHIỆP -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header danger">Xác Nhận Xóa Sản Phẩm</div>
            <div class="modal-body">
                <div id="deleteAlert"></div>
                <p>Bạn có chắc chắn muốn xóa sản phẩm:</p>
                <strong id="deleteProductName" style="font-size: 20px; color: #dc2626;"></strong>
                <p style="margin-top: 20px; color: #991b1b; font-weight: 600;">
                    Hành động này <u>không thể hoàn tác</u>!
                </p>
            </div>
            <div class="modal-footer center">
                <button type="button" class="btn-secondary" id="closeDeleteModal">Hủy</button>
                <button type="button" class="btn-danger" id="confirmDelete">Xóa Ngay</button>
            </div>
        </div>
    </div>

    <script>
        // Live Search
        let searchTimeout;
        const liveInput = document.getElementById('liveSearchInput');
        const loading = document.getElementById('searchLoading');
        const clearBtn = document.getElementById('clearSearch');

        liveInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            loading.style.display = 'block';
            query.length > 0 ? clearBtn.classList.add('show') : clearBtn.classList.remove('show');

            searchTimeout = setTimeout(() => {
                const url = new URL(window.location);
                if (query) url.searchParams.set('search', query);
                else url.searchParams.delete('search');
                history.pushState({}, '', url);

                fetch(url.pathname + url.search, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.querySelector('.table-container');
                    if (newTable) document.querySelector('.table-container').innerHTML = newTable.innerHTML;
                    loading.style.display = 'none';
                })
                .catch(() => {
                    loading.style.display = 'none';
                    alert('Lỗi tải dữ liệu');
                });
            }, 300);
        });

        clearBtn.addEventListener('click', () => {
            liveInput.value = '';
            clearBtn.classList.remove('show');
            loading.style.display = 'none';
            history.pushState({}, '', 'list.php');
            location.reload();
        });
        // ==================== MODAL XÓA ====================
        const deleteModal = document.getElementById('deleteModal');
        const deleteAlert = document.getElementById('deleteAlert');
        const deleteProductName = document.getElementById('deleteProductName');
        let productIdToDelete = null;

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                productIdToDelete = btn.dataset.id;
                deleteProductName.textContent = btn.dataset.ten;
                deleteAlert.innerHTML = '';
                deleteModal.style.display = 'flex';
            });
        });

        document.getElementById('closeDeleteModal').addEventListener('click', () => {
            deleteModal.style.display = 'none';
        });

        document.getElementById('confirmDelete').addEventListener('click', async () => {
            const formData = new FormData();
            formData.append('action', 'delete_product');
            formData.append('ma_san_pham', productIdToDelete);

            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    deleteAlert.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    setTimeout(() => {
                        document.querySelector(`tr[data-id="${productIdToDelete}"]`).style.transition = 'all 0.4s';
                        document.querySelector(`tr[data-id="${productIdToDelete}"]`).style.opacity = '0';
                        document.querySelector(`tr[data-id="${productIdToDelete}"]`).style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            document.querySelector(`tr[data-id="${productIdToDelete}"]`).remove();
                            deleteModal.style.display = 'none';
                        }, 400);
                    }, 1000);
                } else {
                    deleteAlert.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
                }
            } catch (err) {
                deleteAlert.innerHTML = `<div class="alert alert-error">Lỗi kết nối mạng</div>`;
            }
        });

        // ==================== MODAL THÊM & SỬA (giữ nguyên) ====================
        const createModal = document.getElementById('createModal');
        const createAlert = document.getElementById('createAlert');
        document.getElementById('openCreateModal').addEventListener('click', () => {
            document.getElementById('createForm').reset();
            createAlert.innerHTML = '';
            createModal.style.display = 'flex';
        });
        document.getElementById('closeCreateModal').addEventListener('click', () => createModal.style.display = 'none');
        document.getElementById('saveCreate').addEventListener('click', async () => {
            const formData = new FormData(document.getElementById('createForm'));
            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    createAlert.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    createAlert.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
                }
            } catch (err) {
                createAlert.innerHTML = `<div class="alert alert-error">Lỗi kết nối mạng</div>`;
            }
        });

        const editModal = document.getElementById('editModal');
        const editAlert = document.getElementById('editAlert');
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('editId').value = btn.dataset.id;
                document.getElementById('editTen').value = btn.dataset.ten;
                document.getElementById('editGia').value = btn.dataset.gia;
                document.getElementById('editDonVi').value = btn.dataset.donvi;
                document.getElementById('editMoTa').value = btn.dataset.mota;
                editAlert.innerHTML = '';
                editModal.style.display = 'flex';
            });
        });
        document.getElementById('closeEditModal').addEventListener('click', () => editModal.style.display = 'none');
        document.getElementById('saveEdit').addEventListener('click', async () => {
            const formData = new FormData(document.getElementById('editForm'));
            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    editAlert.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    editAlert.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
                }
            } catch (err) {
                editAlert.innerHTML = `<div class="alert alert-error">Lỗi kết nối mạng</div>`;
            }
        });

        // Đóng modal khi click ngoài
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.style.display = 'none';
            });
        });
    </script>
</body>
</html>