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

// Get raw input
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Check for JSON decode errors
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Login API - JSON decode error: " . json_last_error_msg());
    error_log("Login API - Raw input: " . $rawInput);
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
