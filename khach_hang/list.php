<?php
include '../config.php';
checkLogin();
requirePermission('manage_users');

$search = $_GET['search'] ?? '';
$trang_thai = $_GET['trang_thai'] ?? '';

$sql = "SELECT * FROM khach_hang WHERE 1=1";

if ($search) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (ten_khach_hang LIKE '%$search%' OR dien_thoai LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($trang_thai) {
    $trang_thai = $conn->real_escape_string($trang_thai);
    $sql .= " AND trang_thai = '$trang_thai'";
}

$sql .= " ORDER BY ngay_tao DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Khách Hàng</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<style>
    /* ==================== LIST VIEW KHÁCH HÀNG - CHUYÊN NGHIỆP & FULL WIDTH ==================== */

/* Main content full height & đẹp hơn */
main {
    flex: 1;
    padding: 40px 20px;
    background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
    min-height: 100vh;
}

/* Tiêu đề trang */
main > h1 {
    font-size: 32px;
    font-weight: 800;
    color: #4338ca;
    margin-bottom: 10px;
    text-align: center;
}

main > h1 + p {
    text-align: center;
    color: #64748b;
    margin-bottom: 40px;
    font-size: 18px;
}

/* Filter Section - Card đẹp */
.filter-section {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    border: 1px solid #e0e7ff;
}

.filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    align-items: end;
}

.filter-form input,
.filter-form select {
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 16px;
    transition: all 0.3s;
}

.filter-form input:focus,
.filter-form select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
}

.filter-form button,
.filter-form a {
    padding: 14px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    text-align: center;
    transition: all 0.3s;
    cursor: pointer;
}

/* Actions Section - Nút thêm mới nổi bật */
.actions-section {
    text-align: right;
    margin-bottom: 30px;
}

.actions-section .btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    padding: 16px 32px;
    font-size: 18px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.actions-section .btn-primary:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

/* Table List View - Siêu chuyên nghiệp */
.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid #e0e7ff;
}

.table thead {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.table th {
    color: white;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 14px;
    padding: 20px 24px;
    text-align: left;
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: linear-gradient(to right, #f8f9ff, #f0f4ff);
    transform: scale(1.01);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.1);
}

.table td {
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}

/* Tên khách hàng nổi bật */
.table td:first-child strong {
    font-size: 18px;
    color: #1e293b;
}

/* Trạng thái khách hàng - Hoạt động (xanh) / Ngừng (đỏ) */
.table .status-hoạt-động {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
    border: 3px solid #10b981;
    padding: 10px 20px;
    border-radius: 9999px;
    font-weight: 700;
    text-transform: uppercase;
    min-width: 130px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
}

.table .status-ngừng-hoạt-động {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #7f1d1d;
    border: 3px solid #ef4444;
    padding: 10px 20px;
    border-radius: 9999px;
    font-weight: 700;
    text-transform: uppercase;
    min-width: 130px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
}

/* Nút hành động - Sửa (vàng) / Xóa (đỏ) riêng biệt */
.table .btn-warning {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 600;
    margin-right: 10px;
    box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
    transition: all 0.3s;
}

.table .btn-warning:hover {
    background: #d97706;
    transform: translateY(-2px);
}

.table .btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    transition: all 0.3s;
}

.table .btn-danger:hover {
    background: #b91c1c;
    transform: translateY(-2px);
}

/* Empty state */
.table tbody td[colspan] {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
    font-size: 18px;
    font-style: italic;
}

/* Responsive - Mobile: bảng thành card */
@media (max-width: 768px) {
    .filter-form {
        grid-template-columns: 1fr;
    }

    .table thead {
        display: none;
    }

    .table tbody tr {
        display: block;
        margin-bottom: 20px;
        padding: 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        border: 1px solid #e0e7ff;
    }

    .table td {
        display: block;
        text-align: right;
        padding: 12px 0;
        border: none;
        position: relative;
    }

    .table td::before {
        content: attr(data-label);
        position: absolute;
        left: 0;
        font-weight: 600;
        color: #6366f1;
        text-transform: uppercase;
        font-size: 13px;
    }

    .table td:last-child {
        text-align: center;
    }
}

/* Thêm data-label cho mobile */
.table th:nth-child(1), .table td:nth-child(1) { data-label: "Tên KH"; }
.table th:nth-child(2), .table td:nth-child(2) { data-label: "Điện thoại"; }
.table th:nth-child(3), .table td:nth-child(3) { data-label: "Email"; }
.table th:nth-child(4), .table td:nth-child(4) { data-label: "Địa chỉ"; }
.table th:nth-child(5), .table td:nth-child(5) { data-label: "Trạng thái"; }
.table th:nth-child(6), .table td:nth-child(6) { data-label: "Ngày tạo"; }
.table th:nth-child(7), .table td:nth-child(7) { data-label: "Hành động"; }
</style>
<body>
     <?php include '../header.php'; ?>
    <div class="container">
       
        <h1>Danh Sách Khách Hàng</h1>
           

        <main>
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Tìm kiếm tên, SĐT, email..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="trang_thai">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Hoạt động" <?php if ($trang_thai == 'Hoạt động') echo 'selected'; ?>>Hoạt động</option>
                        <option value="Ngừng hoạt động" <?php if ($trang_thai == 'Ngừng hoạt động') echo 'selected'; ?>>Ngừng hoạt động</option>
                    </select>

                    <button type="submit" class="btn-primary">Tìm Kiếm</button>
                    <a href="list.php" class="btn-secondary">Xóa Lọc</a>
                </form>
            </div>

            <div class="actions-section">
                <a href="create.php" class="btn-primary">+ Thêm Khách Hàng Mới</a>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Tên Khách Hàng</th>
                        <th>Điện Thoại</th>
                        <th>Email</th>
                        <th>Địa Chỉ</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>" . htmlspecialchars($row['ten_khach_hang']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row['dien_thoai'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($row['dia_chi'] ?? '') . "</td>";
                            echo "<td><span class='status-" . strtolower(str_replace(' ', '-', $row['trang_thai'])) . "'>" . $row['trang_thai'] . "</span></td>";
                            echo "<td>" . date('d/m/Y', strtotime($row['ngay_tao'])) . "</td>";
                            echo "<td>";
                            echo "<a href='edit.php?id=" . $row['ma_khach_hang'] . "' class='btn-warning'>Sửa</a> ";
                            echo "<a href='delete.php?id=" . $row['ma_khach_hang'] . "' class='btn-danger' onclick='return confirm(\"Bạn chắc chắn muốn xóa?\")'>Xóa</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center;'>Không có khách hàng</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>