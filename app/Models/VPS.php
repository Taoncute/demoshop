<?php
/**
 * VPS Inventory Model
 */

require_once __DIR__ . '/../Config/Database.php';

class VPS {
    private $conn;
    private $table = 'vps_inventory';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Get all VPS by product ID
     */
    public function getByProductId($productId) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE product_id = :product_id 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get available VPS by product ID
     */
    public function getAvailableByProductId($productId, $limit = 1) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE product_id = :product_id AND status = 'available' 
                  ORDER BY created_at ASC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get VPS by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Create new VPS
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (product_id, ip_address, username, password, os_info, specs, status) 
                  VALUES (:product_id, :ip_address, :username, :password, :os_info, :specs, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':product_id', $data['product_id']);
        $stmt->bindParam(':ip_address', $data['ip_address']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':os_info', $data['os_info']);
        $stmt->bindParam(':specs', $data['specs']);
        $stmt->bindParam(':status', $data['status']);
        
        return $stmt->execute();
    }

    /**
     * Mark VPS as sold
     */
    public function markAsSold($vpsId, $userId) {
        $query = "UPDATE {$this->table} SET 
                  status = 'sold', 
                  sold_to_user_id = :user_id, 
                  sold_at = NOW() 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':id', $vpsId);
        
        return $stmt->execute();
    }

    /**
     * Update VPS
     */
    public function update($id, $data) {
        $query = "UPDATE {$this->table} SET 
                  ip_address = :ip_address,
                  username = :username,
                  password = :password,
                  os_info = :os_info,
                  specs = :specs,
                  status = :status
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':ip_address', $data['ip_address']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':os_info', $data['os_info']);
        $stmt->bindParam(':specs', $data['specs']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Delete VPS
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Count available VPS for a product
     */
    public function countAvailable($productId) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} 
                  WHERE product_id = :product_id AND status = 'available'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * Get VPS info for order item
     */
    public function getVPSForOrder($vpsId) {
        $query = "SELECT v.*, p.name as product_name 
                  FROM {$this->table} v 
                  LEFT JOIN products p ON v.product_id = p.id 
                  WHERE v.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $vpsId);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
