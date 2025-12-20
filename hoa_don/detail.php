<?php
include '../config.php';
checkLogin();
requirePermission('view_invoice');

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM hoa_don WHERE ma_hoa_don = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit;
}

$hoa_don = $result->fetch_assoc();

// Chi tiết sản phẩm
$stmt_ct = $conn->prepare("
    SELECT ct.*, sp.ten_san_pham, sp.don_vi 
    FROM chi_tiet_hoa_don ct
    JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
    WHERE ct.ma_hoa_don = ?
");
$stmt_ct->bind_param("i", $id);
$stmt_ct->execute();
$items = $stmt_ct->get_result();

// Lịch sử thanh toán
$stmt_tt = $conn->prepare("SELECT * FROM thanh_toan WHERE ma_hoa_don = ? ORDER BY ngay_tra DESC");
$stmt_tt->bind_param("i", $id);
$stmt_tt->execute();
$payments = $stmt_tt->get_result();

$tong_da_tra = 0;
while ($pay = $payments->fetch_assoc()) {
    $tong_da_tra += $pay['so_tien_tra'];
}
$con_no = $hoa_don['tong_tien'] - $tong_da_tra;

// Reset con trỏ để hiển thị bảng
$payments->data_seek(0);
?>
<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Hóa Đơn #<?= $id ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#8b5cf6',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex flex-col">

    <?php include '../header.php'; ?>

    <main class="flex-1 container mx-auto px-4 py-12 max-w-7xl">
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-bold text-indigo-800 mb-2">Chi Tiết Hóa Đơn</h1>
            <p class="text-2xl text-purple-700 font-semibold">#<?= $id ?></p>
        </div>

        <!-- Card Thông Tin Hóa Đơn -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 mb-10 border border-indigo-100">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div>
                    <p class="text-sm font-medium text-gray-600">Trạng Thái</p>
                    <p class="mt-3">
                        <span class="inline-flex px-6 py-3 text-sm font-bold rounded-full border-4 
                            <?= $hoa_don['trang_thai'] == 'Đã thanh toán' ? 'bg-green-100 text-green-800 border-green-500' : 'bg-red-100 text-red-800 border-red-500' ?>">
                            <?= htmlspecialchars($hoa_don['trang_thai']) ?>
                        </span>
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-600">Ngày Xuất Hóa Đơn</p>
                    <p class="mt-3 text-xl font-semibold text-gray-900"><?= date('d/m/Y', strtotime($hoa_don['ngay_xuat_hd'])) ?></p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-600">Khuyến Mại</p>
                    <p class="mt-3 text-xl font-semibold text-orange-600"><?= formatMoney($hoa_don['khuyen_mai_tong']) ?> VNĐ</p>
                </div>

                <div class="lg:col-span-3">
                    <div class="border-t pt-6 mt-6 grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                        <div>
                            <p class="text-sm text-gray-600">Tổng Tiền Hóa Đơn</p>
                            <p class="text-3xl font-bold text-indigo-600 mt-2"><?= formatMoney($hoa_don['tong_tien']) ?> VNĐ</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Đã Thanh Toán</p>
                            <p class="text-3xl font-bold text-green-600 mt-2"><?= formatMoney($tong_da_tra) ?> VNĐ</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Còn Nợ</p>
                            <p class="text-3xl font-bold text-red-600 mt-2"><?= formatMoney($con_no) ?> VNĐ</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chi Tiết Sản Phẩm -->
        <h3 class="text-2xl font-bold text-indigo-800 mb-6">Chi Tiết Sản Phẩm</h3>
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden mb-10">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                        <tr>
                            <th class="px-8 py-5 text-left font-semibold">Sản Phẩm</th>
                            <th class="px-8 py-5 text-center font-semibold">Đơn Vị</th>
                            <th class="px-8 py-5 text-right font-semibold">Số Lượng</th>
                            <th class="px-8 py-5 text-right font-semibold">Đơn Giá</th>
                            <th class="px-8 py-5 text-right font-semibold">Chiết Khấu</th>
                            <th class="px-8 py-5 text-right font-semibold">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($row = $items->fetch_assoc()): ?>
                        <tr class="hover:bg-indigo-50 transition">
                            <td class="px-8 py-6 font-medium text-gray-900"><?= htmlspecialchars($row['ten_san_pham']) ?></td>
                            <td class="px-8 py-6 text-center text-gray-700"><?= htmlspecialchars($row['don_vi']) ?></td>
                            <td class="px-8 py-6 text-right font-bold text-indigo-600"><?= $row['so_luong'] ?></td>
                            <td class="px-8 py-6 text-right text-gray-700"><?= formatMoney($row['don_gia']) ?> VNĐ</td>
                            <td class="px-8 py-6 text-right text-orange-600"><?= formatMoney($row['chiet_khau']) ?> VNĐ</td>
                            <td class="px-8 py-6 text-right font-bold text-purple-600 text-xl"><?= formatMoney($row['thanh_tien']) ?> VNĐ</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lịch Sử Thanh Toán -->
        <h3 class="text-2xl font-bold text-indigo-800 mb-6">Lịch Sử Thanh Toán</h3>
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                        <tr>
                            <th class="px-8 py-5 text-left font-semibold">Ngày Trả</th>
                            <th class="px-8 py-5 text-right font-semibold">Số Tiền</th>
                            <th class="px-8 py-5 text-left font-semibold">Loại Thanh Toán</th>
                            <th class="px-8 py-5 text-left font-semibold">Ghi Chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($payments->num_rows > 0): ?>
                            <?php while ($pay = $payments->fetch_assoc()): ?>
                            <tr class="hover:bg-indigo-50 transition">
                                <td class="px-8 py-6 font-medium"><?= date('d/m/Y', strtotime($pay['ngay_tra'])) ?></td>
                                <td class="px-8 py-6 text-right font-bold text-green-600 text-xl"><?= formatMoney($pay['so_tien_tra']) ?> VNĐ</td>
                                <td class="px-8 py-6"><?= htmlspecialchars($pay['loai_thanh_toan']) ?></td>
                                <td class="px-8 py-6 text-gray-600"><?= htmlspecialchars($pay['ghi_chu'] ?? '-') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-8 py-16 text-center text-gray-500 text-lg italic">
                                    Chưa có lịch sử thanh toán
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Nút Quay Lại -->
        <div class="mt-12 text-center">
            <a href="list.php" class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-gray-600 to-gray-700 text-white text-xl font-bold rounded-xl hover:from-gray-700 hover:to-gray-800 shadow-2xl transition transform hover:scale-105">
                ← Quay Lại Danh Sách Hóa Đơn
            </a>
        </div>
    </main>
</body>
</html>