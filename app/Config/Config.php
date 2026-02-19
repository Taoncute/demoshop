<?php
/**
 * Application Configuration
 */

// Base URL - Auto detect or use environment variable
if (getenv('RAILWAY_PUBLIC_DOMAIN')) {
    define('BASE_URL', 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN') . '/');
} elseif (getenv('BASE_URL')) {
    define('BASE_URL', getenv('BASE_URL'));
} else {
    define('BASE_URL', '/');
}

// Site Information
define('SITE_NAME', getenv('SITE_NAME') ?: 'Kho Code Của Vanh');
define('SITE_DESCRIPTION', getenv('SITE_DESCRIPTION') ?: 'Shop bán code và VPS chất lượng cao');

// Telegram Bot Configuration
define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN') ?: 'YOUR_TELEGRAM_BOT_TOKEN');
define('TELEGRAM_ADMIN_CHAT_ID', getenv('TELEGRAM_ADMIN_CHAT_ID') ?: 'YOUR_ADMIN_CHAT_ID');

// VietQR Configuration
define('VIETQR_ACCOUNT_NO', getenv('VIETQR_ACCOUNT_NO') ?: 'YOUR_ACCOUNT_NUMBER');
define('VIETQR_ACCOUNT_NAME', getenv('VIETQR_ACCOUNT_NAME') ?: 'YOUR_ACCOUNT_NAME');
define('VIETQR_BANK_ID', getenv('VIETQR_BANK_ID') ?: 'VCB');
define('VIETQR_TEMPLATE', getenv('VIETQR_TEMPLATE') ?: 'compact');

// Payment Check Configuration (Optional)
define('PAYMENT_API_KEY', getenv('PAYMENT_API_KEY') ?: '');
define('PAYMENT_API_URL', getenv('PAYMENT_API_URL') ?: '');

// Session Configuration
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 days

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../../public/assets/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Pagination
define('ITEMS_PER_PAGE', 12);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_TIME', 3600); // 1 hour
