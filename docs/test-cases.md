# Test Case Suite – Badminton Shop

Tài liệu này liệt kê các test case cốt lõi cần thực hiện thủ công hoặc tự động cho hệ thống Badminton Shop. Các test case được tổ chức theo module chức năng chính: xác thực, sản phẩm, giỏ hàng, thanh toán, quản trị, đánh giá, báo cáo và hệ thống hỗ trợ. Mỗi test case bao gồm ID, mục tiêu, tiền điều kiện, bước thực hiện và kết quả kỳ vọng.

> Gợi ý: nên ghi lại kết quả thực tế, trạng thái (Pass/Fail) và ghi chú bổ sung vào cột riêng khi thực thi.

## 1. Authentication & Authorization

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| AUTH-01 | Đăng ký người dùng mới | Không đăng nhập | 1. Vào `/register` 2. Nhập thông tin hợp lệ 3. Gửi form | Người dùng được tạo, tự động đăng nhập, chuyển tới trang chủ, email chào mừng (nếu bật) |
| AUTH-02 | Kiểm tra validate đăng ký | Không đăng nhập | 1. Vào `/register` 2. Để trống hoặc nhập email trùng 3. Gửi form | Hệ thống hiển thị thông báo lỗi tương ứng, không tạo tài khoản |
| AUTH-03 | Đăng nhập người dùng hợp lệ | Đã có tài khoản | 1. Vào `/login` 2. Nhập email & mật khẩu hợp lệ 3. Gửi form | Đăng nhập thành công, chuyển đến trang chủ hoặc trang đã bảo vệ |
| AUTH-04 | Chặn đăng nhập sai mật khẩu | Đã có tài khoản | 1. Vào `/login` 2. Nhập email hợp lệ nhưng mật khẩu sai 3. Gửi form | Thông báo "Thông tin không chính xác", không đăng nhập |
| AUTH-05 | Reset mật khẩu qua email | Người dùng tồn tại, SMTP hoạt động | 1. Vào `/password/reset` 2. Nhập email 3. Nhấp link trong email 4. Đặt mật khẩu mới | Email reset được gửi, người dùng đặt lại thành công, có thể đăng nhập bằng mật khẩu mới |
| AUTH-06 | Hạn chế truy cập admin | Tài khoản user thường | 1. Đăng nhập user thường 2. Truy cập `/admin` | Bị chuyển hướng hoặc thấy thông báo "Access denied" |
| AUTH-07 | Quyền admin hợp lệ | Tài khoản admin tồn tại | 1. Đăng nhập admin 2. Truy cập `/admin/dashboard` | Dashboard hiển thị đầy đủ widget |

## 2. Product Catalog & Search

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| PROD-01 | Xem danh sách danh mục | Có danh mục hoạt động | 1. Vào `/categories` | Danh mục kèm ảnh hiển thị, có phân trang nếu >12 |
| PROD-02 | Lọc sản phẩm theo danh mục | Có sản phẩm đa danh mục | 1. Vào `/categories/{slug}` 2. Quan sát danh sách | Chỉ hiển thị sản phẩm thuộc danh mục, breadcrumb chính xác |
| PROD-03 | Tìm kiếm sản phẩm | Có sản phẩm tên chứa "Yonex" | 1. Sử dụng ô tìm kiếm nhập "Yonex" 2. Xem kết quả | Sản phẩm liên quan xuất hiện, không có lỗi ký tự |
| PROD-04 | Xem chi tiết sản phẩm | Sản phẩm hoạt động | 1. Từ danh sách nhấn sản phẩm | Trang chi tiết hiển thị ảnh, tồn kho, giá, review, sản phẩm liên quan |
| PROD-05 | Ảnh sản phẩm load từ storage | Sản phẩm có ảnh tải lên | 1. Mở trang chi tiết 2. Kiểm tra đường dẫn ảnh | Ảnh lấy từ `storage/products/...`, không 404 |

