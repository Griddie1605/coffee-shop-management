# Tổng hợp định hướng đồ án: Xây dựng website đặt hàng trực tuyến và quản lý kho nguyên liệu cho quán cà phê

Tài liệu này lưu lại toàn bộ thống nhất từ quá trình trao đổi để làm mốc tham chiếu và backup xuyên suốt quá trình làm đồ án môn Công nghệ web.

---

## 1. Tên đề tài, Mục tiêu & Phạm vi

- **Tên đề tài chính thức:** _Xây dựng website đặt hàng trực tuyến và quản lý kho nguyên liệu cho quán cà phê_ (hoặc _Xây dựng website đặt đồ uống trực tuyến và quản lý vận hành quán cà phê_).
- **Định hướng cốt lõi:** Hệ thống bán đồ uống / bánh ngọt trực tuyến (đặt mang đi / giao hàng) kết hợp quản lý vận hành đơn hàng và kho nguyên liệu theo công thức định lượng.
- **Điểm nhấn kỹ thuật:** Liên kết chặt chẽ giữa **Đơn hàng Online $\leftrightarrow$ Công thức định lượng (Recipe/BOM) $\leftrightarrow$ Tồn kho nguyên liệu**.
- **Những phần KHÔNG làm (để tránh loãng & vỡ tiến độ):**
  - Không làm POS tại bàn (sơ đồ bàn, mở/đóng bàn, tách/gộp bàn).
  - Không làm quản lý chấm công, ca làm việc, tính lương nhân viên.

---

## 2. Luồng chức năng chính

### A. Phía Khách hàng (Storefront — Đặt hàng Online)

- Xem thực đơn theo danh mục, tìm kiếm, lọc món; món hết nguyên liệu có thể báo tạm hết hàng.
- Tùy chỉnh món: chọn **Size** (S/M/L), mức đá, mức đường, thêm **Topping** và tự động cập nhật giá.
- Quản lý giỏ hàng, nhập mã giảm giá (voucher), chọn hình thức nhận hàng (_Giao tận nơi_ / _Đến quán lấy_), chọn phương thức thanh toán (_COD_ / _Chuyển khoản_).
- Theo dõi trạng thái đơn hàng (_Chờ xác nhận_ $\rightarrow$ _Đang pha chế_ $\rightarrow$ _Đang giao_ $\rightarrow$ _Hoàn thành_) và xem lịch sử đơn.

### B. Phía Quản trị & Vận hành (Admin / Nhân viên)

- Quản lý danh mục, sản phẩm, giá theo size, danh sách topping và trạng thái còn/hết món.
- **Quản lý kho nguyên liệu & định lượng:**
  - Danh mục nguyên liệu đầu vào (_cà phê hạt, sữa tươi, sữa đặc, đường, trân châu, ly..._) kèm đơn vị tính (_gram, ml, cái_).
  - Nhập kho, theo dõi tồn kho và cảnh báo nguyên liệu sắp hết.
  - Cấu hình định lượng nguyên liệu cho từng món (và size/topping).
- **Xử lý đơn hàng & tự động trừ kho:**
  - Duyệt đơn / cập nhật trạng thái đơn và tự động trừ tồn kho nguyên liệu theo định lượng (dùng **Transaction** để đảm bảo toàn vẹn dữ liệu; kiểm tra đủ nguyên liệu và hỗ trợ hoàn kho nếu hủy đơn sau khi đã trừ).
- Quản lý tài khoản/phân quyền (Admin / Nhân viên), voucher và thống kê doanh thu, chi phí nhập nguyên liệu, top món bán chạy.

---

## 3. Công nghệ & Kiến trúc

