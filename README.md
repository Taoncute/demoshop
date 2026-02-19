# Shop Online PHP - Hệ thống bán hàng trực tuyến

## Tính năng chính

### 1. Hệ thống người dùng
- ✅ Đăng ký / Đăng nhập
- ✅ Phân quyền User / Admin
- ✅ Quản lý hồ sơ cá nhân
- ✅ Số dư tài khoản

### 2. Quản lý sản phẩm
- ✅ Hiển thị danh sách sản phẩm
- ✅ Tìm kiếm và lọc theo danh mục
- ✅ Admin: Thêm/Sửa/Xóa sản phẩm
- ✅ Quản lý danh mục

### 3. Quản lý VPS đặc biệt
- ✅ Admin: Thêm VPS (IP, Username, Password)
- ✅ Thêm hàng loạt VPS
- ✅ Tự động giao VPS khi mua hàng
- ✅ Hiển thị thông tin VPS trong đơn hàng

### 4. Giỏ hàng & Thanh toán
- ✅ Thêm/Xóa/Cập nhật giỏ hàng
- ✅ Thanh toán bằng số dư tài khoản
- ✅ Thanh toán qua VietQR (chuyển khoản ngân hàng)
- ✅ Tạo mã QR tự động

### 5. Nạp tiền
- ✅ Nạp tiền qua VietQR
- ✅ Tạo mã QR nạp tiền tự động
- ✅ Lịch sử giao dịch nạp tiền

### 6. Telegram Bot
- ✅ Thông báo đơn hàng mới cho Admin
- ✅ Thông báo nạp tiền cho Admin
- ✅ Gửi thông tin VPS cho người mua (nếu liên kết Telegram)

### 7. Lịch sử giao dịch
- ✅ Lịch sử đơn hàng
- ✅ Chi tiết đơn hàng với thông tin VPS
- ✅ Lịch sử nạp tiền

## Cài đặt

### 1. Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL/MariaDB
- Apache/Nginx với mod_rewrite
- Extension: PDO, PDO_MySQL

### 2. Cài đặt cơ sở dữ liệu

```bash
# Import file database.sql vào MySQL
mysql -u root -p < database.sql

# Hoặc sử dụng phpMyAdmin để import
```

### 3. Cấu hình

Chỉnh sửa file `app/Config/Config.php`:

```php
// Thông tin Telegram Bot
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN');
define('TELEGRAM_ADMIN_CHAT_ID', 'YOUR_CHAT_ID');

// Thông tin VietQR
define('VIETQR_ACCOUNT_NO', 'SỐ_TÀI_KHOẢN');
define('VIETQR_ACCOUNT_NAME', 'TÊN_TÀI_KHOẢN');
define('VIETQR_BANK_ID', 'MÃ_NGÂN_HÀNG'); // VCB, TCB, MB, etc.
```

Chỉnh sửa file `app/Config/Database.php`:

```php
private $host = 'localhost';
private $db_name = 'shop_online';
private $username = 'root';
private $password = 'YOUR_PASSWORD';
```

### 4. Cấu hình Web Server

#### Apache (.htaccess đã có sẵn)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /shop_online/public/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [L]
</IfModule>
```

#### Nginx
```nginx
location /shop_online {
    try_files $uri $uri/ /shop_online/public/index.php?$query_string;
}
```

### 5. Phân quyền thư mục

```bash
chmod -R 755 shop_online/
chmod -R 777 shop_online/public/assets/uploads/
```

## Tài khoản mặc định

**Admin:**
- Username: `admin`
- Password: `admin123`
- Email: `admin@shop.com`

**Lưu ý:** Vui lòng đổi mật khẩu admin ngay sau khi cài đặt!

## Cấu trúc thư mục

```
shop_online/
├── app/
│   ├── Config/          # Cấu hình
│   │   ├── Config.php
│   │   └── Database.php
│   ├── Core/            # Lớp lõi
│   │   ├── Auth.php
│   │   ├── Session.php
│   │   ├── Validator.php
│   │   ├── VietQR.php
│   │   └── TelegramBot.php
│   ├── Models/          # Models
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── VPS.php
│   │   ├── Cart.php
│   │   ├── Order.php
│   │   └── Topup.php
│   └── Views/           # (Chưa sử dụng - tích hợp trực tiếp)
├── public/              # Thư mục công khai
│   ├── admin/           # Admin panel
│   ├── assets/          # Hình ảnh, file tĩnh
│   ├── css/             # CSS
│   ├── js/              # JavaScript
│   ├── index.php        # Trang chủ
│   ├── login.php
│   ├── register.php
│   ├── cart.php
│   ├── checkout.php
│   ├── orders.php
│   ├── order_detail.php
│   └── topup.php
└── database.sql         # File SQL
```

## Hướng dẫn sử dụng

### Quản lý sản phẩm VPS

1. Đăng nhập Admin
2. Vào "Quản lý sản phẩm" → Tạo sản phẩm VPS (chọn loại "VPS")
3. Vào "Quản lý VPS" → Chọn sản phẩm VPS vừa tạo
4. Thêm VPS theo 2 cách:
   - **Thêm từng VPS:** Nhập thông tin từng VPS
   - **Thêm hàng loạt:** Mỗi dòng theo format: `IP|Username|Password|OS|Specs`

Ví dụ thêm hàng loạt:
```
123.45.67.89|root|Pass@123|Ubuntu 22.04|2CPU 4GB RAM
123.45.67.90|admin|Pass@456|CentOS 8|4CPU 8GB RAM
```

### Thanh toán VietQR

1. Người dùng thêm sản phẩm vào giỏ hàng
2. Chọn "Thanh toán" → Chọn phương thức "VietQR"
3. Hệ thống tạo mã QR với nội dung chuyển khoản duy nhất
4. Người dùng quét mã QR và chuyển khoản
5. **Lưu ý:** Hiện tại cần xác nhận thủ công hoặc tích hợp API ngân hàng

### Tích hợp API ngân hàng (Tùy chọn)

Để tự động xác nhận thanh toán, bạn có thể tích hợp:
- **PayOS:** https://payos.vn
- **Casso:** https://casso.vn
- **VietQR API:** https://vietqr.io

Chỉnh sửa file `app/Core/VietQR.php` → hàm `verifyPayment()`

### Cấu hình Telegram Bot

1. Tạo bot qua @BotFather trên Telegram
2. Lấy Bot Token
3. Lấy Chat ID của Admin (có thể dùng @userinfobot)
4. Cập nhật vào `app/Config/Config.php`

## Bảo mật

### Khuyến nghị:
- ✅ Đổi mật khẩu admin mặc định
- ✅ Sử dụng HTTPS trong production
- ✅ Cấu hình CSRF token (đã tích hợp)
- ✅ Mã hóa mật khẩu (sử dụng password_hash)
- ✅ Prepared statements (chống SQL Injection)
- ✅ XSS protection (htmlspecialchars)

### Cần làm thêm:
- Giới hạn số lần đăng nhập sai
- Rate limiting cho API
- Backup database định kỳ
- Log hệ thống

## Hỗ trợ

Nếu gặp vấn đề, vui lòng kiểm tra:
1. PHP version >= 7.4
2. Extension PDO đã bật
3. Database đã import đúng
4. Cấu hình database chính xác
5. Phân quyền thư mục uploads

## License

MIT License - Tự do sử dụng cho mục đích cá nhân và thương mại.

## Credits

- CSS Design: Dựa trên template vanhkazen-web_shop
- VietQR: https://vietqr.io
- Font Awesome: https://fontawesome.com
- Google Fonts: Poppins
