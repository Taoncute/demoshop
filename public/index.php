<?php
require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/Cart.php';
require_once __DIR__ . '/../app/Models/VPS.php';

$productModel = new Product();
$categoryModel = new Category();
$vpsModel = new VPS();

// Get filter parameters
$categoryId = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;

// Get products
$products = $productModel->getAll(50, 0, $categoryId, $search);
$categories = $categoryModel->getAll();

// Update VPS stock count
foreach ($products as &$product) {
    if ($product['product_type'] === 'vps') {
        $product['stock'] = $vpsModel->countAvailable($product['id']);
    }
}

$user = Auth::user();
$cartCount = 0;
if (Auth::check()) {
    $cartModel = new Cart();
    $cartCount = $cartModel->count(Auth::id());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        .user-info {
            background: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .balance {
            font-weight: bold;
            color: #28a745;
            font-size: 18px;
        }
        .search-bar {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .search-bar input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .filter-btn {
            padding: 8px 15px;
            background: white;
            border: 2px solid #007bff;
            color: #007bff;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .filter-btn:hover, .filter-btn.active {
            background: #007bff;
            color: white;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        .sidebar a {
            position: relative;
        }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <h2><i class="fa-solid fa-code"></i> <?= SITE_NAME ?></h2>
        
        <?php if (Auth::check()): ?>
            <a href="<?= BASE_URL ?>"><i class="fa-solid fa-home"></i> Trang chủ</a>
            <a href="<?= BASE_URL ?>cart.php">
                <i class="fa-solid fa-shopping-cart"></i> Giỏ hàng
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>orders.php"><i class="fa-solid fa-receipt"></i> Đơn hàng của tôi</a>
            <a href="<?= BASE_URL ?>topup.php"><i class="fa-solid fa-wallet"></i> Nạp tiền</a>
            <a href="<?= BASE_URL ?>profile.php"><i class="fa-solid fa-user"></i> Tài khoản</a>
            <?php if (Auth::isAdmin()): ?>
                <a href="<?= BASE_URL ?>admin/"><i class="fa-solid fa-shield-halved"></i> Admin Panel</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>"><i class="fa-solid fa-home"></i> Trang chủ</a>
            <a href="<?= BASE_URL ?>login.php"><i class="fa-solid fa-right-to-bracket"></i> Đăng nhập</a>
            <a href="<?= BASE_URL ?>register.php"><i class="fa-solid fa-user-plus"></i> Đăng ký</a>
        <?php endif; ?>
    </div>

    <div class="main-content">
        <?php if (Auth::check()): ?>
            <div class="user-info">
                <div>
                    <i class="fa-solid fa-user"></i> Xin chào, <strong><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></strong>
                </div>
                <div class="balance">
                    <i class="fa-solid fa-wallet"></i> <?= number_format($user['balance']) ?>đ
                </div>
            </div>
        <?php endif; ?>

        <h1><?= SITE_NAME ?></h1>

        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($search ?? '') ?>">
            </form>
        </div>

        <div class="filter-buttons">
            <a href="<?= BASE_URL ?>" class="filter-btn <?= !$categoryId ? 'active' : '' ?>">
                <i class="fa-solid fa-layer-group"></i> Tất cả
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="?category=<?= $cat['id'] ?>" class="filter-btn <?= $categoryId == $cat['id'] ? 'active' : '' ?>">
                    <?= $cat['name'] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="container">
            <?php if (empty($products)): ?>
                <p style="text-align: center; width: 100%; padding: 40px;">Không tìm thấy sản phẩm nào</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="code-item">
                        <img src="<?= BASE_URL ?>assets/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <p class="code-title"><?= htmlspecialchars($product['name']) ?></p>
                        <p class="price">
                            <?php if ($product['price'] == 0): ?>
                                Miễn phí
                            <?php else: ?>
                                Giá: <?= number_format($product['price']) ?>đ
                            <?php endif; ?>
                        </p>
                        <?php if ($product['product_type'] === 'vps'): ?>
                            <p style="font-size: 12px; color: #666;">
                                <i class="fa-solid fa-server"></i> Còn <?= $product['stock'] ?> VPS
                            </p>
                        <?php endif; ?>
                        <div class="buttons">
                            <a href="<?= BASE_URL ?>product.php?id=<?= $product['id'] ?>">Chi tiết</a>
                            <?php if (Auth::check()): ?>
                                <?php if ($product['status'] === 'available' && ($product['product_type'] !== 'vps' || $product['stock'] > 0)): ?>
                                    <form method="POST" action="<?= BASE_URL ?>cart_action.php" style="flex: 1;">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" style="width: 100%;">Thêm vào giỏ</button>
                                    </form>
                                <?php else: ?>
                                    <button class="disabled" disabled>Hết hàng</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>login.php">Đăng nhập</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>
</html>
