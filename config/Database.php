<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct() {
        $this->host = env('DB_HOST', 'localhost');
        $this->db_name = env('DB_DATABASE', 'mark_db');
        $this->username = env('DB_USERNAME', 'root');
        $this->password = env('DB_PASSWORD', '');
        $this->port = env('DB_PORT', 3306);
    }

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die('Database Connection Error: ' . $e->getMessage());
        }

        return $this->conn;
    }

    public static function getInstance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance->connect();
    }
}

// Create global database instance
$GLOBALS['db'] = Database::getInstance();
