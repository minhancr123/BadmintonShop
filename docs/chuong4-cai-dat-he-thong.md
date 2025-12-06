# CHƯƠNG 4: CÀI ĐẶT HỆ THỐNG (Badminton Shop)

## 4.1. Tổng quan (Overview)

Chương này mô tả toàn bộ quy trình triển khai hệ thống Badminton Shop theo từng bước thực tế: chuẩn bị hạ tầng, thiết lập backend Laravel, build frontend bằng Vite, và cấu hình các dịch vụ nâng cao như queue, cache, scheduler, logging và backup. Mỗi tiểu mục cung cấp hướng dẫn chi tiết kèm lệnh triển khai, giúp đội ngũ kỹ thuật có thể áp dụng cho cả môi trường phát triển và sản xuất.

### 4.1.1. Thành phần triển khai
| Thành phần | Mô tả | Vị trí trong repo |
|------------|-------|-------------------|
| Web Application | Ứng dụng Laravel phục vụ API + Blade view | Thư mục gốc (`app`, `routes`, `resources`)
| Asset Pipeline | Build CSS/JS thông qua Vite | `resources/css`, `resources/js`, `vite.config.js`
| Database | MySQL 8.x lưu trữ dữ liệu nghiệp vụ | Migration trong `database/migrations`
| Cache & Queue | Redis, phục vụ cache & hàng đợi | Cấu hình `.env`, `config/cache.php`, `config/queue.php`
| Storage | Ảnh sản phẩm và file upload | `storage/app/public` (public thông qua `public/storage`)
| Công cụ nền | Supervisor, cron, Horizon/Scheduler | Mô tả tại mục 4.6

### 4.1.2. Luồng triển khai tổng thể
1. Chuẩn bị môi trường: cài PHP, Node, MySQL/Redis, tạo tài khoản dịch vụ.
2. Clone mã nguồn, cài Composer dependencies, thiết lập `.env`.
3. Chạy migration + seed, tạo symbolic link cho storage.
4. Cấu hình web server (dev: `artisan serve`, prod: NGINX + PHP-FPM).
5. Cài đặt frontend, build asset với Vite, tích hợp vào Blade.
6. Bật các dịch vụ nâng cao: queue worker, scheduler, cache, logging.
7. Kiểm tra sau triển khai: health check, upload ảnh, đặt đơn hàng thử nghiệm.

## 4.2. MÔI TRƯỜNG CÀI ĐẶT (Setup Environment) ..................... 26

