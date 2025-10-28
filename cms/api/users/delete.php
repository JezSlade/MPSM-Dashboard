<?php
require '../../config.php';
require '../../functions.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
$userId = (int)($payload['id'] ?? 0);

if ($userId <= 0) {
    jsonError('User id is required', 400);
}

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Session missing user id');
    }

    if ($userId === (int)$_SESSION['user_id']) {
        jsonError('You cannot delete the currently logged in user', 400);
    }

    $pdo = getDatabase();
    $stmt = $pdo->prepare("DELETE FROM " . DB_PREFIX . "users WHERE id = ?");
    $stmt->execute([$userId]);

    jsonSuccess(['message' => 'User deleted successfully']);
} catch (Exception $e) {
    jsonError('Failed to delete user: ' . $e->getMessage());
}

