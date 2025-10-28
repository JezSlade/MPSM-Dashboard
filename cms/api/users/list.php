<?php
require '../../config.php';
require '../../functions.php';

requireAuth();

try {
    $pdo = getDatabase();
    $stmt = $pdo->query("SELECT id, username, created_at FROM " . DB_PREFIX . "users ORDER BY username ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonSuccess(['users' => $users]);
} catch (Exception $e) {
    jsonError('Failed to load users: ' . $e->getMessage());
}

