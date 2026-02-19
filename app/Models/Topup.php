<?php
/**
 * Topup Transaction Model
 */

require_once __DIR__ . '/../Config/Database.php';

class Topup {
    private $conn;
    private $table = 'topup_transactions';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Create topup transaction
     */
    public function create($userId, $amount) {
        // Generate unique transaction code
        $transactionCode = 'TOPUP_' . strtoupper(substr(md5(uniqid()), 0, 10));
        
        $query = "INSERT INTO {$this->table} 
                  (user_id, transaction_code, amount, status, payment_method) 
                  VALUES (:user_id, :transaction_code, :amount, :status, :payment_method)";
        
        $stmt = $this->conn->prepare($query);
        
        $status = 'pending';
        $paymentMethod = 'vietqr';
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':transaction_code', $transactionCode);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':payment_method', $paymentMethod);
        
        if ($stmt->execute()) {
            return [
                'id' => $this->conn->lastInsertId(),
                'transaction_code' => $transactionCode
            ];
        }
        
        return false;
    }

    /**
     * Get topup by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Get topup by transaction code
     */
    public function getByCode($transactionCode) {
        $query = "SELECT * FROM {$this->table} WHERE transaction_code = :code LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $transactionCode);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Get user topup transactions
     */
    public function getUserTopups($userId, $limit = 50, $offset = 0) {
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
     * Update status
     */
    public function updateStatus($id, $status) {
        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Complete topup
     */
    public function complete($id) {
        return $this->updateStatus($id, 'completed');
    }

    /**
     * Get all topups (for admin)
     */
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT t.*, u.username 
                  FROM {$this->table} t 
                  LEFT JOIN users u ON t.user_id = u.id 
                  ORDER BY t.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
