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

// 2) Rebuild migration_lock table
$pdo->exec("DROP TABLE IF EXISTS migration_lock");
$pdo->exec("
  CREATE TABLE migration_lock (
    id     TINYINT PRIMARY KEY,
    locked TINYINT(1) NOT NULL DEFAULT 0
  )
");
$pdo->exec("INSERT IGNORE INTO migration_lock (id,locked) VALUES (1,0)");

// 3) Acquire lock and run migrations
$locked = (int)$pdo->query("SELECT locked FROM migration_lock WHERE id = 1")->fetchColumn();
if ($locked === 0) {
    $pdo->exec("UPDATE migration_lock SET locked = 1 WHERE id = 1");
    foreach (glob(__DIR__ . '/../migrations/*.sql') as $file) {
        $version = basename($file, '.sql');
        $seen = $pdo->prepare("SELECT 1 FROM migrations WHERE version = ?");
        $seen->execute([$version]);
        if (!$seen->fetch()) {
            $pdo->exec(file_get_contents($file));
            $pdo->prepare("INSERT INTO migrations (version) VALUES (?)")->execute([$version]);
        }
    }
    $pdo->exec("UPDATE migration_lock SET locked = 0 WHERE id = 1");
}

// 4) Ensure logs directory
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

// 5) Auto-seed default admin if no users exist
try {
    $res = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    if ($res) {
        $count = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count === 0) {
            $adminU = DEFAULT_ADMIN_USER ?? 'developer';
            $adminP = DEFAULT_ADMIN_PASS ?? 'DevPass123';
            $hash   = password_hash($adminP, PASSWORD_ARGON2ID);
            $pdo->prepare("INSERT INTO users (username,password_hash) VALUES (?,?)")->execute([$adminU,$hash]);
            debug_log("Seeded default admin: $adminU",[], 'INFO');
            $userId = $pdo->lastInsertId();
            $roleId = $pdo->query("SELECT id FROM roles WHERE name='Developer'")->fetchColumn();
            if ($roleId) {
                $pdo->prepare("INSERT INTO user_roles (user_id,role_id) VALUES (?,?)")->execute([$userId,$roleId]);
                debug_log("Assigned Developer role to $adminU",[], 'INFO');
            }
        }
    }
} catch (Exception $e) {
    debug_log("Error auto-seeding admin", ['error'=>$e->getMessage()], 'ERROR');
}
