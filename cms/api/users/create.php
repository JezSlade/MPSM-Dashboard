<?php
require '../../config.php';
require '../../functions.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
$username = trim($payload['username'] ?? '');
$password = trim($payload['password'] ?? '');

if ($username === '' || $password === '') {
    jsonError('Username and password are required', 400);
}

try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("INSERT INTO " . DB_PREFIX . "users (username, password) VALUES (?, ?)");
    $stmt->execute([
        $username,
        password_hash($password, PASSWORD_DEFAULT)
    ]);

    jsonSuccess(['message' => 'User created successfully']);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        jsonError('Username already exists', 400);
    }
    jsonError('Failed to create user: ' . $e->getMessage());
}

