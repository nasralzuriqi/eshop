<?php
class Database {
    // Database credentials - **REPLACE WITH YOUR ACTUAL CREDENTIALS**
    private $host = '127.0.0.1';
    private $db_name = 'eshop_db'; // <<< Replace with your database name
    private $username = 'root';
    private $password = ''; // <<< Replace with your database password

    private static $instance = null;
    private $conn;

    /**
     * Private constructor to prevent direct creation of object
     */
    private function __construct() {
        // Set CORS headers and handle pre-flight requests
        if (!headers_sent()) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(204);
            exit();
        }

        $this->conn = null;

        try {
            // Data Source Name (DSN)
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            // Create a new PDO instance
            $this->conn = new PDO($dsn, $this->username, $this->password);

            // Set PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Set default fetch mode to associative array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // Disable emulation of prepared statements for security
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        } catch(PDOException $e) {
            // In a real application, you would log this error, not echo it publicly
            http_response_code(500); // Internal Server Error
            echo json_encode(
                ['status' => 'error', 'message' => 'Database Connection Error: ' . $e->getMessage()]
            );
            // Stop script execution on connection failure
            exit();
        }
    }

    /**
     * The method to get the single instance of the class
     * @return Database The single instance of the Database class
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get the database connection object
     * @return PDO The PDO database connection object
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() { }

    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() { }
}
