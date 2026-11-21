CREATE DATABASE IF NOT EXISTS `coffee_shop`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `coffee_shop`;

-- 1. Nguoi dung & Phan quyen
CREATE TABLE `nguoi_dung` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ho_ten` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `so_dien_thoai` VARCHAR(20) DEFAULT NULL,
  `mat_khau` VARCHAR(255) NOT NULL,
  `dia_chi` TEXT DEFAULT NULL,
  `vai_tro` ENUM('khach_hang', 'nhan_vien', 'quan_tri') NOT NULL DEFAULT 'khach_hang',
  `trang_thai_hoat_dong` TINYINT(1) NOT NULL DEFAULT 1,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nguoi_dung_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Danh muc mon (do uong / banh)
CREATE TABLE `danh_muc` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_danh_muc` VARCHAR(100) NOT NULL,
  `duong_dan` VARCHAR(120) NOT NULL,
  `loai_mon` ENUM('do_uong', 'banh') NOT NULL DEFAULT 'do_uong',
  `thu_tu_hien_thi` INT NOT NULL DEFAULT 0,
  `con_ban` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_danh_muc_duong_dan` (`duong_dan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. San pham (mon nuoc & banh)
CREATE TABLE `san_pham` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `danh_muc_id` INT UNSIGNED NOT NULL,
  `ten_san_pham` VARCHAR(150) NOT NULL,
  `duong_dan` VARCHAR(170) NOT NULL,
  `mo_ta` TEXT DEFAULT NULL,
  `hinh_anh` VARCHAR(255) DEFAULT NULL,
  `gia_goc` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `con_ban` TINYINT(1) NOT NULL DEFAULT 1,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_san_pham_duong_dan` (`duong_dan`),
  KEY `idx_san_pham_danh_muc` (`danh_muc_id`),
  CONSTRAINT `fk_san_pham_danh_muc`
    FOREIGN KEY (`danh_muc_id`) REFERENCES `danh_muc` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bien the san pham (Size S, M, L...)
CREATE TABLE `bien_the_san_pham` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `san_pham_id` INT UNSIGNED NOT NULL,
  `ten_size` VARCHAR(30) NOT NULL,
  `gia_cong_them` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `la_mac_dinh` TINYINT(1) NOT NULL DEFAULT 0,
  `con_ban` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_bien_the_san_pham` (`san_pham_id`),
  CONSTRAINT `fk_bien_the_san_pham`
    FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Topping ban kem
CREATE TABLE `topping` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_topping` VARCHAR(100) NOT NULL,
  `gia_ban` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `con_ban` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Kho nguyen lieu
CREATE TABLE `nguyen_lieu` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ten_nguyen_lieu` VARCHAR(120) NOT NULL,
  `don_vi_tinh` VARCHAR(20) NOT NULL,
  `so_luong_ton` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `nguong_canh_bao` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `don_gia_nhap` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `con_su_dung` TINYINT(1) NOT NULL DEFAULT 1,
  `ngay_cap_nhat` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Dinh luong san pham (cong thuc nguyen lieu theo mon / size)
CREATE TABLE `dinh_luong_san_pham` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `san_pham_id` INT UNSIGNED NOT NULL,
  `bien_the_id` INT UNSIGNED DEFAULT NULL,
  `nguyen_lieu_id` INT UNSIGNED NOT NULL,
  `so_luong_tieu_hao` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dinh_luong_san_pham` (`san_pham_id`),
  KEY `idx_dinh_luong_bien_the` (`bien_the_id`),
  KEY `idx_dinh_luong_nguyen_lieu` (`nguyen_lieu_id`),
  CONSTRAINT `fk_dinh_luong_san_pham`
    FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_dinh_luong_bien_the`
    FOREIGN KEY (`bien_the_id`) REFERENCES `bien_the_san_pham` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_dinh_luong_nguyen_lieu`
    FOREIGN KEY (`nguyen_lieu_id`) REFERENCES `nguyen_lieu` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Ma giam gia
CREATE TABLE `ma_giam_gia` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_code` VARCHAR(40) NOT NULL,
  `kieu_giam` ENUM('phan_tram', 'so_tien') NOT NULL DEFAULT 'so_tien',
  `gia_tri_giam` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `don_toi_thieu` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `ngay_het_han` DATETIME DEFAULT NULL,
  `con_hieu_luc` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ma_giam_gia_code` (`ma_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Don hang
