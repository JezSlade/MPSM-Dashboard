<?php
// USAGE: To get your token in any script:
// require_once '/mps_monitor/includes/global_token_handler.php';
// $token = get_valid_mps_token();
// $accessToken = $token['access_token'] ?? null;

// PATCHED: global_token_handler.php v3.6 - Token Autorefresh Centralized
// ------------------------------------------------------
// • Ensures token refreshes before expiry
// • Centralized logic for all API calls

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PHP Debugging Lines - START
// Enable all error reporting for development purposes.
// This helps in identifying and debugging issues quickly.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

function get_valid_mps_token(): array {
    global $token;

    $token     = $_SESSION['mps_token'] ?? null;
    $timestamp = $_SESSION['mps_token_timestamp'] ?? null;

    if ($token && $timestamp) {
        $expiresIn = (int)($token['expires_in'] ?? 0);
        $elapsed   = time() - $timestamp;
        $remaining = max($expiresIn - $elapsed, 0);

        if ($remaining >= 60) {
            return $token;
        }
    }

    // If token missing or near expiration, refresh
    $refreshUrl = dirname(__DIR__) . "/api/get_token.php" .
        ((isset($token['refresh_token']) && $token['refresh_token']) ? '?refresh=true' : '');

    $refreshContext = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => 5,
            'header'  => "Accept: application/json"
        ]
    ]);

    $response = @file_get_contents($refreshUrl, false, $refreshContext);
    $decoded  = json_decode($response, true);

    if (is_array($decoded) && isset($decoded['data']['access_token'])) {
        $_SESSION['mps_token'] = $decoded['data'];
        $_SESSION['mps_token_timestamp'] = time();
        $token = $decoded['data'];
        return $token;
    }

    return $token ?: [];
}
?>
