# Hệ Thống Quản Lý Đơn Hàng

Một ứng dụng web hoàn chỉnh để quản lý quy trình kinh doanh bán hàng từ đặt hàng đến thanh toán.

## 🎯 Tính Năng Chính

### 1. **Quản Lý Khách Hàng**
   - Thêm, sửa, xóa khách hàng
   - Lưu trữ thông tin liên hệ (điện thoại, email, địa chỉ)
   - Theo dõi trạng thái hoạt động

### 2. **Quản Lý Sản Phẩm**
   - Quản lý danh mục sản phẩm
   - Lưu giá bán, đơn vị tính
   - Mô tả chi tiết sản phẩm

### 3. **Phiếu Đặt Hàng (PO)**
   - Tạo phiếu đặt hàng từ khách hàng
   - Thêm nhiều sản phẩm vào một đơn hàng
   - Duyệt/Hủy đơn hàng
   - Theo dõi trạng thái đơn

### 4. **Phiếu Bán Hàng**
   - Chuyển đổi PO thành phiếu bán hàng
   - Quản lý chi tiết bán hàng
   - Theo dõi trạng thái bán hàng

### 5. **Hóa Đơn & Thanh Toán**
   - Tạo hóa đơn từ phiếu bán hàng
   - Ghi nhận thanh toán
   - Quản lý công nợ
   - Hỗ trợ nhiều hình thức thanh toán

### 6. **Tồn Kho**
   - Theo dõi số lượng tồn kho
   - Cảnh báo khi hàng cạn (≤5 sản phẩm)
   - Cập nhật tự động khi xuất/nhập kho

### 7. **Quản Lý Trả Hàng**
   - Ghi nhận yêu cầu trả hàng
   - Lý do trả hàng
   - Theo dõi trạng thái xử lý

### 8. **Dashboard**
   - Thống kê tổng quan
   - Doanh thu tháng
   - Số đơn chờ duyệt
   - Hóa đơn chưa thanh toán

## 📋 Yêu Cầu Hệ Thống

- **Web Server**: Apache, Nginx hoặc IIS
- **PHP**: Phiên bản 7.4 trở lên
- **Database**: MySQL 5.7+ hoặc MariaDB 10.2+
- **Browser**: Chrome, Firefox, Safari, Edge (phiên bản gần đây)

## 🚀 Cài Đặt

### 1. Chuẩn Bị Cơ Sở Dữ Liệu

```sql
-- Sử dụng phpMyAdmin hoặc MySQL CLI
mysql -u root -p < quanlydonhang.sql
```

### 2. Cấu Hình Kết Nối

Chỉnh sửa file `config.php`:

```php
$servername = "localhost";
$username = "root";           // Tên user MySQL
$password = "";              // Mật khẩu MySQL
$dbname = "quanlydonhang";   // Tên database
```

### 3. Copy Files

Copy toàn bộ thư mục `QuanLyDonHang` vào:
- **XAMPP**: `C:\xampp\htdocs\`
- **WAMP**: `C:\wamp\www\`
- **LAMP**: `/var/www/html/`

### 4. Phân Quyền Thư Mục (Linux/Mac)

```bash
chmod -R 755 QuanLyDonHang
chmod -R 777 QuanLyDonHang/css
```

## 🔐 Đăng Nhập

**Tài khoản Demo:**
- **Username**: `admin`
- **Password**: `admin123`

> ⚠️ **Quan trọng**: Thay đổi mật khẩu ngay sau khi đăng nhập lần đầu!

## 📁 Cấu Trúc Thư Mục

```
QuanLyDonHang/
├── index.php                 # Trang chủ
├── login.php                 # Đăng nhập
├── logout.php                # Đăng xuất
├── config.php                # Cấu hình kết nối
├── quanlydonhang.sql         # Cơ sở dữ liệu
├── css/
│   └── style.css             # Stylesheet chính
├── khach_hang/
│   ├── list.php              # Danh sách khách hàng
│   ├── create.php            # Thêm khách hàng
│   ├── edit.php              # Sửa khách hàng
│   └── delete.php            # Xóa khách hàng
├── san_pham/
│   ├── list.php              # Danh sách sản phẩm
│   ├── create.php            # Thêm sản phẩm
│   ├── edit.php              # Sửa sản phẩm
│   └── delete.php            # Xóa sản phẩm
├── phieu_dat_hang/
│   ├── list.php              # Danh sách PO
│   ├── create.php            # Tạo PO mới
│   ├── detail.php            # Chi tiết PO
│   ├── approve.php           # Duyệt PO
│   ├── delete.php            # Xóa PO
│   └── edit.php              # Sửa PO
├── phieu_ban_hang/
│   ├── list.php              # Danh sách phiếu bán
│   └── detail.php            # Chi tiết phiếu bán
├── hoa_don/
│   ├── list.php              # Danh sách hóa đơn
│   └── detail.php            # Chi tiết hóa đơn
├── thanh_toan/
│   └── list.php              # Danh sách thanh toán
├── tra_hang/
│   ├── list.php              # Danh sách trả hàng
│   └── detail.php            # Chi tiết trả hàng
└── ton_kho/
    └── list.php              # Tồn kho
