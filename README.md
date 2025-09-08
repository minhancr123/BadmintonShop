# Badminton Shop - Website Bán Cầu Lông

## Mô tả dự án
Website thương mại điện tử chuyên bán các sản phẩm cầu lông bao gồm vợt, giày, quả cầu, phụ kiện và quần áo thể thao. Được xây dựng bằng Laravel Framework với giao diện Bootstrap, hệ thống quản lý đơn hàng và phân quyền người dùng.

## Yêu cầu hệ thống
- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js & NPM (cho assets compilation)

## Tính năng chính

### 1. Người dùng (User)
- Đăng ký/Đăng nhập
- Xem danh sách sản phẩm
- Tìm kiếm và lọc sản phẩm
- Xem chi tiết sản phẩm
- Thêm sản phẩm vào giỏ hàng
- Đặt hàng
- Xem lịch sử đơn hàng
- Hủy đơn hàng (nếu đơn hàng đang pending)

### 2. Quản trị viên (Admin)
- Tất cả quyền của User
- Quản lý sản phẩm (CRUD)
- Quản lý danh mục (CRUD)
- Quản lý đơn hàng
- Quản lý người dùng
- Xem thống kê

## Cấu trúc cơ sở dữ liệu (ERD)

```
┌─────────────┐      ┌─────────────┐      ┌──────────────┐
│   users     │      │  categories │      │   products   │
├─────────────┤      ├─────────────┤      ├──────────────┤
│ id          │      │ id          │      │ id           │
│ name        │      │ name        │◄─────┤ category_id  │
│ email       │      │ slug        │      │ name         │
│ password    │      │ description │      │ slug         │
│ role        │      │ image       │      │ description  │
│ phone       │      │ is_active   │      │ price        │
│ address     │      └─────────────┘      │ sale_price   │
└─────────────┘                           │ quantity     │
       │                                  │ sku          │
       │                                  │ brand        │
       │                                  │ weight       │
       ▼                                  │ flex         │
┌─────────────┐                           │ is_featured  │
│   orders    │                           │ is_active    │
├─────────────┤                           └──────────────┘
│ id          │                                   │
│ user_id     │                                   │
│ order_number│                                   ▼
│ total_amount│                           ┌──────────────┐
│ status      │◄──────────────────────────┤ order_items  │
│ payment_    │                           ├──────────────┤
│  status     │                           │ id           │
│ shipping_*  │                           │ order_id     │
└─────────────┘                           │ product_id   │
                                          │ quantity     │
                                          │ price        │
                                          │ total        │
                                          └──────────────┘
```

## Use Cases

### UC1: Đăng ký tài khoản
- **Actor**: Khách
- **Mô tả**: Người dùng tạo tài khoản mới
- **Luồng chính**:
  1. Truy cập trang đăng ký
  2. Nhập thông tin (tên, email, mật khẩu)
  3. Xác nhận đăng ký
  4. Hệ thống tạo tài khoản với role "user"

### UC2: Mua hàng
- **Actor**: User
- **Mô tả**: Người dùng đặt mua sản phẩm
- **Luồng chính**:
  1. Đăng nhập
  2. Xem sản phẩm
  3. Thêm vào giỏ hàng
  4. Thanh toán
  5. Nhập thông tin giao hàng
  6. Xác nhận đơn hàng

### UC3: Quản lý sản phẩm
- **Actor**: Admin
- **Mô tả**: Admin thêm/sửa/xóa sản phẩm
- **Luồng chính**:
  1. Đăng nhập với quyền admin
  2. Vào trang quản lý sản phẩm
  3. Thực hiện thao tác CRUD
  4. Lưu thay đổi

## Hướng dẫn cài đặt

### Bước 1: Clone project
```bash
git clone https://github.com/yourusername/badminton-shop.git
cd badminton-shop
```

### Bước 2: Cài đặt PHP và Composer
1. Tải và cài đặt XAMPP (bao gồm PHP và MySQL): https://www.apachefriends.org/
2. Tải và cài đặt Composer: https://getcomposer.org/download/

### Bước 3: Cài đặt dependencies
```bash
composer install
```

### Bước 4: Cấu hình môi trường
```bash
# Copy file .env (đã có sẵn)
# Hoặc tạo từ .env.example nếu cần
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Bước 5: Tạo database
1. Mở phpMyAdmin (http://localhost/phpmyadmin)
2. Tạo database mới tên: `badminton_shop`

### Bước 6: Chạy migrations và seeders
```bash
# Tạo các bảng
php artisan migrate

# Chèn dữ liệu mẫu
php artisan db:seed
```

### Bước 7: Tạo symbolic link cho storage
```bash
php artisan storage:link
```

### Bước 8: Chạy server
```bash
php artisan serve
```

Truy cập: http://localhost:8000

## Tài khoản mặc định

### Admin
- Email: admin@badmintonshop.com
- Password: password123

### User
- Email: user@badmintonshop.com  
- Password: password123

## Cấu trúc thư mục chính
```
badminton-shop/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Controllers xử lý logic
│   │   └── Middleware/        # Middleware (AdminMiddleware)
│   └── Models/                # Eloquent Models
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── resources/
│   └── views/                 # Blade templates
│       ├── layouts/           # Layout templates
│       ├── products/          # Product views
│       ├── categories/        # Category views
│       └── admin/             # Admin panel views
├── routes/
│   └── web.php                # Web routes
├── public/                    # Public assets
└── storage/                   # File storage
```

## Phân công công việc (Cho nhóm)

### Thành viên 1: Backend Developer
- Thiết kế database
- Tạo migrations và models
- Xây dựng API/Controllers
- Implement authentication

### Thành viên 2: Frontend Developer  
- Thiết kế giao diện với Bootstrap
- Tạo các Blade templates
- Responsive design
- JavaScript interactions

### Thành viên 3: Full-stack Developer
- Tích hợp frontend và backend
- Testing và debugging
- Deployment
- Documentation

## Công nghệ sử dụng
- **Backend**: Laravel 10.x
- **Database**: MySQL với Eloquent ORM
- **Frontend**: Blade Template Engine + Bootstrap 5
- **Authentication**: Laravel built-in auth
- **Authorization**: Middleware cho phân quyền
- **Search & Pagination**: Laravel built-in features
- **Validation**: Laravel Form Request Validation

## Lưu ý khi phát triển

1. **Cài đặt thêm Laravel UI để có auth scaffolding**:
```bash
composer require laravel/ui
php artisan ui bootstrap --auth
npm install && npm run build
```

2. **Nếu gặp lỗi về Composer hoặc PHP**:
- Đảm bảo PHP đã được thêm vào PATH
- Chạy composer update nếu cần

3. **Để hoàn thiện project cần thêm**:
- Các controller còn lại (CategoryController, OrderController, CartController, AdminController)
- Các views cho từng chức năng
- Auth controllers (LoginController, RegisterController)
- Xử lý upload ảnh
- Payment integration (optional)

## Testing
```bash
# Tạo database testing
php artisan migrate --database=testing

# Chạy tests
php artisan test
```

## Deployment
Khi deploy lên production:
1. Đổi APP_ENV=production trong .env
2. Đổi APP_DEBUG=false
3. Cấu hình database production
4. Chạy: `php artisan config:cache`
5. Chạy: `php artisan route:cache`

## License
MIT License

## Liên hệ
- Email: admin@badmintonshop.com
- Phone: 1900 1234

---
Được phát triển cho môn học Lập trình Web - Laravel Framework