## 3. Cart & Checkout

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| CART-01 | Thêm sản phẩm vào giỏ | Đăng nhập hoặc khách | 1. Vào trang sản phẩm 2. Chọn số lượng 3. Thêm vào giỏ | Thông báo thành công, biểu tượng giỏ cập nhật số lượng |
| CART-02 | Cập nhật số lượng giỏ | Giỏ có ít nhất 1 sản phẩm | 1. Vào `/cart` 2. Tăng/giảm số lượng 3. Lưu | Tổng tiền cập nhật đúng, không vượt tồn kho |
| CART-03 | Xóa sản phẩm khỏi giỏ | Giỏ có sản phẩm | 1. Vào `/cart` 2. Xóa item | Item bị xóa, tổng tiền cập nhật |
| CART-04 | Checkout với COD | Giỏ hợp lệ, user đăng nhập | 1. `/checkout` 2. Điền địa chỉ 3. Chọn COD 4. Xác nhận | Đơn hàng trạng thái `pending`, email xác nhận gửi, giỏ trống |
| CART-05 | Checkout khi hết hàng | Sản phẩm tồn kho = 0 | 1. Thêm sản phẩm hết hàng vào giỏ 2. Checkout | Hệ thống chặn đặt hàng, thông báo hết hàng |
| CART-06 | Bảo vệ giỏ khi chưa đăng nhập | Có sản phẩm trong giỏ khi chưa login | 1. Thêm sản phẩm 2. Đăng nhập | Sau login giỏ vẫn giữ nguyên (session/cart merge) |

## 4. Payment Gateway

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| PAY-01 | Thanh toán VNPay thành công | Cấu hình sandbox VNPay | 1. Checkout chọn VNPay 2. Chuyển hướng VNPay 3. Thanh toán sandbox | Hệ thống nhận callback, đơn hàng `paid`, lưu transaction ID |
| PAY-02 | Thanh toán VNPay bị hủy | Cấu hình sandbox VNPay | 1. Checkout chọn VNPay 2. Bấm hủy tại VNPay | Đơn hàng giữ trạng thái `pending`/`cancelled`, log ghi nhận |
| PAY-03 | Thanh toán MoMo thành công | Cấu hình sandbox MoMo | 1. Checkout chọn MoMo 2. Hoàn tất trên MoMo | Đơn hàng `paid`, nhận IPN hợp lệ, không trùng lặp xử lý |
| PAY-04 | COD không cần gateway | Cấu hình COD | 1. Checkout chọn COD | Đơn `pending`, không tạo bản ghi payment gateway |
| PAY-05 | Bảo vệ chữ ký callback | Có tool giả callback | 1. Gửi callback sai signature | Hệ thống từ chối, log cảnh báo, đơn không thay đổi |

## 5. Order Management

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| ORDER-01 | Lịch sử đơn hàng người dùng | User có đơn | 1. Đăng nhập user 2. Vào `/orders` | Danh sách đơn hiển thị đúng, có phân trang |
| ORDER-02 | Xem chi tiết đơn | User có đơn | 1. `/orders/{id}` | Thông tin nhận hàng, item, tổng tiền, trạng thái đầy đủ |
| ORDER-03 | Hủy đơn ở trạng thái pending | Đơn `pending` | 1. `/orders/{id}/cancel` | Đơn chuyển `cancelled`, tồn kho được hoàn lại |
| ORDER-04 | Xuất hóa đơn PDF | Đơn `completed` | 1. `/orders/{id}/invoice` | Tải file PDF, nội dung đúng định dạng |
| ORDER-05 | Thống kê admin | Admin đăng nhập | 1. `/admin/dashboard` | Biểu đồ, số liệu thống kê hiển thị (doanh thu, đơn mới, sản phẩm sắp hết hàng) |

## 6. Admin – Quản lý Sản phẩm & Danh mục

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| ADM-PROD-01 | Tạo sản phẩm mới | Admin đăng nhập, danh mục có sẵn | 1. `/admin/products/create` 2. Nhập thông tin hợp lệ 3. Upload ảnh 4. Lưu | Sản phẩm tạo thành công, ảnh lưu vào `storage/app/public/products` |
| ADM-PROD-02 | Validate sản phẩm | Admin đăng nhập | 1. Submit form tạo sản phẩm thiếu tên/giá | Thông báo lỗi tương ứng, không tạo bản ghi |
| ADM-PROD-03 | Cập nhật sản phẩm và ảnh | Sản phẩm tồn tại | 1. `/admin/products/{id}/edit` 2. Đổi giá, upload ảnh mới 3. Lưu | Sản phẩm cập nhật, ảnh cũ thay thế, frontend hiển thị ảnh mới |
| ADM-PROD-04 | Bulk action (kích hoạt/vô hiệu) | Có nhiều sản phẩm | 1. Chọn checkbox nhiều sản phẩm 2. Chọn hành động "Disable" | Trạng thái sản phẩm cập nhật đồng loạt, log ghi nhận |
| ADM-CAT-01 | Quản lý danh mục | Danh mục tồn tại | 1. `/admin/categories` 2. Tạo/sửa/xóa | Danh mục thay đổi, slug cập nhật, frontend phản ánh đúng |

