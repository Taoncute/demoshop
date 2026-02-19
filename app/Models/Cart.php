<?php
/**
 * Cart Model
 */

require_once __DIR__ . '/../Config/Database.php';

class Cart {
    private $conn;
    private $table = 'cart';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Get cart items for user
     */
    public function getByUserId($userId) {
        $query = "SELECT c.*, p.name, p.price, p.image, p.product_type, p.status 
                  FROM {$this->table} c 
                  LEFT JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = :user_id 
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Add item to cart
     */
    public function add($userId, $productId, $quantity = 1) {
        // Check if item already exists
        $query = "SELECT id, quantity FROM {$this->table} 
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            return $this->updateQuantity($existing['id'], $newQuantity);
        } else {
            // Insert new item
            $query = "INSERT INTO {$this->table} (user_id, product_id, quantity) 
                      VALUES (:user_id, :product_id, :quantity)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':product_id', $productId);
            $stmt->bindParam(':quantity', $quantity);
            
            return $stmt->execute();
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity($cartId, $quantity) {
        $query = "UPDATE {$this->table} SET quantity = :quantity WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $cartId);
        
        return $stmt->execute();
    }

    /**
     * Remove item from cart
     */
    public function remove($cartId, $userId) {
        $query = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $cartId);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Clear cart for user
     */
    public function clear($userId) {
        $query = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Get cart total
     */
    public function getTotal($userId) {
        $query = "SELECT SUM(c.quantity * p.price) as total 
                  FROM {$this->table} c 
                  LEFT JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Count cart items
     */
    public function count($userId) {
        $query = "SELECT SUM(quantity) as count FROM {$this->table} WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}
