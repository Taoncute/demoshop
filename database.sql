-- =============================================
-- Database Schema for Shop Online System
-- =============================================

CREATE DATABASE IF NOT EXISTS shop_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shop_online;

-- =============================================
-- Table: users
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('user', 'admin') DEFAULT 'user',
    balance DECIMAL(15, 2) DEFAULT 0.00,
    telegram_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: categories
-- =============================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: products
-- =============================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(15, 2) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    product_type ENUM('normal', 'vps') DEFAULT 'normal',
    status ENUM('available', 'out_of_stock') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_type (product_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: vps_inventory (Kho VPS)
-- =============================================
CREATE TABLE vps_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    ip_address VARCHAR(50) NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    os_info VARCHAR(100),
    specs TEXT COMMENT 'CPU, RAM, SSD specifications',
    status ENUM('available', 'sold') DEFAULT 'available',
    sold_to_user_id INT DEFAULT NULL,
    sold_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (sold_to_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: orders
-- =============================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_code VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(15, 2) NOT NULL,
    payment_method ENUM('balance', 'vietqr') DEFAULT 'vietqr',
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    order_status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_order_code (order_code),
    INDEX idx_payment_status (payment_status),
    INDEX idx_order_status (order_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: order_items
-- =============================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    vps_id INT DEFAULT NULL COMMENT 'Link to VPS if product is VPS type',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (vps_id) REFERENCES vps_inventory(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: topup_transactions (Giao dịch nạp tiền)
-- =============================================
CREATE TABLE topup_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_code VARCHAR(50) UNIQUE NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    payment_method ENUM('vietqr') DEFAULT 'vietqr',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_transaction_code (transaction_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: cart (Giỏ hàng)
-- =============================================
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: payment_logs (Logs thanh toán từ VietQR)
-- =============================================
CREATE TABLE payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(100) UNIQUE NOT NULL,
    reference_code VARCHAR(50) NOT NULL COMMENT 'Order code or topup code',
    amount DECIMAL(15, 2) NOT NULL,
    transaction_type ENUM('order', 'topup') NOT NULL,
    bank_info TEXT,
    status ENUM('success', 'failed') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reference (reference_code),
    INDEX idx_type (transaction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Insert default admin account
-- Password: admin123 (hashed with password_hash)
-- =============================================
INSERT INTO users (username, email, password, full_name, role, balance) VALUES
('admin', 'admin@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 0.00);

-- =============================================
-- Insert sample categories
-- =============================================
INSERT INTO categories (name, description) VALUES
('Code HTML/CSS', 'Các mã nguồn HTML, CSS đẹp mắt'),
('Code Python', 'Các script Python hữu ích'),
('VPS Server', 'Máy chủ ảo VPS các loại'),
('Template Web', 'Các template website đẹp');

-- =============================================
-- Insert sample products
-- =============================================
INSERT INTO products (category_id, name, description, price, image, stock, product_type, status) VALUES
(1, 'Hoa Tulips', 'Code HTML hiển thị hoa Tulips đẹp mắt', 10000, 'buy2.jpg', 100, 'normal', 'available'),
(1, 'Matrix Birthday V2', 'Code sinh nhật phong cách Matrix', 45000, 'buy1.jpg', 100, 'normal', 'available'),
(1, 'Thư Tình', 'Code thư tình lãng mạn', 40000, 'buy3.jpg', 100, 'normal', 'available'),
(1, 'Thể hiện tình cảm', 'Code thể hiện tình cảm đặc biệt', 10000, 'buy4.jpg', 100, 'normal', 'available'),
(1, 'Troll Bạn Bè', 'Code troll bạn bè vui vẻ', 25000, 'troll.jpg', 100, 'normal', 'available'),
(1, 'Happy Birthday', 'Code chúc mừng sinh nhật', 25000, 'buy5.jpg', 100, 'normal', 'available'),
(4, 'Portfolio Dev', 'Template portfolio cho developer', 0, 'portfolio1.jpg', 1000, 'normal', 'available'),
(4, 'Portfolio Basic', 'Template portfolio cơ bản', 0, 'portfolio3.jpg', 1000, 'normal', 'available'),
(3, 'VPS Singapore Giá Rẻ', 'VPS Singapore cấu hình cao, tốc độ nhanh', 250000, 'vps.jpg', 0, 'vps', 'available');

-- =============================================
-- Insert sample VPS inventory
-- =============================================
INSERT INTO vps_inventory (product_id, ip_address, username, password, os_info, specs, status) VALUES
(9, '123.45.67.89', 'root', 'Abc@12345', 'Ubuntu 22.04', '2 CPU, 4GB RAM, 50GB SSD', 'available'),
(9, '123.45.67.90', 'admin', 'Xyz@67890', 'Ubuntu 22.04', '2 CPU, 4GB RAM, 50GB SSD', 'available'),
(9, '123.45.67.91', 'root', 'Pass@54321', 'CentOS 8', '4 CPU, 8GB RAM, 100GB SSD', 'available');