### 4.2.1. Bảng so sánh yêu cầu Local vs Production
| Hạng mục | Local Development | Production |
|----------|-------------------|------------|
| Hệ điều hành | Windows 11 / macOS 14 / Ubuntu 22.04 | Ubuntu 22.04 LTS (khuyến nghị) |
| PHP | 8.2 (XAMPP hoặc PHP-FPM) | 8.2 FPM cài qua `apt` hoặc `ppa:ondrej/php` |
| PHP extensions | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip` | Giống môi trường local |
| Composer | v2.7 trở lên | v2.7 trở lên (đặt tại `/usr/bin/composer`) |
| Node.js | 20.x LTS + npm/pnpm | 20.x LTS (dùng build asset trên CI/CD) |
| Database | MySQL 8 (local instance) | MySQL 8 managed (RDS) hoặc self-hosted |
| Cache/Queue | Redis (tùy chọn) | Redis managed (Elasticache) hoặc VM riêng |
| Web Server | `php artisan serve` hoặc Apache | NGINX reverse proxy + PHP-FPM |
| SSL | Không bắt buộc | Bắt buộc (Let’s Encrypt / ACM) |

### 4.2.2. Kiểm tra phiên bản & phụ thuộc
Chạy các lệnh sau để xác nhận môi trường đủ điều kiện:

```powershell
php -v
composer -V
node -v
npm -v
mysql --version
redis-cli --version   # nếu sử dụng Redis
```

### 4.2.3. Chuẩn bị tài khoản và quyền truy cập
- **Hệ điều hành (prod):** tạo user `deploy`, cấp quyền sudo hạn chế; bật SSH qua key.
- **Database:**
  ```sql
  CREATE DATABASE badminton_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'badminton_app'@'localhost' IDENTIFIED BY 'StrongPassword!';
  GRANT ALL ON badminton_shop.* TO 'badminton_app'@'localhost';
  FLUSH PRIVILEGES;
  ```
- **Redis:** bật `requirepass` trong `redis.conf`, ghi mật khẩu vào `.env` (`REDIS_PASSWORD`).
- **Dịch vụ thứ ba:** chuẩn bị API key cho Mailgun/SMTP, VNPay/MoMo, AWS S3. Không commit giá trị bí mật.

### 4.2.4. Phân quyền thư mục & cấu trúc dự án
```bash
sudo chown -R www-data:www-data /var/www/BadmintonShop
sudo chmod -R 775 /var/www/BadmintonShop/storage /var/www/BadmintonShop/bootstrap/cache
```
Cấu trúc chuẩn:
```
/var/www/BadmintonShop
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
└── storage/
```
Đảm bảo user chạy PHP-FPM (`www-data`) có quyền ghi vào `storage/` & `bootstrap/cache/` để tránh lỗi khi deploy.

## 4.3. CÀI ĐẶT BACKEND (Laravel Application) ..................... 27

Mục tiêu của mục này là dựng ứng dụng Laravel hoàn chỉnh: tải mã nguồn, cấu hình `.env`, khởi tạo database, publish storage, tối ưu hoá cấu hình và triển khai web server.

### 4.3.1. Clone repository & cài đặt Composer dependencies
```bash
cd /var/www
sudo git clone git@github.com:minhancr123/BadmintonShop.git
cd BadmintonShop
composer install --no-interaction --prefer-dist --optimize-autoloader
```
- Tùy chọn dev: `composer install` (không cần `--optimize-autoloader`).
- Đảm bảo `composer.lock` được giữ nguyên để đồng bộ phiên bản packages.

### 4.3.2. Thiết lập `.env` và APP_KEY
```bash
cp .env.example .env
php artisan key:generate
```
Cập nhật các biến chính (ví dụ production):
```env
APP_NAME="Badminton Shop"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://badmintonshop.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=badminton_shop
DB_USERNAME=badminton_app
DB_PASSWORD=StrongPassword!

FILESYSTEM_DISK=public
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@example.com
MAIL_PASSWORD=mailgun-secret
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=support@badmintonshop.com
MAIL_FROM_NAME="Badminton Shop"

VNPAY_TMN_CODE=...
VNPAY_HASH_SECRET=...
MOMO_PARTNER_CODE=...
MOMO_ACCESS_KEY=...
```

### 4.3.3. Migration & dữ liệu khởi tạo
```bash
php artisan migrate --force
php artisan db:seed --force
```
- Migration tạo toàn bộ bảng (`users`, `products`, `orders`, `reviews`, ...).
- Seeder sinh dữ liệu demo và tài khoản admin (`admin@badmintonshop.com` / `password123`). Đổi mật khẩu ngay sau khi deploy.

### 4.3.4. Thiết lập kho lưu trữ tệp (storage)
```bash
php artisan storage:link
```
Kiểm tra liên kết (Windows):
```powershell
Get-ChildItem .\public\storage\products
```
Nếu liên kết cũ sai, xóa trước rồi tạo lại (đã áp dụng khi khắc phục lỗi ảnh):
```powershell
Remove-Item -Path ".\public\storage" -Recurse -Force
php artisan storage:link
```

### 4.3.5. Tối ưu hoá cấu hình & clear cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```
Sau khi cập nhật code, xoá cache cũ để tránh lỗi:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 4.3.6. Chạy server phát triển & cấu hình NGINX production
- **Dev nhanh:**
  ```bash
  php artisan serve --host=0.0.0.0 --port=8000
  ```
- **Production (NGINX + PHP-FPM):**
  ```nginx
  server {
    listen 80;
    server_name badmintonshop.com www.badmintonshop.com;
    root /var/www/BadmintonShop/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    location / {
      try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
      include fastcgi_params;
      fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
      fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\. {
      deny all;
    }
  }
  ```
  Kiểm tra & reload:
  ```bash
  sudo nginx -t
  sudo systemctl reload nginx
  sudo systemctl restart php8.2-fpm
  ```