## 7. Reviews & Ratings

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| REV-01 | Gửi đánh giá sau khi mua | Người dùng đã mua sản phẩm | 1. `/products/{slug}` 2. Mở form review 3. Nhập nội dung 4. Submit | Review được lưu (trạng thái pending nếu cần duyệt), điểm trung bình cập nhật |
| REV-02 | Chặn review khi chưa mua | User chưa mua | 1. Thử submit review | Hệ thống từ chối, thông báo yêu cầu mua |
| REV-03 | Duyệt review trong admin | Admin đăng nhập, review pending | 1. `/admin/reviews` 2. Approve | Review hiển thị trên frontend, counters cập nhật |
| REV-04 | Tính trung bình rating | Có nhiều review khác điểm | 1. Approve nhiều review 2. Xem sản phẩm | `average_rating` và `reviews_count` cập nhật chính xác |

## 8. Báo cáo & Xuất dữ liệu

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| REPORT-01 | Xem báo cáo tổng quan | Admin có quyền | 1. `/admin/reports` 2. Chọn khoảng thời gian | Bảng doanh thu, số đơn, AOV hiển thị đúng |
| REPORT-02 | Xuất Excel báo cáo | Có dữ liệu | 1. `/admin/reports/export?format=xlsx` | Tải file XLSX, nội dung phù hợp chọn lọc |
| REPORT-03 | Xuất PDF báo cáo | Có dữ liệu | 1. `/admin/reports/export?format=pdf` | File PDF hiển thị biểu đồ/bảng đúng định dạng |

## 9. Media & Storage

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| MEDIA-01 | Upload ảnh sản phẩm | Admin đăng nhập | 1. Tạo/cập nhật sản phẩm với ảnh 2. Lưu | File xuất hiện trong `storage/app/public/products`, định dạng đúng |
| MEDIA-02 | Ảnh hiển thị ngoài storefront | Sản phẩm có ảnh mới | 1. Truy cập trang sản phẩm | Ảnh load từ `public/storage/products`, không lỗi quyền |
| MEDIA-03 | Đồng bộ CDN (nếu bật) | Cấu hình ASSET_URL | 1. Mở trang 2. Kiểm tra URL ảnh | Ảnh sử dụng CDN domain đúng |

## 10. Hệ thống & Bảo trì

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| SYS-01 | Queue worker hoạt động | Supervisor chạy | 1. Gửi tác vụ (ví dụ export) 2. Kiểm tra trạng thái queue | Job chuyển từ `pending` → `completed`, không job failed |
| SYS-02 | Scheduler chạy đúng giờ | Cron hoạt động | 1. Xóa file log 2. Chờ 1-2 phút 3. Kiểm tra `/var/log/cron-badminton.log` | Log có mục chạy `schedule:run`, các command được kích hoạt |
| SYS-03 | Log lưu đúng | Hệ thống hoạt động | 1. Thực hiện hành động gây log 2. Kiểm tra `storage/logs/laravel.log` | Log ghi thông tin, không lỗi phân quyền |
| SYS-04 | Backup cơ sở dữ liệu | Script backup thiết lập | 1. Chạy script backup thủ công | File backup `.sql` tạo trong thư mục backup, dung lượng hợp lý |
| SYS-05 | Khôi phục từ backup | Backup tồn tại | 1. Khôi phục DB từ file backup 2. Kiểm tra trang | Dữ liệu khôi phục thành công, không lỗi migrate |

## 11. Khả năng mở rộng & Hiệu năng (khuyến nghị kiểm thử tải)

| ID | Mục tiêu | Tiền điều kiện | Bước thực hiện | Kết quả kỳ vọng |
|----|----------|----------------|----------------|-----------------|
| PERF-01 | Cache trang danh mục | Redis hoạt động | 1. Truy cập danh mục nhiều lần 2. Kiểm tra log/cache | Request sau nhanh hơn, cache hit ghi nhận |
| PERF-02 | Stress checkout | Dùng tool (JMeter/K6) mô phỏng 20 request/giây | 1. Chạy script stress 2. Theo dõi CPU/memory | Hệ thống không timeout, lỗi < 1% |
| PERF-03 | Kiểm tra lazy load ảnh | Trang sản phẩm có nhiều ảnh | 1. Mở trang, xem DevTools | Request ảnh chỉ tải khi cuộn xuống (nếu đã bật lazy load) |

---

### Ghi chú triển khai test
- Ưu tiên tự động hóa nhóm test AUTH, CART, PAYMENT, ORDER bằng PHPUnit/Pest hoặc Cypress.
- Với luồng thanh toán sandbox, cần cập nhật thông tin `returnUrl` và `ipnUrl` chính xác trước khi test.
- Nên duy trì môi trường staging riêng với dữ liệu gần giống production để chạy các test regression định kỳ.
