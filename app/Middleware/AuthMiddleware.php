<?php

namespace App\Middleware;

class AuthMiddleware {
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login.php');
            exit();
        }
    }

    /**
     * Check if user has specific role
     */
    public static function requireRole($required_role) {
        self::isAuthenticated();

        $user_role = $_SESSION['user_role'] ?? null;

        if ($user_role != $required_role) {
            header('HTTP/1.1 403 Forbidden');
            die('Access Denied');
        }
    }

    /**
     * Check if user is admin (role = 1)
     */
    public static function requireAdmin() {
        self::requireRole(1);
    }

    /**
     * Check if user is employee (role = 2)
     */
    public static function requireEmployee() {
        self::requireRole(2);
    }

    /**
     * Check if user is manager (role = 3)
     */
    public static function requireManager() {
        self::requireRole(3);
    }

    /**
     * Check if user is HR (role = 4)
     */
    public static function requireHR() {
        self::requireRole(4);
    }
}
