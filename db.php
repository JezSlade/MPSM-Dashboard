<?php
// db.php
function load_env($path) {
    $env = [];
    $env_file = $path . '/.env';
    error_log("Attempting to load .env file from: $env_file");
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line && strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                [$key, $value] = array_map('trim', explode('=', $line, 2));
                $env[$key] = $value;
                error_log("Loaded env variable: $key=" . ($key === 'DB_PASS' ? '****' : $value));
            }
        }
    } else {
        error_log("No .env file found at $env_file.");
    }
    return $env;
}

// Initialize database connection
function connect_db() {
    $dir = __DIR__;
    error_log("Base directory for .env load: $dir");
    $env = load_env($dir);
    $host = $env['DB_HOST'] ?? 'localhost';
    $user = $env['DB_USER'] ?? '';
    $pass = $env['DB_PASS'] ?? '';
    $dbname = $env['DB_NAME'] ?? '';

    error_log("Database connection attempt: host=$host, user=$user, dbname=$dbname");
    if (empty($user) || empty($pass) || empty($dbname)) {
        error_log("Missing database credentials: DB_USER=$user, DB_PASS=****, DB_NAME=$dbname");
        return null;
    }

    $mysqli = new mysqli($host, $user, $pass, $dbname);
    if ($mysqli->connect_error) {
        error_log("Database connection failed: " . $mysqli->connect_error);
        return null;
    }
    error_log("Database connection successful.");
    return $mysqli;
}

// Set global $db on include
global $db;
$db = connect_db();

if ($db === null) {
    error_log("Database connection not established in db.php.");
}
?>