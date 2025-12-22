<?php
include '../config.php';
checkLogin();
requirePermission('edit_po');

$ma_po = intval($_GET['id'] ?? 0);
if (!$ma_po) {
    header('Location: list.php');
    exit;
}

// Load dữ liệu PO hiện có
$sql_po = "
    SELECT 
        p.*,
        k.ten_khach_hang
    FROM phieu_dat_hang p
    JOIN khach_hang k ON p.ma_khach_hang = k.ma_khach_hang
    WHERE p.ma_phieu_dat_hang = ?
";
$stmt_po = $conn->prepare($sql_po);
$stmt_po->bind_param("i", $ma_po);
$stmt_po->execute();
$po_data = $stmt_po->get_result()->fetch_assoc();

if (!$po_data) {
    header('Location: list.php?error=po_not_found');
    exit;
}

// Load chi tiết sản phẩm hiện có
$sql_ct = "
    SELECT 
        ct.*,
        sp.ten_san_pham,
        sp.gia_ban
    FROM chi_tiet_phieu_dat_hang ct
    JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
    WHERE ct.ma_phieu_dat_hang = ?
";
$stmt_ct = $conn->prepare($sql_ct);
$stmt_ct->bind_param("i", $ma_po);
$stmt_ct->execute();
$ct_result = $stmt_ct->get_result();

// Load danh sách khách hàng và sản phẩm cho form
$kh_result = $conn->query("SELECT * FROM khach_hang WHERE trang_thai = 'Hoạt động' ORDER BY ten_khach_hang");
$sp_result = $conn->query("SELECT * FROM san_pham ORDER BY ten_san_pham");

