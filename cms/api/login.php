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

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    jsonError('Username and password required', 400);
}

if (loginUser($username, $password)) {
    trackVisit('/api/login.php');
    jsonSuccess(['message' => 'Login successful']);
} else {
    jsonError('Invalid credentials', 401);
}
