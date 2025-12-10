-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 11:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quanlydonhang`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user` varchar(100) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user`, `action`, `details`, `timestamp`) VALUES
(1, 'Admin', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 10:03:21'),
(2, 'Admin', 'LOGOUT', 'Đăng xuất', '2025-12-01 10:37:18'),
(3, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 10:41:00'),
(4, 'Kế Toán 1', 'LOGOUT', 'Đăng xuất', '2025-12-01 10:42:38'),
(5, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 10:42:43'),
(6, 'Sale 1', 'LOGOUT', 'Đăng xuất', '2025-12-01 11:25:28'),
(7, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 11:25:37'),
(8, 'Kế Toán 1', 'LOGOUT', 'Đăng xuất', '2025-12-01 11:48:52'),
(9, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 11:48:57'),
(10, 'Sale 1', 'LOGOUT', 'Đăng xuất', '2025-12-01 11:49:10'),
(11, 'Nhân viên Kho 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 11:49:15'),
(12, 'Nhân viên Kho 1', 'LOGOUT', 'Đăng xuất', '2025-12-01 11:49:19'),
(13, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 11:49:26');

-- --------------------------------------------------------

--
-- Table structure for table `chi_tiet_hoa_don`
--

CREATE TABLE `chi_tiet_hoa_don` (
  `ma_chi_tiet` int(11) NOT NULL,
  `ma_hoa_don` int(11) NOT NULL,
  `ma_san_pham` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `don_gia` decimal(15,2) NOT NULL,
  `chiet_khau` decimal(15,2) DEFAULT 0.00,
  `thanh_tien` decimal(15,2) GENERATED ALWAYS AS (`so_luong` * `don_gia` - `chiet_khau`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chi_tiet_hoa_don`
--

INSERT INTO `chi_tiet_hoa_don` (`ma_chi_tiet`, `ma_hoa_don`, `ma_san_pham`, `so_luong`, `don_gia`, `chiet_khau`) VALUES
(1, 1, 1, 2, 15000000.00, 0.00),
(2, 1, 2, 5, 500000.00, 0.00);

--
-- Triggers `chi_tiet_hoa_don`
--
DELIMITER $$
CREATE TRIGGER `cap_nhat_tong_tien_hoa_don_sau_sua` AFTER UPDATE ON `chi_tiet_hoa_don` FOR EACH ROW BEGIN
    UPDATE hoa_don SET tong_tien = (SELECT SUM(thanh_tien) FROM chi_tiet_hoa_don WHERE ma_hoa_don = NEW.ma_hoa_don)
    WHERE ma_hoa_don = NEW.ma_hoa_don;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `cap_nhat_tong_tien_hoa_don_sau_them` AFTER INSERT ON `chi_tiet_hoa_don` FOR EACH ROW BEGIN
    UPDATE hoa_don SET tong_tien = (SELECT SUM(thanh_tien) FROM chi_tiet_hoa_don WHERE ma_hoa_don = NEW.ma_hoa_don)
    WHERE ma_hoa_don = NEW.ma_hoa_don;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `chi_tiet_phieu_ban_hang`
--

