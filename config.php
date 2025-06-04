<?php
// Define the base path of the project
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Load environment variables
function load_env($file) {
    $env_file = BASE_PATH . $file;
    if (!file_exists($env_file)) {
        die("Environment file not found: $env_file");
    }
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv("$key=$value");
        }
    }
}
load_env('.env');
?>