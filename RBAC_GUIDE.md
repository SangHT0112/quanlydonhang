# Hướng Dẫn Hệ Thống RBAC (Role-Based Access Control)

## 📋 Tổng Quan

Hệ thống quản lý đơn hàng đã được tích hợp RBAC với 4 vai trò chính:

| Vai Trò | Quyền Hạn | Chức Năng |
|---------|-----------|----------|
| **Admin** | manage_users | Quản lý người dùng, vai trò, quyền hạn |
| **Sale** | create_po, edit_po, approve_po | Tạo, sửa, duyệt phiếu đặt hàng |
| **Kho** | execute_pxk, create_pxk | Xuất kho, quản lý tồn kho |
| **Kế Toán** | create_bh, create_invoice, issue_invoice, record_payment, create_return | Tạo phiếu bán hàng, hóa đơn, thanh toán, xử lý trả hàng |

---

## 🔐 Các Hàm RBAC Trong config.php

### 1. `currentUser()`
Lấy thông tin người dùng hiện tại từ session và database.

```php
$user = currentUser();
if ($user) {
    echo $user['full_name'];  // Tên đầy đủ
    echo $user['username'];   // Tên đăng nhập
}
```

### 2. `hasPermission($permissionName)`
Kiểm tra xem người dùng có quyền cụ thể không.

```php
if (hasPermission('create_po')) {
    echo "Có quyền tạo phiếu đặt hàng";
}
```

### 3. `hasRole($roleName)`
Kiểm tra xem người dùng có vai trò cụ thể không.

```php
if (hasRole('sale')) {
    echo "Đây là nhân viên bán hàng";
}
```

### 4. `requirePermission($permissionName)`
Bảo vệ trang, trả lại lỗi 403 nếu không có quyền.

```php
<?php
include '../config.php';
checkLogin();
requirePermission('create_po');  // ← Thêm dòng này
// Mã trang chỉ chạy nếu người dùng có quyền
```

### 5. `getUserRoles()`
Lấy danh sách tất cả vai trò của người dùng hiện tại.

```php
$roles = getUserRoles();
foreach ($roles as $role) {
    echo $role['name'];  // admin, sale, kho, ketoan
}
```

### 6. `getUserPermissions()`
Lấy danh sách tất cả quyền hạn của người dùng hiện tại.

```php
$permissions = getUserPermissions();
foreach ($permissions as $perm) {
    echo $perm['name'];  // create_po, approve_po, etc.
}
```

---

## 📝 Cách Sử Dụng Trong Các Trang

### Ví Dụ 1: Bảo Vệ Trang Tạo Phiếu Đặt Hàng

```php
<?php
include '../config.php';
checkLogin();
requirePermission('create_po');  // Chỉ người có quyền create_po mới vào được

// Phần còn lại của code
if ($_POST) {
    // Xử lý tạo phiếu
}
?>
```

### Ví Dụ 2: Hiển Thị Button Có Điều Kiện

```php
<?php if ($po['trang_thai'] == 'Chờ duyệt'): ?>
    <?php if (hasPermission('edit_po')): ?>
        <a href="edit.php?id=<?php echo $id; ?>" class="btn-warning">Sửa</a>
    <?php endif; ?>
    
    <?php if (hasPermission('approve_po')): ?>
        <a href="approve.php?id=<?php echo $id; ?>" class="btn-primary">Duyệt</a>
    <?php endif; ?>
<?php endif; ?>
```

### Ví Dụ 3: Ẩn Menu Theo Vai Trò

```php
<nav class="sidebar">
    <?php if (hasPermission('create_po')): ?>
        <a href="phieu_dat_hang/list.php">Phiếu Đặt Hàng</a>
    <?php endif; ?>
    
    <?php if (hasPermission('create_bh')): ?>
        <a href="phieu_ban_hang/list.php">Phiếu Bán Hàng</a>
    <?php endif; ?>
    
    <?php if (hasPermission('record_payment')): ?>
        <a href="thanh_toan/list.php">Thanh Toán</a>
    <?php endif; ?>
</nav>
```

---

## 👥 Tài Khoản Demo

Dùng tài khoản sau để test hệ thống:

