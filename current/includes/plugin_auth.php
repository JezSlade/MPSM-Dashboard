<?php
// includes/plugin_auth.php
// -------------------------------------------------------------------
// Verifies incoming ChatGPT plugin bearer token before proceeding.
// -------------------------------------------------------------------

/**
 * Reads the Authorization header and compares it to PLUGIN_BEARER_TOKEN.
 * Exits with 401 if missing or invalid.
 */
function verify_plugin_bearer(): void
{
    // Ensure the env parser loaded PLUGIN_BEARER_TOKEN
    if (!defined('PLUGIN_BEARER_TOKEN') || PLUGIN_BEARER_TOKEN === '') {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error'   => 'Server misconfiguration',
            'message' => 'PLUGIN_BEARER_TOKEN is not set'
        ]);
        exit;
    }

    // Fetch Authorization header
    $auth = $_SERVER['HTTP_AUTHORIZATION']
          ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');

    if (stripos($auth, 'Bearer ') !== 0) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error'=>'Unauthorized: missing token']);
        exit;
    }

    $incoming = trim(substr($auth, 7));
    if ($incoming !== PLUGIN_BEARER_TOKEN) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error'=>'Unauthorized: invalid token']);
        exit;
    }
}
