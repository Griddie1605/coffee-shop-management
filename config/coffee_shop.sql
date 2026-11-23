-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 23, 2026 lúc 10:22 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `coffee_shop`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bien_the_san_pham`
--

CREATE TABLE `bien_the_san_pham` (
  `id` int(10) UNSIGNED NOT NULL,
  `san_pham_id` int(10) UNSIGNED NOT NULL,
  `ten_size` varchar(30) NOT NULL,
  `gia_cong_them` decimal(10,2) NOT NULL DEFAULT 0.00,
  `la_mac_dinh` tinyint(1) NOT NULL DEFAULT 0,
  `con_ban` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_don_hang`
--

CREATE TABLE `chi_tiet_don_hang` (
  `id` int(10) UNSIGNED NOT NULL,
  `don_hang_id` int(10) UNSIGNED NOT NULL,
  `san_pham_id` int(10) UNSIGNED NOT NULL,
  `bien_the_id` int(10) UNSIGNED DEFAULT NULL,
  `ten_san_pham` varchar(150) NOT NULL,
  `ten_size` varchar(30) DEFAULT NULL,
  `ghi_chu` varchar(255) DEFAULT NULL,
  `don_gia` decimal(10,2) NOT NULL DEFAULT 0.00,
  `so_luong` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `thanh_tien` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_don_hang`
--

INSERT INTO `chi_tiet_don_hang` (`id`, `don_hang_id`, `san_pham_id`, `bien_the_id`, `ten_san_pham`, `ten_size`, `ghi_chu`, `don_gia`, `so_luong`, `thanh_tien`) VALUES
(1, 1, 10, NULL, 'Tiramisu hũ (Size 1 hũ) + Trân châu đen, Thêm shot espresso [Ghi chú: 123456]', '1 hũ', NULL, 52000.00, 1, 52000.00),
(2, 1, 10, NULL, 'Tiramisu hũ (Size 1 hũ)', '1 hũ', NULL, 35000.00, 1, 35000.00),
(3, 1, 5, NULL, 'Trà sữa truyền thống (Size S)', 'S', NULL, 32000.00, 1, 32000.00),
(4, 1, 3, NULL, 'Latte đá (Size S) + Kem cheese [Ghi chú: 3213123123]', 'S', NULL, 45000.00, 1, 45000.00),
(5, 2, 10, NULL, 'Tiramisu hũ (Size 1 hũ)', '1 hũ', NULL, 35000.00, 1, 35000.00),
(6, 2, 9, NULL, 'Bánh su kem (Size 1 cái) + Đào miếng [Ghi chú: 123321]', '1 cái', NULL, 30000.00, 1, 30000.00),
(7, 2, 5, NULL, 'Trà sữa truyền thống (Size L) + Đào miếng [Ghi chú: ít đá ít đường]', 'L', NULL, 49000.00, 3, 147000.00),
(8, 3, 10, NULL, 'Tiramisu hũ (Size 1 hũ) + Đào miếng [Ghi chú: 321123]', '1 hũ', NULL, 43000.00, 1, 43000.00),
(9, 3, 3, NULL, 'Latte đá (Size M) + Thêm shot espresso [Ghi chú: không đá]', 'M', NULL, 51000.00, 1, 51000.00),
(10, 4, 5, NULL, 'Trà sữa truyền thống (Size L) + Đào miếng [Ghi chú: ít đá ít đường]', 'L', NULL, 49000.00, 3, 147000.00),
(11, 4, 5, NULL, 'Trà sữa truyền thống (Size L) + Đào miếng [Ghi chú: ít ngọt]', 'L', NULL, 49000.00, 4, 196000.00),
(12, 5, 11, NULL, 'Capuchino', NULL, NULL, 45000.00, 2, 90000.00),
(13, 6, 12, NULL, 'táo', NULL, NULL, 25000.00, 26, 650000.00),
(14, 7, 11, NULL, 'Capuchino + Kem cheese', NULL, NULL, 55000.00, 1, 55000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_phieu_nhap`
--

CREATE TABLE `chi_tiet_phieu_nhap` (
  `id` int(10) UNSIGNED NOT NULL,
  `phieu_nhap_id` int(10) UNSIGNED NOT NULL,
  `nguyen_lieu_id` int(10) UNSIGNED NOT NULL,
  `so_luong` decimal(12,3) NOT NULL DEFAULT 0.000,
  `don_gia_nhap` decimal(12,2) NOT NULL DEFAULT 0.00,
  `thanh_tien` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_phieu_nhap`
--

INSERT INTO `chi_tiet_phieu_nhap` (`id`, `phieu_nhap_id`, `nguyen_lieu_id`, `so_luong`, `don_gia_nhap`, `thanh_tien`) VALUES
(1, 1, 8, 2250.000, 66.67, 150000.08),
(2, 2, 9, 2000.000, 35.00, 70000.00),
(3, 2, 10, 750.000, 40.00, 30000.00),
(4, 2, 11, 500.000, 240.00, 120000.00),
(5, 2, 12, 500.000, 50.00, 25000.00),
(6, 3, 12, 2500.000, 50.00, 125000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cong_thuc_pha_che`
--

CREATE TABLE `cong_thuc_pha_che` (
  `id` int(10) UNSIGNED NOT NULL,
  `san_pham_id` int(10) UNSIGNED NOT NULL,
  `nguyen_lieu_id` int(10) UNSIGNED NOT NULL,
  `dinh_luong` decimal(12,3) NOT NULL DEFAULT 0.000,
  `ghi_chu` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cong_thuc_pha_che`
--

INSERT INTO `cong_thuc_pha_che` (`id`, `san_pham_id`, `nguyen_lieu_id`, `dinh_luong`, `ghi_chu`) VALUES
(1, 1, 1, 20.000, 'Cà phê sữa đá'),
(2, 1, 2, 35.000, 'Sữa đặc'),
(3, 2, 1, 15.000, 'Bạc xỉu'),
(4, 2, 2, 40.000, 'Sữa đặc'),
(5, 2, 3, 50.000, 'Sữa tươi'),
(6, 3, 1, 18.000, 'Latte đá'),
(7, 3, 3, 150.000, 'Sữa tươi'),
(8, 6, 4, 8.000, 'Matcha latte'),
(9, 6, 3, 140.000, 'Sữa tươi'),
(10, 11, 1, 20.000, NULL),
(11, 11, 3, 50.000, NULL),
(12, 12, 11, 20.000, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc`
--

CREATE TABLE `danh_muc` (
  `id` int(10) UNSIGNED NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL,
  `duong_dan` varchar(120) NOT NULL,
  `loai_mon` enum('do_uong','banh') NOT NULL DEFAULT 'do_uong',
  `thu_tu_hien_thi` int(11) NOT NULL DEFAULT 0,
  `con_ban` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc`
--

INSERT INTO `danh_muc` (`id`, `ten_danh_muc`, `duong_dan`, `loai_mon`, `thu_tu_hien_thi`, `con_ban`) VALUES
(1, 'Cà phê', 'ca-phe', 'do_uong', 1, 1),
(2, 'Trà trái cây & Trà sữa', 'tra-trai-cay-tra-sua', 'do_uong', 2, 1),
(3, 'Matcha & Đá xay', 'matcha-da-xay', 'do_uong', 3, 1),
(4, 'Bánh ngọt & Ăn kèm', 'banh-ngot-an-kem', 'banh', 4, 1),
(7, 'trái cây', 'trai-cay', 'banh', 5, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_hang`
--

CREATE TABLE `don_hang` (
  `id` int(10) UNSIGNED NOT NULL,
  `ma_don_hang` varchar(30) NOT NULL,
  `nguoi_dung_id` int(10) UNSIGNED DEFAULT NULL,
  `ma_giam_gia_id` int(10) UNSIGNED DEFAULT NULL,
  `ten_nguoi_nhan` varchar(100) NOT NULL,
  `sdt_nguoi_nhan` varchar(20) NOT NULL,
  `hinh_thuc_nhan` enum('giao_tan_noi','den_quan_lay') NOT NULL DEFAULT 'giao_tan_noi',
  `dia_chi_giao` text DEFAULT NULL,
  `phuong_thuc_thanh_toan` enum('tien_mat','chuyen_khoan') NOT NULL DEFAULT 'tien_mat',
  `trang_thai_thanh_toan` enum('cho_thanh_toan','da_thanh_toan','hoan_tien') NOT NULL DEFAULT 'cho_thanh_toan',
  `tam_tinh` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tien_giam` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tong_thanh_toan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `trang_thai_don` enum('cho_xac_nhan','dang_pha_che','dang_giao','hoan_thanh','da_huy') NOT NULL DEFAULT 'cho_xac_nhan',
  `da_tru_kho` tinyint(1) NOT NULL DEFAULT 0,
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `don_hang`
--

INSERT INTO `don_hang` (`id`, `ma_don_hang`, `nguoi_dung_id`, `ma_giam_gia_id`, `ten_nguoi_nhan`, `sdt_nguoi_nhan`, `hinh_thuc_nhan`, `dia_chi_giao`, `phuong_thuc_thanh_toan`, `trang_thai_thanh_toan`, `tam_tinh`, `tien_giam`, `tong_thanh_toan`, `trang_thai_don`, `da_tru_kho`, `ghi_chu`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'DH26112216175338', NULL, NULL, 'Trọng', '123456', 'giao_tan_noi', 'pt', 'tien_mat', 'cho_thanh_toan', 164000.00, 0.00, 164000.00, 'cho_xac_nhan', 0, 'ít đá', '2026-11-22 15:17:53', NULL),
(2, 'DH26112216302992', NULL, 1, 'Anh Thư', '098765', 'den_quan_lay', NULL, 'chuyen_khoan', 'cho_thanh_toan', 212000.00, 15000.00, 197000.00, 'cho_xac_nhan', 0, 'ít đá, giao nhanh giúp e, cần gấp', '2026-11-22 15:30:29', NULL),
(3, 'DH26112216573762', 1, 1, 'Quản trị viên quán', '0901000001', 'den_quan_lay', NULL, 'tien_mat', 'cho_thanh_toan', 94000.00, 15000.00, 79000.00, 'dang_giao', 0, 'free duoc khong', '2026-11-22 15:57:37', NULL),
(4, 'DH26112309230922', 1, NULL, 'Quản trị viên quán', '0901000001', 'giao_tan_noi', '123 Đường 3/2, Long Xuyên, An Giang', 'tien_mat', 'cho_thanh_toan', 343000.00, 0.00, 343000.00, 'cho_xac_nhan', 0, NULL, '2026-11-23 08:23:09', '2026-11-23 08:37:01'),
(5, 'DH26112309291267', 1, NULL, 'Quản trị viên quán', '0901000001', 'giao_tan_noi', '123 Đường 3/2, Long Xuyên, An Giang', 'tien_mat', 'cho_thanh_toan', 90000.00, 0.00, 90000.00, 'dang_pha_che', 1, NULL, '2026-11-23 08:29:12', '2026-11-23 08:54:50'),
(6, 'DH26112309575995', 1, NULL, 'Quản trị viên quán', '0901000001', 'giao_tan_noi', '123 Đường 3/2, Long Xuyên, An Giang', 'tien_mat', 'cho_thanh_toan', 650000.00, 0.00, 650000.00, 'da_huy', 0, NULL, '2026-11-23 08:57:59', '2026-11-23 08:58:15'),
(7, 'DH26112310160816', 1, NULL, 'Quản trị viên quán', '0901000001', 'giao_tan_noi', '123 Đường 3/2, Long Xuyên, An Giang', 'chuyen_khoan', 'cho_thanh_toan', 55000.00, 0.00, 55000.00, 'da_huy', 0, NULL, '2026-11-23 09:16:08', '2026-11-23 09:20:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_kho`
--

CREATE TABLE `lich_su_kho` (
  `id` int(10) UNSIGNED NOT NULL,
  `nguyen_lieu_id` int(10) UNSIGNED NOT NULL,
  `loai_bien_dong` enum('nhap_kho','xuat_ban','hoan_don','xuat_huy') NOT NULL,
  `so_luong_thay_doi` decimal(12,3) NOT NULL,
  `ton_sau_bien_dong` decimal(12,3) NOT NULL,
  `ma_tham_chieu` varchar(40) DEFAULT NULL,
  `ghi_chu` varchar(255) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_su_kho`
--

INSERT INTO `lich_su_kho` (`id`, `nguyen_lieu_id`, `loai_bien_dong`, `so_luong_thay_doi`, `ton_sau_bien_dong`, `ma_tham_chieu`, `ghi_chu`, `ngay_tao`) VALUES
(1, 8, 'nhap_kho', 2250.000, 2250.000, 'PN26112307571099', 'Nhập kho nguyên liệu', '2026-11-23 06:57:10'),
(2, 8, 'xuat_huy', -50.000, 2200.000, 'XH26112308012023', 'làm hư món', '2026-11-23 07:01:20'),
(3, 9, 'nhap_kho', 2000.000, 2000.000, 'PN26112309263745', 'nhập hàng tuần', '2026-11-23 08:26:37'),
(4, 10, 'nhap_kho', 750.000, 750.000, 'PN26112309263745', 'nhập hàng tuần', '2026-11-23 08:26:37'),
(5, 11, 'nhap_kho', 500.000, 500.000, 'PN26112309263745', 'nhập hàng tuần', '2026-11-23 08:26:37'),
(6, 12, 'nhap_kho', 500.000, 500.000, 'PN26112309263745', 'nhập hàng tuần', '2026-11-23 08:26:37'),
(7, 12, 'nhap_kho', 2500.000, 3000.000, 'PN26112309274355', 'nhập ngày 23.11', '2026-11-23 08:27:43'),
(8, 1, 'xuat_ban', -40.000, 4960.000, 'DH26112309291267', 'Trừ kho tự động khi xử lý đơn DH26112309291267', '2026-11-23 08:36:59'),
(9, 3, 'xuat_ban', -100.000, 7900.000, 'DH26112309291267', 'Trừ kho tự động khi xử lý đơn DH26112309291267', '2026-11-23 08:36:59'),
(10, 1, 'hoan_don', 40.000, 5000.000, 'DH26112309291267', 'Hoàn kho tự động khi hủy đơn DH26112309291267', '2026-11-23 08:54:27'),
(11, 3, 'hoan_don', 100.000, 8000.000, 'DH26112309291267', 'Hoàn kho tự động khi hủy đơn DH26112309291267', '2026-11-23 08:54:27'),
(12, 1, 'xuat_ban', -40.000, 4960.000, 'DH26112309291267', 'Trừ kho tự động khi xử lý đơn DH26112309291267', '2026-11-23 08:54:50'),
(13, 3, 'xuat_ban', -100.000, 7900.000, 'DH26112309291267', 'Trừ kho tự động khi xử lý đơn DH26112309291267', '2026-11-23 08:54:50');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ma_giam_gia`
--

CREATE TABLE `ma_giam_gia` (
  `id` int(10) UNSIGNED NOT NULL,
  `ma_code` varchar(40) NOT NULL,
  `kieu_giam` enum('phan_tram','so_tien') NOT NULL DEFAULT 'so_tien',
  `gia_tri_giam` decimal(10,2) NOT NULL DEFAULT 0.00,
  `don_toi_thieu` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ngay_het_han` datetime DEFAULT NULL,
  `con_hieu_luc` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ma_giam_gia`
--

INSERT INTO `ma_giam_gia` (`id`, `ma_code`, `kieu_giam`, `gia_tri_giam`, `don_toi_thieu`, `ngay_het_han`, `con_hieu_luc`) VALUES
(1, 'CHAO15K', 'so_tien', 15000.00, 60000.00, '2026-12-31 23:59:59', 1),
(2, 'CAFE10', 'phan_tram', 10.00, 50000.00, '2026-12-31 23:59:59', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int(10) UNSIGNED NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `dia_chi` text DEFAULT NULL,
  `vai_tro` enum('khach_hang','nhan_vien','quan_tri') NOT NULL DEFAULT 'khach_hang',
  `trang_thai_hoat_dong` tinyint(1) NOT NULL DEFAULT 1,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ho_ten`, `email`, `so_dien_thoai`, `mat_khau`, `dia_chi`, `vai_tro`, `trang_thai_hoat_dong`, `ngay_tao`) VALUES
(1, 'Quản trị viên quán', 'admin@coffeeshop.local', '0901000001', '$2y$10$xyBBnfOROPLRTA3Vhc/gTuwQUERKGrpvzKC9c6Y/OKyloJXZ.TmfO', '123 Đường 3/2, Long Xuyên, An Giang', 'quan_tri', 1, '2026-11-21 14:50:39'),
(2, 'Nhân viên pha chế & kho', 'staff@coffeeshop.local', '0901000002', '$2y$10$xyBBnfOROPLRTA3Vhc/gTuwQUERKGrpvzKC9c6Y/OKyloJXZ.TmfO', '123 Đường 3/2, Long Xuyên, An Giang', 'nhan_vien', 1, '2026-11-21 14:50:39'),
(3, 'Khách hàng mẫu', 'khach@coffeeshop.local', '0901000003', '$2y$10$xyBBnfOROPLRTA3Vhc/gTuwQUERKGrpvzKC9c6Y/OKyloJXZ.TmfO', '45 Trần Hưng Đạo, Long Xuyên, An Giang', 'khach_hang', 1, '2026-11-21 14:50:39'),
(4, 'Trần Bình Trọng', 'binhtrongtran159@gmail.com', '0378095005', '$2y$10$xyBBnfOROPLRTA3Vhc/gTuwQUERKGrpvzKC9c6Y/OKyloJXZ.TmfO', NULL, 'khach_hang', 1, '2026-11-22 15:46:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguyen_lieu`
--

CREATE TABLE `nguyen_lieu` (
  `id` int(10) UNSIGNED NOT NULL,
  `ma_nguyen_lieu` varchar(30) NOT NULL,
  `ten_nguyen_lieu` varchar(120) NOT NULL,
  `don_vi_tinh` varchar(30) NOT NULL,
  `ton_kho_hien_tai` decimal(12,3) NOT NULL DEFAULT 0.000,
  `nguong_canh_bao` decimal(12,3) NOT NULL DEFAULT 0.000,
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguyen_lieu`
--

INSERT INTO `nguyen_lieu` (`id`, `ma_nguyen_lieu`, `ten_nguyen_lieu`, `don_vi_tinh`, `ton_kho_hien_tai`, `nguong_canh_bao`, `trang_thai`, `ngay_tao`) VALUES
(1, 'NL001', 'Cà phê hạt rang xay', 'g', 4960.000, 800.000, 1, '2026-11-23 06:16:20'),
(2, 'NL002', 'Sữa đặc có đường', 'ml', 6000.000, 1000.000, 1, '2026-11-23 06:16:20'),
(3, 'NL003', 'Sữa tươi thanh trùng', 'ml', 7900.000, 1500.000, 1, '2026-11-23 06:16:20'),
(4, 'NL004', 'Bột Matcha Nhật', 'g', 1500.000, 300.000, 1, '2026-11-23 06:16:20'),
(5, 'NL005', 'Đường nước / Đường cát', 'g', 5000.000, 1000.000, 1, '2026-11-23 06:16:20'),
(8, 'NL006', 'Syrup Caramel', 'ml', 2200.000, 1500.000, 1, '2026-11-23 06:50:37'),
(9, 'NL007', 'Sữa tươi', 'ml', 2000.000, 2000.000, 1, '2026-11-23 08:24:37'),
(10, 'NL008', 'Syrup Đào', 'ml', 750.000, 1500.000, 1, '2026-11-23 08:25:01'),
(11, 'NL009', 'Mật ong', 'ml', 500.000, 500.000, 1, '2026-11-23 08:25:16'),
(12, 'NL010', 'Trà đen', 'g', 3000.000, 1000.000, 1, '2026-11-23 08:25:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_nhap_kho`
--

CREATE TABLE `phieu_nhap_kho` (
  `id` int(10) UNSIGNED NOT NULL,
  `ma_phieu_nhap` varchar(30) NOT NULL,
  `ten_nha_cung_cap` varchar(150) DEFAULT NULL,
  `nguoi_tao_id` int(10) UNSIGNED DEFAULT NULL,
  `tong_tien` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ghi_chu` varchar(255) DEFAULT NULL,
  `ngay_nhap` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phieu_nhap_kho`
--

INSERT INTO `phieu_nhap_kho` (`id`, `ma_phieu_nhap`, `ten_nha_cung_cap`, `nguoi_tao_id`, `tong_tien`, `ghi_chu`, `ngay_nhap`) VALUES
(1, 'PN26112307571099', 'Kho Khởi Minh Long Xuyên', 1, 150000.08, NULL, '2026-11-23 06:57:10'),
(2, 'PN26112309263745', 'Kho Khởi Minh Long Xuyên', 1, 245000.00, 'nhập hàng tuần', '2026-11-23 08:26:37'),
(3, 'PN26112309274355', 'Kho Khởi Minh Long Xuyên', 1, 125000.00, 'nhập ngày 23.11', '2026-11-23 08:27:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham`
--

CREATE TABLE `san_pham` (
  `id` int(10) UNSIGNED NOT NULL,
  `danh_muc_id` int(10) UNSIGNED NOT NULL,
  `ten_san_pham` varchar(150) NOT NULL,
  `duong_dan` varchar(170) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `gia_goc` decimal(10,2) NOT NULL DEFAULT 0.00,
  `con_ban` tinyint(1) NOT NULL DEFAULT 1,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham`
--

INSERT INTO `san_pham` (`id`, `danh_muc_id`, `ten_san_pham`, `duong_dan`, `mo_ta`, `hinh_anh`, `gia_goc`, `con_ban`, `ngay_tao`) VALUES
(1, 1, 'Cà phê sữa đá', 'ca-phe-sua-da', 'Cà phê rang đậm vị kết hợp sữa đặc béo ngậy, dùng kèm đá viên.', 'ca-phe-sua-da.jpg', 25000.00, 1, '2026-11-21 14:50:39'),
(2, 1, 'Bạc xỉu', 'bac-xiu', 'Nhiều sữa hơn cà phê, vị ngọt dịu nhẹ, thơm béo dễ uống.', 'bac-xiu.jpg', 28000.00, 1, '2026-11-21 14:50:39'),
(3, 1, 'Latte đá', 'latte-da', 'Espresso hài hòa cùng sữa tươi mát lạnh, lớp kem sữa mịn nhẹ.', 'latte-da.jpg', 35000.00, 1, '2026-11-21 14:50:39'),
(4, 2, 'Trà đào cam sả', 'tra-dao-cam-sa', 'Trà thanh mát hòa quyện đào miếng giòn ngọt, hương cam và sả.', 'tra-dao-cam-sa.jpg', 35000.00, 1, '2026-11-21 14:50:39'),
(5, 2, 'Trà sữa truyền thống', 'tra-sua-truyen-thong', 'Trà đậm hương, vị béo cân bằng, hợp khi thêm trân châu hoặc kem cheese.', 'tra-sua-truyen-thong.jpg', 32000.00, 1, '2026-11-21 14:50:39'),
(6, 3, 'Matcha latte', 'matcha-latte', 'Bột matcha xanh mịn kết hợp sữa tươi, hậu vị thanh ngọt.', 'matcha-latte.jpg', 38000.00, 1, '2026-11-21 14:50:39'),
(7, 3, 'Sô-cô-la đá xay', 'so-co-la-da-xay', 'Sô-cô-la đậm đà xay cùng đá mát lạnh, thêm lớp kem tươi bên trên.', 'so-co-la-da-xay.jpg', 40000.00, 1, '2026-11-21 14:50:39'),
(8, 4, 'Croissant bơ', 'croissant-bo', 'Bánh ngàn lớp giòn rụm bên ngoài, mềm thơm mùi bơ bên trong.', 'croissant-bo.jpg', 26000.00, 1, '2026-11-21 14:50:39'),
(9, 4, 'Bánh su kem', 'banh-su-kem', 'Vỏ bánh mềm nhẹ, nhân kem vani mát lạnh không ngấy.', 'banh-su-kem.jpg', 22000.00, 1, '2026-11-21 14:50:39'),
(10, 4, 'Tiramisu hũ', 'tiramisu-hu', 'Bánh mềm ẩm hương cà phê và ca-cao, đóng hũ tiện giao đi.', 'tiramisu-hu.jpg', 35000.00, 1, '2026-11-21 14:50:39'),
(11, 1, 'Capuchino', 'capuchino', 'Cappuccino kết hợp espresso đậm đà, sữa tươi béo ngậy và lớp bọt sữa mềm mịn, mang đến hương vị thơm ngon và hài hòa.', 'assets/images/products/prod_20261122_200235_fd516ac16e7c.jpg', 45000.00, 1, '2026-11-22 19:02:35'),
(12, 7, 'táo', 'tao', 'bcvcv', NULL, 25000.00, 0, '2026-11-22 19:09:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `topping`
--

CREATE TABLE `topping` (
  `id` int(10) UNSIGNED NOT NULL,
  `ten_topping` varchar(100) NOT NULL,
  `gia_ban` decimal(10,2) NOT NULL DEFAULT 0.00,
  `con_ban` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `topping`
--

INSERT INTO `topping` (`id`, `ten_topping`, `gia_ban`, `con_ban`) VALUES
(1, 'Trân châu đen', 7000.00, 1),
(2, 'Kem cheese', 10000.00, 1),
(3, 'Đào miếng', 8000.00, 1),
(4, 'Thêm shot espresso', 10000.00, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `topping_don_hang`
--

CREATE TABLE `topping_don_hang` (
  `id` int(10) UNSIGNED NOT NULL,
  `chi_tiet_don_hang_id` int(10) UNSIGNED NOT NULL,
  `topping_id` int(10) UNSIGNED NOT NULL,
  `ten_topping` varchar(100) NOT NULL,
  `gia_topping` decimal(10,2) NOT NULL DEFAULT 0.00,
  `so_luong` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bien_the_san_pham`
--
ALTER TABLE `bien_the_san_pham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bien_the_san_pham` (`san_pham_id`);

--
-- Chỉ mục cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ctdh_don_hang` (`don_hang_id`),
  ADD KEY `idx_ctdh_san_pham` (`san_pham_id`),
  ADD KEY `idx_ctdh_bien_the` (`bien_the_id`);

--
-- Chỉ mục cho bảng `chi_tiet_phieu_nhap`
--
ALTER TABLE `chi_tiet_phieu_nhap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ctpn_phieu` (`phieu_nhap_id`),
  ADD KEY `idx_ctpn_nl` (`nguyen_lieu_id`);

--
-- Chỉ mục cho bảng `cong_thuc_pha_che`
--
ALTER TABLE `cong_thuc_pha_che`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ct_san_pham_nl` (`san_pham_id`,`nguyen_lieu_id`),
  ADD KEY `idx_ct_nguyen_lieu` (`nguyen_lieu_id`);

--
-- Chỉ mục cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_danh_muc_duong_dan` (`duong_dan`);

--
-- Chỉ mục cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_don_hang_ma` (`ma_don_hang`),
  ADD KEY `idx_don_hang_nguoi_dung` (`nguoi_dung_id`),
  ADD KEY `idx_don_hang_ma_giam_gia` (`ma_giam_gia_id`),
  ADD KEY `idx_don_hang_trang_thai` (`trang_thai_don`),
  ADD KEY `idx_don_hang_thanh_toan` (`trang_thai_thanh_toan`);

--
-- Chỉ mục cho bảng `lich_su_kho`
--
ALTER TABLE `lich_su_kho`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lsk_nl` (`nguyen_lieu_id`),
  ADD KEY `idx_lsk_ngay` (`ngay_tao`);

--
-- Chỉ mục cho bảng `ma_giam_gia`
--
ALTER TABLE `ma_giam_gia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ma_giam_gia_code` (`ma_code`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_nguoi_dung_email` (`email`);

--
-- Chỉ mục cho bảng `nguyen_lieu`
--
ALTER TABLE `nguyen_lieu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_nguyen_lieu_ma` (`ma_nguyen_lieu`),
  ADD KEY `idx_nguyen_lieu_ten` (`ten_nguyen_lieu`);

--
-- Chỉ mục cho bảng `phieu_nhap_kho`
--
ALTER TABLE `phieu_nhap_kho`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pnk_ma` (`ma_phieu_nhap`),
  ADD KEY `idx_pnk_ngay` (`ngay_nhap`),
  ADD KEY `fk_pnk_nguoi_tao` (`nguoi_tao_id`);

--
-- Chỉ mục cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_san_pham_duong_dan` (`duong_dan`),
  ADD KEY `idx_san_pham_danh_muc` (`danh_muc_id`);

--
-- Chỉ mục cho bảng `topping`
--
ALTER TABLE `topping`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `topping_don_hang`
--
ALTER TABLE `topping_don_hang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tdh_chi_tiet` (`chi_tiet_don_hang_id`),
  ADD KEY `idx_tdh_topping` (`topping_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bien_the_san_pham`
--
ALTER TABLE `bien_the_san_pham`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_phieu_nhap`
--
ALTER TABLE `chi_tiet_phieu_nhap`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `cong_thuc_pha_che`
--
ALTER TABLE `cong_thuc_pha_che`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `lich_su_kho`
--
ALTER TABLE `lich_su_kho`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `ma_giam_gia`
--
ALTER TABLE `ma_giam_gia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `nguyen_lieu`
--
ALTER TABLE `nguyen_lieu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `phieu_nhap_kho`
--
ALTER TABLE `phieu_nhap_kho`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `topping`
--
ALTER TABLE `topping`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `topping_don_hang`
--
ALTER TABLE `topping_don_hang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bien_the_san_pham`
--
ALTER TABLE `bien_the_san_pham`
  ADD CONSTRAINT `fk_bien_the_san_pham` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  ADD CONSTRAINT `fk_ctdh_bien_the` FOREIGN KEY (`bien_the_id`) REFERENCES `bien_the_san_pham` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ctdh_don_hang` FOREIGN KEY (`don_hang_id`) REFERENCES `don_hang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ctdh_san_pham` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `chi_tiet_phieu_nhap`
--
ALTER TABLE `chi_tiet_phieu_nhap`
  ADD CONSTRAINT `fk_ctpn_nl` FOREIGN KEY (`nguyen_lieu_id`) REFERENCES `nguyen_lieu` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ctpn_phieu` FOREIGN KEY (`phieu_nhap_id`) REFERENCES `phieu_nhap_kho` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `cong_thuc_pha_che`
--
ALTER TABLE `cong_thuc_pha_che`
  ADD CONSTRAINT `fk_ct_nguyen_lieu` FOREIGN KEY (`nguyen_lieu_id`) REFERENCES `nguyen_lieu` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ct_san_pham` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  ADD CONSTRAINT `fk_don_hang_ma_giam_gia` FOREIGN KEY (`ma_giam_gia_id`) REFERENCES `ma_giam_gia` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_don_hang_nguoi_dung` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `lich_su_kho`
--
ALTER TABLE `lich_su_kho`
  ADD CONSTRAINT `fk_lsk_nl` FOREIGN KEY (`nguyen_lieu_id`) REFERENCES `nguyen_lieu` (`id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `phieu_nhap_kho`
--
ALTER TABLE `phieu_nhap_kho`
  ADD CONSTRAINT `fk_pnk_nguoi_tao` FOREIGN KEY (`nguoi_tao_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD CONSTRAINT `fk_san_pham_danh_muc` FOREIGN KEY (`danh_muc_id`) REFERENCES `danh_muc` (`id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `topping_don_hang`
--
ALTER TABLE `topping_don_hang`
  ADD CONSTRAINT `fk_tdh_chi_tiet` FOREIGN KEY (`chi_tiet_don_hang_id`) REFERENCES `chi_tiet_don_hang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tdh_topping` FOREIGN KEY (`topping_id`) REFERENCES `topping` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
