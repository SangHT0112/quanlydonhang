<?php
include '../config.php';
checkLogin();
requirePermission('view_inventory');  // Quyền xem chi tiết PXK (tương tự view_invoice)

$id = $_GET['id'] ?? 0;

// Prepared statement cho an toàn
$stmt = $conn->prepare("
    SELECT pxk.*, hd.ma_phieu_dat_hang, hd.ngay_xuat_hd, hd.tong_tien
    FROM phieu_xuat_kho pxk
    JOIN hoa_don hd ON pxk.ma_hoa_don = hd.ma_hoa_don 
    WHERE pxk.ma_phieu_xuat_kho = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Phiếu xuất kho không tồn tại';
    header('Location: list.php');
    exit;
}

$pxk = $result->fetch_assoc();

// Lấy chi tiết sản phẩm xuất kho (prepared)
$stmt_ct = $conn->prepare("
    SELECT ct.*, sp.ten_san_pham, sp.don_vi 
    FROM chi_tiet_phieu_xuat_kho ct
    JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
    WHERE ct.ma_phieu_xuat_kho = ?
");
$stmt_ct->bind_param("i", $id);
$stmt_ct->execute();
$result_ct = $stmt_ct->get_result();

// Lấy thông tin khách hàng từ phiếu đặt hàng qua HD (prepared)
$stmt_po = $conn->prepare("
    SELECT pdh.ma_khach_hang, kh.ten_khach_hang, kh.dien_thoai, kh.dia_chi
    FROM phieu_dat_hang pdh
    JOIN khach_hang kh ON pdh.ma_khach_hang = kh.ma_khach_hang
    WHERE pdh.ma_phieu_dat_hang = ?
");
$stmt_po->bind_param("i", $pxk['ma_phieu_dat_hang']);
$stmt_po->execute();
$result_po = $stmt_po->get_result();
$khach_hang = $result_po->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phiếu Xuất Kho #<?php echo $id; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../header.php'; ?>
        <?php include '../chat/chat.php'; ?>  <!-- ← THÊM: Include chat nếu cần real-time notify -->
        
        <h1>Chi Tiết Phiếu Xuất Kho #<?php echo $id; ?></h1>

        <main>
            <?php 
            if (!empty($_SESSION['error'])): 
            ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="detail-section">
                <h3>Thông Tin Chung</h3>
                <div class="detail-row">
                    <label>Mã Phiếu Xuất Kho:</label>
                    <p><?php echo $pxk['ma_phieu_xuat_kho']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Mã Hóa Đơn:</label>  <!-- ← SỬA: Thay vì Mã Phiếu Bán Hàng -->
                    <p>#<?php echo $pxk['ma_hoa_don']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Mã Phiếu Đặt Hàng:</label>
                    <p>#<?php echo $pxk['ma_phieu_dat_hang']; ?></p>
                </div>
                <div class="detail-row">
                    <label>Khách Hàng:</label>
                    <p><?php echo htmlspecialchars($khach_hang['ten_khach_hang'] ?? 'N/A'); ?></p>
                </div>
                <div class="detail-row">
                    <label>Ngày Xuất:</label>
                    <p><?php echo date('d/m/Y', strtotime($pxk['ngay_xuat'])); ?></p>
                </div>
                <div class="detail-row">
                    <label>Người Xuất:</label>
                    <p><?php echo htmlspecialchars($pxk['nguoi_xuat'] ?? 'N/A'); ?></p>
                </div>
                <div class="detail-row">
                    <label>Trạng Thái:</label>
                    <p><span class='status-<?php echo strtolower(str_replace(' ', '-', $pxk['trang_thai'])); ?>'><?php echo $pxk['trang_thai']; ?></span></p>
                </div>
            </div>

            <div class="detail-section">
                <h3>Chi Tiết Sản Phẩm Xuất Kho</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản Phẩm</th>
                            <th>Đơn Vị</th>
                            <th style="text-align: right;">Số Lượng Xuất</th>
                            <th style="text-align: right;">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        if ($result_ct->num_rows > 0) {
                            while ($row = $result_ct->fetch_assoc()) {
                                $total += $row['thanh_tien'];
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['ten_san_pham']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['don_vi'] ?? 'Cái') . "</td>";
                                echo "<td style='text-align: right;'>" . $row['so_luong_xuat'] . "</td>";
                                echo "<td style='text-align: right;'>" . formatMoney($row['thanh_tien']) . " VNĐ</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center;'>Chưa có sản phẩm</td></tr>";
                        }
                        ?>
                    </tbody>
                    <?php if ($result_ct->num_rows > 0): ?>
                    <tfoot>
                        <tr style="font-weight: bold; border-top: 2px solid #e5e7eb;">
                            <td colspan="3" style="text-align: right;">Tổng Cộng:</td>
                            <td style="text-align: right; padding: 15px;"><?php echo formatMoney($total); ?> VNĐ</td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>

            <div class="form-actions">
                <a href="print.php?id=<?php echo $id; ?>" class="btn-secondary" target="_blank">🖨️ In Phiếu</a>
                <?php if ($pxk['trang_thai'] == 'Đang xuất'): ?>
                    <?php if (hasPermission('execute_pxk')): ?>
                        <a href="edit.php?id=<?php echo $id; ?>" class="btn-warning">Sửa</a>
                        <a href="delete.php?id=<?php echo $id; ?>" class="btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa phiếu xuất kho này?')">Xóa</a>
                    <?php endif; ?>
                    <a href="complete.php?id=<?php echo $id; ?>" class="btn-primary" onclick="return confirm('Bạn chắc chắn muốn hoàn thành xuất kho này?')">Hoàn Thành Xuất Kho</a>
                <?php endif; ?>
                <a href="list.php" class="btn-secondary">Quay Lại Danh Sách</a>
            </div>
        </main>
    </div>
</body>
</html>