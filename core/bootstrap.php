<?php
// core/bootstrap.php
// v1.2.0 [Load core, auto-run migrations, ensure logs]

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/debug.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/mps_api.php';
require_once __DIR__ . '/tracking.php';
require_once __DIR__ . '/widgets.php';

// Auto-run migrations with simple advisory lock
$pdo = get_db();
$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (version VARCHAR(50) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("CREATE TABLE IF NOT EXISTS migration_lock (locked TINYINT(1) DEFAULT 0)");
$lock = $pdo->prepare("SELECT 1 FROM migration_lock WHERE locked=1");
$lock->execute();
if (!$lock->fetch()) {
    $pdo->exec("UPDATE migration_lock SET locked=1");
    foreach (glob(__DIR__ . '/../migrations/*.sql') as $file) {
        $version = basename($file, '.sql');
        $chk = $pdo->prepare("SELECT 1 FROM migrations WHERE version = ?");
        $chk->execute([$version]);
        if (!$chk->fetch()) {
            $pdo->exec(file_get_contents($file));
            $ins = $pdo->prepare("INSERT INTO migrations (version) VALUES (?)");
            $ins->execute([$version]);
        }
    }
    $pdo->exec("UPDATE migration_lock SET locked=0");
}

// Ensure logs folder
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}
