<?php
// core/bootstrap.php
// v1.2.3 [Fixed migration_lock schema; auto-run migrations with safe drop/create; auto-seed admin]

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/debug.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/mps_api.php';
require_once __DIR__ . '/tracking.php';
require_once __DIR__ . '/widgets.php';

$pdo = get_db();

// 1) Ensure migrations table exists
$pdo->exec("
  CREATE TABLE IF NOT EXISTS migrations (
    version VARCHAR(50) PRIMARY KEY,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )
");

// 2) Rebuild migration_lock table to guarantee correct schema
$pdo->exec("DROP TABLE IF EXISTS migration_lock");
$pdo->exec("
  CREATE TABLE migration_lock (
    id     TINYINT PRIMARY KEY,
    locked TINYINT(1) NOT NULL DEFAULT 0
  )
");
// Initialize single lock row
$pdo->exec("INSERT INTO migration_lock (id,locked) VALUES (1,0)");

// 3) Acquire the lock and run any new migrations
$locked = (int)$pdo->query("SELECT locked FROM migration_lock WHERE id = 1")
                   ->fetchColumn();
if ($locked === 0) {
    // Lock it
    $pdo->exec("UPDATE migration_lock SET locked = 1 WHERE id = 1");

    // Execute each SQL file once
    foreach (glob(__DIR__ . '/../migrations/*.sql') as $file) {
        $version = basename($file, '.sql');
        $seen    = $pdo->prepare("SELECT 1 FROM migrations WHERE version = ?");
        $seen->execute([$version]);
        if (!$seen->fetch()) {
            $sql = file_get_contents($file);
            $pdo->exec($sql);
            $ins = $pdo->prepare("INSERT INTO migrations (version) VALUES (?)");
            $ins->execute([$version]);
        }
    }

    // Release the lock
    $pdo->exec("UPDATE migration_lock SET locked = 0 WHERE id = 1");
}

// 4) Ensure the logs directory exists
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// 5) Auto-seed the default admin user if none exist
try {
    // Only proceed if users table exists
    $res = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    if ($res) {
        $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($userCount === 0) {
            // Read defaults from .env (or fall back)
            $adminUser = defined('DEFAULT_ADMIN_USER') ? DEFAULT_ADMIN_USER : 'developer';
            $adminPass = defined('DEFAULT_ADMIN_PASS') ? DEFAULT_ADMIN_PASS : 'DevPass123';
            $hash      = password_hash($adminPass, PASSWORD_ARGON2ID);

            // Create the user
            $pdo->prepare("INSERT INTO users (username,password_hash) VALUES (?,?)")
                ->execute([$adminUser, $hash]);
            debug_log("Seeded default admin user: $adminUser", [], 'INFO');
            $userId = $pdo->lastInsertId();

            // Assign Developer role
            $roleId = $pdo->prepare("SELECT id FROM roles WHERE name = 'Developer'");
            $roleId->execute();
            $roleId = $roleId->fetchColumn();
            if ($roleId) {
                $pdo->prepare("INSERT INTO user_roles (user_id,role_id) VALUES (?,?)")
                    ->execute([$userId, $roleId]);
                debug_log("Assigned 'Developer' role to $adminUser", [], 'INFO');
            }
        }
    }
} catch (Exception $e) {
    debug_log("Error auto-seeding admin user", ['error' => $e->getMessage()], 'ERROR');
}
