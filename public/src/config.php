<?php
/**
 * config.php
 *
 * Loads environment variables and defines application constants
 * for the MPS Monitor API integration.
 *
 * Requires a .env file with at least:
 *   CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD,
 *   SCOPE, TOKEN_URL, BASE_URL, DEALER_CODE, DEALER_ID,
 *   DEVICE_PAGE_SIZE
 *
 * Reference: .env configuration requirements :contentReference[oaicite:0]{index=0}
 */

// If you're using vlucas/phpdotenv, ensure it's installed and autoloaded:
// require __DIR__ . '/vendor/autoload.php';
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

// Define core API credentials as constants
define('CLIENT_ID',        $_ENV['CLIENT_ID']);
define('CLIENT_SECRET',    $_ENV['CLIENT_SECRET']);
define('USERNAME',         $_ENV['USERNAME']);
define('PASSWORD',         $_ENV['PASSWORD']);
define('SCOPE',            $_ENV['SCOPE']);
define('TOKEN_URL',        $_ENV['TOKEN_URL']);
define('BASE_URL',         rtrim($_ENV['BASE_URL'], '/'));
define('DEALER_CODE',      $_ENV['DEALER_CODE']);
define('DEALER_ID',        $_ENV['DEALER_ID']);
define('DEVICE_PAGE_SIZE', (int) $_ENV['DEVICE_PAGE_SIZE']);

/**
 * DEBUG_MODE flag.
 *
 * Controls whether DebugPanel outputs its logs.
 * Defaults to false if not set in environment.
 *
 * Add to your .env:
 *   DEBUG_MODE=true
 *
 * This follows our logging advice: “Use debugging flag to show raw JSON errors.” :contentReference[oaicite:1]{index=1}
 */
define(
    'DEBUG_MODE',
    isset($_ENV['DEBUG_MODE'])
        ? filter_var($_ENV['DEBUG_MODE'], FILTER_VALIDATE_BOOLEAN)
        : false
);
