<?php
/**
 * Database Configuration for AquaAware
 */

// Database configuration constants
define('DB_HOST', 'localhost');        
define('DB_NAME', 'aquaware');
define('DB_USER', 'root');             
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');



/**
 * Database Connection Class
 */
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $connection = null;
    
    /**
     * Get database connection using PDO
     */
    public function getConnection() {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->charset
                ];
                
                $this->connection = new PDO($dsn, $this->username, $this->password, $options);
                
            } catch(PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new Exception("Database connection failed. Please check your configuration.");
            }
        }
        
        return $this->connection;
    }
    
    /**
     * Get MySQLi connection (alternative method)
     */
    public function getMySQLiConnection() {
        try {
            $connection = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            if ($connection->connect_error) {
                throw new Exception("Connection failed: " . $connection->connect_error);
            }
            
            // Set charset
            $connection->set_charset($this->charset);
            
            return $connection;
            
        } catch(Exception $e) {
            error_log("MySQLi connection error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your configuration.");
        }
    }
    
    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->query("SELECT 1");
            return ['success' => true, 'message' => 'Database connection successful'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

/**
 * Global database instance
 */
function getDB() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->getConnection();
}

/**
 * Initialize database connection and test it
 */
try {
    // Test the connection when this file is included
    $db = new Database();
    $test_result = $db->testConnection();
    
    if (!$test_result['success']) {
        error_log("Database configuration error: " . $test_result['message']);
        // In development, you might want to display the error
        // In production, log it and show a generic message
    }
    
} catch(Exception $e) {
    error_log("Database initialization error: " . $e->getMessage());
}

// Set error reporting for development (remove in production)
if (defined('DEVELOPMENT') && DEVELOPMENT === true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
?>