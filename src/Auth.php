<?php
// src/Auth.php

class Auth {
    /**
     * Ensure session is started.
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Redirects to the login page if not authenticated.
     * Uses dynamic path detection based on script location.
     */
    public static function checkLogin() {
        self::init();
        if (!isset($_SESSION['user_id'])) {
            $base = dirname($_SERVER['SCRIPT_NAME']);
            $loginPath = rtrim($base, '/') . '/login.php';
            header("Location: {$loginPath}");
            exit;
        }
    }

    /**
     * Returns true if current user is marked as admin.
     */
    public static function isAdmin(): bool {
        self::init();
        return ($_SESSION['is_admin'] ?? false) === true;
    }
}
