<?php
require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Models/Order.php';
require_once __DIR__ . '/../app/Models/VPS.php';

Auth::requireAuth();

$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    header('Location: ' . BASE_URL . 'orders.php');
    exit;
}

$orderModel = new Order();
$vpsModel = new VPS();

$order = $orderModel->getById($orderId);

if (!$order || $order['user_id'] != Auth::id()) {
    header('Location: ' . BASE_URL . 'orders.php');
    exit;
}

$orderItems = $orderModel->getItems($orderId);

// Get VPS info for VPS products
foreach ($orderItems as &$item) {
    if ($item['product_type'] === 'vps' && $item['vps_id']) {
        $item['vps_info'] = $vpsModel->getVPSForOrder($item['vps_id']);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        .detail-container { max-width: 900px; margin: 0 auto; }
        .detail-section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 20px; }
        .order-header { text-align: center; padding: 20px; background: linear-gradient(135deg, #007bff, #0056b3); color: white; border-radius: 10px; margin-bottom: 20px; }
        .order-code { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .info-label { font-weight: 600; color: #666; }
        .info-value { color: #333; }
        .item-card { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; gap: 15px; align-items: center; }
        .item-image { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; }
        .item-info { flex: 1; }
        .item-name { font-weight: 600; margin-bottom: 5px; }
        .item-price { color: #007bff; font-weight: bold; }
        .vps-info { background: #e7f3ff; padding: 15px; border-radius: 8px; margin-top: 10px; border-left: 4px solid #007bff; }
        .vps-field { margin: 8px 0; font-family: monospace; }
        .vps-field strong { color: #333; }
        .vps-field code { background: white; padding: 4px 8px; border-radius: 4px; color: #dc3545; }
        .total-row { font-size: 20px; font-weight: bold; color: #28a745; padding-top: 15px; margin-top: 15px; border-top: 2px solid #007bff; }
        .status-badge { padding: 8px 15px; border-radius: 5px; font-size: 14px; font-weight: 600; display: inline-block; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cfe2ff; color: #084298; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn-back { padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; display: inline-block; }
        .btn-back:hover { background: #5a6268; }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>

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
        <div class="detail-container">
            <a href="<?= BASE_URL ?>orders.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>

            <div class="order-header">
                <div class="order-code">
                    <i class="fa-solid fa-receipt"></i> <?= $order['order_code'] ?>
                </div>
                <div><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></div>
            </div>

            <div class="detail-section">
                <h3>Thông tin đơn hàng</h3>
                <div class="info-row">
                    <span class="info-label">Trạng thái đơn hàng:</span>
                    <span class="info-value">
                        <span class="status-badge status-<?= $order['order_status'] ?>">
                            <?php
                            $statusText = [
                                'pending' => 'Chờ xử lý',
                                'processing' => 'Đang xử lý',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy'
                            ];
                            echo $statusText[$order['order_status']] ?? $order['order_status'];
                            ?>
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái thanh toán:</span>
                    <span class="info-value">
                        <span class="status-badge status-<?= $order['payment_status'] === 'completed' ? 'completed' : 'pending' ?>">
                            <?php
                            $paymentStatusText = [
                                'pending' => 'Chờ thanh toán',
                                'completed' => 'Đã thanh toán',
                                'failed' => 'Thất bại'
                            ];
                            echo $paymentStatusText[$order['payment_status']] ?? $order['payment_status'];
                            ?>
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phương thức thanh toán:</span>
                    <span class="info-value">
                        <?= $order['payment_method'] === 'balance' ? 'Số dư tài khoản' : 'VietQR' ?>
                    </span>
                </div>
                <div class="info-row" style="border: none;">
                    <span class="info-label">Thời gian đặt hàng:</span>
                    <span class="info-value"><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></span>
                </div>
            </div>

            <div class="detail-section">
                <h3>Sản phẩm</h3>
                <?php foreach ($orderItems as $item): ?>
                    <div class="item-card">
                        <img src="<?= BASE_URL ?>assets/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="item-image">
                        <div class="item-info">
                            <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                            <div>Số lượng: <?= $item['quantity'] ?></div>
                            <div class="item-price"><?= number_format($item['price']) ?>đ x <?= $item['quantity'] ?> = <?= number_format($item['price'] * $item['quantity']) ?>đ</div>
                            
                            <?php if (isset($item['vps_info']) && $item['vps_info']): ?>
                                <div class="vps-info">
                                    <div style="font-weight: bold; margin-bottom: 10px;">
                                        <i class="fa-solid fa-server"></i> Thông tin VPS
                                    </div>
                                    <div class="vps-field">
                                        <strong>IP Address:</strong> <code><?= $item['vps_info']['ip_address'] ?></code>
                                    </div>
                                    <div class="vps-field">
                                        <strong>Username:</strong> <code><?= $item['vps_info']['username'] ?></code>
                                    </div>
                                    <div class="vps-field">
                                        <strong>Password:</strong> <code><?= $item['vps_info']['password'] ?></code>
                                    </div>
                                    <?php if (!empty($item['vps_info']['os_info'])): ?>
                                        <div class="vps-field">
                                            <strong>Hệ điều hành:</strong> <?= $item['vps_info']['os_info'] ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['vps_info']['specs'])): ?>
                                        <div class="vps-field">
                                            <strong>Cấu hình:</strong> <?= $item['vps_info']['specs'] ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 5px; font-size: 13px;">
                                        <i class="fa-solid fa-exclamation-triangle"></i> Vui lòng lưu lại thông tin này!
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="info-row total-row">
                    <span>Tổng cộng:</span>
                    <span><?= number_format($order['total_amount']) ?>đ</span>
                </div>
            </div>

            <?php if ($order['payment_status'] === 'pending' && $order['payment_method'] === 'vietqr'): ?>
                <div class="detail-section" style="text-align: center; background: #fff3cd;">
                    <h3 style="color: #856404;">
                        <i class="fa-solid fa-clock"></i> Đơn hàng đang chờ thanh toán
                    </h3>
                    <p>Vui lòng chuyển khoản với nội dung: <strong><?= $order['order_code'] ?></strong></p>
                    <p>Sau khi chuyển khoản thành công, đơn hàng sẽ được xử lý tự động.</p>
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
