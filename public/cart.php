<?php
require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Models/Cart.php';
require_once __DIR__ . '/../app/Models/VPS.php';

Auth::requireAuth();

$cartModel = new Cart();
$vpsModel = new VPS();
$cartItems = $cartModel->getByUserId(Auth::id());
$total = $cartModel->getTotal(Auth::id());

// Update VPS stock for cart items
foreach ($cartItems as &$item) {
    if ($item['product_type'] === 'vps') {
        $item['available_stock'] = $vpsModel->countAvailable($item['product_id']);
    }
}

$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        .cart-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .cart-item {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .cart-item-price {
            color: #007bff;
            font-weight: bold;
            font-size: 16px;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-control button {
            width: 30px;
            height: 30px;
            border: none;
            background: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .quantity-control input {
            width: 60px;
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .cart-summary {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .summary-total {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .btn-checkout {
            width: 100%;
            padding: 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-checkout:hover {
            background: #218838;
        }
        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
        }
        .empty-cart i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <h2><i class="fa-solid fa-code"></i> <?= SITE_NAME ?></h2>
        <a href="<?= BASE_URL ?>"><i class="fa-solid fa-home"></i> Trang chủ</a>
        <a href="<?= BASE_URL ?>cart.php"><i class="fa-solid fa-shopping-cart"></i> Giỏ hàng</a>
        <a href="<?= BASE_URL ?>orders.php"><i class="fa-solid fa-receipt"></i> Đơn hàng của tôi</a>
        <a href="<?= BASE_URL ?>topup.php"><i class="fa-solid fa-wallet"></i> Nạp tiền</a>
        <a href="<?= BASE_URL ?>profile.php"><i class="fa-solid fa-user"></i> Tài khoản</a>
        <a href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
    </div>

    <div class="main-content">
        <div class="cart-container">
            <h1><i class="fa-solid fa-shopping-cart"></i> Giỏ hàng của bạn</h1>

            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <h2>Giỏ hàng trống</h2>
                    <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                    <a href="<?= BASE_URL ?>" class="btn-checkout" style="max-width: 300px; margin: 20px auto; display: block;">
                        Tiếp tục mua sắm
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <img src="<?= BASE_URL ?>assets/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="cart-item-info">
                            <div class="cart-item-title"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="cart-item-price"><?= number_format($item['price']) ?>đ</div>
                            <?php if ($item['product_type'] === 'vps'): ?>
                                <div style="font-size: 13px; color: #666; margin-top: 5px;">
                                    <i class="fa-solid fa-server"></i> VPS - Còn <?= $item['available_stock'] ?> máy
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="quantity-control">
                            <?php if ($item['product_type'] !== 'vps'): ?>
                                <form method="POST" action="cart_action.php" style="display: flex; gap: 10px; align-items: center;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>">-</button>
                                    <input type="number" name="quantity_display" value="<?= $item['quantity'] ?>" readonly>
                                    <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>">+</button>
                                </form>
                            <?php else: ?>
                                <span>Số lượng: <?= $item['quantity'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong><?= number_format($item['price'] * $item['quantity']) ?>đ</strong>
                        </div>
                        <div>
                            <form method="POST" action="cart_action.php" onsubmit="return confirm('Xóa sản phẩm này?')">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-remove">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?= number_format($total) ?>đ</span>
                    </div>
                    <div class="summary-row" style="border: none;">
                        <span style="font-size: 20px; font-weight: 600;">Tổng cộng:</span>
                        <span class="summary-total"><?= number_format($total) ?>đ</span>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px;">
                        <div style="margin-bottom: 10px;">
                            <strong>Số dư hiện tại:</strong> <?= number_format($user['balance']) ?>đ
                        </div>
                        <?php if ($user['balance'] < $total): ?>
                            <div style="color: #dc3545; margin-bottom: 10px;">
                                <i class="fa-solid fa-exclamation-triangle"></i> 
                                Số dư không đủ. Vui lòng nạp thêm <?= number_format($total - $user['balance']) ?>đ
                            </div>
                            <a href="<?= BASE_URL ?>topup.php" class="btn-checkout" style="background: #007bff;">
                                <i class="fa-solid fa-wallet"></i> Nạp tiền ngay
                            </a>
                        <?php endif; ?>
                    </div>
                    <a href="<?= BASE_URL ?>checkout.php" class="btn-checkout">
                        <i class="fa-solid fa-credit-card"></i> Tiến hành thanh toán
                    </a>
                </div>
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
