<?php
require_once __DIR__ . '/../../app/Config/Config.php';
require_once __DIR__ . '/../../app/Core/Session.php';
require_once __DIR__ . '/../../app/Core/Auth.php';
require_once __DIR__ . '/../../app/Core/Validator.php';
require_once __DIR__ . '/../../app/Models/VPS.php';
require_once __DIR__ . '/../../app/Models/Product.php';

Auth::requireAdmin();

$vpsModel = new VPS();
$productModel = new Product();

// Get all VPS products
$allProducts = $productModel->getAll(100, 0);
$vpsProducts = array_filter($allProducts, function($p) {
    return $p['product_type'] === 'vps';
});

$action = $_GET['action'] ?? 'list';
$vpsId = $_GET['id'] ?? null;
$productId = $_GET['product_id'] ?? null;
$errors = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $data = [
                'product_id' => $_POST['product_id'] ?? '',
                'ip_address' => Validator::sanitize($_POST['ip_address'] ?? ''),
                'username' => Validator::sanitize($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'os_info' => Validator::sanitize($_POST['os_info'] ?? ''),
                'specs' => Validator::sanitize($_POST['specs'] ?? ''),
                'status' => 'available'
            ];

            if ($vpsModel->create($data)) {
                Session::flash('success', 'Thêm VPS thành công');
                header('Location: vps.php?product_id=' . $data['product_id']);
                exit;
            }
        } elseif ($_POST['action'] === 'add_bulk') {
            // Bulk add VPS from textarea
            $productId = $_POST['product_id'];
            $vpsData = $_POST['vps_data'];
            $lines = explode("\n", trim($vpsData));
            $count = 0;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $parts = explode('|', $line);
                if (count($parts) >= 3) {
                    $data = [
                        'product_id' => $productId,
                        'ip_address' => trim($parts[0]),
                        'username' => trim($parts[1]),
                        'password' => trim($parts[2]),
                        'os_info' => isset($parts[3]) ? trim($parts[3]) : '',
                        'specs' => isset($parts[4]) ? trim($parts[4]) : '',
                        'status' => 'available'
                    ];

                    if ($vpsModel->create($data)) {
                        $count++;
                    }
                }
            }

            Session::flash('success', "Đã thêm {$count} VPS thành công");
            header('Location: vps.php?product_id=' . $productId);
            exit;
        } elseif ($_POST['action'] === 'delete') {
            if ($vpsModel->delete($_POST['vps_id'])) {
                Session::flash('success', 'Xóa VPS thành công');
                header('Location: vps.php?product_id=' . $_POST['product_id']);
                exit;
            }
        }
    }
}

// Get VPS list
$vpsList = [];
if ($productId) {
    $vpsList = $vpsModel->getByProductId($productId);
}

