<?php
require_once __DIR__ . '/../../app/Config/Config.php';
require_once __DIR__ . '/../../app/Core/Session.php';
require_once __DIR__ . '/../../app/Core/Auth.php';

// Require admin authentication
Auth::requireAdmin();

$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .stat-card i {
            font-size: 40px;
            color: #007bff;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        .menu-item {
            background: #007bff;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        .menu-item:hover {
            background: #0056b3;
        }
        .menu-item i {
            font-size: 30px;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <h2><i class="fa-solid fa-shield-halved"></i> Admin Panel</h2>
        <a href="<?= BASE_URL ?>admin/"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>admin/products.php"><i class="fa-solid fa-box"></i> Quản lý sản phẩm</a>
        <a href="<?= BASE_URL ?>admin/categories.php"><i class="fa-solid fa-list"></i> Danh mục</a>
        <a href="<?= BASE_URL ?>admin/vps.php"><i class="fa-solid fa-server"></i> Quản lý VPS</a>
        <a href="<?= BASE_URL ?>admin/orders.php"><i class="fa-solid fa-shopping-cart"></i> Đơn hàng</a>
        <a href="<?= BASE_URL ?>admin/users.php"><i class="fa-solid fa-users"></i> Người dùng</a>
        <a href="<?= BASE_URL ?>"><i class="fa-solid fa-home"></i> Về trang chủ</a>
        <a href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
    </div>

    <div class="main-content">
        <h1>Bảng điều khiển Admin</h1>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fa-solid fa-box"></i>
                <div class="stat-number">0</div>
                <div class="stat-label">Tổng sản phẩm</div>
            </div>
            
            <div class="stat-card">
                <i class="fa-solid fa-shopping-cart"></i>
                <div class="stat-number">0</div>
                <div class="stat-label">Đơn hàng</div>
            </div>
            
            <div class="stat-card">
                <i class="fa-solid fa-users"></i>
                <div class="stat-number">0</div>
                <div class="stat-label">Người dùng</div>
            </div>
            
            <div class="stat-card">
                <i class="fa-solid fa-dollar-sign"></i>
                <div class="stat-number">0đ</div>
                <div class="stat-label">Doanh thu</div>
            </div>
        </div>

        <h2 style="margin-top: 40px;">Quản lý nhanh</h2>
        <div class="admin-menu">
            <a href="<?= BASE_URL ?>admin/products.php?action=add" class="menu-item">
                <i class="fa-solid fa-plus"></i>
                Thêm sản phẩm
            </a>
            <a href="<?= BASE_URL ?>admin/vps.php?action=add" class="menu-item">
                <i class="fa-solid fa-server"></i>
                Thêm VPS
            </a>
            <a href="<?= BASE_URL ?>admin/categories.php?action=add" class="menu-item">
                <i class="fa-solid fa-folder-plus"></i>
                Thêm danh mục
            </a>
            <a href="<?= BASE_URL ?>admin/orders.php" class="menu-item">
                <i class="fa-solid fa-list-check"></i>
                Xem đơn hàng
            </a>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>
</html>
