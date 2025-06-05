<?php
// db.php
function load_env($path) {
    $env = [];
    $env_file = $path . '.env';
    if (file_exists($env_file)) {
        $lines = explode("\n", file_get_contents($env_file));
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line && strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                [$key, $value] = explode('=', $line, 2);
                $env[$key] = $value;
            }
        }
    }
    return $env;
}

// Initialize database connection
function connect_db() {
    $env = load_env(__DIR__);
    $host = $env['DB_HOST'] ?? 'localhost';
    $user = $env['DB_USER'] ?? '';
    $pass = $env['DB_PASS'] ?? '';
    $dbname = $env['DB_NAME'] ?? '';

    $mysqli = new mysqli($host, $user, $pass, $dbname);
    if ($mysqli->connect_error) {
        error_log("Database connection failed: " . $mysqli->connect_error);
        return null;
    }
    return $mysqli;
}

// Set global $db on include
global $db;
$db = connect_db();

if ($db === null) {
    error_log("Failed to initialize database connection in db.php.");
}
?>