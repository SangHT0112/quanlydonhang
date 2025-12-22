<?php
include '../config.php';
checkLogin();
requirePermission('view_po');  // Thêm permission này cho list

$search = $_GET['search'] ?? '';
$trang_thai = $_GET['trang_thai'] ?? '';

// Xây dựng câu SQL tìm kiếm (sửa: prepared)
$sql = "
SELECT 
    p.ma_phieu_dat_hang,
    k.ten_khach_hang,
    p.ngay_dat,
    p.tong_tien,
    p.trang_thai,
    hd.ma_hoa_don
FROM phieu_dat_hang p
JOIN khach_hang k 
    ON p.ma_khach_hang = k.ma_khach_hang
LEFT JOIN hoa_don hd 
    ON hd.ma_phieu_dat_hang = p.ma_phieu_dat_hang
WHERE 1=1
";

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

// Load danh sách khách hàng cho modal
$kh_result = $conn->query("SELECT * FROM khach_hang WHERE trang_thai = 'Hoạt động' ORDER BY ten_khach_hang");

// Load danh sách sản phẩm cho modal
$sp_result = $conn->query("SELECT * FROM san_pham ORDER BY ten_san_pham");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Phiếu Đặt Hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
    <!-- SweetAlert2 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border: none;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: black;
        }
        .modal-body {
            padding: 20px;
        }
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #ddd;
            text-align: right;
        }
        .product-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: end;
        }
        .product-row .form-group {
            flex: 1;
            min-width: 150px;
        }
        @media (max-width: 768px) {
            .product-row {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <?php include '../chat/chat.php'; ?>
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
                    <button onclick="openCreateModal()" class="btn-primary">+ Tạo Phiếu Đặt Hàng Mới</button>
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
                            echo "<tr data-po-id='" . $row['ma_phieu_dat_hang'] . "'>";
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
                                    echo "<button onclick=\"confirmDelete({$row['ma_phieu_dat_hang']}, 'Phiếu đặt hàng #{$row['ma_phieu_dat_hang']} - {$row['ten_khach_hang']}')\" class='btn-danger'>Xóa</button>";
                                }
                            }
                           if ($row['trang_thai'] == 'Đã duyệt' && hasPermission('create_invoice')) {

                            if ($row['ma_hoa_don']) {
                                // ✅ ĐÃ TẠO HÓA ĐƠN
                                echo "<span class='badge badge-success'>Đã tạo hóa đơn</span>";
                            } else {
                                // 🟢 CHƯA TẠO
                                echo "<a href='../hoa_don/create.php?ma_po={$row['ma_phieu_dat_hang']}'
                                        class='btn-primary'>
                                        Tạo hóa đơn
                                    </a>";
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

    <!-- Modal Tạo Phiếu Đặt Hàng -->
    <div id="createPoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tạo Phiếu Đặt Hàng Mới</h2>
                <span class="close" onclick="closeCreateModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-error" style="display: none;"></div>
                <div id="modalSuccess" class="alert alert-success" style="display: none;"></div>

                <form id="createPoForm" method="POST">
                    <div class="form-group">
                        <label for="ma_khach_hang">Khách Hàng:</label>
                        <select name="ma_khach_hang" id="ma_khach_hang" required>
                            <option value="">-- Chọn khách hàng --</option>
                            <?php
                            $kh_result->data_seek(0); // Reset pointer
                            while($row = $kh_result->fetch_assoc()) {
                                echo "<option value='" . $row['ma_khach_hang'] . "'>" . 
                                     htmlspecialchars($row['ten_khach_hang']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ngay_dat">Ngày Đặt:</label>
                        <input type="date" name="ngay_dat" id="ngay_dat" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="ghi_chu">Ghi Chú:</label>
                        <textarea name="ghi_chu" id="ghi_chu" rows="3"></textarea>
                    </div>

                    <h3>Chi Tiết Sản Phẩm</h3>
                    <div id="products-container"></div>

                    <div style="margin-top: 20px;">
                        <button type="button" class="btn-secondary" onclick="addProduct()">+ Thêm Sản Phẩm</button>
                    </div>

                    <!-- Input hidden để lưu tổng tiền -->
                    <input type="hidden" id="tong_tien" name="tong_tien" value="0">

                    <!-- Hiển thị tổng tiền -->
                    <div style="margin-top: 20px; padding: 15px; background-color: #f0f0f0; border-radius: 5px; font-weight: bold;">
                        <h3 id="total-display">Tổng Tiền: 0 VNĐ</h3>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCreateModal()">Hủy</button>
                <button type="submit" form="createPoForm" class="btn-primary">Tạo Phiếu Đặt Hàng</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- XÓA DUPLICATE: CHỈ GIỮ 1 SCRIPT SOCKET.IO -->
    <script src="http://localhost:4000/socket.io/socket.io.js"></script>
    <script>
    let productCount = 1; // Global cho modal

    function openCreateModal() {
        document.getElementById('createPoModal').style.display = 'block';
        productCount = 1; // Reset count
        document.getElementById('products-container').innerHTML = ''; // Clear products
        document.getElementById('createPoForm').reset(); // Reset form
        document.getElementById('ngay_dat').value = new Date().toISOString().split('T')[0]; // Set today
        document.getElementById('total-display').textContent = 'Tổng Tiền: 0 VNĐ';
        document.getElementById('tong_tien').value = '0';
        addProduct(); // Add first product
        hideAlerts();
    }

    function closeCreateModal() {
        document.getElementById('createPoModal').style.display = 'none';
        hideAlerts();
    }

    function hideAlerts() {
        $('#modalError, #modalSuccess').hide();
    }

    // Modal close on outside click
    window.onclick = function(event) {
        const modal = document.getElementById('createPoModal');
        if (event.target == modal) {
            closeCreateModal();
        }
    }

    function addProduct() {
        const container = document.getElementById('products-container');
        const newProduct = document.createElement('div');
        newProduct.className = 'product-row';
        newProduct.id = `product-row-${productCount}`;
        newProduct.innerHTML = `
            <div class="form-group">
                <label>Sản Phẩm:</label>
                <select name="ma_san_pham_${productCount}" class="product-select" onchange="updatePrice(this, ${productCount})">
                    <option value="">-- Chọn sản phẩm --</option>
                    <?php
                    $sp_result->data_seek(0); // Reset pointer
                    while($row = $sp_result->fetch_assoc()) {
                        echo "<option value='" . $row['ma_san_pham'] . "' data-price='" . $row['gia_ban'] . "'>" . 
                             htmlspecialchars($row['ten_san_pham']) . " (" . formatMoney($row['gia_ban']) . " VNĐ)</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Số Lượng:</label>
                <input type="number" name="so_luong_${productCount}" min="1" value="1" class="quantity-input" onchange="calculateTotal()">
            </div>
            <div class="form-group">
                <label>Giá Đặt:</label>
                <input type="number" name="gia_dat_${productCount}" step="0.01" min="0" value="0" class="price-input" onchange="calculateTotal()">
            </div>
            <button type="button" class="btn-danger" onclick="removeProduct(${productCount})">Xóa</button>
        `;
        container.appendChild(newProduct);
        productCount++;
    }

    function updatePrice(select, index) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price') || 0;
        document.querySelector(`input[name="gia_dat_${index}"]`).value = price;
        calculateTotal();
    }

    function removeProduct(index) {
        const row = document.getElementById(`product-row-${index}`);
        if (row) {
            row.remove();
        }
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        const priceInputs = document.querySelectorAll('#createPoModal .price-input');
        const quantityInputs = document.querySelectorAll('#createPoModal .quantity-input');
        
        for (let i = 0; i < priceInputs.length; i++) {
            const price = parseFloat(priceInputs[i].value) || 0;
            const quantity = parseInt(quantityInputs[i].value) || 0;
            total += price * quantity;
        }
        
        // Cập nhật hiển thị tổng tiền
        const totalDisplay = document.getElementById('total-display');
        if (totalDisplay) {
            totalDisplay.textContent = 'Tổng Tiền: ' + formatCurrency(total) + ' VNĐ';
        }
        
        // Cập nhật giá trị input hidden
        document.getElementById('tong_tien').value = total;
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('vi-VN').format(value);
    }

    // AJAX Submit Form
    $('#createPoForm').on('submit', function(e) {
        e.preventDefault();
        hideAlerts();

        $.ajax({
            url: 'create.php',
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Thêm row mới vào table (tương tự socket event)
                    const poData = response.data;
                    const statusClass = poData.trang_thai.toLowerCase().replace(/ /g, '-').replace(/[^a-z0-9-]/g, '');
                    const newRow = `
                        <tr data-po-id="${poData.ma_phieu_dat_hang}">
                            <td><strong>#${poData.ma_phieu_dat_hang}</strong></td>
                            <td>${poData.ten_khach_hang || 'N/A'}</td>
                            <td>${new Date(poData.ngay_dat).toLocaleDateString('vi-VN')}</td>
                            <td>${formatCurrency(poData.tong_tien)} VNĐ</td>
                            <td><span class="status-${statusClass}">${poData.trang_thai}</span></td>
                            <td>
                                <a href="detail.php?id=${poData.ma_phieu_dat_hang}" class="btn-info">Xem</a>
                                ${<?php echo json_encode($_SESSION["role"] ?? "guest"); ?> === 'ketoan' && poData.trang_thai === 'Chờ duyệt' ? '<a href="approve.php?id=' + poData.ma_phieu_dat_hang + '" class="btn-primary ketoan-only">Duyệt</a>' : ''}
                            </td>
                        </tr>
                    `;
                    $('#poTable tbody').prepend(newRow);

                    // Hiển thị success và đóng modal
                    $('#modalSuccess').text('Tạo phiếu đặt hàng thành công!').show();
                    setTimeout(() => {
                        closeCreateModal();
                    }, 1500);
                } else {
                    $('#modalError').text(response.error || 'Có lỗi xảy ra!').show();
                }
            },
            error: function() {
                $('#modalError').text('Lỗi kết nối. Vui lòng thử lại!').show();
            }
        });
    });

    // Socket.io (giữ nguyên phần cũ)
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
                    <tr data-po-id="${poData.ma_phieu_dat_hang}">
                        <td><strong>#${poData.ma_phieu_dat_hang}</strong></td>
                        <td>${poData.ten_khach_hang || 'N/A'}</td>
                        <td>${new Date(poData.ngay_dat).toLocaleDateString('vi-VN')}</td>
                        <td>${formatCurrency(poData.tong_tien)} VNĐ</td>
                        <td><span class="status-${statusClass}">${poData.trang_thai}</span></td>
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

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount);
        }
    })();

    // SweetAlert2 cho xóa PO
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            html: `Bạn có chắc chắn muốn xóa <strong>${name}</strong>? Hành động này không thể hoàn tác.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Có, xóa ngay!',
            cancelButtonText: 'Hủy',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX delete
                $.ajax({
                    url: 'delete.php?id=' + id,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Xóa row khỏi table với animation
                            $(`tr[data-po-id="${id}"]`).fadeOut(300, function() { 
                                $(this).remove(); 
                            });
                            
                            Swal.fire(
                                'Đã xóa!',
                                response.message,
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Lỗi!',
                                response.error || 'Có lỗi xảy ra!',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Lỗi kết nối!',
                            'Vui lòng thử lại.',
                            'error'
                        );
                    }
                });
            }
        });
    }
    </script>
</body>
</html>