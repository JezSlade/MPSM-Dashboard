<?php
// File: /mps_monitor/includes/global_token_handler.php
// Version: v3.2 - Fixed constant redefinition & ensured MPS_API_* constants
// ------------------------------------------------------
// • Now conditionally defines LOG_* constants if not already defined.
// • Explicitly verifies & defines missing MPS_API_* constants from .env if needed.
// • Prevents undefined constant errors in get_token.php.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure LOG_* constants not redefined
if (!defined('LOG_INFO')) define('LOG_INFO', 6);
if (!defined('LOG_WARNING')) define('LOG_WARNING', 4);
if (!defined('LOG_DEBUG')) define('LOG_DEBUG', 7);

// Load config only if not loaded
$configPath = dirname(__DIR__) . '/config/mps_config.php';
if (file_exists($configPath) && (!defined('MPS_API_USERNAME') || !defined('MPS_API_CLIENT_ID'))) {
    require_once $configPath;
}

// Fallback: parse .env if constants still not defined
$envPath = dirname(__DIR__, 2) . '/.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    if (!defined('MPS_API_USERNAME') && isset($env['MPS_API_USERNAME'])) define('MPS_API_USERNAME', $env['MPS_API_USERNAME']);
    if (!defined('MPS_API_PASSWORD') && isset($env['MPS_API_PASSWORD'])) define('MPS_API_PASSWORD', $env['MPS_API_PASSWORD']);
    if (!defined('MPS_API_CLIENT_ID') && isset($env['MPS_API_CLIENT_ID'])) define('MPS_API_CLIENT_ID', $env['MPS_API_CLIENT_ID']);
    if (!defined('MPS_API_SECRET') && isset($env['MPS_API_SECRET'])) define('MPS_API_SECRET', $env['MPS_API_SECRET']);
    if (!defined('MPS_API_SCOPE') && isset($env['MPS_API_SCOPE'])) define('MPS_API_SCOPE', $env['MPS_API_SCOPE']);
    if (!defined('MPS_TOKEN_URL') && isset($env['MPS_TOKEN_URL'])) define('MPS_TOKEN_URL', $env['MPS_TOKEN_URL']);
}

function get_valid_mps_token(): array {
    $token = $_SESSION['mps_token'] ?? null;
    $timestamp = $_SESSION['mps_token_timestamp'] ?? null;

    if ($token && $timestamp) {
        $expiresIn = (int)$token['expires_in'];
        $elapsed = time() - $timestamp;
        $remaining = max($expiresIn - $elapsed, 0);

        if ($remaining < 60 && !empty($token['refresh_token'])) {
            $refreshUrl = "/mps_monitor/api/get_token.php?refresh=true";
            $refreshResponse = @file_get_contents($refreshUrl);
            if ($refreshResponse) {
                $decoded = json_decode($refreshResponse, true);
                if (isset($decoded['data']['access_token'])) {
                    $_SESSION['mps_token'] = $decoded['data'];
                    $_SESSION['mps_token_timestamp'] = time();
                    $token = $_SESSION['mps_token'];
                }
            }
        }
    } else {
        $tokenUrl = "/mps_monitor/api/get_token.php";
        $tokenResponse = @file_get_contents($tokenUrl);
        if ($tokenResponse) {
            $decoded = json_decode($tokenResponse, true);
            if (isset($decoded['data']['access_token'])) {
                $_SESSION['mps_token'] = $decoded['data'];
                $_SESSION['mps_token_timestamp'] = time();
                $token = $_SESSION['mps_token'];
            }
        }
    }

    return $token ?? [];
}
?>
