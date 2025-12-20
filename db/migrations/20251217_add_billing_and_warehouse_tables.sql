-- Migration: Add billing, warehouse, stock history, and returns support
-- Run this on a backup copy first. Adjust column names/types to match your existing DB.

START TRANSACTION;

-- 1) Add columns to phieu_dat_hang (order header)
ALTER TABLE phieu_dat_hang
  ADD COLUMN IF NOT EXISTS nguoi_duyet INT NULL,
  ADD COLUMN IF NOT EXISTS ngay_duyet DATETIME NULL,
  -- If you already have trang_thai, skip the next line or adapt the enum/list
  ADD COLUMN IF NOT EXISTS trang_thai ENUM('Chờ duyệt','Đã duyệt','Từ chối','Đã hủy') NOT NULL DEFAULT 'Chờ duyệt';

-- 2) Ensure `san_pham` has a stock column
ALTER TABLE san_pham
  ADD COLUMN IF NOT EXISTS ton_kho INT NOT NULL DEFAULT 0;

-- 3) Create invoice (hoa_don) header table
CREATE TABLE IF NOT EXISTS hoa_don (
  ma_hoa_don BIGINT AUTO_INCREMENT PRIMARY KEY,
  ma_phieu_dat_hang BIGINT NULL,
  tong_tien DECIMAL(15,2) NOT NULL DEFAULT 0,
  loai ENUM('XUAT','NHAP') NOT NULL DEFAULT 'XUAT', -- XUAT: bán/phiếu xuất; NHAP: nhập kho
  trang_thai ENUM('NHÁP','SENT','CHUYEN_KHO','HOAN_TAT','HUY') NOT NULL DEFAULT 'NHÁP',
  ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  nguoi_tao INT NULL,
  ngay_duyet DATETIME NULL,
  nguoi_duyet INT NULL,
  ghi_chu TEXT,
  INDEX (ma_phieu_dat_hang),
  CONSTRAINT fk_hoa_don_phieu_dat_hang FOREIGN KEY (ma_phieu_dat_hang) REFERENCES phieu_dat_hang(ma_phieu_dat_hang) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Invoice details
CREATE TABLE IF NOT EXISTS chi_tiet_hoa_don (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  ma_hoa_don BIGINT NOT NULL,
  ma_san_pham INT NOT NULL,
  so_luong INT NOT NULL,
  gia DECIMAL(15,2) NOT NULL DEFAULT 0,
  thanh_tien DECIMAL(15,2) AS (so_luong * gia) STORED,
  CONSTRAINT fk_cthd_hoa_don FOREIGN KEY (ma_hoa_don) REFERENCES hoa_don(ma_hoa_don) ON DELETE CASCADE,
  CONSTRAINT fk_cthd_san_pham FOREIGN KEY (ma_san_pham) REFERENCES san_pham(ma_san_pham)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) Warehouse pick/dispatch (phieu_xuat_kho)
CREATE TABLE IF NOT EXISTS phieu_xuat_kho (
  ma_phieu_xuat BIGINT AUTO_INCREMENT PRIMARY KEY,
  ma_hoa_don BIGINT NULL,
  ngay_xuat DATETIME NULL,
  nguoi_tao INT NULL,
  nguoi_giao VARCHAR(255) NULL,
  trang_thai ENUM('Dang_xu_ly','Hoan_tat','Huy') NOT NULL DEFAULT 'Dang_xu_ly',
  ghi_chu TEXT,
  CONSTRAINT fk_pxx_hoa_don FOREIGN KEY (ma_hoa_don) REFERENCES hoa_don(ma_hoa_don) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6) Stock history (lich_su_ton_kho)
CREATE TABLE IF NOT EXISTS lich_su_ton_kho (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  ma_san_pham INT NOT NULL,
  thay_doi INT NOT NULL,
  loai ENUM('NHAP','XUAT','ADJUST','RETURN') NOT NULL,
  ref_table VARCHAR(100) NULL,
  ref_id BIGINT NULL,
  ghi_chu TEXT,
  nguoi_thuc_hien INT NULL,
  ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_lstk_san_pham FOREIGN KEY (ma_san_pham) REFERENCES san_pham(ma_san_pham)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7) Returns (tra_hang)
CREATE TABLE IF NOT EXISTS tra_hang (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  ma_phieu_xuat BIGINT NULL,
  ma_hoa_don BIGINT NULL,
  ngay_tra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  so_luong INT NOT NULL,
  ly_do TEXT,
  trang_thai ENUM('Moi','Dang_xu_ly','Da_hoan_tat') NOT NULL DEFAULT 'Moi',
  nguoi_tao INT NULL,
  CONSTRAINT fk_tra_phieu_xuat FOREIGN KEY (ma_phieu_xuat) REFERENCES phieu_xuat_kho(ma_phieu_xuat) ON DELETE SET NULL,
  CONSTRAINT fk_tra_hoa_don FOREIGN KEY (ma_hoa_don) REFERENCES hoa_don(ma_hoa_don) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8) Activity log (if not present yet)
CREATE TABLE IF NOT EXISTS activity_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(150) NOT NULL,
  target_table VARCHAR(150) NULL,
  target_id BIGINT NULL,
  note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;

-- === NOTES ===
-- * Before running: BACKUP the database. Remove "IF NOT EXISTS" on ALTER lines if your MySQL version doesn't support it, or pre-check with information_schema.
-- * Consider adding indexes on frequently queried columns (ma_hoa_don, trang_thai, ngay_tao, etc.).
-- * Implement application-level checks (SELECT ... FOR UPDATE) when creating invoices / dispatch to avoid overselling.
-- * You can add triggers or stored procedures later to automatically insert rows into lich_su_ton_kho when updating san_pham. For now, update stock inside transactions in application code.
-- * Update your PHP code (DAO/controllers) to use these tables and to write stock history when stock changes.
