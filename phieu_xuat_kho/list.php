<?php
include '../config.php';
checkLogin();
requirePermission('view_inventory');

$search = $_GET['search'] ?? '';
$trang_thai = $_GET['trang_thai'] ?? '';

$sql = "
    SELECT 
        pxk.ma_phieu_xuat_kho,
        pxk.ngay_xuat,
        pxk.nguoi_xuat,
        pxk.trang_thai,
        pbh.tong_tien,
        pdh.ma_phieu_dat_hang,
        k.ten_khach_hang
    FROM phieu_xuat_kho pxk
    JOIN phieu_ban_hang pbh ON pxk.ma_phieu_ban_hang = pbh.ma_phieu_ban_hang
    JOIN phieu_dat_hang pdh ON pbh.ma_phieu_dat_hang = pdh.ma_phieu_dat_hang
    JOIN khach_hang k ON pdh.ma_khach_hang = k.ma_khach_hang
    WHERE 1=1
";

$params = [];
$types = "";

if ($search) {
    $sql .= " AND (k.ten_khach_hang LIKE ? OR pxk.ma_phieu_xuat_kho LIKE ? OR pxk.nguoi_xuat LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($trang_thai) {
    $sql .= " AND pxk.trang_thai = ?";
    $params[] = $trang_thai;
    $types .= "s";
}

$sql .= " ORDER BY pxk.ngay_xuat DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Phiếu Xuất Kho</title>
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
        <?php include '../chat/chat.php'; ?>
    <main class="flex-1 container mx-auto px-4 py-12 max-w-7xl">
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-bold text-indigo-800 mb-2">Danh Sách Phiếu Xuất Kho</h1>
            <p class="text-xl text-gray-600">Quản lý và theo dõi quá trình xuất hàng</p>
        </div>

        <!-- Filter & Create Button -->
        <div class="bg-white rounded-2xl shadow-lg p-4 mb-8 border border-indigo-100">
            <form method="GET"
                class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center">

                <!-- Search -->
                <input type="text"
                    name="search"
                    placeholder="Tìm khách hàng, người xuất, mã PXK..."
                    value="<?= htmlspecialchars($search) ?>"
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition">

                <!-- Trạng thái -->
                <select name="trang_thai"
                        class="w-full lg:w-56 px-4 py-3 border border-gray-300 rounded-lg focus:border-indigo-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Đang xuất" <?= $trang_thai == 'Đang xuất' ? 'selected' : '' ?>>Đang xuất</option>
                    <option value="Hoàn thành" <?= $trang_thai == 'Hoàn thành' ? 'selected' : '' ?>>Hoàn thành</option>
                </select>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                        Lọc
                    </button>

                    <a href="list.php"
                    class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                        Reset
                    </a>
                </div>

                <!-- Create PXK -->
                <?php if (hasPermission('create_pxk')): ?>
                <div class="lg:ml-auto">
                    <a href="../hoa_don/list.php"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-lg hover:from-green-700 hover:to-emerald-700 shadow transition">
                        + Tạo PXK
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>


        <!-- Table List -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                        <tr>
                            <th class="px-8 py-6 text-left font-semibold">Mã PXK</th>
                            <th class="px-8 py-6 text-left font-semibold">Ngày Xuất</th>
                            <th class="px-8 py-6 text-left font-semibold">Người Xuất</th>
                            <th class="px-8 py-6 text-left font-semibold">Khách Hàng</th>
                            <th class="px-8 py-6 text-right font-semibold">Tổng Tiền</th>
                            <th class="px-8 py-6 text-center font-semibold">Trạng Thái</th>
                            <th class="px-8 py-6 text-center font-semibold">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $status_class = $row['trang_thai'] == 'Hoàn thành' ? 'bg-green-100 text-green-800 border-green-500' : 'bg-orange-100 text-orange-800 border-orange-500';
                            ?>
                                <tr class="hover:bg-indigo-50 transition">
                                    <td class="px-8 py-6 font-bold text-indigo-700">#<?= $row['ma_phieu_xuat_kho'] ?></td>
                                    <td class="px-8 py-6 font-medium"><?= date('d/m/Y', strtotime($row['ngay_xuat'])) ?></td>
                                    <td class="px-8 py-6"><?= htmlspecialchars($row['nguoi_xuat']) ?></td>
                                    <td class="px-8 py-6 font-semibold text-gray-900"><?= htmlspecialchars($row['ten_khach_hang']) ?></td>
                                    <td class="px-8 py-6 text-right font-bold text-purple-600 text-xl"><?= formatMoney($row['tong_tien']) ?> VNĐ</td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="inline-flex px-6 py-3 text-sm font-bold rounded-full border-4 <?= $status_class ?>">
                                            <?= $row['trang_thai'] ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <div class="flex justify-center gap-4">
                                            <a href="detail.php?id=<?= $row['ma_phieu_xuat_kho'] ?>"
                                               class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg transition">
                                                Xem Chi Tiết
                                            </a>
                                            <?php if ($row['trang_thai'] == 'Đang xuất' && hasPermission('execute_pxk')): ?>
                                                <a href="execute.php?id=<?= $row['ma_phieu_xuat_kho'] ?>"
                                                   onclick="return confirm('Xác nhận hoàn thành xuất kho?')"
                                                   class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-xl hover:from-green-700 hover:to-emerald-700 shadow-lg transition transform hover:scale-105">
                                                    Thực Xuất
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-8 py-20 text-center text-gray-500 text-xl italic">
                                    Chưa có phiếu xuất kho nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>