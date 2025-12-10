<?php
include '../config.php';
checkLogin();
requirePermission('execute_pxk');

$ma_pbh = $_GET['ma_phieu_ban_hang'] ?? 0;

if ($ma_pbh == 0) {
    header('Location: ../phieu_ban_hang/list.php');
    exit;
}

// Kiểm tra phiếu bán hàng có tồn tại không
$sql_pbh = "SELECT pbh.*, pdh.ma_khach_hang 
            FROM phieu_ban_hang pbh
            JOIN phieu_dat_hang pdh ON pbh.ma_phieu_dat_hang = pdh.ma_phieu_dat_hang
            WHERE pbh.ma_phieu_ban_hang = " . intval($ma_pbh);
$result_pbh = $conn->query($sql_pbh);

if ($result_pbh->num_rows == 0) {
    header('Location: ../phieu_ban_hang/list.php');
    exit;
}

$pbh = $result_pbh->fetch_assoc();

// Kiểm tra đã có phiếu xuất kho chưa
$sql_check = "SELECT ma_phieu_xuat_kho FROM phieu_xuat_kho WHERE ma_phieu_ban_hang = " . intval($ma_pbh);
$result_check = $conn->query($sql_check);
if ($result_check->num_rows > 0) {
    $existing = $result_check->fetch_assoc();
    header('Location: detail.php?id=' . $existing['ma_phieu_xuat_kho']);
    exit;
}

$error = '';

