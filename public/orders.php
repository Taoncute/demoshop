<?php
require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Models/Order.php';

Auth::requireAuth();

$orderModel = new Order();
$orders = $orderModel->getUserOrders(Auth::id());
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        .orders-container { max-width: 1200px; margin: 0 auto; }
        .order-card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 15px; }
        .order-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; margin-bottom: 15px; }
        .order-code { font-size: 18px; font-weight: 600; color: #007bff; }
        .order-date { color: #666; font-size: 14px; }
        .order-body { display: flex; justify-content: space-between; align-items: center; }
        .order-info { flex: 1; }
        .order-total { font-size: 20px; font-weight: bold; color: #28a745; }
        .status-badge { padding: 6px 12px; border-radius: 5px; font-size: 13px; font-weight: 600; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cfe2ff; color: #084298; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn-detail { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block; }
        .btn-detail:hover { background: #0056b3; }
        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 10px; }
        .empty-state i { font-size: 80px; color: #ddd; margin-bottom: 20px; }
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
        <div class="orders-container">
            <h1><i class="fa-solid fa-receipt"></i> Đơn hàng của tôi</h1>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-receipt"></i>
                    <h2>Chưa có đơn hàng nào</h2>
                    <p>Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm ngay!</p>
                    <a href="<?= BASE_URL ?>" class="btn-detail" style="margin-top: 20px;">
                        <i class="fa-solid fa-shopping-bag"></i> Mua sắm ngay
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-code">
                                    <i class="fa-solid fa-hashtag"></i> <?= $order['order_code'] ?>
                                </div>
                                <div class="order-date">
                                    <i class="fa-solid fa-clock"></i> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </div>
                            </div>
                            <div>
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
                            </div>
                        </div>
                        <div class="order-body">
                            <div class="order-info">
                                <div style="margin-bottom: 8px;">
                                    <strong>Phương thức thanh toán:</strong> 
                                    <?= $order['payment_method'] === 'balance' ? 'Số dư tài khoản' : 'VietQR' ?>
                                </div>
                                <div>
                                    <strong>Trạng thái thanh toán:</strong> 
                                    <span style="color: <?= $order['payment_status'] === 'completed' ? '#28a745' : '#dc3545' ?>">
                                        <?php
                                        $paymentStatusText = [
                                            'pending' => 'Chờ thanh toán',
                                            'completed' => 'Đã thanh toán',
                                            'failed' => 'Thất bại'
                                        ];
                                        echo $paymentStatusText[$order['payment_status']] ?? $order['payment_status'];
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="margin-bottom: 10px;">
                                    <div style="color: #666; font-size: 14px;">Tổng tiền</div>
                                    <div class="order-total"><?= number_format($order['total_amount']) ?>đ</div>
                                </div>
                                <a href="<?= BASE_URL ?>order_detail.php?id=<?= $order['id'] ?>" class="btn-detail">
                                    <i class="fa-solid fa-eye"></i> Xem chi tiết
                                </a>
                            </div>
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
