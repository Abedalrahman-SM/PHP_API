<?php
// Database connection settings
define('DB_HOST', 'localhost');       // Host name
define('DB_USER', 'root');            // Username
define('DB_PASS', '');                // Password
define('DB_NAME', 'ecommmerc'); // Database name

// Create database connection using MySQLi
class Db {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    public $conn;

    public function __construct() {
        $this->connectDB();
    }

    // Connect to the database
    private function connectDB() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }

    // Return the connection to be used elsewhere
    public function getConnection() {
        return $this->conn;
    }

    // Close the database connection
    public function closeConnection() {
        $this->conn->close();
    }
}
?>
