<?php
require_once BASE_PATH . '.env.loader.php';

$env = load_env(BASE_PATH . '.env');
$db_host = $env['DB_HOST'];
$db_user = $env['DB_USER'];
$db_pass = $env['DB_PASS'];
$db_name = $env['DB_NAME'];
$debug_mode = filter_var($env['DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

if ($debug_mode) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

$db = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$db->set_charset("utf8mb4");
?>