CREATE TABLE `don_hang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ma_don_hang` VARCHAR(30) NOT NULL,
  `nguoi_dung_id` INT UNSIGNED DEFAULT NULL,
  `ma_giam_gia_id` INT UNSIGNED DEFAULT NULL,
  `ten_nguoi_nhan` VARCHAR(100) NOT NULL,
  `sdt_nguoi_nhan` VARCHAR(20) NOT NULL,
  `hinh_thuc_nhan` ENUM('giao_tan_noi', 'den_quan_lay') NOT NULL DEFAULT 'giao_tan_noi',
  `dia_chi_giao` TEXT DEFAULT NULL,
  `phuong_thuc_thanh_toan` ENUM('tien_mat', 'chuyen_khoan') NOT NULL DEFAULT 'tien_mat',
  `tam_tinh` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tien_giam` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tong_thanh_toan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `trang_thai_don` ENUM('cho_xac_nhan', 'dang_pha_che', 'dang_giao', 'hoan_thanh', 'da_huy') NOT NULL DEFAULT 'cho_xac_nhan',
  `da_tru_kho` TINYINT(1) NOT NULL DEFAULT 0,
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_don_hang_ma` (`ma_don_hang`),
  KEY `idx_don_hang_nguoi_dung` (`nguoi_dung_id`),
  KEY `idx_don_hang_ma_giam_gia` (`ma_giam_gia_id`),
  KEY `idx_don_hang_trang_thai` (`trang_thai_don`),
  CONSTRAINT `fk_don_hang_nguoi_dung`
    FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_don_hang_ma_giam_gia`
    FOREIGN KEY (`ma_giam_gia_id`) REFERENCES `ma_giam_gia` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Chi tiet mon trong don hang
CREATE TABLE `chi_tiet_don_hang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `don_hang_id` INT UNSIGNED NOT NULL,
  `san_pham_id` INT UNSIGNED NOT NULL,
  `bien_the_id` INT UNSIGNED DEFAULT NULL,
  `ten_san_pham` VARCHAR(150) NOT NULL,
  `ten_size` VARCHAR(30) DEFAULT NULL,
  `don_gia` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `so_luong` INT UNSIGNED NOT NULL DEFAULT 1,
  `thanh_tien` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_ctdh_don_hang` (`don_hang_id`),
  KEY `idx_ctdh_san_pham` (`san_pham_id`),
  KEY `idx_ctdh_bien_the` (`bien_the_id`),
  CONSTRAINT `fk_ctdh_don_hang`
    FOREIGN KEY (`don_hang_id`) REFERENCES `don_hang` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctdh_san_pham`
    FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ctdh_bien_the`
    FOREIGN KEY (`bien_the_id`) REFERENCES `bien_the_san_pham` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Topping theo tung dong mon trong don hang
CREATE TABLE `topping_don_hang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `chi_tiet_don_hang_id` INT UNSIGNED NOT NULL,
  `topping_id` INT UNSIGNED NOT NULL,
  `ten_topping` VARCHAR(100) NOT NULL,
  `gia_topping` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `so_luong` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_tdh_chi_tiet` (`chi_tiet_don_hang_id`),
  KEY `idx_tdh_topping` (`topping_id`),
  CONSTRAINT `fk_tdh_chi_tiet`
    FOREIGN KEY (`chi_tiet_don_hang_id`) REFERENCES `chi_tiet_don_hang` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_tdh_topping`
    FOREIGN KEY (`topping_id`) REFERENCES `topping` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Nhat ky bien dong ton kho
