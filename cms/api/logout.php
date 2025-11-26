<?php
/**
 * Logout API
 */

require '../config.php';
require '../functions.php';

requireAuth();
// Enforce POST-only semantics to prevent accidental logouts via GET
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    jsonError('Method Not Allowed');
    exit;
}

logoutUser();
jsonSuccess(['message' => 'Logged out successfully']);

/*
CHANGELOG
2025-11-26 Codex
- Secured logout endpoint to require POST requests, preventing accidental logouts by GET navigation.
*/