### 4.3.7. Hàng đợi (queue) với Supervisor
Tạo `/etc/supervisor/conf.d/badminton-queue.conf`:
```ini
[program:badminton-queue]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/BadmintonShop/artisan queue:work redis --sleep=3 --tries=3 --timeout=120
numprocs=2
autostart=true
autorestart=true
user=www-data
directory=/var/www/BadmintonShop
stdout_logfile=/var/log/supervisor/badminton-queue.log
stderr_logfile=/var/log/supervisor/badminton-queue-error.log
stopwaitsecs=3600
```
Áp dụng cấu hình:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status badminton-queue:*
```

### 4.3.8. Scheduler (cron) cho tác vụ định kỳ
```bash
echo "* * * * * www-data cd /var/www/BadmintonShop && php artisan schedule:run >> /var/log/cron-badminton.log 2>&1" | sudo tee /etc/cron.d/badminton-schedule
sudo systemctl restart cron
```
Laravel sẽ thực thi các command đăng ký trong `app/Console/Kernel.php` (ví dụ: cập nhật thống kê review, dọn cart hết hạn).

### 4.3.9. Kiểm tra hậu triển khai backend
- `php artisan migrate:status` để xác nhận trạng thái migration.
- Truy cập `/admin/login`, đăng nhập với tài khoản admin demo, đổi mật khẩu.
- Upload ảnh sản phẩm, xác minh file xuất hiện tại `storage/app/public/products` và hiển thị qua `public/storage/products`.
- Kiểm tra queue: `php artisan queue:work --once` (local) hoặc xem `supervisorctl status` + log `/var/log/supervisor/badminton-queue.log`.

## 4.4. CÀI ĐẶT FRONTEND (Vite + Blade) ............................. 31

Frontend trong dự án sử dụng Blade template kết hợp Vite để build CSS/JS, AlpineJS và Tailwind/Bootstrap.

### 4.4.1. Cài dependency Node & scripts
```bash
npm install   # local development
# hoặc
npm ci        # trên CI/CD để tái tạo exact lockfile
```
Các script chính trong `package.json`:
```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  }
}
```

### 4.4.2. Cấu hình Vite (`vite.config.js`)
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js'
      ],
      refresh: true,
    }),
  ],
  server: {
    host: '0.0.0.0',
    port: 5173,
  },
});
```
- Có thể bổ sung `https: true` nếu chạy dev qua HTTPS.
- Nếu backend chạy domain khác, cấu hình `server.proxy` để tránh CORS khi dev.

### 4.4.3. Tích hợp asset vào Blade layout
`resources/views/layouts/app.blade.php` (trích đoạn):
```php
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Badminton Shop') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="bg-gray-50">
    @include('partials.nav')
    <main class="py-4">@yield('content')</main>
    @include('partials.footer')
  </body>
</html>
```
- Các view admin có thể sử dụng layout khác (`resources/views/admin/layouts/app.blade.php`) nhưng vẫn dùng `@vite` tương tự.

### 4.4.4. Quy trình build & triển khai
- **Dev (HMR):**
  ```bash
  npm run dev
  ```
  Vite phục vụ asset tại `http://localhost:5173`, Laravel plugin tự cấu hình khi `APP_ENV=local`.
- **Production build:**
  ```bash
  npm run build
  ```
  Kết quả nằm tại `public/build` với file đã fingerprint. Đưa thư mục này cùng code backend khi deploy production.
- **Preview:**
  ```bash
  npm run preview
  ```
  Kiểm tra asset đã build hoạt động trước khi phát hành.

### 4.4.5. Quản lý asset tĩnh, CDN và cache
- Thiết lập CDN bằng cách đặt `ASSET_URL=https://cdn.badmintonshop.com` trong `.env` production.
- Đồng bộ `public/build` và `public/storage` lên S3/CloudFront hoặc Cloudflare R2 thông qua pipeline CI/CD.
- Bật gzip/Brotli trên web server để giảm dung lượng asset.
- Sử dụng lazy-loading (`loading="lazy"`) cho ảnh sản phẩm trong `resources/views/products/*.blade.php` nhằm tối ưu hiệu năng.

### 4.4.6. Kiểm tra frontend sau deploy
- Mở trang chủ và trang chi tiết sản phẩm để đảm bảo CSS/JS load thành công (không lỗi 404).
- Kiểm tra console browser để chắc chắn không có lỗi CORS/JS.
- Thực hiện quy trình đặt hàng thử nghiệm và kiểm tra các component JS (form validation, modal).
- Nếu dùng CDN, xóa cache CDN sau mỗi lần build để đảm bảo người dùng nhận asset mới nhất.