CREATE TABLE `nhat_ky_kho` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nguyen_lieu_id` INT UNSIGNED NOT NULL,
  `don_hang_id` INT UNSIGNED DEFAULT NULL,
  `so_luong_thay_doi` DECIMAL(12,2) NOT NULL,
  `loai_thao_tac` ENUM('nhap_kho', 'tru_kho_don_hang', 'hoan_kho_huy_don', 'dieu_chinh_thu_cong') NOT NULL,
  `ghi_chu` VARCHAR(255) DEFAULT NULL,
  `thoi_gian` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nkk_nguyen_lieu` (`nguyen_lieu_id`),
  KEY `idx_nkk_don_hang` (`don_hang_id`),
  CONSTRAINT `fk_nkk_nguyen_lieu`
    FOREIGN KEY (`nguyen_lieu_id`) REFERENCES `nguyen_lieu` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_nkk_don_hang`
    FOREIGN KEY (`don_hang_id`) REFERENCES `don_hang` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

USE `coffee_shop`;

-- 1. Nguoi dung mau (mat khau deu la: 123456)
INSERT INTO `nguoi_dung` (`ho_ten`, `email`, `so_dien_thoai`, `mat_khau`, `dia_chi`, `vai_tro`, `trang_thai_hoat_dong`) VALUES
('Quản trị viên quán', 'admin@coffeeshop.local', '0901000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Đường 3/2, Long Xuyên, An Giang', 'quan_tri', 1),
('Nhân viên pha chế & kho', 'staff@coffeeshop.local', '0901000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Đường 3/2, Long Xuyên, An Giang', 'nhan_vien', 1),
('Khách hàng mẫu', 'khach@coffeeshop.local', '0901000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '45 Trần Hưng Đạo, Long Xuyên, An Giang', 'khach_hang', 1);

-- 2. Danh muc mon
INSERT INTO `danh_muc` (`ten_danh_muc`, `duong_dan`, `loai_mon`, `thu_tu_hien_thi`, `con_ban`) VALUES
('Cà phê', 'ca-phe', 'do_uong', 1, 1),
('Trà trái cây & Trà sữa', 'tra-trai-cay-tra-sua', 'do_uong', 2, 1),
('Matcha & Đá xay', 'matcha-da-xay', 'do_uong', 3, 1),
('Bánh ngọt & Ăn kèm', 'banh-ngot-an-kem', 'banh', 4, 1);

-- 3. San pham (7 mon nuoc + 3 mon banh)
INSERT INTO `san_pham` (`danh_muc_id`, `ten_san_pham`, `duong_dan`, `mo_ta`, `hinh_anh`, `gia_goc`, `con_ban`) VALUES
(1, 'Cà phê sữa đá', 'ca-phe-sua-da', 'Cà phê rang đậm vị kết hợp sữa đặc béo ngậy, dùng kèm đá viên.', 'ca-phe-sua-da.jpg', 25000.00, 1),
(1, 'Bạc xỉu', 'bac-xiu', 'Nhiều sữa hơn cà phê, vị ngọt dịu nhẹ, thơm béo dễ uống.', 'bac-xiu.jpg', 28000.00, 1),
(1, 'Latte đá', 'latte-da', 'Espresso hài hòa cùng sữa tươi mát lạnh, lớp kem sữa mịn nhẹ.', 'latte-da.jpg', 35000.00, 1),
(2, 'Trà đào cam sả', 'tra-dao-cam-sa', 'Trà thanh mát hòa quyện đào miếng giòn ngọt, hương cam và sả.', 'tra-dao-cam-sa.jpg', 35000.00, 1),
(2, 'Trà sữa truyền thống', 'tra-sua-truyen-thong', 'Trà đậm hương, vị béo cân bằng, hợp khi thêm trân châu hoặc kem cheese.', 'tra-sua-truyen-thong.jpg', 32000.00, 1),
(3, 'Matcha latte', 'matcha-latte', 'Bột matcha xanh mịn kết hợp sữa tươi, hậu vị thanh ngọt.', 'matcha-latte.jpg', 38000.00, 1),
(3, 'Sô-cô-la đá xay', 'so-co-la-da-xay', 'Sô-cô-la đậm đà xay cùng đá mát lạnh, thêm lớp kem tươi bên trên.', 'so-co-la-da-xay.jpg', 40000.00, 1),
(4, 'Croissant bơ', 'croissant-bo', 'Bánh ngàn lớp giòn rụm bên ngoài, mềm thơm mùi bơ bên trong.', 'croissant-bo.jpg', 26000.00, 1),
(4, 'Bánh su kem', 'banh-su-kem', 'Vỏ bánh mềm nhẹ, nhân kem vani mát lạnh không ngấy.', 'banh-su-kem.jpg', 22000.00, 1),
(4, 'Tiramisu hũ', 'tiramisu-hu', 'Bánh mềm ẩm hương cà phê và ca-cao, đóng hũ tiện giao đi.', 'tiramisu-hu.jpg', 35000.00, 1);

-- 4. Bien the san pham (Size cho do uong & quy cach cho banh)
-- Gia tri id san_pham tu 1 den 10 theo thu tu INSERT o tren
INSERT INTO `bien_the_san_pham` (`san_pham_id`, `ten_size`, `gia_cong_them`, `la_mac_dinh`, `con_ban`) VALUES
-- Ca phe sua da (id=1)
(1, 'S', 0.00, 1, 1),
(1, 'M', 5000.00, 0, 1),
(1, 'L', 9000.00, 0, 1),
-- Bac xiu (id=2)
(2, 'S', 0.00, 1, 1),
(2, 'M', 5000.00, 0, 1),
(2, 'L', 9000.00, 0, 1),
-- Latte da (id=3)
(3, 'S', 0.00, 1, 1),
(3, 'M', 6000.00, 0, 1),
(3, 'L', 10000.00, 0, 1),
-- Tra dao cam sa (id=4)
(4, 'S', 0.00, 1, 1),
(4, 'M', 5000.00, 0, 1),
(4, 'L', 10000.00, 0, 1),
-- Tra sua truyen thong (id=5)
(5, 'S', 0.00, 1, 1),
(5, 'M', 5000.00, 0, 1),
(5, 'L', 9000.00, 0, 1),
-- Matcha latte (id=6)
(6, 'S', 0.00, 1, 1),
(6, 'M', 6000.00, 0, 1),
(6, 'L', 10000.00, 0, 1),
-- So-co-la da xay (id=7)
(7, 'M', 0.00, 1, 1),
(7, 'L', 8000.00, 0, 1),
-- Banh (id=8, 9, 10)
(8, '1 cái', 0.00, 1, 1),
(9, '1 cái', 0.00, 1, 1),
(10, '1 hũ', 0.00, 1, 1);

-- 5. Topping ban kem
INSERT INTO `topping` (`ten_topping`, `gia_ban`, `con_ban`) VALUES
('Trân châu đen', 7000.00, 1),
('Kem cheese', 10000.00, 1),
('Đào miếng', 8000.00, 1),
('Thêm shot espresso', 10000.00, 1);

-- 6. Kho nguyen lieu (id 1..12)
INSERT INTO `nguyen_lieu` (`ten_nguyen_lieu`, `don_vi_tinh`, `so_luong_ton`, `nguong_canh_bao`, `don_gia_nhap`, `con_su_dung`) VALUES
('Cà phê hạt rang', 'g', 5000.00, 500.00, 0.35, 1),
('Sữa đặc', 'ml', 6000.00, 800.00, 0.06, 1),
('Sữa tươi không đường', 'ml', 10000.00, 1500.00, 0.03, 1),
('Cốt trà đào & sả', 'ml', 4000.00, 500.00, 0.12, 1),
('Cốt trà đen pha sẵn', 'ml', 5000.00, 600.00, 0.05, 1),
('Bột matcha', 'g', 1500.00, 200.00, 1.20, 1),
('Bột sô-cô-la', 'g', 2000.00, 250.00, 0.45, 1),
('Ly nhựa kèm nắp', 'cái', 500.00, 80.00, 1200.00, 1),
('Ống hút', 'cái', 500.00, 100.00, 250.00, 1),
('Bánh Croissant bơ (thành phẩm)', 'cái', 30.00, 5.00, 15000.00, 1),
('Bánh su kem (thành phẩm)', 'cái', 35.00, 5.00, 12000.00, 1),
('Bánh Tiramisu hũ (thành phẩm)', 'hũ', 20.00, 4.00, 20000.00, 1);

-- 7. Dinh luong san pham (cong thuc cho 1 don vi mon mac dinh)
INSERT INTO `dinh_luong_san_pham` (`san_pham_id`, `bien_the_id`, `nguyen_lieu_id`, `so_luong_tieu_hao`) VALUES
-- Ca phe sua da (id=1)
(1, NULL, 1, 20.00),
(1, NULL, 2, 35.00),
(1, NULL, 8, 1.00),
(1, NULL, 9, 1.00),
-- Bac xiu (id=2)
(2, NULL, 1, 15.00),
(2, NULL, 2, 40.00),
(2, NULL, 3, 50.00),
(2, NULL, 8, 1.00),
(2, NULL, 9, 1.00),
-- Latte da (id=3)
(3, NULL, 1, 18.00),
(3, NULL, 3, 150.00),
(3, NULL, 8, 1.00),
(3, NULL, 9, 1.00),
-- Tra dao cam sa (id=4)
(4, NULL, 4, 120.00),
(4, NULL, 8, 1.00),
(4, NULL, 9, 1.00),
-- Tra sua truyen thong (id=5)
(5, NULL, 5, 140.00),
(5, NULL, 2, 25.00),
(5, NULL, 3, 40.00),
(5, NULL, 8, 1.00),
(5, NULL, 9, 1.00),
-- Matcha latte (id=6)
(6, NULL, 6, 6.00),
(6, NULL, 3, 160.00),
(6, NULL, 8, 1.00),
(6, NULL, 9, 1.00),
-- So-co-la da xay (id=7)
(7, NULL, 7, 15.00),
(7, NULL, 3, 120.00),
(7, NULL, 2, 20.00),
(7, NULL, 8, 1.00),
(7, NULL, 9, 1.00),
-- Banh (id=8, 9, 10)
(8, NULL, 10, 1.00),
(9, NULL, 11, 1.00),
(10, NULL, 12, 1.00);

-- 8. Ma giam gia mau
INSERT INTO `ma_giam_gia` (`ma_code`, `kieu_giam`, `gia_tri_giam`, `don_toi_thieu`, `ngay_het_han`, `con_hieu_luc`) VALUES
('CHAO15K', 'so_tien', 15000.00, 60000.00, '2026-12-31 23:59:59', 1),
('CAFE10', 'phan_tram', 10.00, 50000.00, '2026-12-31 23:59:59', 1);

-- 9. Nhat ky nhap kho ban dau cho kho nguyen lieu
INSERT INTO `nhat_ky_kho` (`nguyen_lieu_id`, `don_hang_id`, `so_luong_thay_doi`, `loai_thao_tac`, `ghi_chu`)
SELECT `id`, NULL, `so_luong_ton`, 'nhap_kho', 'Nhập tồn kho ban đầu dữ liệu mẫu'
FROM `nguyen_lieu`;