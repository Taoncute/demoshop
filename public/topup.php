<?php
require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Core/VietQR.php';
require_once __DIR__ . '/../app/Core/Validator.php';
require_once __DIR__ . '/../app/Models/Topup.php';

Auth::requireAuth();

$topupModel = new Topup();
$user = Auth::user();

$errors = [];
$topupCreated = false;
$topupInfo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? 0;
    
    $validator = new Validator(['amount' => $amount]);
    $validator->required('amount', 'Vui lòng nhập số tiền')
              ->numeric('amount', 'Số tiền phải là số')
              ->minValue('amount', 10000, 'Số tiền tối thiểu là 10,000đ');
    
    if ($validator->fails()) {
        $errors = $validator->errors();
    } else {
        $topupData = $topupModel->create(Auth::id(), $amount);
        
        if ($topupData) {
            $topupCreated = true;
            $topupInfo = [
                'id' => $topupData['id'],
                'code' => $topupData['transaction_code'],
                'amount' => $amount,
                'qr_url' => VietQR::generateTopupQR($topupData['transaction_code'], $amount)
            ];
        } else {
            $errors['general'] = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}

// Get recent topup history
$topupHistory = $topupModel->getUserTopups(Auth::id(), 10, 0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nạp tiền - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        .topup-container { max-width: 800px; margin: 0 auto; }
        .topup-section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 20px; }
        .balance-display { text-align: center; padding: 30px; background: linear-gradient(135deg, #007bff, #0056b3); color: white; border-radius: 10px; margin-bottom: 20px; }
        .balance-amount { font-size: 36px; font-weight: bold; margin: 10px 0; }
        .amount-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 20px 0; }
        .amount-btn { padding: 15px; border: 2px solid #007bff; background: white; color: #007bff; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .amount-btn:hover, .amount-btn.selected { background: #007bff; color: white; }
        .custom-amount { margin: 20px 0; }
        .custom-amount input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; box-sizing: border-box; }
        .btn-submit { width: 100%; padding: 15px; background: #28a745; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: #218838; }
        .qr-container { text-align: center; padding: 30px; }
        .qr-container img { max-width: 400px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .history-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .history-table th, .history-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .history-table th { background: #f8f9fa; font-weight: 600; }
        .status-badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
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
        <a href="<?= BASE_URL ?>topup.php"><i class="fa-solid fa-wallet"></i> Nạp tiền</a>
        <a href="<?= BASE_URL ?>profile.php"><i class="fa-solid fa-user"></i> Tài khoản</a>
        <a href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
    </div>

    <div class="main-content">
        <div class="topup-container">
            <h1><i class="fa-solid fa-wallet"></i> Nạp tiền vào tài khoản</h1>

            <div class="balance-display">
                <div><i class="fa-solid fa-wallet"></i> Số dư hiện tại</div>
                <div class="balance-amount"><?= number_format($user['balance']) ?>đ</div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><i class="fa-solid fa-exclamation-circle"></i> <?= $error ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($topupCreated && $topupInfo): ?>
                <div class="topup-section">
                    <h2 style="color: #28a745; text-align: center;">
                        <i class="fa-solid fa-check-circle"></i> Yêu cầu nạp tiền đã được tạo!
                    </h2>
                    <p style="text-align: center;">Mã giao dịch: <strong><?= $topupInfo['code'] ?></strong></p>
                </div>

                <div class="topup-section qr-container">
                    <h3>Quét mã QR để nạp tiền</h3>
                    <img src="<?= $topupInfo['qr_url'] ?>" alt="VietQR">
                    <p style="margin-top: 20px; font-size: 18px; font-weight: bold; color: #007bff;">
                        Số tiền: <?= number_format($topupInfo['amount']) ?>đ
                    </p>
                    <p style="color: #666;">Nội dung chuyển khoản: <strong><?= $topupInfo['code'] ?></strong></p>
                    <p style="color: #dc3545; margin-top: 20px;">
                        <i class="fa-solid fa-info-circle"></i> Vui lòng chuyển khoản đúng nội dung để số dư được cập nhật tự động
                    </p>
                    <button onclick="location.reload()" class="btn-submit" style="max-width: 300px; margin: 20px auto; display: block;">
                        Tạo yêu cầu nạp tiền mới
                    </button>
                </div>
            <?php else: ?>
                <form method="POST">
                    <div class="topup-section">
                        <h3>Chọn số tiền nạp</h3>
                        <div class="amount-options">
                            <button type="button" class="amount-btn" onclick="selectAmount(50000)">50,000đ</button>
                            <button type="button" class="amount-btn" onclick="selectAmount(100000)">100,000đ</button>
                            <button type="button" class="amount-btn" onclick="selectAmount(200000)">200,000đ</button>
                            <button type="button" class="amount-btn" onclick="selectAmount(500000)">500,000đ</button>
                            <button type="button" class="amount-btn" onclick="selectAmount(1000000)">1,000,000đ</button>
                            <button type="button" class="amount-btn" onclick="selectAmount(2000000)">2,000,000đ</button>
                        </div>

                        <div class="custom-amount">
                            <label style="font-weight: 600; margin-bottom: 10px; display: block;">Hoặc nhập số tiền khác:</label>
                            <input type="number" name="amount" id="amount" placeholder="Nhập số tiền (tối thiểu 10,000đ)" min="10000" step="1000">
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-qrcode"></i> Tạo mã QR nạp tiền
                    </button>
                </form>
            <?php endif; ?>

            <div class="topup-section">
                <h3>Lịch sử nạp tiền</h3>
                <?php if (empty($topupHistory)): ?>
                    <p style="text-align: center; padding: 20px; color: #999;">Chưa có giao dịch nạp tiền nào</p>
                <?php else: ?>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Mã giao dịch</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topupHistory as $t): ?>
                                <tr>
                                    <td><?= $t['transaction_code'] ?></td>
                                    <td><?= number_format($t['amount']) ?>đ</td>
                                    <td>
                                        <span class="status-badge status-<?= $t['status'] ?>">
                                            <?php
                                            $statusText = [
                                                'pending' => 'Chờ thanh toán',
                                                'completed' => 'Thành công',
                                                'failed' => 'Thất bại'
                                            ];
                                            echo $statusText[$t['status']] ?? $t['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }

        function selectAmount(amount) {
            document.getElementById('amount').value = amount;
            document.querySelectorAll('.amount-btn').forEach(btn => btn.classList.remove('selected'));
            event.target.classList.add('selected');
        }
    </script>
</body>
</html>
