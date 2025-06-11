<?php
/**
 * Load .env into $_ENV (unchanged)
 */
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $t = trim($line);
        if (str_starts_with($t, '#') || !str_contains($t, '=')) continue;
        [$name, $value] = explode('=', $t, 2);
        $_ENV[trim($name)] = trim($value);
    }
}
loadEnv(__DIR__ . '/../.env');

/**
 * Hard-coded Dealer Code
 */
define('DEALER_CODE', 'NY06AGDWUQ');  // ← now fixed, no more env misses

/**
 * The rest of your config…
 */
define('API_BASE_URL', rtrim($_ENV['BASE_URL'] ?? '', '/'));
define('CLIENT_ID',      $_ENV['CLIENT_ID']       ?? '');
define('CLIENT_SECRET',  $_ENV['CLIENT_SECRET']   ?? '');
define('USERNAME',       $_ENV['USERNAME']        ?? '');
define('PASSWORD',       $_ENV['PASSWORD']        ?? '');
define('SCOPE',          $_ENV['SCOPE']           ?? '');
define('TOKEN_URL',      $_ENV['TOKEN_URL']       ?? '');
define('DEALER_ID',      $_ENV['DEALER_ID']       ?? '');
define('DEVICE_PAGE_SIZE', intval($_ENV['DEVICE_PAGE_SIZE'] ?? 50));
