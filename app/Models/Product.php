<?php
/**
 * Product Model
 */

require_once __DIR__ . '/../Config/Database.php';

class Product {
    private $conn;
    private $table = 'products';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Get all products with pagination
     */
    public function getAll($limit = 12, $offset = 0, $categoryId = null, $search = null) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->table} p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE 1=1";
        
        if ($categoryId) {
            $query .= " AND p.category_id = :category_id";
        }
        
        if ($search) {
            $query .= " AND p.name LIKE :search";
        }
        
        $query .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if ($categoryId) {
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        }
        
        if ($search) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get product by ID
     */
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->table} p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Create new product
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (category_id, name, description, price, image, stock, product_type, status) 
                  VALUES (:category_id, :name, :description, :price, :image, :stock, :product_type, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':image', $data['image']);
        $stmt->bindParam(':stock', $data['stock']);
        $stmt->bindParam(':product_type', $data['product_type']);
        $stmt->bindParam(':status', $data['status']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Update product
     */
    public function update($id, $data) {
        $query = "UPDATE {$this->table} SET 
                  category_id = :category_id,
                  name = :name,
                  description = :description,
                  price = :price,
                  image = :image,
                  stock = :stock,
                  product_type = :product_type,
                  status = :status
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':image', $data['image']);
        $stmt->bindParam(':stock', $data['stock']);
        $stmt->bindParam(':product_type', $data['product_type']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Delete product
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Update stock
     */
    public function updateStock($id, $quantity) {
        $query = "UPDATE {$this->table} SET stock = stock - :quantity WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Get VPS stock count
     */
    public function getVPSStockCount($productId) {
        $query = "SELECT COUNT(*) as count FROM vps_inventory 
                  WHERE product_id = :product_id AND status = 'available'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * Count total products
     */
    public function count($categoryId = null, $search = null) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        
        if ($categoryId) {
            $query .= " AND category_id = :category_id";
        }
        
        if ($search) {
            $query .= " AND name LIKE :search";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($categoryId) {
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        }
        
        if ($search) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
}
