<?php
/**
 * config/permissions.php
 *
 * - Creates/updates schema for roles, modules, role_module, users.
 * - Seeds “guest”/“admin”, Dashboard/Customers/DevTools/Admin modules.
 * - Grants Dashboard → all roles, Admin → all modules.
 * - Provides current_user() and user_has_permission().
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If you still want a fallback “hard‐coded” version string, you can keep it.
// But we’ll read version.txt at runtime in the header, so APP_VERSION isn’t strictly necessary:
define('APP_VERSION', 'v0.1.5');


/**
 * Ensure that:
 *  - Tables exist (roles, modules, role_module, users)
 *  - “Guest” and “Admin” roles exist
 *  - “guest” and “admin” users exist
 *  - Dashboard, Customers, DevTools, Admin modules exist
 *  - role_module links:
 *       • Dashboard → all roles
 *       • Admin → all modules
 *  - A valid session['user_id'] is set (defaults to guest)
 */
function ensureGuestSession(PDO $pdo): void {
    // ────────────────────────────────────────────────────────────────
    // 1) Create tables if missing
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE
        );
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE
        );
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS role_module (
            role_id INT NOT NULL,
            module_id INT NOT NULL,
            PRIMARY KEY (role_id, module_id),
            FOREIGN KEY (role_id)   REFERENCES roles(id)   ON DELETE CASCADE,
            FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
        );
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL
            -- role_id will be added if missing
        );
    ");

    // ────────────────────────────────────────────────────────────────
    // 2) Migrate users.role_id if missing
    $col = $pdo->query("SHOW COLUMNS FROM users LIKE 'role_id'")->fetch();
    if (! $col) {
        // Ensure “Guest” role exists
        $pdo->prepare("INSERT IGNORE INTO roles (name) VALUES (?)")->execute(['Guest']);
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute(['Guest']);
        $guestId = (int)$stmt->fetchColumn();

        // Add column with default = guestId
        $pdo->exec("
            ALTER TABLE users
            ADD COLUMN role_id INT NOT NULL DEFAULT {$guestId};
        ");
        $pdo->exec("
            ALTER TABLE users
            ADD CONSTRAINT fk_users_role
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE;
        ");
    }

    // ────────────────────────────────────────────────────────────────
    // 3) Seed “Guest” and “Admin” roles
    $pdo->prepare("INSERT IGNORE INTO roles (name) VALUES (?)")->execute(['Guest']);
    $pdo->prepare("INSERT IGNORE INTO roles (name) VALUES (?)")->execute(['Admin']);

    // ────────────────────────────────────────────────────────────────
    // 4) Seed modules: Dashboard, Customers, DevTools, Admin
    $modulesToSeed = ['Dashboard', 'Customers', 'DevTools', 'Admin'];
    $insertMod = $pdo->prepare("INSERT IGNORE INTO modules (name) VALUES (?)");
    foreach ($modulesToSeed as $modName) {
        $insertMod->execute([$modName]);
    }

    // ────────────────────────────────────────────────────────────────
    // 5) Seed “guest” user (role=Guest), “admin” user (role=Admin)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    //  5a) guest
    $stmt->execute(['guest']);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare("SELECT id FROM roles WHERE name = ?")->execute(['Guest']);
        $gId = (int)$pdo->query("SELECT id FROM roles WHERE name='Guest'")->fetchColumn();
        // random password to lock it down (users switch via dropdown)
        $pw = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $pdo->prepare("
            INSERT INTO users (username, password_hash, role_id)
            VALUES (?, ?, ?)
        ")->execute(['guest', $pw, $gId]);
    }
    //  5b) admin
    $stmt->execute(['admin']);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare("SELECT id FROM roles WHERE name = ?")->execute(['Admin']);
        $aId = (int)$pdo->query("SELECT id FROM roles WHERE name='Admin'")->fetchColumn();
        $hp = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("
            INSERT INTO users (username, password_hash, role_id)
            VALUES (?, ?, ?)
        ")->execute(['admin', $hp, $aId]);
    }

    // ────────────────────────────────────────────────────────────────
    // 6) Grant EVERY role → Dashboard
    $stmt = $pdo->prepare("SELECT id FROM modules WHERE name = ?");
    $stmt->execute(['Dashboard']);
    $dashId = (int)$stmt->fetchColumn();

    $roleRows = $pdo->query("SELECT id FROM roles")->fetchAll(PDO::FETCH_COLUMN);
    $insertRM = $pdo->prepare("
        INSERT IGNORE INTO role_module (role_id, module_id)
        VALUES (?, ?)
    ");
    foreach ($roleRows as $rid) {
        $insertRM->execute([(int)$rid, $dashId]);
    }

    // ────────────────────────────────────────────────────────────────
    // 7) Grant “Admin” → all modules
    $stmt->execute(['Admin']);
    $admRoleId = (int)$stmt->fetchColumn();

    $allModuleIds = $pdo->query("SELECT id FROM modules")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($allModuleIds as $mid) {
        $insertRM->execute([$admRoleId, (int)$mid]);
    }

    // ────────────────────────────────────────────────────────────────
    // 8) Ensure session['user_id'] points to a valid user (default = guest)
    if (! isset($_SESSION['user_id']) ) {
        $uid = $pdo->query("SELECT id FROM users WHERE username='guest'")->fetchColumn();
        $_SESSION['user_id'] = (int)$uid;
    } else {
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
        $check->execute([$_SESSION['user_id']]);
        if ((int)$check->fetchColumn() === 0) {
            $uid = $pdo->query("SELECT id FROM users WHERE username='guest'")->fetchColumn();
            $_SESSION['user_id'] = (int)$uid;
        }
    }
}


/**
 * Returns the currently‐logged‐in user (id, username, role_id, role_name) or null.
 */
function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    static $memo = null;
    if ($memo !== null) {
        return $memo;
    }
    $pdo = require __DIR__ . '/db.php';
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.role_id, r.name AS role_name
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $memo = ($user ?: null);
}


/**
 * Checks if the current user has permission to see $moduleKey.
 * (i.e. there is a row in role_module where role_id = user's role_id
 *  and module_id matches the module named $moduleKey.)
 */
function user_has_permission(string $moduleKey): bool {
    $user = current_user();

    // If we didn’t get an array back, bail out immediately
    if (!is_array($user)) {
        return false;
    }

    $pdo = require __DIR__ . '/db.php';
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM role_module rm
        JOIN modules m ON rm.module_id = m.id
        WHERE rm.role_id = :role_id
          AND m.name     = :moduleKey
    ");
    $stmt->execute([
        ':role_id'   => $user['role_id'],
        ':moduleKey' => $moduleKey
    ]);
    return ((int)$stmt->fetchColumn()) > 0;
}
?>
