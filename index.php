<?php
// Minimal bootstrap
define('ROOT_DIR', __DIR__);
define('DB_FILE', ROOT_DIR . '/db/cms.db');

// Load ErrorHandler first
require_once ROOT_DIR . '/lib/ErrorHandler.php';
ErrorHandler::initialize();

try {
    // Check if setup is needed
    if (!file_exists(DB_FILE) || filesize(DB_FILE) === 0) {
        header('Location: /setup.php');
        exit;
    }

    // Normal routing
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (str_starts_with($_SERVER['REQUEST_URI'], '/api')) {
        require ROOT_DIR . '/api/index.php';
    } else {
        require ROOT_DIR . '/dashboard/index.php';
    }

} catch (Exception $e) {
    ErrorHandler::handleException($e);
}