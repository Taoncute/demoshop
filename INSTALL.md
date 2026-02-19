# Hướng dẫn cài đặt nhanh

## Bước 1: Giải nén và upload

1. Giải nén file `shop_online_complete.zip`
2. Upload toàn bộ thư mục `shop_online` lên server
3. Đặt trong thư mục web root (ví dụ: `/var/www/html/shop_online`)

## Bước 2: Tạo database

```sql
-- Tạo database
CREATE DATABASE shop_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import file database.sql
mysql -u root -p shop_online < database.sql
```

Hoặc sử dụng phpMyAdmin:
1. Tạo database tên `shop_online`
2. Import file `database.sql`

## Bước 3: Cấu hình database

Chỉnh sửa file `app/Config/Database.php`:

```php
private $host = 'localhost';
private $db_name = 'shop_online';
private $username = 'root';
private $password = 'YOUR_PASSWORD';  // Đổi password của bạn
```

## Bước 4: Cấu hình VietQR và Telegram

Chỉnh sửa file `app/Config/Config.php`:

```php
// Base URL - Đổi theo domain của bạn
define('BASE_URL', 'http://yourdomain.com/shop_online/public/');

// Telegram Bot (Tùy chọn)
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN');
define('TELEGRAM_ADMIN_CHAT_ID', 'YOUR_CHAT_ID');

// VietQR - Thông tin tài khoản ngân hàng
define('VIETQR_ACCOUNT_NO', '0123456789');
define('VIETQR_ACCOUNT_NAME', 'NGUYEN VAN A');
define('VIETQR_BANK_ID', 'VCB'); // VCB, TCB, MB, ACB, etc.
```

### Danh sách mã ngân hàng phổ biến:
- VCB: Vietcombank
- TCB: Techcombank
- MB: MB Bank
- ACB: ACB
- VPB: VPBank
- TPB: TPBank
- STB: Sacombank
- CTG: VietinBank
- BIDV: BIDV

## Bước 5: Phân quyền thư mục

```bash
chmod -R 755 shop_online/
chmod -R 777 shop_online/public/assets/
```

## Bước 6: Truy cập website

Mở trình duyệt và truy cập:
```
http://yourdomain.com/shop_online/public/
```

## Đăng nhập Admin

```
URL: http://yourdomain.com/shop_online/public/login.php
Username: admin
Password: admin123
```

**⚠️ QUAN TRỌNG:** Đổi mật khẩu admin ngay sau khi đăng nhập lần đầu!

## Cấu hình Telegram Bot (Tùy chọn)

### Bước 1: Tạo Bot
1. Mở Telegram, tìm @BotFather
2. Gửi lệnh `/newbot`
3. Đặt tên bot và username
4. Lưu lại **Bot Token**

### Bước 2: Lấy Chat ID
1. Tìm @userinfobot trên Telegram
2. Gửi bất kỳ tin nhắn nào
3. Bot sẽ trả về Chat ID của bạn
4. Lưu lại **Chat ID**

### Bước 3: Cập nhật Config
Điền Bot Token và Chat ID vào file `app/Config/Config.php`

## Kiểm tra cài đặt

### 1. Kiểm tra PHP
```bash
php -v  # Phải >= 7.4
php -m | grep pdo  # Phải có PDO
```

### 2. Kiểm tra database
```bash
mysql -u root -p
USE shop_online;
SHOW TABLES;  # Phải có 10 bảng
```

### 3. Kiểm tra kết nối
Truy cập: `http://yourdomain.com/shop_online/public/`
- Nếu thấy trang chủ → Thành công!
- Nếu lỗi database → Kiểm tra lại cấu hình Database.php
- Nếu lỗi 404 → Kiểm tra BASE_URL trong Config.php

## Sử dụng cơ bản

### Thêm sản phẩm
1. Đăng nhập Admin
2. Vào "Quản lý sản phẩm" → "Thêm sản phẩm mới"
3. Điền thông tin và lưu

### Thêm VPS
1. Tạo sản phẩm VPS trước (loại: VPS)
2. Vào "Quản lý VPS" → Chọn sản phẩm
3. Thêm VPS theo format: `IP|Username|Password|OS|Specs`

Ví dụ:
```
103.56.158.199|root|Pass@123|Ubuntu 22.04|2CPU 4GB RAM
103.56.158.200|admin|Pass@456|CentOS 8|4CPU 8GB RAM
```

### Test thanh toán
1. Đăng ký tài khoản user
2. Thêm sản phẩm vào giỏ
3. Nạp tiền (hoặc admin có thể cộng tiền trực tiếp vào database)
4. Thanh toán bằng số dư

## Xử lý lỗi thường gặp

### Lỗi: "Connection Error"
→ Kiểm tra thông tin database trong `app/Config/Database.php`

### Lỗi: "404 Not Found"
→ Kiểm tra BASE_URL trong `app/Config/Config.php`

### Lỗi: "Permission denied"
→ Chạy lệnh phân quyền thư mục

### Không hiển thị hình ảnh
→ Kiểm tra thư mục `public/assets/` có ảnh không

## Nâng cao

### Tích hợp API ngân hàng tự động
Để tự động xác nhận thanh toán, tích hợp:
- PayOS: https://payos.vn
- Casso: https://casso.vn

Chỉnh sửa file `app/Core/VietQR.php` → hàm `verifyPayment()`

### Sử dụng HTTPS
1. Cài SSL certificate (Let's Encrypt miễn phí)
2. Bật dòng redirect HTTPS trong `.htaccess`

### Backup tự động
```bash
# Tạo cron job backup database hàng ngày
0 2 * * * mysqldump -u root -p'password' shop_online > /backup/shop_$(date +\%Y\%m\%d).sql
```

## Hỗ trợ

Nếu gặp vấn đề, kiểm tra:
1. PHP error log: `/var/log/apache2/error.log`
2. MySQL error log: `/var/log/mysql/error.log`
3. Bật display_errors trong php.ini để debug

## Cập nhật

Để cập nhật phiên bản mới:
1. Backup database
2. Backup thư mục hiện tại
3. Upload file mới
4. Chạy file migration SQL (nếu có)