CREATE TABLE `chi_tiet_phieu_ban_hang` (
  `ma_chi_tiet` int(11) NOT NULL,
  `ma_phieu_ban_hang` int(11) NOT NULL,
  `ma_san_pham` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `gia_ban` decimal(15,2) NOT NULL,
  `chiet_khau` decimal(15,2) DEFAULT 0.00,
  `thanh_tien` decimal(15,2) GENERATED ALWAYS AS (`so_luong` * `gia_ban` - `chiet_khau`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chi_tiet_phieu_ban_hang`
--

INSERT INTO `chi_tiet_phieu_ban_hang` (`ma_chi_tiet`, `ma_phieu_ban_hang`, `ma_san_pham`, `so_luong`, `gia_ban`, `chiet_khau`) VALUES
(1, 1, 1, 2, 15000000.00, 100000.00),
(2, 1, 2, 5, 500000.00, 0.00);

--
-- Triggers `chi_tiet_phieu_ban_hang`
--
DELIMITER $$
CREATE TRIGGER `cap_nhat_tong_tien_phieu_ban_sau_sua` AFTER UPDATE ON `chi_tiet_phieu_ban_hang` FOR EACH ROW BEGIN
    UPDATE phieu_ban_hang SET tong_tien = (SELECT SUM(thanh_tien) FROM chi_tiet_phieu_ban_hang WHERE ma_phieu_ban_hang = NEW.ma_phieu_ban_hang)
    WHERE ma_phieu_ban_hang = NEW.ma_phieu_ban_hang;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `cap_nhat_tong_tien_phieu_ban_sau_them` AFTER INSERT ON `chi_tiet_phieu_ban_hang` FOR EACH ROW BEGIN
    UPDATE phieu_ban_hang SET tong_tien = (SELECT SUM(thanh_tien) FROM chi_tiet_phieu_ban_hang WHERE ma_phieu_ban_hang = NEW.ma_phieu_ban_hang)
    WHERE ma_phieu_ban_hang = NEW.ma_phieu_ban_hang;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `chi_tiet_phieu_dat_hang`
--

CREATE TABLE `chi_tiet_phieu_dat_hang` (
  `ma_chi_tiet` int(11) NOT NULL,
  `ma_phieu_dat_hang` int(11) NOT NULL,
  `ma_san_pham` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `gia_dat` decimal(15,2) NOT NULL,
  `thanh_tien` decimal(15,2) GENERATED ALWAYS AS (`so_luong` * `gia_dat`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chi_tiet_phieu_dat_hang`
--

INSERT INTO `chi_tiet_phieu_dat_hang` (`ma_chi_tiet`, `ma_phieu_dat_hang`, `ma_san_pham`, `so_luong`, `gia_dat`) VALUES
(1, 1, 1, 2, 15000000.00),
(2, 1, 2, 5, 500000.00);

--
-- Triggers `chi_tiet_phieu_dat_hang`
--
DELIMITER $$
CREATE TRIGGER `cap_nhat_tong_tien_phieu_dat_sau_sua` AFTER UPDATE ON `chi_tiet_phieu_dat_hang` FOR EACH ROW BEGIN
    UPDATE phieu_dat_hang SET tong_tien = (SELECT SUM(thanh_tien) FROM chi_tiet_phieu_dat_hang WHERE ma_phieu_dat_hang = NEW.ma_phieu_dat_hang)
    WHERE ma_phieu_dat_hang = NEW.ma_phieu_dat_hang;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `cap_nhat_tong_tien_phieu_dat_sau_them` AFTER INSERT ON `chi_tiet_phieu_dat_hang` FOR EACH ROW BEGIN
    UPDATE phieu_dat_hang SET tong_tien = (SELECT SUM(thanh_tien) FROM chi_tiet_phieu_dat_hang WHERE ma_phieu_dat_hang = NEW.ma_phieu_dat_hang)
    WHERE ma_phieu_dat_hang = NEW.ma_phieu_dat_hang;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `chi_tiet_phieu_xuat_kho`
--

CREATE TABLE `chi_tiet_phieu_xuat_kho` (
  `ma_chi_tiet` int(11) NOT NULL,
  `ma_phieu_xuat_kho` int(11) NOT NULL,
  `ma_san_pham` int(11) NOT NULL,
  `so_luong_xuat` int(11) NOT NULL,
  `thanh_tien` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chi_tiet_phieu_xuat_kho`
--

INSERT INTO `chi_tiet_phieu_xuat_kho` (`ma_chi_tiet`, `ma_phieu_xuat_kho`, `ma_san_pham`, `so_luong_xuat`, `thanh_tien`) VALUES
(1, 1, 1, 2, 30000000.00),
(2, 1, 2, 5, 2500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `chi_tiet_tra_hang`
--

CREATE TABLE `chi_tiet_tra_hang` (
  `ma_chi_tiet` int(11) NOT NULL,
  `ma_tra_hang` int(11) NOT NULL,
  `ma_san_pham` int(11) NOT NULL,
  `so_luong_tra` int(11) NOT NULL,
  `gia_tra` decimal(15,2) DEFAULT NULL,
  `thanh_tien_tra` decimal(15,2) GENERATED ALWAYS AS (`so_luong_tra` * `gia_tra`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chi_tiet_tra_hang`
--

INSERT INTO `chi_tiet_tra_hang` (`ma_chi_tiet`, `ma_tra_hang`, `ma_san_pham`, `so_luong_tra`, `gia_tra`) VALUES
(1, 1, 2, 1, 500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `hoa_don`
--

CREATE TABLE `hoa_don` (
  `ma_hoa_don` int(11) NOT NULL,
  `ma_phieu_xuat_kho` int(11) NOT NULL,
  `ngay_xuat_hd` date NOT NULL,
  `tong_tien` decimal(15,2) DEFAULT 0.00,
  `khuyen_mai_tong` decimal(15,2) DEFAULT 0.00,
  `trang_thai` enum('Chưa thanh toán','Đã thanh toán','Công nợ') DEFAULT 'Chưa thanh toán'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoa_don`
--

INSERT INTO `hoa_don` (`ma_hoa_don`, `ma_phieu_xuat_kho`, `ngay_xuat_hd`, `tong_tien`, `khuyen_mai_tong`, `trang_thai`) VALUES
(1, 1, '2025-11-27', 32500000.00, 0.00, 'Chưa thanh toán');

-- --------------------------------------------------------

--
-- Table structure for table `khach_hang`
--

CREATE TABLE `khach_hang` (
  `ma_khach_hang` int(11) NOT NULL,
  `ten_khach_hang` varchar(255) NOT NULL,
  `dia_chi` varchar(500) DEFAULT NULL,
  `dien_thoai` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `trang_thai` enum('Hoạt động','Ngừng hoạt động') DEFAULT 'Hoạt động'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `khach_hang`
--

INSERT INTO `khach_hang` (`ma_khach_hang`, `ten_khach_hang`, `dia_chi`, `dien_thoai`, `email`, `ngay_tao`, `trang_thai`) VALUES
(1, 'Công ty ABC', NULL, '0123456789', NULL, '2025-11-27 20:10:13', 'Hoạt động');

-- --------------------------------------------------------

--
-- Table structure for table `khuyen_mai`
--

CREATE TABLE `khuyen_mai` (
  `ma_khuyen_mai` int(11) NOT NULL,
  `ten_khuyen_mai` varchar(255) NOT NULL,
  `loai_khuyen_mai` enum('Phần trăm','Số tiền cố định') NOT NULL,
  `gia_tri` decimal(15,2) NOT NULL,
  `ap_dung_cho` enum('PO','PBH','HD') DEFAULT 'HD',
  `ngay_bat_dau` date DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `dieu_kien` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `khuyen_mai`
--

INSERT INTO `khuyen_mai` (`ma_khuyen_mai`, `ten_khuyen_mai`, `loai_khuyen_mai`, `gia_tri`, `ap_dung_cho`, `ngay_bat_dau`, `ngay_ket_thuc`, `dieu_kien`) VALUES
(1, 'Giảm 10% cho đơn >10tr', 'Phần trăm', 10.00, 'HD', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`) VALUES
(1, 'create_po', NULL),
(2, 'edit_po', NULL),
(3, 'approve_po', NULL),
(4, 'create_bh', NULL),
(5, 'create_pxk', NULL),
(6, 'approve_pxk', NULL),
(7, 'execute_pxk', NULL),
(8, 'create_invoice', NULL),
(9, 'issue_invoice', NULL),
(10, 'record_payment', NULL),
(11, 'create_return', NULL),
(12, 'approve_return', NULL),
(13, 'apply_discount', NULL),
(14, 'manage_promotions', NULL),
(15, 'manage_users', NULL),
(16, 'manage_roles', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `phieu_ban_hang`
--

CREATE TABLE `phieu_ban_hang` (
  `ma_phieu_ban_hang` int(11) NOT NULL,
  `ma_phieu_dat_hang` int(11) NOT NULL,
  `ngay_lap` date NOT NULL,
  `tong_tien` decimal(15,2) DEFAULT 0.00,
  `trang_thai` enum('Duyệt tồn kho','Gửi kế toán','Hoàn thành') DEFAULT 'Duyệt tồn kho'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieu_ban_hang`
--

INSERT INTO `phieu_ban_hang` (`ma_phieu_ban_hang`, `ma_phieu_dat_hang`, `ngay_lap`, `tong_tien`, `trang_thai`) VALUES
(1, 1, '2025-11-27', 32400000.00, 'Duyệt tồn kho');

-- --------------------------------------------------------

--
-- Table structure for table `phieu_dat_hang`
--

CREATE TABLE `phieu_dat_hang` (
  `ma_phieu_dat_hang` int(11) NOT NULL,
  `ma_khach_hang` int(11) NOT NULL,
  `ngay_dat` date NOT NULL,
  `tong_tien` decimal(15,2) DEFAULT 0.00,
  `trang_thai` enum('Chờ duyệt','Đã duyệt','Hủy') DEFAULT 'Chờ duyệt',
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieu_dat_hang`
--

INSERT INTO `phieu_dat_hang` (`ma_phieu_dat_hang`, `ma_khach_hang`, `ngay_dat`, `tong_tien`, `trang_thai`, `ghi_chu`) VALUES
(1, 1, '2025-11-27', 32500000.00, 'Chờ duyệt', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `phieu_xuat_kho`
--

CREATE TABLE `phieu_xuat_kho` (
  `ma_phieu_xuat_kho` int(11) NOT NULL,
  `ma_phieu_ban_hang` int(11) NOT NULL,
  `ngay_xuat` date NOT NULL,
  `nguoi_xuat` varchar(100) DEFAULT NULL,
  `trang_thai` enum('Đang xuất','Hoàn thành') DEFAULT 'Đang xuất'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieu_xuat_kho`
--

INSERT INTO `phieu_xuat_kho` (`ma_phieu_xuat_kho`, `ma_phieu_ban_hang`, `ngay_xuat`, `nguoi_xuat`, `trang_thai`) VALUES
(1, 1, '2025-11-27', NULL, 'Đang xuất');

--
-- Triggers `phieu_xuat_kho`
--
DELIMITER $$
CREATE TRIGGER `cap_nhat_ton_kho_sau_xuat_kho` AFTER UPDATE ON `phieu_xuat_kho` FOR EACH ROW BEGIN
    IF NEW.trang_thai = 'Hoàn thành' THEN
        UPDATE ton_kho tk
        JOIN chi_tiet_phieu_xuat_kho ct ON ct.ma_san_pham = tk.ma_san_pham
        JOIN phieu_xuat_kho pxk ON pxk.ma_phieu_xuat_kho = ct.ma_phieu_xuat_kho
        SET tk.so_luong_ton = tk.so_luong_ton - ct.so_luong_xuat,
            tk.ngay_cap_nhat = CURRENT_TIMESTAMP
        WHERE pxk.ma_phieu_xuat_kho = NEW.ma_phieu_xuat_kho;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Administrator'),
(2, 'sale', 'Sale / Kinh doanh'),
(3, 'kho', 'Kho / Warehouse'),
(4, 'ketoan', 'Kế toán / Finance');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(2, 1),
(2, 2),
(2, 4),
(2, 13),
(3, 5),
(3, 6),
(3, 7),
(4, 8),
(4, 9),
(4, 10),
(4, 12);

-- --------------------------------------------------------

--
-- Table structure for table `san_pham`
--

CREATE TABLE `san_pham` (
  `ma_san_pham` int(11) NOT NULL,
  `ten_san_pham` varchar(255) NOT NULL,
  `ma_loai_san_pham` int(11) DEFAULT NULL,
  `gia_ban` decimal(15,2) NOT NULL,
  `don_vi` varchar(50) DEFAULT 'Cái',
  `mo_ta` text DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `san_pham`
--

INSERT INTO `san_pham` (`ma_san_pham`, `ten_san_pham`, `ma_loai_san_pham`, `gia_ban`, `don_vi`, `mo_ta`, `ngay_tao`) VALUES
(1, 'Laptop Dell', NULL, 15000000.00, 'Cái', NULL, '2025-11-27 20:10:13'),
(2, 'Chuột không dây', NULL, 500000.00, 'Cái', NULL, '2025-11-27 20:10:13'),
(3, 'Laptop gaming', NULL, 120000000.00, 'Cái', '', '2025-12-01 13:06:07');

-- --------------------------------------------------------

--
-- Table structure for table `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','sale','ketoan','kho') NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thanh_toan`
--

CREATE TABLE `thanh_toan` (
  `ma_thanh_toan` int(11) NOT NULL,
  `ma_hoa_don` int(11) NOT NULL,
  `so_tien_tra` decimal(15,2) NOT NULL,
  `ngay_tra` date NOT NULL,
  `loai_thanh_toan` enum('Tiền mặt','Chuyển khoản','Công nợ') DEFAULT 'Công nợ',
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thanh_toan`
--

INSERT INTO `thanh_toan` (`ma_thanh_toan`, `ma_hoa_don`, `so_tien_tra`, `ngay_tra`, `loai_thanh_toan`, `ghi_chu`) VALUES
(1, 1, 7000000.00, '2025-11-27', 'Công nợ', 'Trả trước 7tr');

-- --------------------------------------------------------

--
-- Table structure for table `ton_kho`
--

CREATE TABLE `ton_kho` (
  `ma_ton_kho` int(11) NOT NULL,
  `ma_san_pham` int(11) NOT NULL,
  `so_luong_ton` int(11) DEFAULT 0,
  `ngay_cap_nhat` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ton_kho`
--

INSERT INTO `ton_kho` (`ma_ton_kho`, `ma_san_pham`, `so_luong_ton`, `ngay_cap_nhat`) VALUES
(1, 1, 10, '2025-11-27 20:10:13'),
(2, 2, 50, '2025-11-27 20:10:13');

-- --------------------------------------------------------

--
-- Table structure for table `tra_hang`
--

CREATE TABLE `tra_hang` (
  `ma_tra_hang` int(11) NOT NULL,
  `ma_hoa_don` int(11) NOT NULL,
  `ngay_tra` date NOT NULL,
  `ly_do` text DEFAULT NULL,
  `trang_thai` enum('Đang xử lý','Hoàn thành') DEFAULT 'Đang xử lý'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tra_hang`
--

INSERT INTO `tra_hang` (`ma_tra_hang`, `ma_hoa_don`, `ngay_tra`, `ly_do`, `trang_thai`) VALUES
(1, 1, '2025-11-28', 'Hàng lỗi', 'Đang xử lý');

--
-- Triggers `tra_hang`
--
DELIMITER $$
CREATE TRIGGER `cap_nhat_ton_kho_sau_tra_hang` AFTER UPDATE ON `tra_hang` FOR EACH ROW BEGIN
    IF NEW.trang_thai = 'Hoàn thành' THEN
        UPDATE ton_kho tk
        JOIN chi_tiet_tra_hang ct ON ct.ma_san_pham = tk.ma_san_pham
        JOIN tra_hang th ON th.ma_tra_hang = ct.ma_tra_hang
        SET tk.so_luong_ton = tk.so_luong_ton + ct.so_luong_tra,
            tk.ngay_cap_nhat = CURRENT_TIMESTAMP
        WHERE th.ma_tra_hang = NEW.ma_tra_hang;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `status`, `created_at`) VALUES
(5, 'admin', 'admin123', 'Administrator', 1, '2025-12-01 16:40:25'),
(6, 'sale1', 'sale123', 'Sale 1', 1, '2025-12-01 16:40:25'),
(7, 'ketoan1', 'ketoan123', 'Kế Toán 1', 1, '2025-12-01 16:40:25'),
(8, 'kho1', 'kho123', 'Nhân viên Kho 1', 1, '2025-12-01 16:40:25');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(5, 1),
(6, 2),
(7, 4),
(8, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chi_tiet_hoa_don`
--
ALTER TABLE `chi_tiet_hoa_don`
  ADD PRIMARY KEY (`ma_chi_tiet`),
  ADD KEY `ma_hoa_don` (`ma_hoa_don`),
  ADD KEY `ma_san_pham` (`ma_san_pham`);

--
-- Indexes for table `chi_tiet_phieu_ban_hang`
--
ALTER TABLE `chi_tiet_phieu_ban_hang`
  ADD PRIMARY KEY (`ma_chi_tiet`),
  ADD KEY `ma_phieu_ban_hang` (`ma_phieu_ban_hang`),
  ADD KEY `ma_san_pham` (`ma_san_pham`);

--
-- Indexes for table `chi_tiet_phieu_dat_hang`
--
ALTER TABLE `chi_tiet_phieu_dat_hang`
  ADD PRIMARY KEY (`ma_chi_tiet`),
  ADD KEY `ma_phieu_dat_hang` (`ma_phieu_dat_hang`),
  ADD KEY `ma_san_pham` (`ma_san_pham`);

--
-- Indexes for table `chi_tiet_phieu_xuat_kho`
--
ALTER TABLE `chi_tiet_phieu_xuat_kho`
  ADD PRIMARY KEY (`ma_chi_tiet`),
  ADD KEY `ma_phieu_xuat_kho` (`ma_phieu_xuat_kho`),
  ADD KEY `ma_san_pham` (`ma_san_pham`);

--
-- Indexes for table `chi_tiet_tra_hang`
--
ALTER TABLE `chi_tiet_tra_hang`
  ADD PRIMARY KEY (`ma_chi_tiet`),
  ADD KEY `ma_tra_hang` (`ma_tra_hang`),
  ADD KEY `ma_san_pham` (`ma_san_pham`);

--
-- Indexes for table `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD PRIMARY KEY (`ma_hoa_don`),
  ADD KEY `ma_phieu_xuat_kho` (`ma_phieu_xuat_kho`);

--
-- Indexes for table `khach_hang`
--
ALTER TABLE `khach_hang`
  ADD PRIMARY KEY (`ma_khach_hang`);

--
-- Indexes for table `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  ADD PRIMARY KEY (`ma_khuyen_mai`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `phieu_ban_hang`
--
ALTER TABLE `phieu_ban_hang`
  ADD PRIMARY KEY (`ma_phieu_ban_hang`),
  ADD KEY `ma_phieu_dat_hang` (`ma_phieu_dat_hang`);

--
-- Indexes for table `phieu_dat_hang`
--
ALTER TABLE `phieu_dat_hang`
  ADD PRIMARY KEY (`ma_phieu_dat_hang`),
  ADD KEY `ma_khach_hang` (`ma_khach_hang`);

--
-- Indexes for table `phieu_xuat_kho`
--
ALTER TABLE `phieu_xuat_kho`
  ADD PRIMARY KEY (`ma_phieu_xuat_kho`),
  ADD KEY `ma_phieu_ban_hang` (`ma_phieu_ban_hang`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `san_pham`
--
ALTER TABLE `san_pham`
  ADD PRIMARY KEY (`ma_san_pham`);

--
-- Indexes for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD PRIMARY KEY (`ma_thanh_toan`),
  ADD KEY `ma_hoa_don` (`ma_hoa_don`);

--
-- Indexes for table `ton_kho`
--
ALTER TABLE `ton_kho`
  ADD PRIMARY KEY (`ma_ton_kho`),
  ADD KEY `ma_san_pham` (`ma_san_pham`);

--
-- Indexes for table `tra_hang`
--
ALTER TABLE `tra_hang`
  ADD PRIMARY KEY (`ma_tra_hang`),
  ADD KEY `ma_hoa_don` (`ma_hoa_don`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `chi_tiet_hoa_don`
--
ALTER TABLE `chi_tiet_hoa_don`
  MODIFY `ma_chi_tiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chi_tiet_phieu_ban_hang`
--
ALTER TABLE `chi_tiet_phieu_ban_hang`
  MODIFY `ma_chi_tiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chi_tiet_phieu_dat_hang`
--
ALTER TABLE `chi_tiet_phieu_dat_hang`
  MODIFY `ma_chi_tiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chi_tiet_phieu_xuat_kho`
--
ALTER TABLE `chi_tiet_phieu_xuat_kho`
  MODIFY `ma_chi_tiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chi_tiet_tra_hang`
--
ALTER TABLE `chi_tiet_tra_hang`
  MODIFY `ma_chi_tiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hoa_don`
--
ALTER TABLE `hoa_don`
  MODIFY `ma_hoa_don` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `khach_hang`
--
ALTER TABLE `khach_hang`
  MODIFY `ma_khach_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  MODIFY `ma_khuyen_mai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `phieu_ban_hang`
--
ALTER TABLE `phieu_ban_hang`
  MODIFY `ma_phieu_ban_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `phieu_dat_hang`
--
ALTER TABLE `phieu_dat_hang`
  MODIFY `ma_phieu_dat_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `phieu_xuat_kho`
--
ALTER TABLE `phieu_xuat_kho`
  MODIFY `ma_phieu_xuat_kho` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `ma_san_pham` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  MODIFY `ma_thanh_toan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ton_kho`
--
ALTER TABLE `ton_kho`
  MODIFY `ma_ton_kho` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tra_hang`
--
ALTER TABLE `tra_hang`
  MODIFY `ma_tra_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chi_tiet_hoa_don`
--
ALTER TABLE `chi_tiet_hoa_don`
  ADD CONSTRAINT `chi_tiet_hoa_don_ibfk_1` FOREIGN KEY (`ma_hoa_don`) REFERENCES `hoa_don` (`ma_hoa_don`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_hoa_don_ibfk_2` FOREIGN KEY (`ma_san_pham`) REFERENCES `san_pham` (`ma_san_pham`);

--
-- Constraints for table `chi_tiet_phieu_ban_hang`
--
ALTER TABLE `chi_tiet_phieu_ban_hang`
  ADD CONSTRAINT `chi_tiet_phieu_ban_hang_ibfk_1` FOREIGN KEY (`ma_phieu_ban_hang`) REFERENCES `phieu_ban_hang` (`ma_phieu_ban_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_phieu_ban_hang_ibfk_2` FOREIGN KEY (`ma_san_pham`) REFERENCES `san_pham` (`ma_san_pham`);

--
-- Constraints for table `chi_tiet_phieu_dat_hang`
--
ALTER TABLE `chi_tiet_phieu_dat_hang`
  ADD CONSTRAINT `chi_tiet_phieu_dat_hang_ibfk_1` FOREIGN KEY (`ma_phieu_dat_hang`) REFERENCES `phieu_dat_hang` (`ma_phieu_dat_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_phieu_dat_hang_ibfk_2` FOREIGN KEY (`ma_san_pham`) REFERENCES `san_pham` (`ma_san_pham`);

--
-- Constraints for table `chi_tiet_phieu_xuat_kho`
--
ALTER TABLE `chi_tiet_phieu_xuat_kho`
  ADD CONSTRAINT `chi_tiet_phieu_xuat_kho_ibfk_1` FOREIGN KEY (`ma_phieu_xuat_kho`) REFERENCES `phieu_xuat_kho` (`ma_phieu_xuat_kho`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_phieu_xuat_kho_ibfk_2` FOREIGN KEY (`ma_san_pham`) REFERENCES `san_pham` (`ma_san_pham`);

--
-- Constraints for table `chi_tiet_tra_hang`
--
ALTER TABLE `chi_tiet_tra_hang`
  ADD CONSTRAINT `chi_tiet_tra_hang_ibfk_1` FOREIGN KEY (`ma_tra_hang`) REFERENCES `tra_hang` (`ma_tra_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_tra_hang_ibfk_2` FOREIGN KEY (`ma_san_pham`) REFERENCES `san_pham` (`ma_san_pham`);

--
-- Constraints for table `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD CONSTRAINT `hoa_don_ibfk_1` FOREIGN KEY (`ma_phieu_xuat_kho`) REFERENCES `phieu_xuat_kho` (`ma_phieu_xuat_kho`);

--
-- Constraints for table `phieu_ban_hang`
--
ALTER TABLE `phieu_ban_hang`
  ADD CONSTRAINT `phieu_ban_hang_ibfk_1` FOREIGN KEY (`ma_phieu_dat_hang`) REFERENCES `phieu_dat_hang` (`ma_phieu_dat_hang`);

--
-- Constraints for table `phieu_dat_hang`
--
ALTER TABLE `phieu_dat_hang`
  ADD CONSTRAINT `phieu_dat_hang_ibfk_1` FOREIGN KEY (`ma_khach_hang`) REFERENCES `khach_hang` (`ma_khach_hang`);

--
-- Constraints for table `phieu_xuat_kho`
--
ALTER TABLE `phieu_xuat_kho`
  ADD CONSTRAINT `phieu_xuat_kho_ibfk_1` FOREIGN KEY (`ma_phieu_ban_hang`) REFERENCES `phieu_ban_hang` (`ma_phieu_ban_hang`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD CONSTRAINT `thanh_toan_ibfk_1` FOREIGN KEY (`ma_hoa_don`) REFERENCES `hoa_don` (`ma_hoa_don`);

--
-- Constraints for table `ton_kho`
--
ALTER TABLE `ton_kho`
  ADD CONSTRAINT `ton_kho_ibfk_1` FOREIGN KEY (`ma_san_pham`) REFERENCES `san_pham` (`ma_san_pham`) ON DELETE CASCADE;

--
-- Constraints for table `tra_hang`
--
ALTER TABLE `tra_hang`
  ADD CONSTRAINT `tra_hang_ibfk_1` FOREIGN KEY (`ma_hoa_don`) REFERENCES `hoa_don` (`ma_hoa_don`);

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
