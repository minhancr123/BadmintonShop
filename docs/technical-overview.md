# Tài liệu kỹ thuật dự án Badminton Shop

## 1. Tổng quan hệ thống
Badminton Shop là nền tảng thương mại điện tử xây dựng trên Laravel, phục vụ việc bán lẻ các sản phẩm cầu lông (vợt, giày, cầu, phụ kiện, trang phục). Ứng dụng cung cấp đầy đủ tính năng cho cả khách hàng và quản trị viên: duyệt danh mục, tìm kiếm, giỏ hàng, thanh toán đa cổng (VNPay, MoMo, COD), quản lý đơn hàng, đánh giá sản phẩm, thống kê – báo cáo, cùng giao diện quản trị giàu chức năng.

## 2. Công nghệ sử dụng
- **Ngôn ngữ & Framework:** PHP ^8.1, Laravel 10
- **Máy chủ phát triển:** XAMPP (Apache + MySQL + PHP CLI/FPM)
- **Cơ sở dữ liệu:** MySQL (InnoDB) truy xuất qua Eloquent ORM
- **Cache:** Redis (tuỳ chọn, truy cập qua Predis) với cache tags
- **Frontend:** Blade template, Bootstrap 5, Font Awesome, JavaScript thuần/AlpineJS
- **Thư viện Composer chính:**
  - `laravel/ui`: scaffolding auth
  - `maatwebsite/excel`: xuất CSV/XLSX
  - `barryvdh/laravel-dompdf`: xuất PDF
  - `intervention/image`: xử lý ảnh server-side
  - `predis/predis`: driver Redis
  - `laravel/sanctum`, `laravel/tinker`, `laravel/pint`, `spatie/laravel-ignition`

## 3. Kiến trúc ứng dụng
- **Lớp trình bày:** Blade view tại `resources/views`, tách riêng layout cho storefront (`layouts.app`) và admin (`admin.layout`).
- **Định tuyến:** `routes/web.php` khai báo tuyến public, tuyến cần đăng nhập và tuyến quản trị (prefix `admin`, middleware `AdminMiddleware`).
- **Controller:** tổ chức theo miền nghiệp vụ trong `app/Http/Controllers` (sản phẩm, danh mục, giỏ hàng, đơn hàng, thanh toán, đánh giá, quản trị, xác thực).
- **Model:** `app/Models` định nghĩa logic, quan hệ, scope và accessor như `Product::scopeActive`, `Product::getCurrentPriceAttribute`.
- **Service:** `app/Services/CacheService` cho caching, `ImageOptimizationService` cho xử lý ảnh.
- **Export:** `app/Exports/ReportExport` phục vụ xuất báo cáo Excel.
- **Middleware:** `AdminMiddleware` kiểm soát vai trò, kết hợp middleware mặc định của Laravel.
- **Storage:** tập tin upload lưu tại `storage/app/public`, truy cập qua symbolic link `public/storage`.

## 4. Mô hình dữ liệu
| Bảng | Trường chính | Quan hệ |
| --- | --- | --- |
| `users` | thông tin cá nhân, `role` | `hasMany(Order)`; admin khi `role = admin` |
| `categories` | `name`, `slug`, `description`, `image`, `is_active` | `hasMany(Product)` |
| `products` | giá, tồn kho, SKU, gallery, thuộc tính kỹ thuật, trạng thái hiển thị | `belongsTo(Category)`, `hasMany(OrderItem)`, `hasMany(Review)` |
| `orders` | số đơn, khách hàng, tổng tiền, trạng thái, phương thức/thông tin giao hàng | `belongsTo(User)`, `hasMany(OrderItem)` |
| `order_items` | liên kết sản phẩm–đơn hàng, số lượng, giá, tổng | `belongsTo(Order)`, `belongsTo(Product)` |
| `reviews` | rating, tiêu đề, nhận xét, ưu/nhược điểm, cờ duyệt | `belongsTo(Product)`, `belongsTo(User)` |

Các migration bổ sung bảng `password_reset_tokens`, cột `average_rating` & `reviews_count` cho bảng sản phẩm.

## 5. Chức năng chính
### 5.1 Xác thực & phân quyền
- Auth mặc định từ `laravel/ui` (Login/Register controller).
- Phân quyền dựa trên session; `AdminMiddleware` giới hạn truy cập khu vực quản trị.

