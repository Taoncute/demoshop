<?php
/**
 * Order Model
 */

require_once __DIR__ . '/../Config/Database.php';

class Order {
    private $conn;
    private $table = 'orders';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Create new order
     */
    public function create($userId, $totalAmount, $paymentMethod = 'balance') {
        // Generate unique order code
        $orderCode = 'SHOP_' . strtoupper(substr(md5(uniqid()), 0, 10));
        
        $query = "INSERT INTO {$this->table} 
                  (user_id, order_code, total_amount, payment_method, payment_status, order_status) 
                  VALUES (:user_id, :order_code, :total_amount, :payment_method, :payment_status, :order_status)";
        
        $stmt = $this->conn->prepare($query);
        
        $paymentStatus = $paymentMethod === 'balance' ? 'completed' : 'pending';
        $orderStatus = 'pending';
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':order_code', $orderCode);
        $stmt->bindParam(':total_amount', $totalAmount);
        $stmt->bindParam(':payment_method', $paymentMethod);
        $stmt->bindParam(':payment_status', $paymentStatus);
        $stmt->bindParam(':order_status', $orderStatus);
        
        if ($stmt->execute()) {
            return [
                'id' => $this->conn->lastInsertId(),
                'order_code' => $orderCode
            ];
        }
        
        return false;
    }

    /**
     * Add order item
     */
    public function addItem($orderId, $productId, $productName, $quantity, $price, $vpsId = null) {
        $query = "INSERT INTO order_items 
                  (order_id, product_id, product_name, quantity, price, vps_id) 
                  VALUES (:order_id, :product_id, :product_name, :quantity, :price, :vps_id)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':product_name', $productName);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':vps_id', $vpsId);
        
        return $stmt->execute();
    }

    /**
     * Get order by ID
     */
    public function getById($orderId) {
        $query = "SELECT o.*, u.username, u.email 
                  FROM {$this->table} o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  WHERE o.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $orderId);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Get order by code
     */
    public function getByCode($orderCode) {
        $query = "SELECT * FROM {$this->table} WHERE order_code = :order_code LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_code', $orderCode);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Get order items
     */
    public function getItems($orderId) {
        $query = "SELECT oi.*, p.image, p.product_type 
                  FROM order_items oi 
                  LEFT JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get user orders
     */
    public function getUserOrders($userId, $limit = 50, $offset = 0) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get all orders (for admin)
     */
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT o.*, u.username 
                  FROM {$this->table} o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  ORDER BY o.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($orderId, $status) {
        $query = "UPDATE {$this->table} SET payment_status = :status WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $orderId);
        
        return $stmt->execute();
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($orderId, $status) {
        $query = "UPDATE {$this->table} SET order_status = :status WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $orderId);
        
        return $stmt->execute();
    }

    /**
     * Complete order (mark as completed)
     */
    public function completeOrder($orderId) {
        $query = "UPDATE {$this->table} 
                  SET payment_status = 'completed', order_status = 'completed' 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $orderId);
        
        return $stmt->execute();
    }
}
