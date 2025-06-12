<?php
/**
 * token.php
 *
 * Simple endpoint to fetch a valid OAuth2 access token on demand.
 * 
 * Usage:
 *   GET /token.php
 * 
 * Returns JSON:
 *   {
 *     "status": "success",
 *     "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9…"
 *   }
 * or on error:
 *   {
 *     "status": "error",
 *     "message": "Detailed error message"
 *   }
 *
 * This file:
 *  1. Boots your config (paths, env, debug settings)
 *  2. Includes helpers (debug_log, getAccessToken, respond_json)
 *  3. Attempts to get a token (cached or new)
 *  4. Sends a clean JSON response and exits
 */

// 1) Bootstrap application settings and helpers
require_once __DIR__ . '/config.php';      // loads DEBUG_MODE, APP_BASE_PATH, paths, error_reporting, session_start(), etc.
require_once __DIR__ . '/functions.php';   // loads debug_log(), getAccessToken(), respond_json(), output buffering, etc.

// 2) Attempt to fetch the token
try {
    // Calls loadCachedToken() or requestNewToken(), with debug_log entries for each step
    $token = getAccessToken();

    // 3a) On success, return the token
    respond_json([
        'status'       => 'success',
        'access_token' => $token,
    ]);

} catch (Throwable $e) {
    // 3b) On any error, log it and return an error JSON
    debug_log('Error in token.php: ' . $e->getMessage(), 'ERROR');

    respond_json([
        'status'  => 'error',
        'message' => $e->getMessage(),
    ]);
}
