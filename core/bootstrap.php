<?php
// core/bootstrap.php
// v1.2.2 [Load core, auto-run migrations with locking, ensure logs, auto-seed default admin]

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/debug.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/mps_api.php';
require_once __DIR__ . '/tracking.php';
require_once __DIR__ . '/widgets.php';

// 1) Migrations & Lock
$pdo = get_db();

// Create migrations table
$pdo->exec("
  CREATE TABLE IF NOT EXISTS migrations (
    version VARCHAR(50) PRIMARY KEY,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )
");
// Create a simple lock table
$pdo->exec("
  CREATE TABLE IF NOT EXISTS migration_lock (
    id TINYINT PRIMARY KEY,
    locked TINYINT(1) NOT NULL DEFAULT 0
  )
");
// Ensure single lock row
$pdo->exec("INSERT IGNORE INTO migration_lock (id, locked) VALUES (1, 0)");

// Acquire lock if free
$locked = $pdo->query("SELECT locked FROM migration_lock WHERE id = 1")
              ->fetchColumn();
if ($locked == 0) {
    // Lock it
    $pdo->exec("UPDATE migration_lock SET locked = 1 WHERE id = 1");

    // Run new migrations
    foreach (glob(__DIR__ . '/../migrations/*.sql') as $file) {
        $version = basename($file, '.sql');
        $exists  = $pdo->prepare("SELECT 1 FROM migrations WHERE version = ?");
        $exists->execute([$version]);
        if (!$exists->fetch()) {
            $pdo->exec(file_get_contents($file));
            $ins = $pdo->prepare("INSERT INTO migrations (version) VALUES (?)");
            $ins->execute([$version]);
        }
    }

    // Release lock
    $pdo->exec("UPDATE migration_lock SET locked = 0 WHERE id = 1");
}

// 2) Ensure logs folder
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// 3) Auto-seed default admin user if none exist
try {
    // Check if users table exists & is empty
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->fetch()) {
        $count = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count === 0) {
            // Grab defaults from .env or fallback
            $adminUser = defined('DEFAULT_ADMIN_USER') ? DEFAULT_ADMIN_USER : 'developer';
            $adminPass = defined('DEFAULT_ADMIN_PASS') ? DEFAULT_ADMIN_PASS : 'DevPass123';
            $hash = password_hash($adminPass, PASSWORD_ARGON2ID);

            // Insert user
            $pdo->prepare("INSERT INTO users (username,password_hash) VALUES (?,?)")
                ->execute([$adminUser, $hash]);
            debug_log("Seeded default admin user: $adminUser", [], 'INFO');

            // Assign Developer role
            $roleId = $pdo->prepare("SELECT id FROM roles WHERE name = 'Developer'");
            $roleId->execute();
            $roleId = $roleId->fetchColumn();
            $userId = $pdo->lastInsertId();
            if ($roleId && $userId) {
                $pdo->prepare("INSERT INTO user_roles (user_id,role_id) VALUES (?,?)")
                    ->execute([$userId, $roleId]);
                debug_log("Assigned 'Developer' role to $adminUser", [], 'INFO');
            }
        }
    }
} catch (Exception $e) {
    debug_log("Error auto-seeding admin user", ['error' => $e->getMessage()], 'ERROR');
}