$successMessage = Session::flash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý VPS - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 13px; }
        th { background: #007bff; color: white; }
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; color: white; margin-right: 5px; display: inline-block; border: none; cursor: pointer; }
        .btn-primary { background: #007bff; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; }
        .status-badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; }
        .status-available { background: #d4edda; color: #155724; }
        .status-sold { background: #f8d7da; color: #721c24; }
        .product-selector { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>

    <div class="sidebar" id="sidebar">
        <h2><i class="fa-solid fa-shield-halved"></i> Admin Panel</h2>
        <a href="<?= BASE_URL ?>admin/"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>admin/products.php"><i class="fa-solid fa-box"></i> Quản lý sản phẩm</a>
        <a href="<?= BASE_URL ?>admin/categories.php"><i class="fa-solid fa-list"></i> Danh mục</a>
        <a href="<?= BASE_URL ?>admin/vps.php"><i class="fa-solid fa-server"></i> Quản lý VPS</a>
        <a href="<?= BASE_URL ?>admin/orders.php"><i class="fa-solid fa-shopping-cart"></i> Đơn hàng</a>
        <a href="<?= BASE_URL ?>"><i class="fa-solid fa-home"></i> Về trang chủ</a>
        <a href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
    </div>

    <div class="main-content">
        <h1>Quản lý VPS</h1>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>

        <div class="product-selector">
            <h3>Chọn sản phẩm VPS</h3>
            <form method="GET" style="display: flex; gap: 10px;">
                <select name="product_id" onchange="this.form.submit()" style="flex: 1;">
                    <option value="">-- Chọn sản phẩm VPS --</option>
                    <?php foreach ($vpsProducts as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $productId == $p['id'] ? 'selected' : '' ?>>
                            <?= $p['name'] ?> (<?= $vpsModel->countAvailable($p['id']) ?> available)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($productId): ?>
            <?php if ($action === 'add'): ?>
                <h2>Thêm VPS mới</h2>
                <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h3>Thêm từng VPS</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $productId ?>">

                        <div class="form-group">
                            <label>IP Address</label>
                            <input type="text" name="ip_address" required placeholder="123.45.67.89">
                        </div>

                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" required placeholder="root">
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="text" name="password" required placeholder="Password@123">
                        </div>

                        <div class="form-group">
                            <label>Hệ điều hành</label>
                            <input type="text" name="os_info" placeholder="Ubuntu 22.04">
                        </div>

                        <div class="form-group">
                            <label>Thông số kỹ thuật</label>
                            <input type="text" name="specs" placeholder="2 CPU, 4GB RAM, 50GB SSD">
                        </div>

                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-save"></i> Thêm VPS</button>
                        <a href="vps.php?product_id=<?= $productId ?>" class="btn btn-primary">Quay lại</a>
                    </form>
                </div>

                <div style="background: white; padding: 20px; border-radius: 10px;">
                    <h3>Thêm hàng loạt VPS</h3>
                    <p>Mỗi dòng theo định dạng: <code>IP|Username|Password|OS|Specs</code></p>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_bulk">
                        <input type="hidden" name="product_id" value="<?= $productId ?>">

                        <div class="form-group">
                            <label>Danh sách VPS (mỗi VPS một dòng)</label>
                            <textarea name="vps_data" rows="10" placeholder="123.45.67.89|root|Pass@123|Ubuntu 22.04|2CPU 4GB RAM&#10;123.45.67.90|admin|Pass@456|CentOS 8|4CPU 8GB RAM"></textarea>
                        </div>

                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-upload"></i> Thêm hàng loạt</button>
                    </form>
                </div>
            <?php else: ?>
                <a href="vps.php?action=add&product_id=<?= $productId ?>" class="btn btn-success">
                    <i class="fa-solid fa-plus"></i> Thêm VPS mới
                </a>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>IP Address</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>OS</th>
                            <th>Specs</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vpsList)): ?>
                            <tr><td colspan="8" style="text-align: center;">Chưa có VPS nào</td></tr>
                        <?php else: ?>
                            <?php foreach ($vpsList as $vps): ?>
                                <tr>
                                    <td><?= $vps['id'] ?></td>
                                    <td><?= $vps['ip_address'] ?></td>
                                    <td><?= $vps['username'] ?></td>
                                    <td><?= $vps['password'] ?></td>
                                    <td><?= $vps['os_info'] ?></td>
                                    <td><?= $vps['specs'] ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $vps['status'] === 'available' ? 'available' : 'sold' ?>">
                                            <?= $vps['status'] === 'available' ? 'Có sẵn' : 'Đã bán' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($vps['status'] === 'available'): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Xác nhận xóa?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="vps_id" value="<?= $vps['id'] ?>">
                                                <input type="hidden" name="product_id" value="<?= $productId ?>">
                                                <button type="submit" class="btn btn-danger">Xóa</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #999;">Đã bán</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php else: ?>
            <p style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                Vui lòng chọn sản phẩm VPS để quản lý
            </p>
        <?php endif; ?>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>
</html>
