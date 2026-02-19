<?php
require_once __DIR__ . '/../../app/Config/Config.php';
require_once __DIR__ . '/../../app/Core/Session.php';
require_once __DIR__ . '/../../app/Core/Auth.php';
require_once __DIR__ . '/../../app/Core/Validator.php';
require_once __DIR__ . '/../../app/Models/Category.php';

Auth::requireAdmin();

$categoryModel = new Category();
$action = $_GET['action'] ?? 'list';
$categoryId = $_GET['id'] ?? null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $data = [
            'name' => Validator::sanitize($_POST['name'] ?? ''),
            'description' => Validator::sanitize($_POST['description'] ?? '')
        ];

        if ($_POST['action'] === 'add') {
            if ($categoryModel->create($data)) {
                Session::flash('success', 'Thêm danh mục thành công');
                header('Location: categories.php');
                exit;
            }
        } elseif ($_POST['action'] === 'edit') {
            if ($categoryModel->update($_POST['category_id'], $data)) {
                Session::flash('success', 'Cập nhật danh mục thành công');
                header('Location: categories.php');
                exit;
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($categoryModel->delete($_POST['category_id'])) {
                Session::flash('success', 'Xóa danh mục thành công');
                header('Location: categories.php');
                exit;
            }
        }
    }
}

$category = null;
if ($action === 'edit' && $categoryId) {
    $category = $categoryModel->getById($categoryId);
}

$categories = $categoryModel->getAll();
$successMessage = Session::flash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; color: white; margin-right: 5px; display: inline-block; border: none; cursor: pointer; }
        .btn-primary { background: #007bff; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; }
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
        <h1>Quản lý danh mục</h1>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <h2><?= $action === 'add' ? 'Thêm danh mục mới' : 'Chỉnh sửa danh mục' ?></h2>
            <form method="POST" style="background: white; padding: 20px; border-radius: 10px; max-width: 600px;">
                <input type="hidden" name="action" value="<?= $action ?>">
                <?php if ($category): ?>
                    <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Tên danh mục</label>
                    <input type="text" name="name" value="<?= $category['name'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" rows="4"><?= $category['description'] ?? '' ?></textarea>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-save"></i> <?= $action === 'add' ? 'Thêm danh mục' : 'Cập nhật' ?>
                </button>
                <a href="categories.php" class="btn btn-primary">Quay lại</a>
            </form>
        <?php else: ?>
            <a href="categories.php?action=add" class="btn btn-success">
                <i class="fa-solid fa-plus"></i> Thêm danh mục mới
            </a>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Mô tả</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="4" style="text-align: center;">Chưa có danh mục nào</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= $cat['id'] ?></td>
                                <td><?= htmlspecialchars($cat['name']) ?></td>
                                <td><?= htmlspecialchars($cat['description']) ?></td>
                                <td>
                                    <a href="categories.php?action=edit&id=<?= $cat['id'] ?>" class="btn btn-primary">Sửa</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Xác nhận xóa?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