- **Front-end:** HTML5, CSS3, Bootstrap 5, JavaScript (tính giá tạm tính khi đổi size/topping, thao tác giỏ hàng, validate form, tương tác mượt).
- **Back-end:** PHP thuần theo mô hình **MVC** (sử dụng **PDO** và **Prepared Statement** để chống SQL Injection, **Transaction** khi xử lý đơn & trừ kho).
- **Cơ sở dữ liệu:** MySQL quản lý qua **phpMyAdmin** trên môi trường **XAMPP**.

### Cấu trúc thư mục MVC dự kiến

```text
coffee_mvc/
├── config/
│   └── database.php
├── core/
│   ├── Controller.php
│   ├── Model.php
│   └── Router.php
├── controllers/
│   ├── HomeController.php
│   ├── CartController.php
│   ├── OrderController.php
│   ├── AuthController.php
│   └── AdminController.php
├── models/
│   ├── ProductModel.php
│   ├── OrderModel.php
│   ├── InventoryModel.php
│   └── UserModel.php
├── views/
│   ├── layouts/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── admin_layout.php
│   ├── customer/
│   │   ├── home.php
│   │   ├── menu.php
│   │   ├── cart.php
│   │   ├── checkout.php
│   │   └── orders.php
│   ├── auth/
│   │   └── login.php
│   └── admin/
│       ├── dashboard.php
│       ├── products.php
│       ├── inventory.php
│       ├── recipes.php
│       └── orders.php
├── public/
│   ├── index.php
│   └── assets/
│       ├── css/
│       │   └── custom.css
│       ├── js/
│       │   └── app.js
│       └── images/
└── .htaccess
```

---

## 4. Định hướng giao diện (Dựa theo mẫu "47th cafe")

- **Bảng màu chủ đạo:**
  - Kem/be nhạt (`#F6E7D4` cho navbar, `#FAF2E6` / `#F8EFE2` cho khu vực địa chỉ/giờ mở cửa và dải bản quyền).
  - Trắng (`#FFFFFF`) cho các khối nội dung xen kẽ.
  - Xanh rêu / ô liu (`#747D6B` cho thanh thông báo deal, `#4B583E` cho footer chính).
  - Chữ và viền sắc nét tông đen/than đậm, nút dạng viền mảnh (`outline`) góc vuông nhẹ.
- **Kiểu chữ:** Font Serif cho tiêu đề lớn tạo nét ấm áp, cổ điển; font Sans-serif rõ ràng cho nội dung, giá tiền, form và bảng quản trị.
- **Bố cục trang chủ (`home.php`):**
  1. Navbar nền kem nhạt (logo chữ bên trái, link điều hướng + Log In + Cart bên phải).
  2. Hero banner ảnh cà phê toàn chiều ngang với lớp phủ tối nhẹ, tiêu đề lớn và 2 nút `Order Online` / `Our Menu`.
  3. Thanh thông báo deal màu xanh rêu có mũi tên chuyển slide.
  4. Khối Split 50/50 **"Eat."** (trái chữ, phải ảnh).
  5. Khối Split 50/50 **"Drink."** (đảo chiều: trái ảnh, phải chữ).
  6. Khối **"Drop By for a Bite."** nền kem nhạt chia 2 cột `Address` và `Opening Hours` có vạch ngăn giữa.
  7. Dải ảnh gallery 5 ô ngang sát nhau full chiều ngang.
  8. Footer nền xanh rêu trầm (3 cột: Newsletter, Talk to Us, liên kết mạng xã hội serif lớn) và dải bản quyền kem nhạt dưới cùng.
- **Các trang phụ & Admin:** Đồng bộ bảng màu, font chữ và phong cách nút/viền với trang chủ; trang Admin dùng layout quản trị gọn gàng để xử lý đơn hàng, kho nguyên liệu, công thức định lượng và thống kê.

## Trước khi bắt đầu code trên máy hiện tại: luôn kéo bản ## mới nhất từ GitHub về trước:

# Bash

git pull origin main

# Khi code xong một phần và muốn sang máy kia làm tiếp:

# Bash

git add .
git commit -m "Mo ta viec vua lam"
git push origin main
