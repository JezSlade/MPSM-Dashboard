<?php
// config.php

// Define SERVER_ROOT_PATH for server-side file includes
// This assumes config.php is in the root of your application (e.g., /mpsm/config.php)
// __DIR__ gives the directory of the current file (config.php)
// realpath() resolves symbolic links and redundant slashes
define('SERVER_ROOT_PATH', realpath(__DIR__ . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

// Define WEB_ROOT_PATH for client-side URLs (e.g., for images, CSS, JavaScript)
// This depends on your actual web server configuration.
// If your application is directly in the domain's root (e.g., example.com/index.php), it's '/'.
// If it's in a subdirectory (e.g., example.com/mpsm/index.php), it's '/mpsm/'.
// You might need to adjust this based on your deployment.

// A common way to calculate WEB_ROOT_PATH dynamically:
$doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/'); // Remove trailing slash from DOCUMENT_ROOT
$current_script_path = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME'])); // Standardize slashes
$app_path = str_replace($doc_root, '', $current_script_path);
$app_dir = dirname($app_path); // Get the directory portion

// If the app is in the root, $app_dir might be '/', otherwise it's like '/mpsm'
// Ensure it ends with a slash if it's not just '/'
if ($app_dir === '/' || $app_dir === '\\' || $app_dir === '.') {
    define('WEB_ROOT_PATH', '/');
} else {
    define('WEB_ROOT_PATH', rtrim($app_dir, '/') . '/');
}

// Alternatively, for simplicity during development, you can hardcode it:
// define('WEB_ROOT_PATH', '/mpsm/'); // If your app is at http://yourdomain.com/mpsm/

// Error reporting (useful for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Other configurations like default timezone, etc.
date_default_timezone_set('America/New_York');

?>