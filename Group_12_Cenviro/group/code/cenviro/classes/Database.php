<?php
/*This class handles all database connections.*/
class Database {
    /*Database connection settings - loaded from config file*/
    private $host;
    private $username;
    private $password; 
    private $database;
    
    public $connection;
    
    /*Constructor - automatically runs when we create a new Database object*/
    public function __construct() {
        // Load database configuration
        if (file_exists(__DIR__ . '/../config/db_config.php')) {
            require_once __DIR__ . '/../config/db_config.php';
            $this->host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $this->username = defined('DB_USERNAME') ? DB_USERNAME : 'root';
            $this->password = defined('DB_PASSWORD') ? DB_PASSWORD : '';
            $this->database = defined('DB_NAME') ? DB_NAME : 'sustainedge';
        } else {
            // Fallback to default local settings
            $this->host = "localhost";
            $this->username = "root";
            $this->password = "";
            $this->database = "sustainedge";
        }
        $this->connect();
    }
    
    /*Connect to the database*/ 
    private function connect() {
        $this->connection = mysqli_connect($this->host, $this->username, $this->password);
        
        if (!$this->connection) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
        // Check if database exists, if not create it
        $db_check = mysqli_select_db($this->connection, $this->database);
        if (!$db_check) {
            mysqli_query($this->connection, "CREATE DATABASE IF NOT EXISTS {$this->database} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            mysqli_select_db($this->connection, $this->database);
        }
        
        // Set the character encoding to support special characters
        mysqli_set_charset($this->connection, "utf8");
    }
    
    /*Get the database connection*/
    public function getConnection() {
        return $this->connection;
    }
    
    /*Close the database connection*/
    public function close() {
        if ($this->connection) {
            mysqli_close($this->connection);
        }
    }
}
?>
