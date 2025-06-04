<?php
/**
 * config/db.php
 *
 * Returns a PDO instance using credentials from .env.
 * You must have DB_HOST, DB_USER, DB_PASS, DB_NAME defined in .env.
 */

// Load and parse .env (each line “KEY=VALUE”)
function loadEnv(string $path): array {
    if (! file_exists($path)) {
        throw new Exception(".env file not found at {$path}");
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (! strpos($line, '=')) continue;
        list($key, $val) = explode('=', $line, 2);
        $env[trim($key)] = trim($val);
    }
    return $env;
}

try {
    $env = loadEnv(__DIR__ . '/../.env');
    if (! isset($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME'])) {
        throw new Exception("Required DB_* variables missing in .env");
    }
    $dsn = sprintf(
      'mysql:host=%s;dbname=%s;charset=utf8mb4',
      $env['DB_HOST'],
      $env['DB_NAME']
    );
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (Exception $e) {
    // If we can’t connect, show a simple error
    echo "<h1>Database Connection Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Export $pdo for other scripts
return $pdo;