## 4.6. CẤU HÌNH DỊCH VỤ NÂNG CAO (Advanced Services) ............. 33

### 4.6.1. Redis cho cache & queue
`.env` mẫu:
```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=StrongRedisPass
REDIS_PORT=6379
```
`config/database.php` (trích đoạn) nếu cần password:
```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
    ],
],
```
- Kiểm tra kết nối: `php artisan tinker` → `Cache::put('ping', 'pong')` → `Cache::get('ping')`.

### 4.6.2. Horizon (giám sát queue)
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate --force
```
Giới hạn truy cập dashboard trong `AppServiceProvider`:
```php
Horizon::auth(function ($request) {
    return in_array($request->user()?->email, [
        'admin@badmintonshop.com',
    ]);
});
```
Chạy Horizon:
```bash
php artisan horizon
# Hoặc cấu hình Supervisor:
[program:badminton-horizon]
command=/usr/bin/php /var/www/BadmintonShop/artisan horizon
```

### 4.6.3. Scheduler & command tuỳ chỉnh
Đăng ký command trong `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('reports:daily-sales')->dailyAt('23:30');
    $schedule->command('ratings:recalculate')->hourly();
    $schedule->command('queue:retry all')->dailyAt('01:00');
}
```
Theo dõi log cron tại `/var/log/cron-badminton.log` để kịp thời xử lý lỗi.

### 4.6.4. Lưu trữ đối tượng (S3/MinIO)
`config/filesystems.php` → chỉnh `default` hoặc `disks.s3`:
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=xxxx
AWS_SECRET_ACCESS_KEY=yyyy
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=badminton-media
AWS_URL=https://cdn.badmintonshop.com
```
Đồng bộ ảnh hiện có:
```bash
aws s3 sync storage/app/public s3://badminton-media/uploads --delete
```
Khi chạy local không có S3, có thể dùng MinIO: cấu hình endpoint vào `.env` (`AWS_ENDPOINT=http://127.0.0.1:9000`).

### 4.6.5. Logging & giám sát
- Mặc định log ghi tại `storage/logs/laravel.log`. Thiết lập log centralized:
  ```bash
  composer require sentry/sentry-laravel
  php artisan sentry:publish --dsn="https://<key>@sentry.io/<project>"
  ```
- Dùng `LOG_CHANNEL=stack` kết hợp `daily` + `sentry` trong `config/logging.php`.
- Kiểm tra log bằng `tail -f storage/logs/laravel.log` và cấu hình rotate (`logrotate`).

### 4.6.6. Sao lưu & khôi phục
- **Database:**
  ```bash
  mysqldump -u badminton_app -p badminton_shop > /backups/badminton_$(date +%F).sql
  ```
- **Storage:**
  ```bash
  aws s3 sync storage/app/public s3://badminton-media/uploads --delete
  ```
- **Khôi phục:**
  ```bash
  mysql -u badminton_app -p badminton_shop < backup.sql
  aws s3 sync s3://badminton-media/uploads storage/app/public
  php artisan storage:link
  ```
Thiết lập cron backup hàng ngày và sao chép file backup ra vùng lưu trữ an toàn.

### 4.6.7. Bảo mật & tuân thủ
- Bật HTTPS, cấu hình HSTS trên NGINX hoặc load balancer.
- Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` chính xác để tránh lộ thông tin debug.
- Rate limit API (`RateLimiter::for('api', ...)`) và bật CSRF cho route web.
- Thực hiện `composer audit` và `npm audit` định kỳ hoặc trong pipeline CI.
- Đối với quản trị viên, xem xét bổ sung xác thực hai lớp (có thể sử dụng Laravel Fortify).

---

### Checklist kiểm tra nhanh sau khi cài đặt
1. Truy cập trang chủ và `/admin` để xác nhận web server hoạt động.
2. Upload ảnh sản phẩm → file xuất hiện trong `public/storage/products` và hiển thị trên storefront.
3. Đặt đơn hàng thử nghiệm, xác nhận email/trạng thái đơn hàng cập nhật thành công.
4. Kiểm tra queue và scheduler (`supervisorctl status`, `tail -f /var/log/cron-badminton.log`).
5. Rà soát log lỗi (`tail -f storage/logs/laravel.log`) và dọn cache (`php artisan cache:clear`).

Tài liệu chương 4 đã được cập nhật chi tiết, bám sát nhu cầu triển khai thực tế của dự án Badminton Shop.