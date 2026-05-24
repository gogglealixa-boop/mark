<?php
/**
 * Database Configuration
 * XAMPP MySQL Connection
 */

// Database Connection Details
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default is empty
define('DB_NAME', 'employee_management');
define('DB_PORT', 3306);

// PDO Connection String
define('DB_DSN', 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4');

/**
 * Create Database Connection
 */
function getDBConnection() {
    try {
        $pdo = new PDO(
            DB_DSN,
            DB_USER,
            DB_PASS,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            )
        );
        return $pdo;
    } catch (PDOException $e) {
        die('Database Connection Error: ' . $e->getMessage());
    }
}

// Initialize Database Connection
$db = getDBConnection();
