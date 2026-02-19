<?php
require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Core/VietQR.php';
require_once __DIR__ . '/../app/Models/Cart.php';
require_once __DIR__ . '/../app/Models/Order.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/VPS.php';

Auth::requireAuth();

$cartModel = new Cart();
$orderModel = new Order();
$userModel = new User();
$vpsModel = new VPS();

$cartItems = $cartModel->getByUserId(Auth::id());
$total = $cartModel->getTotal(Auth::id());
$user = Auth::user();

if (empty($cartItems)) {
    header('Location: ' . BASE_URL . 'cart.php');
    exit;
}

$errors = [];
$orderCreated = false;
$orderInfo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? 'balance';
    
    // Validate payment method
    if (!in_array($paymentMethod, ['balance', 'vietqr'])) {
        $errors[] = 'Phương thức thanh toán không hợp lệ';
    }
    
    // Check balance if paying with balance
    if ($paymentMethod === 'balance' && $user['balance'] < $total) {
        $errors[] = 'Số dư không đủ để thanh toán';
    }
    
    // Check VPS availability
    foreach ($cartItems as $item) {
        if ($item['product_type'] === 'vps') {
            $available = $vpsModel->countAvailable($item['product_id']);
            if ($available < $item['quantity']) {
                $errors[] = "Sản phẩm {$item['name']} không đủ số lượng VPS";
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $this->conn->beginTransaction();
            
            // Create order
            $orderData = $orderModel->create(Auth::id(), $total, $paymentMethod);
            
            if ($orderData) {
                $orderId = $orderData['id'];
                $orderCode = $orderData['order_code'];
                
                // Add order items and allocate VPS
                foreach ($cartItems as $item) {
                    $vpsId = null;
                    
                    if ($item['product_type'] === 'vps') {
                        // Allocate VPS
                        $availableVPS = $vpsModel->getAvailableByProductId($item['product_id'], $item['quantity']);
                        if (!empty($availableVPS)) {
                            $vps = $availableVPS[0];
                            $vpsId = $vps['id'];
                            $vpsModel->markAsSold($vpsId, Auth::id());
                        }
                    }
                    
                    $orderModel->addItem($orderId, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $vpsId);
                }
                
                // Process payment
                if ($paymentMethod === 'balance') {
                    // Deduct balance
                    $userModel->subtractBalance(Auth::id(), $total);
                    $orderModel->completeOrder($orderId);
                    
                    // Update session balance
                    $updatedUser = $userModel->findById(Auth::id());
                    Auth::updateBalance($updatedUser['balance']);
                    
                    // Clear cart
                    $cartModel->clear(Auth::id());
                    
                    Session::flash('success', 'Đặt hàng thành công!');
                    header('Location: ' . BASE_URL . 'order_detail.php?id=' . $orderId);
                    exit;
                } else {
                    // VietQR payment
                    $orderCreated = true;
                    $orderInfo = [
                        'id' => $orderId,
                        'code' => $orderCode,
                        'amount' => $total,
                        'qr_url' => VietQR::generateOrderQR($orderCode, $total)
                    ];
                    
                    // Clear cart
                    $cartModel->clear(Auth::id());
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        .checkout-container { max-width: 800px; margin: 0 auto; }
        .checkout-section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 20px; }
        .payment-method { display: flex; gap: 15px; margin-top: 15px; }
        .payment-option { flex: 1; padding: 20px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s; }
        .payment-option:hover { border-color: #007bff; }
        .payment-option input[type="radio"] { display: none; }
        .payment-option input[type="radio"]:checked + label { color: #007bff; font-weight: bold; }
        .payment-option.selected { border-color: #007bff; background: #f0f8ff; }
        .btn-submit { width: 100%; padding: 15px; background: #28a745; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: #218838; }
        .qr-container { text-align: center; padding: 30px; }
        .qr-container img { max-width: 400px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .alert-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>

    <div class="sidebar" id="sidebar">
        <h2><i class="fa-solid fa-code"></i> <?= SITE_NAME ?></h2>
        <a href="<?= BASE_URL ?>"><i class="fa-solid fa-home"></i> Trang chủ</a>
        <a href="<?= BASE_URL ?>cart.php"><i class="fa-solid fa-shopping-cart"></i> Giỏ hàng</a>
        <a href="<?= BASE_URL ?>orders.php"><i class="fa-solid fa-receipt"></i> Đơn hàng của tôi</a>
        <a href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
    </div>

    <div class="main-content">
        <div class="checkout-container">
            <h1><i class="fa-solid fa-credit-card"></i> Thanh toán</h1>

            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><i class="fa-solid fa-exclamation-circle"></i> <?= $error ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($orderCreated && $orderInfo): ?>
                <div class="checkout-section">
                    <h2 style="color: #28a745; text-align: center;">
                        <i class="fa-solid fa-check-circle"></i> Đơn hàng đã được tạo!
                    </h2>
                    <p style="text-align: center;">Mã đơn hàng: <strong><?= $orderInfo['code'] ?></strong></p>
                    <p style="text-align: center;">Vui lòng quét mã QR bên dưới để thanh toán</p>
                </div>

                <div class="checkout-section qr-container">
                    <h3>Quét mã QR để thanh toán</h3>
                    <img src="<?= $orderInfo['qr_url'] ?>" alt="VietQR">
                    <p style="margin-top: 20px; font-size: 18px; font-weight: bold; color: #007bff;">
                        Số tiền: <?= number_format($orderInfo['amount']) ?>đ
                    </p>
                    <p style="color: #666;">Nội dung chuyển khoản: <strong><?= $orderInfo['code'] ?></strong></p>
                    <p style="color: #dc3545; margin-top: 20px;">
                        <i class="fa-solid fa-info-circle"></i> Vui lòng chuyển khoản đúng nội dung để đơn hàng được xử lý tự động
                    </p>
                    <a href="<?= BASE_URL ?>order_detail.php?id=<?= $orderInfo['id'] ?>" class="btn-submit" style="max-width: 300px; margin: 20px auto; display: block;">
                        Xem chi tiết đơn hàng
                    </a>
                </div>
            <?php else: ?>
                <div class="checkout-section">
                    <h3>Thông tin đơn hàng</h3>
                    <table style="width: 100%; margin-top: 15px;">
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?></td>
                                <td style="text-align: right; font-weight: bold;"><?= number_format($item['price'] * $item['quantity']) ?>đ</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="border-top: 2px solid #007bff;">
                            <td style="padding-top: 15px; font-size: 18px; font-weight: bold;">Tổng cộng:</td>
                            <td style="padding-top: 15px; text-align: right; font-size: 20px; font-weight: bold; color: #007bff;">
                                <?= number_format($total) ?>đ
                            </td>
                        </tr>
                    </table>
                </div>

                <form method="POST">
                    <div class="checkout-section">
                        <h3>Phương thức thanh toán</h3>
                        <div class="payment-method">
                            <div class="payment-option <?= ($user['balance'] >= $total) ? '' : 'disabled' ?>" onclick="selectPayment('balance', this)">
                                <input type="radio" name="payment_method" value="balance" id="balance" <?= ($user['balance'] >= $total) ? 'checked' : 'disabled' ?>>
                                <label for="balance" style="cursor: pointer;">
                                    <i class="fa-solid fa-wallet" style="font-size: 30px; display: block; margin-bottom: 10px;"></i>
                                    Số dư tài khoản<br>
                                    <small>(<?= number_format($user['balance']) ?>đ)</small>
                                </label>
                            </div>
                            <div class="payment-option" onclick="selectPayment('vietqr', this)">
                                <input type="radio" name="payment_method" value="vietqr" id="vietqr" <?= ($user['balance'] < $total) ? 'checked' : '' ?>>
                                <label for="vietqr" style="cursor: pointer;">
                                    <i class="fa-solid fa-qrcode" style="font-size: 30px; display: block; margin-bottom: 10px;"></i>
                                    VietQR<br>
                                    <small>(Chuyển khoản ngân hàng)</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-check"></i> Xác nhận thanh toán
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }

        function selectPayment(method, element) {
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById(method).checked = true;
        }

        // Auto-select on load
        document.addEventListener('DOMContentLoaded', function() {
            const checked = document.querySelector('input[name="payment_method"]:checked');
            if (checked) {
                checked.closest('.payment-option').classList.add('selected');
            }
        });
    </script>
</body>
</html>