// Xử lý POST update
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $ma_kh = intval($_POST['ma_khach_hang']);
        $ngay_dat = $_POST['ngay_dat'];
        $ghi_chu = $_POST['ghi_chu'] ?? '';
        $tong_tien = floatval($_POST['tong_tien']);
        $user_id = intval($_SESSION['user_id']);

        if (!$ma_kh || !$ngay_dat) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        $conn->begin_transaction();

        // 1️⃣ UPDATE PHIẾU ĐẶT HÀNG
        $sql_update_po = "
            UPDATE phieu_dat_hang 
            SET ma_khach_hang = ?, ngay_dat = ?, tong_tien = ?, ghi_chu = ?, updated_by = ?, updated_at = NOW()
            WHERE ma_phieu_dat_hang = ?
        ";
        $stmt_update = $conn->prepare($sql_update_po);
        $stmt_update->bind_param("isidsi", $ma_kh, $ngay_dat, $tong_tien, $ghi_chu, $user_id, $ma_po);
        $stmt_update->execute();

        // 2️⃣ DELETE CHI TIẾT CŨ VÀ INSERT MỚI
        $conn->query("DELETE FROM chi_tiet_phieu_dat_hang WHERE ma_phieu_dat_hang = $ma_po");

        $i = 1;
        while (isset($_POST["ma_san_pham_$i"])) {
            $ma_sp = intval($_POST["ma_san_pham_$i"]);
            $sl = intval($_POST["so_luong_$i"]);
            $gia = floatval($_POST["gia_dat_$i"]);

            if ($ma_sp && $sl > 0 && $gia >= 0) {
                $stmt_ct_new = $conn->prepare("
                    INSERT INTO chi_tiet_phieu_dat_hang
                    (ma_phieu_dat_hang, ma_san_pham, so_luong, gia_dat)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt_ct_new->bind_param("iiid", $ma_po, $ma_sp, $sl, $gia);
                $stmt_ct_new->execute();
            }
            $i++;
        }

        $conn->commit();
        logActivity('EDIT_PO', "Cập nhật phiếu đặt hàng #$ma_po");

        $success = 'Cập nhật phiếu đặt hàng thành công!';
        // Reload dữ liệu sau update
        header("Location: edit.php?id=$ma_po&success=1");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Phiếu Đặt Hàng #<?php echo $po_data['ma_phieu_dat_hang']; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .product-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: end; }
        .product-row .form-group { flex: 1; min-width: 150px; }
        @media (max-width: 768px) { .product-row { flex-direction: column; align-items: stretch; } }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <?php include '../chat/chat.php'; ?>
        <h1>Chỉnh Sửa Phiếu Đặt Hàng #<?php echo $po_data['ma_phieu_dat_hang']; ?></h1>
        <main>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" id="editPoForm">
                <div class="form-group">
                    <label for="ma_khach_hang">Khách Hàng:</label>
                    <select name="ma_khach_hang" id="ma_khach_hang" required>
                        <option value="">-- Chọn khách hàng --</option>
                        <?php
                        $kh_result->data_seek(0);
                        while ($kh_row = $kh_result->fetch_assoc()) {
                            $selected = ($kh_row['ma_khach_hang'] == $po_data['ma_khach_hang']) ? 'selected' : '';
                            echo "<option value='{$kh_row['ma_khach_hang']}' $selected>" . htmlspecialchars($kh_row['ten_khach_hang']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ngay_dat">Ngày Đặt:</label>
                    <input type="date" name="ngay_dat" id="ngay_dat" required value="<?php echo htmlspecialchars($po_data['ngay_dat']); ?>">
                </div>

                <div class="form-group">
                    <label for="ghi_chu">Ghi Chú:</label>
                    <textarea name="ghi_chu" id="ghi_chu" rows="3"><?php echo htmlspecialchars($po_data['ghi_chu']); ?></textarea>
                </div>

                <h3>Chi Tiết Sản Phẩm</h3>
                <div id="products-container">
                    <?php
                    $productCount = 1;
                    $ct_result->data_seek(0);
                    while ($ct_row = $ct_result->fetch_assoc()) {
                        echo "<div class='product-row' id='product-row-{$productCount}'>";
                        echo "<div class='form-group'>";
                        echo "<label>Sản Phẩm:</label>";
                        echo "<select name='ma_san_pham_{$productCount}' class='product-select' onchange='updatePrice(this, {$productCount})'>";
                        echo "<option value=''>-- Chọn sản phẩm --</option>";
                        $sp_result->data_seek(0);
                        while ($sp_row = $sp_result->fetch_assoc()) {
                            $selected = ($sp_row['ma_san_pham'] == $ct_row['ma_san_pham']) ? 'selected' : '';
                            echo "<option value='{$sp_row['ma_san_pham']}' data-price='{$sp_row['gia_ban']}' $selected>" . htmlspecialchars($sp_row['ten_san_pham']) . " (" . formatMoney($sp_row['gia_ban']) . " VNĐ)</option>";
                        }
                        echo "</select>";
                        echo "</div>";
                        echo "<div class='form-group'>";
                        echo "<label>Số Lượng:</label>";
                        echo "<input type='number' name='so_luong_{$productCount}' min='1' value='{$ct_row['so_luong']}' class='quantity-input' onchange='calculateTotal()'>";
                        echo "</div>";
                        echo "<div class='form-group'>";
                        echo "<label>Giá Đặt:</label>";
                        echo "<input type='number' name='gia_dat_{$productCount}' step='0.01' min='0' value='{$ct_row['gia_dat']}' class='price-input' onchange='calculateTotal()'>";
                        echo "</div>";
                        echo "<button type='button' class='btn-danger' onclick='removeProduct({$productCount})'>Xóa</button>";
                        echo "</div>";
                        $productCount++;
                    }
                    ?>
                </div>

                <div style="margin-top: 20px;">
                    <button type="button" class="btn-secondary" onclick="addProduct()">+ Thêm Sản Phẩm</button>
                </div>

                <input type="hidden" id="tong_tien" name="tong_tien" value="<?php echo $po_data['tong_tien']; ?>">

                <div style="margin-top: 20px; padding: 15px; background-color: #f0f0f0; border-radius: 5px; font-weight: bold;">
                    <h3 id="total-display">Tổng Tiền: <?php echo formatMoney($po_data['tong_tien']); ?> VNĐ</h3>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn-primary">Cập Nhật Phiếu Đặt Hàng</button>
                    <a href="list.php" class="btn-secondary">Quay Lại Danh Sách</a>
                </div>
            </form>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        let productCount = <?php echo $productCount; ?>; // Tiếp tục từ số lượng hiện có

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
                        $sp_result->data_seek(0);
                        while ($sp_row = $sp_result->fetch_assoc()) {
                            echo "<option value='{$sp_row['ma_san_pham']}' data-price='{$sp_row['gia_ban']}'>" . htmlspecialchars($sp_row['ten_san_pham']) . " (" . formatMoney($sp_row['gia_ban']) . " VNĐ)</option>";
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
            const priceInputs = document.querySelectorAll('.price-input');
            const quantityInputs = document.querySelectorAll('.quantity-input');
            
            for (let i = 0; i < priceInputs.length; i++) {
                const price = parseFloat(priceInputs[i].value) || 0;
                const quantity = parseInt(quantityInputs[i].value) || 0;
                total += price * quantity;
            }
            
            const totalDisplay = document.getElementById('total-display');
            if (totalDisplay) {
                totalDisplay.textContent = 'Tổng Tiền: ' + formatCurrency(total) + ' VNĐ';
            }
            
            document.getElementById('tong_tien').value = total;
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('vi-VN').format(value);
        }

        // Tính tổng ngay khi load (nếu có sản phẩm cũ)
        $(document).ready(function() {
            calculateTotal();
        });
    </script>
</body>
</html>