if ($_POST) {
    try {
        $ngay_xuat = $_POST['ngay_xuat'];
        $nguoi_xuat = $_POST['nguoi_xuat'] ?? '';
        
        if (empty($ngay_xuat)) {
            throw new Exception('Vui lòng chọn ngày xuất');
        }

        // Lấy chi tiết phiếu bán hàng
        $sql_ct_pbh = "SELECT * FROM chi_tiet_phieu_ban_hang WHERE ma_phieu_ban_hang = " . intval($ma_pbh);
        $result_ct_pbh = $conn->query($sql_ct_pbh);
        
        if ($result_ct_pbh->num_rows == 0) {
            throw new Exception('Phiếu bán hàng không có chi tiết sản phẩm');
        }

        $conn->begin_transaction();

        // Tạo phiếu xuất kho
        $sql_pxk = "INSERT INTO phieu_xuat_kho (ma_phieu_ban_hang, ngay_xuat, nguoi_xuat, trang_thai) VALUES (?, ?, ?, 'Đang xuất')";
        $stmt_pxk = $conn->prepare($sql_pxk);
        if (!$stmt_pxk) {
            throw new Exception('Lỗi prepare: ' . $conn->error);
        }
        $stmt_pxk->bind_param("iss", $ma_pbh, $ngay_xuat, $nguoi_xuat);
        if (!$stmt_pxk->execute()) {
            throw new Exception('Lỗi tạo phiếu xuất kho: ' . $stmt_pxk->error);
        }
        $ma_pxk = $conn->insert_id;

        // Thêm chi tiết sản phẩm xuất kho từ chi tiết phiếu bán hàng
        while ($row_ct = $result_ct_pbh->fetch_assoc()) {
            // Tính thành tiền dựa trên giá bán và số lượng
            $thanh_tien = $row_ct['so_luong'] * $row_ct['gia_ban'] - $row_ct['chiet_khau'];
            
            $sql_ct_pxk = "INSERT INTO chi_tiet_phieu_xuat_kho (ma_phieu_xuat_kho, ma_san_pham, so_luong_xuat, thanh_tien) 
                          VALUES (?, ?, ?, ?)";
            $stmt_ct_pxk = $conn->prepare($sql_ct_pxk);
            if (!$stmt_ct_pxk) {
                throw new Exception('Lỗi prepare chi tiết: ' . $conn->error);
            }
            $stmt_ct_pxk->bind_param("iiid", $ma_pxk, $row_ct['ma_san_pham'], $row_ct['so_luong'], $thanh_tien);
            if (!$stmt_ct_pxk->execute()) {
                throw new Exception('Lỗi thêm chi tiết: ' . $stmt_ct_pxk->error);
            }
        }

        $conn->commit();
        logActivity('CREATE_PXK', 'Tạo phiếu xuất kho #' . $ma_pxk . ' từ phiếu bán hàng #' . $ma_pbh);
        
        header('Location: detail.php?id=' . $ma_pxk);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Lấy chi tiết sản phẩm của phiếu bán hàng với thông tin tồn kho
$sql_ct = "SELECT ct.*, sp.ten_san_pham, sp.don_vi, COALESCE(tk.so_luong_ton, 0) as so_luong_ton
          FROM chi_tiet_phieu_ban_hang ct
          JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
          LEFT JOIN ton_kho tk ON ct.ma_san_pham = tk.ma_san_pham
          WHERE ct.ma_phieu_ban_hang = " . intval($ma_pbh);
$result_ct = $conn->query($sql_ct);

// Lấy thông tin khách hàng
$sql_kh = "SELECT * FROM khach_hang WHERE ma_khach_hang = " . intval($pbh['ma_khach_hang']);
$result_kh = $conn->query($sql_kh);
$khach_hang = $result_kh->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Phiếu Xuất Kho</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Tạo Phiếu Xuất Kho</h1>

        <main>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="detail-section">
                <h3>Thông Tin Phiếu Bán Hàng</h3>
                <div class="detail-row">
                    <label>Mã Phiếu Bán Hàng:</label>
                    <p>#<?php echo $pbh['ma_phieu_ban_hang']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Mã Phiếu Đặt Hàng:</label>
                    <p>#<?php echo $pbh['ma_phieu_dat_hang']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Khách Hàng:</label>
                    <p><?php echo htmlspecialchars($khach_hang['ten_khach_hang'] ?? 'N/A'); ?></p>
                </div>
                <div class="detail-row">
                    <label>Tổng Tiền:</label>
                    <p><?php echo formatMoney($pbh['tong_tien']); ?> VNĐ</p>
                </div>
            </div>

            <div class="detail-section">
                <h3>Chi Tiết Sản Phẩm Sẽ Xuất Kho</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản Phẩm</th>
                            <th>Đơn Vị</th>
                            <th style="text-align: right;">Tồn Kho / Cần</th>
                            <th style="text-align: right;">Giá Bán</th>
                            <th style="text-align: right;">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $has_insufficient = false;
                        while ($row = $result_ct->fetch_assoc()) {
                            $thanh_tien = $row['so_luong'] * $row['gia_ban'] - $row['chiet_khau'];
                            $so_luong_ton = $row['so_luong_ton'] ?? 0;
                            $is_insufficient = $so_luong_ton < $row['so_luong'];
                            if ($is_insufficient) $has_insufficient = true;
                            $row_class = $is_insufficient ? " style='background-color: #fee2e2;'" : "";
                            echo "<tr" . $row_class . ">";
                            echo "<td>" . htmlspecialchars($row['ten_san_pham']);
                            if ($is_insufficient) {
                                echo " <span style='color: #ef4444; font-weight: bold;'>⚠️ Không đủ tồn kho</span>";
                            }
                            echo "</td>";
                            echo "<td>" . htmlspecialchars($row['don_vi']) . "</td>";
                            echo "<td style='text-align: right;'>";
                            echo "<span style='color: " . ($is_insufficient ? "#ef4444" : "#059669") . "; font-weight: bold;'>" . $so_luong_ton . "</span>";
                            echo " / " . $row['so_luong'];
                            echo "</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($row['gia_ban']) . " VNĐ</td>";
                            echo "<td style='text-align: right;'>" . formatMoney($thanh_tien) . " VNĐ</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php if ($has_insufficient): ?>
                    <div class="alert alert-warning" style="margin-top: 15px;">
                        <strong>⚠️ Cảnh báo:</strong> Một số sản phẩm không đủ tồn kho. Vui lòng kiểm tra lại trước khi tạo phiếu xuất kho.
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" class="form" onsubmit="<?php echo $has_insufficient ? "return confirm('Một số sản phẩm không đủ tồn kho. Bạn có chắc chắn muốn tạo phiếu xuất kho?');" : ""; ?>">
                <h3>Thông Tin Phiếu Xuất Kho</h3>
                
                <div class="form-group">
                    <label for="ngay_xuat">Ngày Xuất <span style="color: red;">*</span></label>
                    <input type="date" id="ngay_xuat" name="ngay_xuat" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="nguoi_xuat">Người Xuất</label>
                    <input type="text" id="nguoi_xuat" name="nguoi_xuat" placeholder="Tên người xuất kho" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Tạo Phiếu Xuất Kho</button>
                    <a href="../phieu_ban_hang/detail.php?id=<?php echo $ma_pbh; ?>" class="btn-secondary">Hủy</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
