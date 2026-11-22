# Coffee Shop Management - 47th Cafe

Website đặt đồ uống và bánh trực tuyến, kèm trang quản trị dành cho nhân viên/quản trị quán. Dự án sử dụng PHP thuần, mô hình MVC gọn nhẹ, PDO, MySQL và Bootstrap 5.

## Chức năng

### Khách hàng
- Xem trang chủ, thực đơn và lọc món theo danh mục.
- Tìm kiếm món theo tên.
- Chọn size, topping, ghi chú và số lượng.
- Thêm, sửa, cập nhật số lượng và xóa món trong giỏ hàng.
- Áp dụng mã giảm giá theo phần trăm hoặc số tiền.
- Chọn giao tận nơi hoặc đến quán lấy.
- Chọn thanh toán tiền mặt hoặc chuyển khoản.
- Tạo đơn hàng bằng PDO transaction.
- Đăng ký, đăng nhập và đăng xuất.
- Xem lịch sử đơn và trạng thái xử lý.

### Nhân viên và quản trị
- Kiểm tra quyền nhân viên/quản trị trước khi truy cập trang admin.
- Xem danh sách đơn hàng theo trạng thái.
- Cập nhật trạng thái đơn hàng.
- Quản lý danh mục và trạng thái còn bán.
- Quản lý sản phẩm, ảnh, giá gốc, mô tả và trạng thái còn bán.
- Quản lý size và giá cộng thêm theo từng sản phẩm.
- Quản lý topping và giá bán.
- Tìm kiếm khách hàng, xem hồ sơ và lịch sử đơn.
- Khóa hoặc mở tài khoản khách hàng.
- Xem tổng số đơn, đơn đang xử lý, đơn hoàn thành, đơn hủy và doanh thu.
- Lọc thống kê theo 7 ngày, 30 ngày hoặc khoảng thời gian tùy chọn.
- Xem doanh thu theo ngày và top món bán chạy.

## Công nghệ
- PHP thuần với mô hình MVC gọn nhẹ.
- PDO và Prepared Statement.
- MySQL/MariaDB.
- Bootstrap 5, HTML, CSS và JavaScript.
- PHP session để lưu đăng nhập, giỏ hàng và mã giảm giá.

## Cấu trúc thư mục

```text
coffee-shop-management/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── images/
│   │   └── products/
│   └── picture/
├── config/
│   ├── coffee_shop.sql
│   └── database.php
├── controllers/
│   ├── AdminController.php
│   ├── AuthController.php
│   ├── CartController.php
│   ├── CheckoutController.php
│   ├── HomeController.php
│   ├── OrderController.php
│   └── admin/
│       ├── catalog.php
│       ├── customers.php
│       ├── dashboard.php
│       ├── options.php
│       └── orders.php
├── core/
│   ├── db.php
│   └── view.php
├── models/
│   ├── OrderModel.php
│   ├── ProductModel.php
│   ├── ReportModel.php
│   ├── UserModel.php
│   └── VoucherModel.php
├── views/
│   ├── admin/
│   │   ├── _sidebar.php
│   │   ├── catalog.php
│   │   ├── customers.php
│   │   ├── dashboard.php
│   │   └── index.php
│   ├── auth/
│   ├── cart/
│   ├── checkout/
│   ├── home/
│   ├── order/
│   └── layouts/
│       └── main.php
└── index.php
```

### Vai trò thư mục
- `index.php`: entry point; đọc `c` là controller và `a` là action.
- `config/database.php`: cấu hình database và tính `BASE_URL`.
- `config/coffee_shop.sql`: cấu trúc và dữ liệu MySQL để import.
- `core/db.php`: tạo kết nối PDO dùng chung.
- `core/view.php`: render view/layout và định dạng tiền VND.
- `controllers/`: xử lý request và điều hướng theo route.
- `controllers/admin/`: phần nghiệp vụ quản trị được tách theo đơn hàng, catalog, dashboard, khách hàng và tùy chọn size/topping.
- `models/`: truy vấn và xử lý dữ liệu theo nhóm sản phẩm, đơn hàng, người dùng, voucher và báo cáo.
- `views/`: giao diện người dùng và quản trị.
- `assets/css/style.css`: stylesheet chung.
- `assets/images/products/`: ảnh sản phẩm được tải từ trang quản trị.

## Yêu cầu
- PHP 8.0 trở lên.
- PDO MySQL.
- MySQL hoặc MariaDB.
- XAMPP hoặc Apache tương thích PHP.
- phpMyAdmin để tạo và import database.

## Cài đặt

1. Sao chép thư mục dự án vào thư mục web của XAMPP:
   ```text
   D:\xampp\htdocs\coffee-shop-management
   ```
2. Khởi động Apache và MySQL trong XAMPP.
3. Mở phpMyAdmin.
4. Tạo database `coffee_shop`, sau đó import:
   ```text
   config/coffee_shop.sql
   ```
5. Kiểm tra `config/database.php`:
   ```php
   DB_HOST = '127.0.0.1'
   DB_NAME = 'coffee_shop'
   DB_USER = 'root'
   DB_PASS = ''
   DB_CHARSET = 'utf8mb4'
   ```
6. Mở dự án:
   ```text
   http://localhost/coffee-shop-management/
   ```

## Route chính

| Chức năng | Route |
|---|---|
| Trang chủ | `?c=home&a=index` |
| Thực đơn | `?c=home&a=menu` |
| Giỏ hàng | `?c=cart&a=index` |
| Thanh toán | `?c=checkout&a=index` |
| Đơn hàng của tôi | `?c=order&a=index` |
| Đăng nhập | `?c=auth&a=login` |
| Đăng ký | `?c=auth&a=register` |
| Quản trị đơn hàng | `?c=admin&a=index` |
| Dashboard | `?c=admin&a=dashboard` |
| Quản lý catalog | `?c=admin&a=catalog` |
| Quản lý khách hàng | `?c=admin&a=customers` |

## Quy ước an toàn
- Dữ liệu người dùng trong truy vấn SQL được truyền bằng PDO Prepared Statement.
- Chuỗi xuất ra HTML được escape bằng `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- Mật khẩu được băm bằng `password_hash()` và kiểm tra bằng `password_verify()`.
- Các action admin kiểm tra quyền nhân viên/quản trị trước khi xử lý dữ liệu.
- Luồng tạo đơn sử dụng PDO transaction để lưu đơn và chi tiết đơn nhất quán.
- Giá size/topping được lấy lại từ database thay vì tin cậy hoàn toàn vào dữ liệu gửi từ trình duyệt.

## Tài nguyên
- Ảnh sản phẩm được lưu tại `assets/images/products/`.
- `assets/picture/capuchino.jpg` hiện không được tham chiếu trong SQL hoặc code và giữ lại để tránh xóa ngoài ý muốn.
