<?php
require_once __DIR__ . '/src/EnvLoader.php';
require_once __DIR__ . '/src/Db.php';

EnvLoader::load(__DIR__ . '/.env');
$adminUser = $_ENV['ADMIN_USER'] ?? '';
$adminPass = $_ENV['ADMIN_PASS'] ?? '';

if (empty($adminUser) || empty($adminPass)) {
    die("ERROR: ADMIN_USER and ADMIN_PASS must be present in .env\n");
}

$pdo = Db::connect();
$pdo->exec("
  CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$adminUser]);
if (!$stmt->fetch()) {
    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, 1)");
    $insert->execute([$adminUser, $hash]);
    echo "Admin user inserted.\n";
} else {
    echo "Admin user already exists.\n";
}
