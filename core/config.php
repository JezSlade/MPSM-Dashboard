<?php
// core/config.php
// v2.0.0 — Safe .env parser & DB/API config

// Base paths
define('ENV_FILE', __DIR__ . '/../.env');

// Load and parse .env into an associative array
/**
 * @reusable
 */
function loadEnv(): array
{
    $env = [];
    if (!is_readable(ENV_FILE)) {
        return $env;
    }
    $lines = file(ENV_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        // Split only on first '='
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        [$key, $value] = $parts;
        $key   = trim($key);
        $value = trim($value);
        // Remove surrounding quotes if present
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[0] === $value[strlen($value)-1]) {
            $value = substr($value, 1, -1);
        }
        $env[$key] = $value;
    }
    return $env;
}

// Retrieve an env var or fallback
/**
 * @reusable
 */
function env(string $key, $default = null)
{
    static $loaded;
    if ($loaded === null) {
        $loaded = loadEnv();
    }
    return $loaded[$key] ?? $default;
}

// Define configuration constants
define('DB_HOST',     env('DB_HOST', 'localhost'));
define('DB_NAME',     env('DB_NAME', ''));
define('DB_USER',     env('DB_USER', ''));
define('DB_PASS',     env('DB_PASS', ''));
define('API_CLIENT_ID',     env('CLIENT_ID', ''));
define('API_CLIENT_SECRET', env('CLIENT_SECRET', ''));
define('API_USERNAME',      env('API_USER', ''));
define('API_PASSWORD',      env('API_PASS', ''));
define('API_SCOPE',         env('SCOPE', ''));
define('API_TOKEN_URL',     env('TOKEN_URL', ''));
define('API_BASE_URL',      env('BASE_URL', ''));
define('DEFAULT_ADMIN_USER', env('ADMIN_USER', 'admin'));
define('DEFAULT_ADMIN_PASS', env('ADMIN_PASS', 'changeme'));

// PDO instance
/**
 * @reusable
 */
function get_db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    if (DB_NAME === '') {
        throw new RuntimeException('DB_NAME is not set in .env');
    }
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_NAME
    );
    try {
        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        // Let the caller handle/log the exception
        throw new RuntimeException('Database connection failed: ' . $e->getMessage());
    }
    return $pdo;
}

// Make env() accessible from Config::getEnv()
/**
 * @reusable
 */
class Config
{
    /**
     * Return all environment vars as an associative array.
     */
    public static function getEnv(): array
    {
        return loadEnv();
    }
}
