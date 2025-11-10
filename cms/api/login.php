<?php
/**
 * Login API
 * Following Engineering Standards Rule 13: One Responsibility Per File
 */

require '../config.php';
require '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

// Try multiple input methods (fixes php://input issues with some server configs)
// IMPORTANT: Read php://input only ONCE as stream can be exhausted
$rawInput = @file_get_contents('php://input');
$data = [];

// Method 1: JSON from php://input (most common)
if ($rawInput && !empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $data = $decoded;
    }
}

// Method 2: POST parameters (fallback)
if (empty($data) && !empty($_POST)) {
    $data = $_POST;
}

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    // Log metadata only - do not expose sensitive payload data
    $contentType = $_SERVER['CONTENT_TYPE'] ?? 'unknown';
    $hasUsername = !empty($username) ? 'yes' : 'no';
    $hasPassword = !empty($password) ? 'yes' : 'no';
    error_log("Login failed: Empty credentials (username: $hasUsername, password: $hasPassword, content-type: $contentType)");
    jsonError('Username and password required', 400);
}

if (loginUser($username, $password)) {
    trackVisit('/api/login.php');
    jsonSuccess(['message' => 'Login successful']);
} else {
    jsonError('Invalid credentials', 401);
}
