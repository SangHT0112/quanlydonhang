<?php
include '../config.php';
checkLogin();
requirePermission('execute_pxk');

$id = $_GET['id'] ?? 0;

if ($id == 0) {
    header('Location: list.php');
    exit;
}

// Lấy thông tin phiếu xuất kho
$sql = "SELECT pxk.*, pbh.ma_phieu_dat_hang 
        FROM phieu_xuat_kho pxk
        JOIN phieu_ban_hang pbh ON pxk.ma_phieu_ban_hang = pbh.ma_phieu_ban_hang
        WHERE pxk.ma_phieu_xuat_kho = " . intval($id);
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$pxk = $result->fetch_assoc();

// Chỉ cho phép sửa khi còn ở trạng thái "Đang xuất"
if ($pxk['trang_thai'] != 'Đang xuất') {
    header('Location: detail.php?id=' . $id . '&error=' . urlencode('Chỉ có thể sửa phiếu xuất kho khi đang ở trạng thái "Đang xuất"'));
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

        // Cập nhật thông tin phiếu xuất kho
        $sql_update = "UPDATE phieu_xuat_kho SET ngay_xuat = ?, nguoi_xuat = ? WHERE ma_phieu_xuat_kho = ?";
        $stmt = $conn->prepare($sql_update);
        if (!$stmt) {
            throw new Exception('Lỗi prepare: ' . $conn->error);
        }
        $stmt->bind_param("ssi", $ngay_xuat, $nguoi_xuat, $id);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi cập nhật: ' . $stmt->error);
        }

        // Cập nhật chi tiết sản phẩm nếu có thay đổi
        $i = 0;
        while (isset($_POST['ma_san_pham_' . $i])) {
            $ma_sp = intval($_POST['ma_san_pham_' . $i]);
            $so_luong = intval($_POST['so_luong_xuat_' . $i]);
            $thanh_tien = floatval($_POST['thanh_tien_' . $i]);
            $ma_chi_tiet = intval($_POST['ma_chi_tiet_' . $i] ?? 0);

            if ($ma_chi_tiet > 0) {
                // Cập nhật chi tiết hiện có
                $sql_ct = "UPDATE chi_tiet_phieu_xuat_kho 
                          SET so_luong_xuat = ?, thanh_tien = ? 
                          WHERE ma_chi_tiet = ? AND ma_phieu_xuat_kho = ?";
                $stmt_ct = $conn->prepare($sql_ct);
                if ($stmt_ct) {
                    $stmt_ct->bind_param("idii", $so_luong, $thanh_tien, $ma_chi_tiet, $id);
                    $stmt_ct->execute();
                }
            }
            $i++;
        }

        logActivity('EDIT_PXK', 'Sửa phiếu xuất kho #' . $id);
        header('Location: detail.php?id=' . $id);
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Lấy chi tiết sản phẩm hiện tại
$sql_ct = "SELECT ct.*, sp.ten_san_pham, sp.don_vi, tk.so_luong_ton
          FROM chi_tiet_phieu_xuat_kho ct
          JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
          LEFT JOIN ton_kho tk ON ct.ma_san_pham = tk.ma_san_pham
          WHERE ct.ma_phieu_xuat_kho = " . intval($id);
$result_ct = $conn->query($sql_ct);

// Lấy thông tin khách hàng
$sql_kh = "SELECT pdh.ma_khach_hang, kh.ten_khach_hang
           FROM phieu_dat_hang pdh
           JOIN khach_hang kh ON pdh.ma_khach_hang = kh.ma_khach_hang
           WHERE pdh.ma_phieu_dat_hang = " . intval($pxk['ma_phieu_dat_hang']);
$result_kh = $conn->query($sql_kh);
$khach_hang = $result_kh->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Phiếu Xuất Kho #<?php echo $id; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <h1>Sửa Phiếu Xuất Kho #<?php echo $id; ?></h1>

        <main>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="form">
                <h3>Thông Tin Phiếu Xuất Kho</h3>
                
                <div class="form-group">
                    <label for="ngay_xuat">Ngày Xuất <span style="color: red;">*</span></label>
                    <input type="date" id="ngay_xuat" name="ngay_xuat" value="<?php echo $pxk['ngay_xuat']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="nguoi_xuat">Người Xuất</label>
                    <input type="text" id="nguoi_xuat" name="nguoi_xuat" placeholder="Tên người xuất kho" value="<?php echo htmlspecialchars($pxk['nguoi_xuat'] ?? ''); ?>">
                </div>

                <div class="detail-section">
                    <h3>Chi Tiết Sản Phẩm</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sản Phẩm</th>
                                <th>Đơn Vị</th>
                                <th>Tồn Kho</th>
                                <th style="text-align: right;">Số Lượng Xuất</th>
                                <th style="text-align: right;">Thành Tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $index = 0;
                            while ($row = $result_ct->fetch_assoc()):
                                $so_luong_ton = $row['so_luong_ton'] ?? 0;
                                $canh_bao = ($so_luong_ton < $row['so_luong_xuat']) ? ' style="color: #ef4444; font-weight: bold;"' : '';
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['ten_san_pham']); ?></td>
                                    <td><?php echo htmlspecialchars($row['don_vi']); ?></td>
                                    <td<?php echo $canh_bao; ?>>
                                        <?php echo $so_luong_ton; ?>
                                        <?php if ($so_luong_ton < $row['so_luong_xuat']): ?>
                                            <span style="color: #ef4444;"> (Không đủ)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <input type="hidden" name="ma_chi_tiet_<?php echo $index; ?>" value="<?php echo $row['ma_chi_tiet']; ?>">
                                        <input type="hidden" name="ma_san_pham_<?php echo $index; ?>" value="<?php echo $row['ma_san_pham']; ?>">
                                        <input type="number" name="so_luong_xuat_<?php echo $index; ?>" 
                                               value="<?php echo $row['so_luong_xuat']; ?>" 
                                               min="1" 
                                               style="width: 100px; text-align: right;"
                                               onchange="updateThanhTien(<?php echo $index; ?>)">
                                    </td>
                                    <td style="text-align: right;">
                                        <input type="number" name="thanh_tien_<?php echo $index; ?>" 
                                               value="<?php echo $row['thanh_tien']; ?>" 
                                               step="0.01" 
                                               min="0"
                                               style="width: 150px; text-align: right;"
                                               readonly>
                                    </td>
                                </tr>
                            <?php
                                $index++;
                            endwhile;
                            ?>
                        </tbody>
                    </table>
                    <p style="margin-top: 10px; color: #666; font-size: 13px;">
                        <span style="color: #ef4444;">⚠️</span> Cảnh báo: Số lượng xuất không được vượt quá tồn kho hiện có.
                    </p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Lưu Thay Đổi</button>
                    <a href="detail.php?id=<?php echo $id; ?>" class="btn-secondary">Hủy</a>
                </div>
            </form>
        </main>
    </div>

    <script>
    function updateThanhTien(index) {
        // Tính lại thành tiền dựa trên giá bán từ phiếu bán hàng
        // Ở đây giữ nguyên giá trị hiện tại, có thể cải thiện sau
    }
    </script>
</body>
</html>