```

## 📊 Quy Trình Kinh Doanh

```
1. THÊM KHÁCH HÀNG
   ↓
2. THÊM SẢN PHẨM
   ↓
3. TẠO PHIẾU ĐẶT HÀNG (PO)
   ↓
4. DUYỆT PHIẾU ĐẶT HÀNG
   ↓
5. TẠO PHIẾU BÁN HÀNG
   ↓
6. XUẤT KHO
   ↓
7. TẠO HÓA ĐơN
   ↓
8. GIAO HÀNG & THANH TOÁN
   ↓
9. (NẾUE CỐ) TRẢ HÀNG
```

## 🗄️ Cơ Sở Dữ Liệu

### Bảng Chính

| Bảng | Mô Tả |
|------|-------|
| `khach_hang` | Thông tin khách hàng |
| `san_pham` | Danh mục sản phẩm |
| `phieu_dat_hang` | Phiếu đặt hàng |
| `chi_tiet_phieu_dat_hang` | Chi tiết PO |
| `phieu_ban_hang` | Phiếu bán hàng |
| `chi_tiet_phieu_ban_hang` | Chi tiết phiếu bán |
| `hoa_don` | Hóa đơn bán |
| `chi_tiet_hoa_don` | Chi tiết hóa đơn |
| `thanh_toan` | Ghi nhận thanh toán |
| `tra_hang` | Yêu cầu trả hàng |
| `chi_tiet_tra_hang` | Chi tiết trả hàng |
| `ton_kho` | Tồn kho sản phẩm |

## 🔧 Tính Năng Nâng Cao

### Triggers Tự Động

- **Cập nhật tổng tiền**: Khi thêm/sửa chi tiết PO
- **Tính toán tồn kho**: Khi xuất kho hoặc trả hàng
- **Tính thanh tiền**: Tự động nhân số lượng × giá

### Báo Cáo & Thống Kê

- Doanh thu theo tháng
- Số đơn hàng theo trạng thái
- Hóa đơn chưa thanh toán
- Sản phẩm cạn tồn

## 🖥️ Giao Diện

### Màu Sắc
- **Chính**: Tím (Gradient: #667eea → #764ba2)
- **Thành công**: Xanh lá (#10b981)
- **Cảnh báo**: Vàng (#f59e0b)
- **Lỗi**: Đỏ (#ef4444)

### Responsive Design
- ✅ Hiển thị hoàn hảo trên thiết bị di động
- ✅ Bảng cuộn ngang trên điện thoại
- ✅ Menu responsive

## 🐛 Xử Lý Lỗi

### Lỗi Phổ Biến

| Lỗi | Nguyên Nhân | Giải Pháp |
|-----|-----------|----------|
| Không kết nối DB | Sai tài khoản/mật khẩu | Kiểm tra `config.php` |
| Trống trang | PHP không chạy | Kiểm tra web server |
| Lỗi phân quyền | Thư mục không có quyền | `chmod 755` thư mục |

## 📝 Ghi Chú Bảo Mật

1. **Thay đổi mật khẩu mặc định** ngay sau cài đặt
2. **Sử dụng HTTPS** trong production
3. **Xác thực người dùng** trước mỗi hành động
4. **Đóng cổng 3306** (MySQL) khỏi internet
5. **Sao lưu database** định kỳ

## 📞 Hỗ Trợ

Nếu gặp vấn đề:

1. Kiểm tra file `config.php`
2. Xem logs PHP error
3. Kiểm tra quyền thư mục
4. Kiểm tra phiên bản PHP/MySQL

## 📄 Giấy Phép

© 2025 Hệ Thống Quản Lý Đơn Hàng. All rights reserved.

---

**Phiên bản**: 1.0.0  
**Ngày phát hành**: 01/12/2025  
**Cập nhật cuối**: 01/12/2025
