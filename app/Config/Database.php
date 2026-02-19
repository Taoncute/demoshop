<?php
/**
 * Database Configuration
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Support Railway MySQL environment variables
        if (getenv('MYSQLHOST')) {
            // Railway MySQL
            $this->host = getenv('MYSQLHOST');
            $this->db_name = getenv('MYSQLDATABASE') ?: 'railway';
            $this->username = getenv('MYSQLUSER') ?: 'root';
            $this->password = getenv('MYSQLPASSWORD') ?: '';
            $port = getenv('MYSQLPORT') ?: '3306';
            $this->host = $this->host . ':' . $port;
        } else {
            // Local development
            $this->host = 'localhost';
            $this->db_name = 'shop_online';
            $this->username = 'shopuser';
            $this->password = 'shop123';
        }
    }

    /**
     * Get database connection
     */
    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
