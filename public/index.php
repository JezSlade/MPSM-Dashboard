<?php
require_once __DIR__ . '/../config/bootstrap.php';

$path = $_GET['path'] ?? '';

// LOGIN
if ($path === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (Auth::login($_POST['username'], $_POST['password'])) {
            header('Location: ' . APP_BASE . '/');
            exit;
        }
        $error = 'Invalid credentials';
    }
    include SRC_PATH . '/views/login.php';
    exit;
}

// LOGOUT
if ($path === 'logout') {
    Auth::logout();
    header('Location: ' . APP_BASE . '/?path=login');
    exit;
}

// ADMIN
if (strpos($path, 'admin') === 0) {
    Auth::allow(['developer','admin']);
    include ADMIN_PATH . '/index.php';
    exit;
}

// PUBLIC (guest+)
Auth::allow(['guest','service','sales','dealer','admin','developer']);
$theme = Database::getSetting('theme') ?: 'light';
include THEMES_PATH . "/{$theme}/layout.php";
