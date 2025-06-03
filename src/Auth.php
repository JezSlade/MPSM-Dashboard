<?php
// src/Auth.php
class Auth {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    public static function checkLogin() {
        self::init();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
    }
    public static function isAdmin(): bool {
        self::init();
        return ($_SESSION['is_admin'] ?? false) === true;
    }
}
