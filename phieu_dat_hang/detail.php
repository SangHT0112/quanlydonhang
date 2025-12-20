<?php
include '../config.php';
checkLogin();

$id = intval($_GET['id']);

// Lấy chi tiết PO
$stmt = $conn->prepare("
    SELECT p.*, u.full_name AS nguoi_tao, a.full_name AS nguoi_duyet
    FROM phieu_dat_hang p
    LEFT JOIN users u ON p.created_by = u.id
    LEFT JOIN users a ON p.approved_by = a.id
    WHERE p.ma_phieu_dat_hang = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$po = $result->fetch_assoc();

if (!$po) {
    header('Location: list.php?error=PO không tồn tại');
    exit;
}

// Tên khách hàng
$kh_query = $conn->query("SELECT ten_khach_hang FROM khach_hang WHERE ma_khach_hang = " . intval($po['ma_khach_hang']));
$ten_kh = $kh_query->fetch_assoc()['ten_khach_hang'] ?? 'N/A';

// Chi tiết sản phẩm
$stmt_items = $conn->prepare("
    SELECT 
        ct.ma_san_pham,
        sp.ten_san_pham,
        sp.don_vi,
        ct.so_luong,
        ct.gia_dat,
        (ct.so_luong * ct.gia_dat) AS thanh_tien
    FROM chi_tiet_phieu_dat_hang ct
    JOIN san_pham sp ON ct.ma_san_pham = sp.ma_san_pham
    WHERE ct.ma_phieu_dat_hang = ?
");
$stmt_items->bind_param("i", $id);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>
<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết PO #<?= $id ?></title>
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

    <main class="flex-1 container mx-auto px-4 py-12 max-w-6xl">
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-bold text-indigo-800 mb-2">Chi Tiết Phiếu Đặt Hàng</h1>
            <p class="text-2xl text-purple-700 font-semibold">#<?= $id ?></p>
        </div>

        <!-- Card Thông Tin PO -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 mb-10 border border-indigo-100">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm font-medium text-gray-600">Trạng Thái</p>
                    <p class="mt-2">
                        <span class="inline-flex px-6 py-3 text-sm font-bold rounded-full border-4 
                            <?= $po['trang_thai'] == 'Chờ duyệt' ? 'bg-red-100 text-red-800 border-red-500' : 'bg-green-100 text-green-800 border-green-500' ?>">
                            <?= htmlspecialchars($po['trang_thai']) ?>
                        </span>
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-600">Khách Hàng</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900"><?= htmlspecialchars($ten_kh) ?></p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-600">Ngày Đặt</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900"><?= date('d/m/Y', strtotime($po['ngay_dat'])) ?></p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-600">Tổng Tiền</p>
                    <p class="mt-2 text-2xl font-bold text-indigo-600"><?= formatMoney($po['tong_tien']) ?> VNĐ</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-600">Người Tạo</p>
                    <p class="mt-2 text-lg font-medium text-gray-800"><?= htmlspecialchars($po['nguoi_tao'] ?? 'N/A') ?></p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-600">Người Duyệt</p>
                    <p class="mt-2 text-lg font-medium text-gray-800"><?= htmlspecialchars($po['nguoi_duyet'] ?? 'Chưa duyệt') ?></p>
                </div>
            </div>

            <?php if (!empty($po['ghi_chu'])): ?>
            <div class="mt-8">
                <p class="text-sm font-medium text-gray-600">Ghi Chú</p>
                <p class="mt-2 p-4 bg-gray-50 rounded-xl text-gray-800 italic"><?= htmlspecialchars($po['ghi_chu']) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Bảng Chi Tiết Sản Phẩm -->
        <h3 class="text-2xl font-bold text-indigo-800 mb-6">Chi Tiết Sản Phẩm</h3>
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                        <tr>
                            <th class="px-8 py-5 text-left font-semibold">Sản Phẩm</th>
                            <th class="px-8 py-5 text-center font-semibold">Số Lượng</th>
                            <th class="px-8 py-5 text-right font-semibold">Giá Đặt</th>
                            <th class="px-8 py-5 text-right font-semibold">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while($r = $items->fetch_assoc()): ?>
                        <tr class="hover:bg-indigo-50 transition">
                            <td class="px-8 py-6">
                                <div>
                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($r['ten_san_pham']) ?></p>
                                    <p class="text-sm text-gray-500">Đơn vị: <?= htmlspecialchars($r['don_vi']) ?></p>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center font-bold text-xl text-indigo-600"><?= $r['so_luong'] ?></td>
                            <td class="px-8 py-6 text-right font-medium text-gray-700"><?= formatMoney($r['gia_dat']) ?> VNĐ</td>
                            <td class="px-8 py-6 text-right font-bold text-2xl text-purple-600"><?= formatMoney($r['thanh_tien']) ?> VNĐ</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Nút Hành Động -->
        <div class="mt-10 flex flex-wrap justify-center gap-4">
            <a href="list.php" class="px-8 py-4 bg-gray-600 text-white font-bold rounded-xl hover:bg-gray-700 transition shadow-lg">
                ← Quay Lại Danh Sách
            </a>

            <?php if ($po['trang_thai'] == 'Chờ duyệt' && hasPermission('submit_po')): ?>
                <a href="submit.php?id=<?= $id ?>" class="px-8 py-4 bg-amber-500 text-white font-bold rounded-xl hover:bg-amber-600 transition shadow-lg">
                    Gửi Duyệt
                </a>
            <?php endif; ?>

            <?php if ($po['trang_thai'] == 'Chờ duyệt' && hasPermission('approve_po')): ?>
                <a href="approve.php?id=<?= $id ?>" class="px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-xl hover:from-green-700 hover:to-emerald-700 transition shadow-lg transform hover:scale-105">
                    Duyệt Phiếu
                </a>
            <?php endif; ?>

            <?php if ($po['trang_thai'] == 'Chờ duyệt' && hasPermission('edit_po')): ?>
                <a href="edit.php?id=<?= $id ?>" class="px-8 py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg">
                    Sửa Phiếu
                </a>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer (nếu có) -->
    <!-- <?php include '../footer.php'; ?> -->
</body>
</html>