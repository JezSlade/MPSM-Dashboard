<?php
require '../../config.php';
require '../../functions.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
$userId = (int)($payload['id'] ?? 0);
$password = trim($payload['password'] ?? '');

if ($userId <= 0) {
    jsonError('User id is required', 400);
}

if ($password === '') {
    jsonError('Password is required', 400);
}

try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("UPDATE " . DB_PREFIX . "users SET password = ? WHERE id = ?");
    $stmt->execute([
        password_hash($password, PASSWORD_DEFAULT),
        $userId
    ]);

    jsonSuccess(['message' => 'Password updated successfully']);
} catch (Exception $e) {
    jsonError('Failed to update user: ' . $e->getMessage());
}