### 5.2 Quản lý danh mục & sản phẩm
- **ProductController:** liệt kê, lọc (danh mục, thương hiệu, giá, khuyến mãi), xem chi tiết, đề xuất sản phẩm liên quan.
- **CategoryController:** trang danh mục, thống kê danh mục phổ biến, sản phẩm nổi bật.
- **AdminController::products/categories:** giao diện quản trị, lọc, bulk action (kích hoạt, vô hiệu, xoá), CRUD thông qua `ProductController`/`CategoryController`.
- Ảnh được lưu qua `Storage`, có thể tối ưu bằng `ImageOptimizationService`.

### 5.3 Giỏ hàng & thanh toán
- **CartController:** sử dụng session lưu cart, kiểm tra tồn kho, cung cấp endpoint JSON cho AJAX (có logging chi tiết).
- **OrderController::checkout:** xác thực dữ liệu giỏ trước khi hiển thị trang thanh toán, auto-fill thông tin người dùng.

### 5.4 Đơn hàng & giao nhận
- **OrderController::store:** chạy transaction tạo đơn hàng và item, trừ tồn kho, xoá cart.
- Người dùng có thể xem lịch sử, huỷ đơn (nếu cho phép), đặt lại, tải hoá đơn PDF (đơn đã giao).
- **AdminController::orders**: theo dõi, cập nhật trạng thái qua AJAX (`updateOrderStatus`), xem chi tiết.

### 5.5 Thanh toán
- Hai luồng song song:
  1. Logic thanh toán đặt trong `OrderController` (MoMo, VNPay) dùng session `pending_order` để chờ callback.
  2. `PaymentController` triển khai xử lý tương tự. Nên hợp nhất nhằm tránh trùng lặp và sai lệch.
- Hỗ trợ: COD, chuyển hướng cổng MoMo/VNPay, bank transfer (placeholder).

### 5.6 Đánh giá & xếp hạng
- `ReviewController` cho phép lọc theo rating, sắp xếp, thống kê tỷ lệ sao.
- Eloquent event cập nhật `average_rating`, `reviews_count` mỗi khi review thay đổi.
- Kiểm tra mua hàng (`userHasPurchased`) dựa trên đơn giao thành công.

### 5.7 Báo cáo & xuất dữ liệu
- `AdminController::reports`: tổng doanh thu, số đơn, AOV, top sản phẩm, khách hàng mới theo khoảng thời gian (7 ngày, 30 ngày, 3 tháng, 1 năm).
- `exportReport`: xuất Excel/CSV qua `ReportExport`, hoặc PDF qua view `admin.reports.pdf` + DomPDF.

### 5.8 Cache & hiệu năng
- `CacheService`: chuẩn hoá cache tags cho sản phẩm, danh mục, đơn hàng, tìm kiếm; cung cấp invalidate/warm-up và thống kê Redis.
- Khi cập nhật rating sản phẩm, cache liên quan được xoá để đảm bảo đồng nhất.

### 5.9 Quản lý media
- `ImageOptimizationService` (và bản duplicate `ImageOptimizationServiceNew`) hỗ trợ nén, resize, tạo nhiều kích thước, chuyển WebP.
- **Cảnh báo:** Hai file khai báo cùng class `App\Services\ImageOptimizationService` gây xung đột; cần hợp nhất hoặc đổi tên.

### 5.10 Giao diện quản trị
- Dashboard hiển thị số liệu tổng quan, đơn mới, sản phẩm sắp hết hàng, biểu đồ doanh thu 6 tháng.
- Quản trị người dùng với chức năng thay đổi vai trò (có kiểm tra tránh tự hạ cấp).
- Các view admin sử dụng JS helper (`window.showConfirm`, `window.showToast`); cần đảm bảo được khai báo chung.

## 6. Luồng tích hợp
- **Thanh toán:** checkout → lưu session `pending_order` → redirect sang gateway → callback `/payment/momo_return`, `/payment/vnpay/return`, hoặc IPN → tạo đơn/thay đổi trạng thái.
- **Cache:** controller có thể sử dụng `CacheService` để cache danh sách, kết quả tìm kiếm; invalidate khi dữ liệu thay đổi.
- **Liên hệ & newsletter:** `HomeController::contactSubmit` và `newsletter` xử lý form (hiện chưa gửi email thực).

