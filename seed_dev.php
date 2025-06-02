<?php
// seed_dev.php
// Run once: php seed_dev.php

require_once __DIR__ . '/core/bootstrap.php';

$pdo = get_db();

// Ensure Developer role exists
$roleId = $pdo->prepare("SELECT id FROM roles WHERE name = 'Developer'");
$roleId->execute();
$roleId = $roleId->fetchColumn();
if (!$roleId) {
    echo "[ERROR] 'Developer' role not found. Did migrations run?\n";
    exit(1);
}

// Create developer user if missing
$username = 'developer';
$password = 'DevPass123'; // Change this ASAP
$h = password_hash($password, PASSWORD_ARGON2ID);
$userStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$userStmt->execute([$username]);
$userId = $userStmt->fetchColumn();

if (!$userId) {
    $ins = $pdo->prepare("INSERT INTO users (username,password_hash) VALUES (?,?)");
    $ins->execute([$username,$h]);
    $userId = $pdo->lastInsertId();
    echo "[INFO] Created user: $username / $password\n";
} else {
    echo "[INFO] User '$username' already exists.\n";
}

// Assign Developer role to user
$ur = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?,?)");
$ur->execute([$userId,$roleId]);
echo "[INFO] Assigned 'Developer' role to '$username'.\n";
