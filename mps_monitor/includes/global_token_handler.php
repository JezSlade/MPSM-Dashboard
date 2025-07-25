<?php
// File: /mps_monitor/includes/global_token_handler.php
// Version: v3.1 - Fixed missing config include (MPS_API_* constants)
// ------------------------------------------------------
// • Added conditional inclusion of mps_config.php to define required constants.
// • Ensures get_token.php and all token logic have required MPS_API_* values.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure MPS_API_* constants are loaded
$configPath = dirname(__DIR__) . '/config/mps_config.php';
if (file_exists($configPath) && !defined('MPS_API_USERNAME')) {
    require_once $configPath;
}

function get_valid_mps_token(): array {
    $token = $_SESSION['mps_token'] ?? null;
    $timestamp = $_SESSION['mps_token_timestamp'] ?? null;

    if ($token && $timestamp) {
        $expiresIn = (int)$token['expires_in'];
        $elapsed = time() - $timestamp;
        $remaining = max($expiresIn - $elapsed, 0);

        // Auto-refresh silently if less than 60 seconds remain
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
        // No token present; fetch a new one directly
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
