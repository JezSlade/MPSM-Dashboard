<?php
define('ROOT_DIR', __DIR__);
define('DB_FILE', ROOT_DIR . '/db/cms.db');
define('SETUP_COMPLETE_FILE', ROOT_DIR . '/.setup_complete');

// Load ErrorHandler first
require_once ROOT_DIR . '/config.php';
ErrorHandler::initialize();

try {
    // If setup hasn't completed, redirect to setup
    if (!file_exists(SETUP_COMPLETE_FILE)) {
        header('Location: /setup.php');
        exit;
    }

    // Normal routing
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
        require ROOT_DIR . '/api/index.php';
    } else {
        require ROOT_DIR . '/dashboard/index.php';
    }

} catch (Exception $e) {
    ErrorHandler::handleException($e);
}
