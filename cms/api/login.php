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
$data = [];

// Method 1: JSON from php://input (most common)
$rawInput = @file_get_contents('php://input');
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

// Method 3: Raw POST data (another fallback)
if (empty($data) && isset($_SERVER['CONTENT_TYPE']) &&
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $rawInput = @file_get_contents('php://input');
    if ($rawInput) {
        $decoded = @json_decode($rawInput, true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }
}

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    error_log("Login API - Empty credentials. Username: '$username', Data: " . print_r($data, true));
    jsonError('Username and password required', 400);
}

if (loginUser($username, $password)) {
    trackVisit('/api/login.php');
    jsonSuccess(['message' => 'Login successful']);
} else {
    jsonError('Invalid credentials', 401);
}
