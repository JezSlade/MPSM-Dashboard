<?php
// src/Installer.php

require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/Db.php';
require_once __DIR__ . '/DebugLogger.php';

class Installer {
    public static function run(): void {
        EnvLoader::load(__DIR__ . '/../.env');

        $pdo = Db::connect();
        $adminUser = $_ENV['DEFAULT_ADMIN_USER'] ?? 'developer';
        $adminPass = $_ENV['DEFAULT_ADMIN_PASS'] ?? 'DevPass123';

        try {
            // Ensure users table exists and is correct
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(100) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    is_admin TINYINT(1) NOT NULL DEFAULT 0
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Check if admin user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$adminUser]);
            $existing = $stmt->fetch();

            if (!$existing) {
                $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, 1)");
                $insert->execute([$adminUser, $hash]);

                DebugLogger::log("✅ Installer: Admin user '$adminUser' created.");
            } else {
                DebugLogger::log("ℹ️ Installer: Admin user '$adminUser' already exists.");
            }

        } catch (Exception $e) {
            DebugLogger::log("❌ Installer ERROR: " . $e->getMessage());
        }
    }
}
