-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 06:19 AM
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
(13, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 11:49:26'),
(14, 'Kế Toán 1', 'LOGOUT', 'Đăng xuất', '2025-12-01 12:04:27'),
(15, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 12:04:34'),
(16, 'Kế Toán 1', 'LOGOUT', 'Đăng xuất', '2025-12-01 12:05:39'),
(17, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 12:05:44'),
(18, 'Administrator', 'LOGOUT', 'Đăng xuất', '2025-12-01 12:08:44'),
(19, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 12:08:50'),
(20, 'Kế Toán 1', 'LOGOUT', 'Đăng xuất', '2025-12-01 12:14:12'),
(21, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-01 12:14:17'),
(22, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-04 14:29:59'),
(23, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-10 05:45:28'),
(24, 'Administrator', 'LOGOUT', 'Đăng xuất', '2025-12-10 05:45:58'),
(25, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-10 05:46:05'),
(26, 'Kế Toán 1', 'LOGOUT', 'Đăng xuất', '2025-12-10 05:46:14'),
(27, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-10 05:46:20'),
(28, 'Sale 1', 'LOGOUT', 'Đăng xuất', '2025-12-10 05:46:28'),
(29, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-10 05:48:09'),
(30, 'Sale 1', 'LOGOUT', 'Đăng xuất', '2025-12-10 05:51:41'),
(31, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-10 05:51:46'),
(32, 'Administrator', 'CREATE_PRODUCT', 'Thêm sản phẩm: Laptop gaminggg', '2025-12-10 05:51:58'),
(33, 'Administrator', 'CREATE_PO', 'Tạo phiếu đặt hàng #2', '2025-12-10 06:25:05'),
(34, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-13 05:10:45'),
(35, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-13 05:21:29'),
(36, 'Sale 1', 'LOGOUT', 'Đăng xuất', '2025-12-13 05:22:13'),
(37, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-13 05:22:58'),
(38, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 07:41:07'),
(39, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 07:42:06'),
(40, 'Administrator', 'LOGOUT', 'Đăng xuất', '2025-12-17 07:42:20'),
(41, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 07:42:25'),
(42, 'Kế Toán 1', 'LOGOUT', 'Đăng xuất', '2025-12-17 07:47:36'),
(43, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 07:47:40'),
(44, 'Administrator', 'LOGOUT', 'Đăng xuất', '2025-12-17 08:32:20'),
(45, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 08:32:25'),
(46, 'Sale 1', 'LOGOUT', 'Đăng xuất', '2025-12-17 08:32:37'),
(47, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 08:32:42'),
(48, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #3 với tổng tiền: 155.954.354', '2025-12-17 08:33:43'),
(49, 'Kế Toán 1', 'LOGOUT', 'Đăng xuất', '2025-12-17 08:37:06'),
(50, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 08:37:10'),
(51, 'Administrator', 'APPROVE_PO', 'Duyệt phiếu đặt hàng #3', '2025-12-17 08:39:42'),
(52, 'Administrator', 'LOGOUT', 'Đăng xuất', '2025-12-17 09:48:19'),
(53, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 09:48:27'),
(54, 'Sale 1', 'LOGOUT', 'Đăng xuất', '2025-12-17 10:01:10'),
(55, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 10:01:14'),
(56, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 14:28:01'),
(57, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 14:28:46'),
(58, 'Administrator', 'LOGOUT', 'Đăng xuất', '2025-12-17 14:48:56'),
(59, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 14:49:02'),
(60, 'Sale 1', 'LOGOUT', 'Đăng xuất', '2025-12-17 14:51:56'),
(61, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 14:52:01'),
(62, 'Administrator', 'LOGOUT', 'Đăng xuất', '2025-12-17 14:55:42'),
(63, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 14:55:49'),
(64, 'Kế Toán 1', 'LOGOUT', 'Đăng xuất', '2025-12-17 15:16:55'),
(65, 'Administrator', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 15:16:59'),
(66, 'Administrator', 'LOGOUT', 'Đăng xuất', '2025-12-17 15:24:28'),
(67, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 15:24:36'),
(68, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #5 với tổng tiền: 0', '2025-12-17 15:58:11'),
(69, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #7 với tổng tiền: 0', '2025-12-17 15:58:14'),
(70, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #9 với tổng tiền: 0', '2025-12-17 15:58:16'),
(71, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #11 với tổng tiền: 0', '2025-12-17 15:58:18'),
(72, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #13 với tổng tiền: 0', '2025-12-17 15:58:21'),
(73, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #13', '2025-12-17 15:58:32'),
(74, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #4', '2025-12-17 15:58:35'),
(75, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #5', '2025-12-17 15:58:38'),
(76, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #6', '2025-12-17 15:58:40'),
(77, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #7', '2025-12-17 15:58:42'),
(78, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #8', '2025-12-17 15:58:44'),
(79, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #9', '2025-12-17 15:58:45'),
(80, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #10', '2025-12-17 15:58:48'),
(81, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #11', '2025-12-17 16:18:13'),
(82, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #12', '2025-12-17 16:18:15'),
(83, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #15 với tổng tiền: 0', '2025-12-17 16:21:36'),
(84, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #14', '2025-12-17 16:21:49'),
(85, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #15', '2025-12-17 16:21:51'),
(86, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #17 với tổng tiền: 0', '2025-12-17 16:23:11'),
(87, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #18', '2025-12-17 16:26:24'),
(88, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #19 với tổng tiền: 0', '2025-12-17 16:31:41'),
(89, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #20', '2025-12-17 16:45:23'),
(90, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-17 16:48:42'),
(91, 'Sale 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-18 16:22:03'),
(92, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công', '2025-12-18 16:22:48'),
(93, 'Kế Toán 1', 'APPROVE_PO', 'Duyệt PO #18', '2025-12-18 16:26:00'),
(94, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #21', '2025-12-18 16:40:02'),
(95, 'Sale 1', 'LOGOUT', 'Đăng xuất', '2025-12-18 16:42:05'),
(96, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công - Role: ketoan', '2025-12-18 16:43:45'),
(97, 'Kế Toán 1', 'LOGOUT', 'Đăng xuất', '2025-12-18 16:43:50'),
(98, 'Sale 1', 'LOGIN', 'Đăng nhập thành công - Role: sale', '2025-12-18 16:43:55'),
(99, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #22', '2025-12-18 16:44:06'),
(100, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #23', '2025-12-18 16:46:42'),
(101, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #24', '2025-12-18 16:59:49'),
(102, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #25', '2025-12-18 17:00:37'),
(103, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #26', '2025-12-18 17:00:49'),
(104, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #21', '2025-12-18 17:02:04'),
(105, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #22', '2025-12-18 17:02:07'),
(106, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #23', '2025-12-18 17:02:09'),
(107, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #24', '2025-12-18 17:02:10'),
(108, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #25', '2025-12-18 17:02:13'),
(109, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #26', '2025-12-18 17:02:15'),
(110, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #20', '2025-12-18 17:02:17'),
(111, 'Sale 1', 'LOGIN', 'Đăng nhập thành công - Role: sale', '2025-12-19 04:10:55'),
(112, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công - Role: ketoan', '2025-12-19 04:11:06'),
(113, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #27', '2025-12-19 04:11:32'),
(114, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #28', '2025-12-19 04:12:25'),
(115, 'Sale 1', 'LOGIN', 'Đăng nhập thành công - Role: sale', '2025-12-19 04:16:57'),
(116, 'Kế Toán 1', 'LOGIN', 'Đăng nhập thành công - Role: ketoan', '2025-12-19 04:17:02'),
(117, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #29', '2025-12-19 04:17:11'),
(118, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #30', '2025-12-19 04:17:27'),
(119, 'Kế Toán 1', 'APPROVE_PO', 'Duyệt PO #30', '2025-12-19 04:18:10'),
(120, 'Sale 1', 'APPROVE_PO', 'Duyệt PO #27', '2025-12-19 04:37:12'),
(121, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #28', '2025-12-19 04:43:59'),
(122, 'Sale 1', 'DELETE_PO', 'Xóa phiếu đặt hàng #29', '2025-12-19 04:44:03'),
(123, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #31', '2025-12-19 04:49:07'),
(124, 'Sale 1', 'APPROVE_PO', 'Duyệt PO #31', '2025-12-19 04:54:31'),
(125, 'Sale 1', 'CREATE_PO', 'Tạo phiếu đặt hàng #32', '2025-12-19 05:08:16'),
(126, 'Sale 1', 'APPROVE_PO', 'Duyệt PO #32', '2025-12-19 05:08:30'),
(127, 'Kho 1', 'LOGIN', 'Đăng nhập thành công - Role: kho', '2025-12-19 06:15:52');

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
(3, 3, 2, 1, 500000.00, 0.00);

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
(20, 31, 2, 1, 500000.00),
(21, 32, 2, 1, 500000.00);

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

-- --------------------------------------------------------

--
-- Table structure for table `hoa_don`
--

CREATE TABLE `hoa_don` (
  `ma_hoa_don` int(11) NOT NULL,
  `ma_phieu_dat_hang` int(11) NOT NULL,
  `ma_phieu_xuat_kho` int(11) DEFAULT NULL,
  `ngay_xuat_hd` date NOT NULL,
  `tong_tien` decimal(15,2) DEFAULT 0.00,
  `khuyen_mai_tong` decimal(15,2) DEFAULT 0.00,
  `trang_thai` enum('Chưa thanh toán','Đã thanh toán','Công nợ') DEFAULT 'Chưa thanh toán'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoa_don`
--

INSERT INTO `hoa_don` (`ma_hoa_don`, `ma_phieu_dat_hang`, `ma_phieu_xuat_kho`, `ngay_xuat_hd`, `tong_tien`, `khuyen_mai_tong`, `trang_thai`) VALUES
(3, 31, NULL, '2025-12-19', 500000.00, 0.00, 'Chưa thanh toán');

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
(17, 'create_po', 'Tạo phiếu đặt hàng'),
(18, 'edit_po', 'Sửa phiếu đặt hàng'),
(19, 'view_po', 'Xem phiếu đặt hàng'),
(20, 'approve_po', 'Duyệt phiếu đặt hàng'),
(21, 'cancel_po', 'Hủy phiếu đặt hàng'),
(22, 'create_invoice', 'Tạo hóa đơn'),
(23, 'issue_invoice', 'Phát hành hóa đơn'),
(24, 'view_invoice', 'Xem hóa đơn'),
(25, 'create_pxk', 'Lập phiếu xuất kho'),
(26, 'approve_pxk', 'Duyệt phiếu xuất kho'),
(27, 'execute_pxk', 'Thực xuất kho'),
(28, 'view_inventory', 'Xem tồn kho'),
(29, 'record_payment', 'Ghi nhận thanh toán'),
(30, 'view_payment', 'Xem lịch sử thanh toán'),
(31, 'create_return', 'Tạo phiếu trả hàng'),
(32, 'approve_return', 'Duyệt phiếu trả hàng'),
(33, 'manage_users', 'Quản lý người dùng'),
(34, 'manage_roles', 'Quản lý vai trò'),
(35, 'submit_po', 'Gửi phiếu đặt hàng để duyệt'),
(36, 'delete_po', 'Xóa phiếu đặt hàng'),
(37, 'create_bh', 'Tạo phiếu bán hàng');

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

-- --------------------------------------------------------

--
-- Table structure for table `phieu_dat_hang`
--

CREATE TABLE `phieu_dat_hang` (
  `ma_phieu_dat_hang` int(11) NOT NULL,
  `ma_khach_hang` int(11) NOT NULL,
  `ngay_dat` date NOT NULL,
  `tong_tien` decimal(15,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `trang_thai` enum('Chờ duyệt','Đã duyệt','Hủy') DEFAULT 'Chờ duyệt',
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieu_dat_hang`
--

INSERT INTO `phieu_dat_hang` (`ma_phieu_dat_hang`, `ma_khach_hang`, `ngay_dat`, `tong_tien`, `created_by`, `approved_by`, `approved_at`, `trang_thai`, `ghi_chu`) VALUES
(31, 1, '2025-12-19', 500000.00, 2, 2, NULL, 'Đã duyệt', ''),
(32, 1, '2025-12-19', 500000.00, 2, 2, NULL, 'Đã duyệt', '');

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
(5, 'admin', 'Quản trị hệ thống'),
(6, 'sale', 'Nhân viên kinh doanh'),
(7, 'ketoan', 'Kế toán'),
(8, 'kho', 'Nhân viên kho');

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
(5, 17),
(5, 18),
(5, 19),
(5, 20),
(5, 21),
(5, 22),
(5, 23),
(5, 24),
(5, 25),
(5, 26),
(5, 27),
(5, 28),
(5, 29),
(5, 30),
(5, 31),
(5, 32),
(5, 33),
(5, 34),
(6, 17),
(6, 18),
(6, 19),
(6, 20),
(6, 21),
(6, 35),
(6, 36),
(6, 37),
(7, 19),
(7, 22),
(7, 23),
(7, 24),
(7, 29),
(7, 30),
(7, 32),
(7, 37),
(8, 19),
(8, 20),
(8, 24),
(8, 25),
(8, 26),
(8, 27),
(8, 28),
(8, 31),
(8, 32);

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
(3, 'Laptop gaming', NULL, 120000000.00, 'Cái', '', '2025-12-01 13:06:07'),
(4, 'Laptop gaminggg', NULL, 35454354.00, 'Cái', '', '2025-12-10 11:51:58');

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
(2, 2, 49, '2025-12-19 11:19:29');

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
  `full_name` varchar(100) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `role` varchar(50) DEFAULT 'guest',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `status`, `role`, `created_at`) VALUES
(1, 'admin', 'admin123', 'Administrator', 1, 'admin', '2025-12-17 15:47:58'),
(2, 'sale1', 'sale123', 'Sale 1', 1, 'sale', '2025-12-17 15:47:58'),
(3, 'ketoan1', 'ketoan123', 'Kế Toán 1', 1, 'ketoan', '2025-12-17 15:47:58'),
(4, 'kho1', 'kho123', 'Kho 1', 1, 'kho', '2025-12-17 15:47:58');

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
(1, 5),
(2, 6),
(3, 7),
(4, 8);

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
  ADD KEY `ma_khach_hang` (`ma_khach_hang`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_approved_by` (`approved_by`),
  ADD KEY `idx_approved_at` (`approved_at`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `chi_tiet_hoa_don`
--
ALTER TABLE `chi_tiet_hoa_don`
  MODIFY `ma_chi_tiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `chi_tiet_phieu_ban_hang`
--
ALTER TABLE `chi_tiet_phieu_ban_hang`
  MODIFY `ma_chi_tiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chi_tiet_phieu_dat_hang`
--
ALTER TABLE `chi_tiet_phieu_dat_hang`
  MODIFY `ma_chi_tiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
  MODIFY `ma_hoa_don` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `phieu_ban_hang`
--
ALTER TABLE `phieu_ban_hang`
  MODIFY `ma_phieu_ban_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `phieu_dat_hang`
--
ALTER TABLE `phieu_dat_hang`
  MODIFY `ma_phieu_dat_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `phieu_xuat_kho`
--
ALTER TABLE `phieu_xuat_kho`
  MODIFY `ma_phieu_xuat_kho` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `ma_san_pham` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`);

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
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
