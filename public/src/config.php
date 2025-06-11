<?php
/**
 * src/config.php
 *
 * Loads environment variables from a .env file (project root),
 * then defines them as PHP constants. Uses a simple parser,
 * no external dependencies. Throws clear errors if required
 * vars are missing, avoiding PHP “undefined index” warnings.
 *
 * Project structure assumption:
 *  ├─ .env
 *  └─ src/
 *      └─ config.php  ← this file
 */

/**
 * 1) MANUAL .env LOADER
 * Reads each non-comment, non-empty line of "../.env",
 * splits on the first "=", trims quotes, and populates
 * both $_ENV and putenv() so our env() helper can see them.
 */
$envPath = __DIR__ . '/../.env';
if (is_readable($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and malformed lines
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        // Strip surrounding quotes if present
        $value = trim($value, "\"'");
        $_ENV[$name] = $value;
        putenv("{$name}={$value}");
    }
}

/**
 * 2) ENV HELPER
 * Usage: env('KEY', $default = null)
 * - Returns $_ENV or getenv() if set.
 * - If not set and $default provided, returns $default.
 * - If not set and no default, throws to prevent warnings.
 */
function env(string $key, $default = null)
{
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }
    $val = getenv($key);
    if ($val !== false) {
        return $val;
    }
    if ($default !== null) {
        return $default;
    }
    throw new RuntimeException("Required environment variable '{$key}' not defined in .env or system environment.");
}

/**
 * 3) DEFINE CONSTANTS
 * Now we pull each required setting via env(). If any
 * is missing, the RuntimeException above will surface
 * immediately, avoiding undefined‐index warnings.
 */
define('CLIENT_ID',      env('CLIENT_ID'));
define('CLIENT_SECRET',  env('CLIENT_SECRET'));
define('USERNAME',       env('USERNAME'));
define('PASSWORD',       env('PASSWORD'));
define('SCOPE',          env('SCOPE'));
define('TOKEN_URL',      env('TOKEN_URL'));
define('BASE_URL',       rtrim(env('BASE_URL'), '/'));          // ensure no trailing slash
define('DEALER_CODE',    env('DEALER_CODE'));
define('DEALER_ID',      env('DEALER_ID'));
define('DEVICE_PAGE_SIZE', (int) env('DEVICE_PAGE_SIZE', 25));  // default to 25 if unspecified

/**
 * DEBUG_MODE toggle
 * Controls whether DebugPanel::log() actually echoes logs.
 * Default: false if not present.
 */
define(
    'DEBUG_MODE',
    filter_var(env('DEBUG_MODE', 'false'), FILTER_VALIDATE_BOOLEAN)
);
