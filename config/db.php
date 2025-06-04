<?php
/**
 * config/db.php
 * Returns a PDO instance using credentials from .env
 */

// Only declare loadEnvFile() if it doesn’t already exist.
if (! function_exists('loadEnvFile')) {
    function loadEnvFile(string $path): array {
        $env = [];
        if (!file_exists($path)) {
            return $env;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip blank lines or comments
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            if (!strpos($line, '=')) {
                continue;
            }
            list($key, $val) = explode('=', $line, 2);
            $env[trim($key)] = trim($val);
        }
        return $env;
    }
}

try {
    // Load .env into a simple array
    $env = loadEnvFile(__DIR__ . '/../.env');
    $host = $env['DB_HOST'] ?? 'localhost';
    $db   = $env['DB_NAME'] ?? '';
    $user = $env['DB_USER'] ?? '';
    $pass = $env['DB_PASS'] ?? '';

    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
} catch (Exception $e) {
    echo "<h1>Database Connection Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>