| Username | Password | Vai Trò | Quyền Hạn |
|----------|----------|---------|-----------|
| admin | admin123 | Admin | manage_users |
| sale1 | sale123 | Sale | create_po, edit_po, approve_po |
| kho1 | kho123 | Kho | execute_pxk, create_pxk |
| ketoan1 | ketoan123 | Kế Toán | create_bh, create_invoice, issue_invoice, record_payment, create_return |

---

## 🔄 Quy Trình Workflow

```
1. SALE tạo PO (Phiếu Đặt Hàng) → create_po
   ↓
2. SALE duyệt PO → approve_po
   ↓
3. KỀ TOÁN tạo PBH (Phiếu Bán Hàng) → create_bh
   ↓
4. KHO thực hiện PXK (Xuất Kho) → execute_pxk
   ↓
5. KỀ TOÁN tạo HĐ (Hóa Đơn) → create_invoice
   ↓
6. KỌ TOÁN ghi nhận Thanh Toán → record_payment
   ↓
7. (Nếu cần) KỀ TOÁN xử lý Trả Hàng → create_return
```

---

## 📊 Bảng Quyền Hạn Chi Tiết

| Permission | Mô Tả | Vai Trò |
|------------|-------|---------|
| manage_users | Quản lý khách hàng, sản phẩm, người dùng | Admin |
| create_po | Tạo phiếu đặt hàng | Sale |
| edit_po | Sửa phiếu đặt hàng | Sale |
| approve_po | Duyệt phiếu đặt hàng | Sale |
| create_bh | Tạo phiếu bán hàng | Kế Toán |
| create_pxk | Tạo phiếu xuất kho | Kho |
| execute_pxk | Thực hiện xuất kho | Kho |
| create_invoice | Tạo hóa đơn | Kế Toán |
| issue_invoice | Phát hành hóa đơn | Kế Toán |
| record_payment | Ghi nhận thanh toán | Kế Toán |
| create_return | Tạo phiếu trả hàng | Kế Toán |
| approve_return | Duyệt trả hàng | Kế Toán |

---

## 🛠️ Thêm Quyền Hạn Mới (Dành Cho Admin)

1. **Thêm permission vào database:**
   ```sql
   INSERT INTO permissions (name, description) 
   VALUES ('new_permission', 'Mô tả quyền hạn');
   ```

2. **Gán permission cho vai trò:**
   ```sql
   INSERT INTO role_permissions (role_id, permission_id)
   SELECT r.id, p.id 
   FROM roles r, permissions p
   WHERE r.name = 'sale' AND p.name = 'new_permission';
   ```

3. **Dùng trong code:**
   ```php
   <?php
   include '../config.php';
   checkLogin();
   requirePermission('new_permission');
   ?>
   ```

---

## 🔍 Kiểm Tra Quyền Hạn Tại Chỗ

Thêm code debug sau vào bất kỳ trang nào:

```php
<?php
echo "<pre>";
echo "Vai trò: " . print_r(getUserRoles(), true);
echo "Quyền hạn: " . print_r(getUserPermissions(), true);
echo "</pre>";
?>
```

---

## ⚠️ Các Lỗi Thường Gặp

| Lỗi | Nguyên Nhân | Giải Pháp |
|-----|-----------|----------|
| 403 - Không có quyền | Thiếu permission | Đăng nhập với tài khoản có quyền hoặc thêm permission từ DB |
| Không hiển thị menu | hasPermission() trả false | Kiểm tra user_roles và role_permissions trong DB |
| Permission không hoạt động | Chưa thêm requirePermission() | Thêm `requirePermission('permission_name');` vào đầu trang |

---

## 📞 Hỗ Trợ

Để xem chi tiết hệ thống, kiểm tra:
- `config.php` - Các hàm RBAC
- `login.php` - Xác thực người dùng
- `index.php` - Ví dụ conditional rendering

Thực thi các lệnh SQL để xem dữ liệu quyền hạn:
```sql
SELECT * FROM users;
SELECT * FROM roles;
SELECT * FROM permissions;
SELECT * FROM user_roles;
SELECT * FROM role_permissions;
```
