<?php
/**
 * User Model
 */

require_once __DIR__ . '/../Config/Database.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Create new user
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} (username, email, password, full_name, role) 
                  VALUES (:username, :email, :password, :full_name, :role)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':role', $data['role']);
        
        return $stmt->execute();
    }

    /**
     * Find user by username or email
     */
    public function findByUsernameOrEmail($usernameOrEmail) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE username = :identifier OR email = :identifier 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':identifier', $usernameOrEmail);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Find user by ID
     */
    public function findById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username) {
        $query = "SELECT id FROM {$this->table} WHERE username = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $query = "SELECT id FROM {$this->table} WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Update user balance
     */
    public function updateBalance($userId, $newBalance) {
        $query = "UPDATE {$this->table} SET balance = :balance WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':balance', $newBalance);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Add balance to user
     */
    public function addBalance($userId, $amount) {
        $query = "UPDATE {$this->table} SET balance = balance + :amount WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Subtract balance from user
     */
    public function subtractBalance($userId, $amount) {
        $query = "UPDATE {$this->table} SET balance = balance - :amount WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        $query = "UPDATE {$this->table} SET full_name = :full_name, email = :email WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Update password
     */
    public function updatePassword($userId, $newPassword) {
        $query = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Link Telegram account
     */
    public function linkTelegram($userId, $telegramId) {
        $query = "UPDATE {$this->table} SET telegram_id = :telegram_id WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':telegram_id', $telegramId);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Get all users (for admin)
     */
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT id, username, email, full_name, role, balance, created_at 
                  FROM {$this->table} 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
