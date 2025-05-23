<?php
/**
 * Authentication functions for MPSM Dashboard
 */
class Auth {
    /**
     * Check if user is logged in
     * @return bool
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Require login to access a page
     * Redirects to login page if not logged in
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    /**
     * Get current user ID
     * @return int|null
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current username
     * @return string|null
     */
    public static function getCurrentUser() {
        return $_SESSION['username'] ?? null;
    }
    
    /**
     * Get current user role
     * @return string|null
     */
    public static function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
    
    /**
     * Check if current user is an admin
     * @return bool
     */
    public static function isAdmin() {
        return self::getCurrentUserRole() === 'admin';
    }
    
    /**
     * Check if current user is a developer
     * @return bool
     */
    public static function isDeveloper() {
        return self::getCurrentUserRole() === 'developer' || self::getCurrentUserRole() === 'admin';
    }
    
    /**
     * Check if user has a specific permission
     * @param string $permission
     * @return bool
     */
    public static function hasPermission($permission) {
        global $db;
        
        if (!self::isLoggedIn()) {
            return false;
        }
        
        // Admins have all permissions
        if (self::isAdmin()) {
            return true;
        }
        
        // Check user permissions
        $user_id = self::getCurrentUserId();
        $stmt = $db->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = ?");
        $stmt->execute([$user_id, $permission]);
        
        return $stmt->fetchColumn() !== false;
    }
    
    /**
     * Authenticate user
     * @param string $username
     * @param string $password
     * @return bool
     */
    public static function authenticate($username, $password) {
        global $db;
        
        $stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        // Clear all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
    }
}
