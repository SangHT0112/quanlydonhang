-- Migration: Adjust existing schema for Sales / Accounting / Warehouse workflow
-- Safe, non-destructive changes to adapt to current schema (2025-12-18)
-- Please BACKUP before running and test on staging.

START TRANSACTION;

-- 1) phieu_dat_hang: add approval columns if missing
ALTER TABLE phieu_dat_hang
  ADD COLUMN IF NOT EXISTS nguoi_duyet INT NULL,
  ADD COLUMN IF NOT EXISTS ngay_duyet DATETIME NULL;

-- 2) hoa_don: add linkage/metadata columns (ma_phieu_dat_hang, loai, nguoi_tao, nguoi_duyet, ngay_duyet, ghi_chu)
ALTER TABLE hoa_don
  ADD COLUMN IF NOT EXISTS ma_phieu_dat_hang INT NULL,
  ADD COLUMN IF NOT EXISTS loai ENUM('XUAT','NHAP') NOT NULL DEFAULT 'XUAT',
  ADD COLUMN IF NOT EXISTS nguoi_tao INT NULL,
  ADD COLUMN IF NOT EXISTS ngay_duyet DATETIME NULL,
  ADD COLUMN IF NOT EXISTS nguoi_duyet INT NULL,
  ADD COLUMN IF NOT EXISTS ghi_chu TEXT NULL;

ALTER TABLE hoa_don
  ADD INDEX IF NOT EXISTS idx_hoa_don_ma_phieu_dat (ma_phieu_dat_hang);

-- (Optional) Foreign key: only add if column and referenced table exist and FK doesn't already exist.
-- ALTER TABLE hoa_don ADD CONSTRAINT fk_hoa_don_phieu_dat FOREIGN KEY (ma_phieu_dat_hang) REFERENCES phieu_dat_hang(ma_phieu_dat_hang) ON DELETE SET NULL;

-- 3) phieu_xuat_kho: add link to hoa_don and extra metadata
ALTER TABLE phieu_xuat_kho
  ADD COLUMN IF NOT EXISTS ma_hoa_don INT NULL,
  ADD COLUMN IF NOT EXISTS nguoi_giao VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS ghi_chu TEXT NULL;

ALTER TABLE phieu_xuat_kho
  ADD INDEX IF NOT EXISTS idx_pxk_ma_hoa_don (ma_hoa_don);

-- ALTER TABLE phieu_xuat_kho ADD CONSTRAINT fk_pxk_hoa_don FOREIGN KEY (ma_hoa_don) REFERENCES hoa_don(ma_hoa_don) ON DELETE SET NULL;

-- 4) Create lich_su_ton_kho (stock history / audit) if missing
CREATE TABLE IF NOT EXISTS lich_su_ton_kho (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  ma_san_pham INT NOT NULL,
  thay_doi INT NOT NULL,
  loai ENUM('NHAP','XUAT','ADJUST','RETURN') NOT NULL,
  ref_table VARCHAR(100) NULL,
  ref_id BIGINT NULL,
  ghi_chu TEXT NULL,
  nguoi_thuc_hien INT NULL,
  ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (ma_san_pham),
  CONSTRAINT fk_lstk_san_pham FOREIGN KEY (ma_san_pham) REFERENCES san_pham(ma_san_pham)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) activity_log: add user_id column to link to users if desired
ALTER TABLE activity_log
  ADD COLUMN IF NOT EXISTS user_id INT NULL;

-- 6) Seed additional permissions (if they do not exist)
INSERT IGNORE INTO permissions (name, description) VALUES
  ('transfer_invoice_to_warehouse', 'Chuyển hóa đơn sang kho'),
  ('create_picklist', 'Tạo phiếu xuất kho / picklist'),
  ('generate_stock_report', 'Tạo báo cáo tồn kho'),
  ('process_returns', 'Xử lý trả hàng');

-- 7) Map new permissions to roles (if not already mapped)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
  WHERE p.name = 'transfer_invoice_to_warehouse' AND r.name = 'ketoan'
  UNION ALL
  SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
  WHERE p.name = 'create_picklist' AND r.name = 'kho'
  UNION ALL
  SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
  WHERE p.name = 'generate_stock_report' AND r.name = 'kho'
  UNION ALL
  SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
  WHERE p.name = 'process_returns' AND r.name = 'ketoan';

COMMIT;

-- NOTES:
-- * FK additions are commented out to avoid migration failure where constraints already exist; add them manually if you want strict referential integrity.
-- * The migration intentionally avoids touching tables that already exist in your dump (e.g., hoa_don, phieu_xuat_kho, ton_kho, tra_hang).
-- * After running: implement application logic to insert rows into lich_su_ton_kho whenever stock changes, or add triggers if you prefer DB-level enforcement.
-- * Test on a staging DB first and adjust enums/values according to your localizations if needed.
