<?php
// config.php
// Define the server root path based on the directory of this file
// Assumes config.php is directly in your project's root folder.
define('SERVER_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);

// Define the base path (can be same as SERVER_ROOT_PATH if all includes are relative to root)
// This is often used for includes, like require_once BASE_PATH . 'db.php';
define('BASE_PATH', SERVER_ROOT_PATH);

// Default module to load if none specified in URL
define('DEFAULT_MODULE', 'dashboard');

// Set error reporting for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include essential files
require_once BASE_PATH . 'db.php';
require_once BASE_PATH . 'auth.php';
require_once BASE_PATH . 'functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Establish DB connection globally
$db = connect_db();
if (!$db) {
    error_log("config.php: Failed to connect to database in config.php. Check .env and database server status.");
    // In a production environment, you might want to redirect to an error page
    // or display a more user-friendly message without revealing details.
}

?>