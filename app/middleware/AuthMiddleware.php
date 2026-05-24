<?php
/**
 * Authentication Middleware
 * Handles login verification, role checking, and session management
 */

class AuthMiddleware {
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user
     */
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return $_SESSION['user_data'] ?? null;
        }
        return null;
    }
    
    /**
     * Check user role
     */
    public static function hasRole($role) {
        if (!self::isLoggedIn()) {
            return false;
        }
        return $_SESSION['user_data']['role'] === $role || $_SESSION['user_data']['role'] === 'admin';
    }
    
    /**
     * Check multiple roles
     */
    public static function hasAnyRole($roles) {
        if (!self::isLoggedIn()) {
            return false;
        }
        return in_array($_SESSION['user_data']['role'], $roles) || $_SESSION['user_data']['role'] === 'admin';
    }
    
    /**
     * Require login
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'auth/login.php');
            exit;
        }
    }
    
    /**
     * Require specific role
     */
    public static function requireRole($role) {
        self::requireLogin();
        if (!self::hasRole($role)) {
            http_response_code(403);
            die('Access Denied: You do not have permission to access this resource.');
        }
    }
    
    /**
     * Require any of multiple roles
     */
    public static function requireAnyRole($roles) {
        self::requireLogin();
        if (!self::hasAnyRole($roles)) {
            http_response_code(403);
            die('Access Denied: You do not have permission to access this resource.');
        }
    }
    
    /**
     * Prevent double login
     */
    public static function preventDoubleLogin() {
        if (self::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