## 7. Lớp trình bày
- Storefront: view tại `resources/views` (ví dụ `products`, `categories`, `orders`, `pages`). Nội dung đa ngôn ngữ Việt/Anh, dùng Bootstrap card/table/modal.
- Admin: `resources/views/admin/...`, tích hợp bảng dữ liệu, filter, bulk action; không chỉnh sửa file cache trong `storage/framework/views`.

## 8. Cấu hình & môi trường
- `.env`: khai báo DB (`badminton_shop`), cache (predis), mail (Mailpit), khoá thanh toán (`VNPAY_*`, `MOMO_*`).
- `config/payment.php`: mapping biến môi trường với giá trị mặc định sandbox.
- Cần chạy `php artisan storage:link` để công khai thư mục upload.

## 9. CSDL & dữ liệu mẫu
- Migration tạo bảng cho người dùng, danh mục, sản phẩm, đơn, item, review, reset password.
- Seeder: admin (`admin@badmintonshop.com`/`password123`), người dùng demo, 6 danh mục, danh sách sản phẩm mẫu, review (tuỳ chọn).

## 10. Ghi log & giám sát
- Laravel log mặc định tại `storage/logs/laravel.log`.
- Thanh toán VNPay/MoMo log URL, TxnRef, response để hỗ trợ debug.
- `CartController` log chi tiết khi cập nhật/xoá giỏ giúp truy vết lỗi AJAX.

## 11. Kiểm thử & chất lượng
- Dự án có phụ thuộc PHPUnit/Pest nhưng chưa xây dựng test. Khuyến nghị viết test cho: checkout, thanh toán thành công/thất bại, bulk action admin, review.
- Một số view admin chứa comment `TODO` (xuất dữ liệu, verify email, reset password, suspend user) chưa triển khai.

## 12. Triển khai & vận hành
### 12.1 Thiết lập local
1. `composer install`
2. `cp .env.example .env` và chỉnh thông số, chạy `php artisan key:generate`
3. Tạo database `badminton_shop`
4. `php artisan migrate --seed`
5. `php artisan storage:link`
6. `php artisan serve` hoặc cấu hình VirtualHost Apache

### 12.2 Vận hành
- Chưa cấu hình queue/background job; tác vụ lâu (gửi mail, export) chạy đồng bộ.
- Cache warm-up có thể tích hợp scheduler/command tuỳ nhu cầu.

## 13. Vấn đề tồn đọng & khuyến nghị
1. **Trùng lặp service ảnh:** gộp `ImageOptimizationService` và `ImageOptimizationServiceNew` để tránh lỗi class redeclare.
2. **Thanh toán phân mảnh:** chuẩn hoá logic MoMo/VNPay vào một service/controller duy nhất, lưu transaction ID, chuẩn hoá verify chữ ký.
3. **Review helpful:** route `reviews.helpful` tồn tại nhưng controller chưa hiện thực — cần bổ sung.
4. **JS helper:** đảm bảo `showConfirm`/`showToast` khả dụng trên mọi trang admin trước khi gọi.
5. **File runtime trong VCS:** loại bỏ `storage/framework/views`, `storage/logs` khỏi git, chỉ commit source.
6. **Chống spam:** áp dụng throttle/rate-limit cho contact/newsletter/search suggestion.
7. **Giám sát:** cân nhắc tích hợp Sentry/Laravel Telescope, log rotation, monitor cache.

## 14. Định hướng phát triển
1. **Test tự động:** tối thiểu kiểm thử end-to-end quy trình mua hàng.
2. **Tối ưu thanh toán:** tách lớp PaymentService, chuẩn hoá callback/IPN, bổ sung retry & logging chuẩn.
3. **Quản trị người dùng:** hoàn thiện các action còn TODO (xác thực email, reset mật khẩu, khoá tài khoản).
4. **API mở rộng:** xây dựng API REST/Sanctum cho mobile app, đồng thời bảo vệ bằng rate limit & token.
5. **Hiệu năng:** dùng `CacheService` nhiều hơn cho trang phổ biến, thêm lazy-loading hình ảnh, CDN nếu triển khai production.
