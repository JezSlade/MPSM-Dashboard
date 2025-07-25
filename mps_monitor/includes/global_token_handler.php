<?php
// PATCHED: global_token_handler.php v3.4 - Final Unified Version
// ------------------------------------------------------
// • Config-independent (does not load or define any constants).
// • Solely responsible for token retrieval and refresh.
// • Fully cohesive with get_token.php v2.3 and widgets.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function get_valid_mps_token(): array {
    $token = $_SESSION['mps_token'] ?? null;
    $timestamp = $_SESSION['mps_token_timestamp'] ?? null;

    if ($token && $timestamp) {
        $expiresIn = (int)($token['expires_in'] ?? 0);
        $elapsed = time() - $timestamp;
        $remaining = max($expiresIn - $elapsed, 0);

        if ($remaining < 60 && !empty($token['refresh_token'])) {
            $refreshUrl = "/mps_monitor/api/get_token.php?refresh=true";
            $refreshResponse = @file_get_contents($refreshUrl);
            if ($refreshResponse) {
                $decoded = json_decode($refreshResponse, true);
                if (!empty($decoded['data']['access_token'])) {
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
            if (!empty($decoded['data']['access_token'])) {
                $_SESSION['mps_token'] = $decoded['data'];
                $_SESSION['mps_token_timestamp'] = time();
                $token = $_SESSION['mps_token'];
            }
        }
    }

    return $token ?? [];
}
?>