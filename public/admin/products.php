<?php
require_once __DIR__ . '/../../app/Config/Config.php';
require_once __DIR__ . '/../../app/Core/Session.php';
require_once __DIR__ . '/../../app/Core/Auth.php';
require_once __DIR__ . '/../../app/Core/Validator.php';
require_once __DIR__ . '/../../app/Models/Product.php';
require_once __DIR__ . '/../../app/Models/Category.php';

Auth::requireAdmin();

$productModel = new Product();
$categoryModel = new Category();
$categories = $categoryModel->getAll();

$action = $_GET['action'] ?? 'list';
$productId = $_GET['id'] ?? null;
$errors = [];
$success = false;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $data = [
                'category_id' => $_POST['category_id'] ?? '',
                'name' => Validator::sanitize($_POST['name'] ?? ''),
                'description' => Validator::sanitize($_POST['description'] ?? ''),
                'price' => $_POST['price'] ?? 0,
                'stock' => $_POST['stock'] ?? 0,
                'product_type' => $_POST['product_type'] ?? 'normal',
                'status' => $_POST['status'] ?? 'available',
                'image' => $_POST['image'] ?? ''
            ];

            $validator = new Validator($data);
            $validator->required('category_id')->required('name')->required('price')->numeric('price');

            if ($validator->fails()) {
                $errors = $validator->errors();
            } else {
                if ($_POST['action'] === 'add') {
                    if ($productModel->create($data)) {
                        Session::flash('success', 'Thêm sản phẩm thành công');
                        header('Location: products.php');
                        exit;
                    }
                } else {
                    if ($productModel->update($_POST['product_id'], $data)) {
                        Session::flash('success', 'Cập nhật sản phẩm thành công');
                        header('Location: products.php');
                        exit;
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($productModel->delete($_POST['product_id'])) {
                Session::flash('success', 'Xóa sản phẩm thành công');
                header('Location: products.php');
                exit;
            }
        }
    }
}

// Get product for editing
$product = null;
if ($action === 'edit' && $productId) {
    $product = $productModel->getById($productId);
}

// Get all products for listing
$products = $productModel->getAll(100, 0);
$successMessage = Session::flash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; color: white; margin-right: 5px; display: inline-block; }
        .btn-primary { background: #007bff; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; }
        .product-img { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
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
        <h1>Quản lý sản phẩm</h1>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <h2><?= $action === 'add' ? 'Thêm sản phẩm mới' : 'Chỉnh sửa sản phẩm' ?></h2>
            <form method="POST" style="background: white; padding: 20px; border-radius: 10px;">
                <input type="hidden" name="action" value="<?= $action ?>">
                <?php if ($product): ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="category_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($product && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= $cat['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" value="<?= $product['name'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" rows="4"><?= $product['description'] ?? '' ?></textarea>
                </div>

                <div class="form-group">
                    <label>Giá (VNĐ)</label>
                    <input type="number" name="price" value="<?= $product['price'] ?? 0 ?>" required>
                </div>

                <div class="form-group">
                    <label>Hình ảnh (tên file trong thư mục assets)</label>
                    <input type="text" name="image" value="<?= $product['image'] ?? '' ?>" placeholder="vd: buy1.jpg">
                </div>

                <div class="form-group">
                    <label>Số lượng (chỉ cho sản phẩm thường)</label>
                    <input type="number" name="stock" value="<?= $product['stock'] ?? 0 ?>">
                </div>

                <div class="form-group">
                    <label>Loại sản phẩm</label>
                    <select name="product_type">
                        <option value="normal" <?= ($product && $product['product_type'] == 'normal') ? 'selected' : '' ?>>Sản phẩm thường</option>
                        <option value="vps" <?= ($product && $product['product_type'] == 'vps') ? 'selected' : '' ?>>VPS</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="status">
                        <option value="available" <?= ($product && $product['status'] == 'available') ? 'selected' : '' ?>>Còn hàng</option>
                        <option value="out_of_stock" <?= ($product && $product['status'] == 'out_of_stock') ? 'selected' : '' ?>>Hết hàng</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-save"></i> <?= $action === 'add' ? 'Thêm sản phẩm' : 'Cập nhật' ?>
                </button>
                <a href="products.php" class="btn btn-primary">Quay lại</a>
            </form>
        <?php else: ?>
            <a href="products.php?action=add" class="btn btn-success"><i class="fa-solid fa-plus"></i> Thêm sản phẩm mới</a>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Loại</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><img src="<?= BASE_URL ?>assets/<?= $p['image'] ?>" class="product-img" alt=""></td>
                            <td><?= $p['name'] ?></td>
                            <td><?= $p['category_name'] ?></td>
                            <td><?= number_format($p['price']) ?>đ</td>
                            <td><?= $p['product_type'] === 'vps' ? 'VPS' : 'Thường' ?></td>
                            <td><?= $p['status'] === 'available' ? 'Còn hàng' : 'Hết hàng' ?></td>
                            <td>
                                <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-primary">Sửa</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Xác nhận xóa?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>
</html>
