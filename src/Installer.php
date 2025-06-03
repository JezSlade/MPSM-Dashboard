<?php
// src/Installer.php

require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/DebugLogger.php';

class Installer {
    public static function run() {
        EnvLoader::load(__DIR__ . '/../.env');
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $db   = $_ENV['DB_NAME'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        $adminUser = $_ENV['ADMIN_USER'] ?? '';
        $adminPass = $_ENV['ADMIN_PASS'] ?? '';

        try {
            // Connect to MySQL without specifying a database
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            // Drop and recreate the database
            $pdo->exec("DROP DATABASE IF EXISTS `$db`;");
            $pdo->exec("CREATE DATABASE `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            // Reconnect to the newly created database
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            // Create users table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `users` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `username` VARCHAR(100) NOT NULL UNIQUE,
                  `password_hash` VARCHAR(255) NOT NULL,
                  `is_admin` TINYINT(1) NOT NULL DEFAULT 0
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            // Insert admin user if not exists
            $stmt = $pdo->prepare("SELECT `id` FROM `users` WHERE `username` = ?;");
            $stmt->execute([$adminUser]);
            if (!$stmt->fetch()) {
                $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("
                    INSERT INTO `users` (`username`, `password_hash`, `is_admin`)
                    VALUES (?, ?, 1);
                ");
                $insert->execute([$adminUser, $hash]);
            }
        } catch (Exception $e) {
            DebugLogger::log("Installer error: " . $e->getMessage());
            die("Installation failed: " . $e->getMessage());
        }
    }
}
