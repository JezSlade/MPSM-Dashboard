<?php
declare(strict_types=1);
/**
 * token.php
 *
 * Simple endpoint to fetch a valid OAuth2 access token on demand.
 * 
 * Patches applied:
 *  1. **Strict types** declaration.
 *  2. **Error reporting** inherited from config.php.
 *  3. **Guarded includes** with clear logging and JSON on failure.
 *  4. **No closing `?>`** to prevent accidental whitespace.
 */

// ─── 1) Bootstrap application settings and helpers ───────────────────────────
$configPath   = __DIR__ . '/config.php';
$helpersPath  = __DIR__ . '/functions.php';

if (! file_exists($configPath)) {
    error_log('token.php: Missing config.php (bootstrap failure)');
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Server misconfiguration']);
    exit;
}
require_once $configPath;

if (! file_exists($helpersPath)) {
    debug_log('token.php: Missing functions.php', 'ERROR');
    respond_json(['status'=>'error','message'=>'Server misconfiguration']);
    exit;
}
require_once $helpersPath;

// ─── 2) Attempt to fetch the token ────────────────────────────────────────────
try {
    // getAccessToken() handles caching, refresh logic, and logs each step.
    $token = getAccessToken();

    // ─── 3a) On success, return the token ────────────────────────────────────
    respond_json([
        'status'       => 'success',
        'access_token' => $token,
    ]);

} catch (Throwable $e) {
    // ─── 3b) On error, log and return a clean JSON error ────────────────────
    debug_log('Error in token.php: ' . $e->getMessage(), 'ERROR');
    respond_json([
        'status'  => 'error',
        'message' => $e->getMessage(),
    ]);
}

// End of file – no closing PHP tag
