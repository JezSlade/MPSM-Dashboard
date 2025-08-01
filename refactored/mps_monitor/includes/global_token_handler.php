<?php
// PATCHED: global_token_handler.php v3.5 - Global $token Declaration
// ------------------------------------------------------
// • Provides access token retrieval with auto-refresh.
// • Exposes $token globally after validation.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function get_valid_mps_token(): array {
    global $token;

    $token = $_SESSION['mps_token'] ?? null;
    $timestamp = $_SESSION['mps_token_timestamp'] ?? null;

    if ($token && $timestamp) {
        $expiresIn = (int)($token['expires_in'] ?? 0);
        $elapsed = time() - $timestamp;
        $remaining = max($expiresIn - $elapsed, 0);

        if ($remaining < 60 && !empty($token['refresh_token'])) {
            $refreshUrl = dirname(__DIR__) . "/api/get_token.php?refresh=true";
            $refreshResponse = @file_get_contents($refreshUrl);
            if ($refreshResponse) {
                $decoded = json_decode($refreshResponse, true);
                if (is_array($decoded) && isset($decoded['access_token'])) {
                    $_SESSION['mps_token'] = $decoded;
                    $_SESSION['mps_token_timestamp'] = time();
                    $token = $decoded;
                }
            }
        }
    }

    return $token ?: [];
}
?>
