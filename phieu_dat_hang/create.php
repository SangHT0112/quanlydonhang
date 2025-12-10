<?php
include '../config.php';
checkLogin();

$error = '';
$success = '';

if ($_POST) {
    try {
        $ma_kh = intval($_POST['ma_khach_hang']);
        $ngay_dat = $_POST['ngay_dat'];
        $ghi_chu = $_POST['ghi_chu'] ?? '';
        $tong_tien = floatval($_POST['tong_tien'] ?? 0);

        // Kiểm tra dữ liệu
        if (empty($ngay_dat)) {
            throw new Exception('Vui lòng chọn ngày đặt hàng');
        }

        // Bắt đầu transaction
        $conn->begin_transaction();

        // Tạo phiếu đặt hàng với tổng tiền
        $sql_po = "INSERT INTO phieu_dat_hang (ma_khach_hang, ngay_dat, ghi_chu, trang_thai, tong_tien) VALUES (?, ?, ?, 'Chờ duyệt', ?)";
        $stmt_po = $conn->prepare($sql_po);
        if (!$stmt_po) {
            throw new Exception('Lỗi prepare: ' . $conn->error);
        }
        $stmt_po->bind_param("issd", $ma_kh, $ngay_dat, $ghi_chu, $tong_tien);
        if (!$stmt_po->execute()) {
            throw new Exception('Lỗi tạo phiếu đặt hàng: ' . $stmt_po->error);
        }
        $ma_po = $conn->insert_id;

        // Thêm chi tiết sản phẩm
        $products = [];
        $i = 0;
        while (isset($_POST['ma_san_pham_' . $i])) {
            $ma_sp = intval($_POST['ma_san_pham_' . $i]);
            $sl = intval($_POST['so_luong_' . $i]);
            $gia = floatval($_POST['gia_dat_' . $i]);

            if ($sl > 0 && $gia >= 0) {
                $sql_ct = "INSERT INTO chi_tiet_phieu_dat_hang (ma_phieu_dat_hang, ma_san_pham, so_luong, gia_dat) 
                          VALUES (?, ?, ?, ?)";
                $stmt_ct = $conn->prepare($sql_ct);
                if (!$stmt_ct) {
                    throw new Exception('Lỗi prepare chi tiết: ' . $conn->error);
                }
                $stmt_ct->bind_param("iiid", $ma_po, $ma_sp, $sl, $gia);
                if (!$stmt_ct->execute()) {
                    throw new Exception('Lỗi thêm chi tiết: ' . $stmt_ct->error);
                }
            }
            $i++;
        }

        $conn->commit();
        logActivity('CREATE_PO', 'Tạo phiếu đặt hàng #' . $ma_po . ' với tổng tiền: ' . formatMoney($tong_tien));
        
        header('Location: detail.php?id=' . $ma_po);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Load danh sách khách hàng
$kh_result = $conn->query("SELECT * FROM khach_hang WHERE trang_thai = 'Hoạt động' ORDER BY ten_khach_hang");

// Load danh sách sản phẩm
$sp_result = $conn->query("SELECT * FROM san_pham ORDER BY ten_san_pham");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Phiếu Đặt Hàng</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        let productCount = 1;

        function addProduct() {
            const container = document.getElementById('products-container');
            const newProduct = document.createElement('div');
            newProduct.className = 'product-row';
            newProduct.innerHTML = `
                <div class="form-group">
                    <label>Sản Phẩm:</label>
                    <select name="ma_san_pham_${productCount}" class="product-select" onchange="updatePrice(this, ${productCount})">
                        <option value="">-- Chọn sản phẩm --</option>
                        <?php
                        $sp_result2 = $conn->query("SELECT * FROM san_pham ORDER BY ten_san_pham");
                        while($row = $sp_result2->fetch_assoc()) {
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
                <button type="button" class="btn-danger" onclick="removeProduct(this)">Xóa</button>
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

        function removeProduct(btn) {
            btn.parentElement.remove();
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

        window.onload = function() {
            addProduct();
        };
    </script>
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Tạo Phiếu Đặt Hàng Mới</h1>

        <main>
            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" class="form-main">
                    <div class="form-group">
                        <label for="ma_khach_hang">Khách Hàng:</label>
                        <select name="ma_khach_hang" id="ma_khach_hang" required>
                            <option value="">-- Chọn khách hàng --</option>
                            <?php
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

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Tạo Phiếu Đặt Hàng</button>
                        <a href="../index.php" class="btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>