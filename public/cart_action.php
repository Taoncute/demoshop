<?php
require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Models/Cart.php';

Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}

$cartModel = new Cart();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;
        
        if ($productId) {
            $cartModel->add(Auth::id(), $productId, $quantity);
            Session::flash('success', 'Đã thêm sản phẩm vào giỏ hàng');
        }
        break;
        
    case 'update':
        $cartId = $_POST['cart_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;
        
        if ($cartId && $quantity > 0) {
            $cartModel->updateQuantity($cartId, $quantity);
            Session::flash('success', 'Đã cập nhật giỏ hàng');
        }
        break;
        
    case 'remove':
        $cartId = $_POST['cart_id'] ?? null;
        
        if ($cartId) {
            $cartModel->remove($cartId, Auth::id());
            Session::flash('success', 'Đã xóa sản phẩm khỏi giỏ hàng');
        }
        break;
        
    case 'clear':
        $cartModel->clear(Auth::id());
        Session::flash('success', 'Đã xóa toàn bộ giỏ hàng');
        break;
}

// Redirect back
$redirect = $_POST['redirect'] ?? 'cart.php';
header('Location: ' . BASE_URL . $redirect);
exit;
