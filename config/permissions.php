<?php
/**
 * config/permissions.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('APP_VERSION', 'v0.1.5');

// Ensure guest session, seed roles/modules/users
function ensureGuestSession(PDO $pdo): void {
    // Create tables if missing
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE
    );");
    $pdo->exec("CREATE TABLE IF NOT EXISTS modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE
    );");
    $pdo->exec("CREATE TABLE IF NOT EXISTS role_module (
        role_id INT NOT NULL,
        module_id INT NOT NULL,
        PRIMARY KEY(role_id, module_id),
        FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE,
        FOREIGN KEY(module_id) REFERENCES modules(id) ON DELETE CASCADE
    );");
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL
    );");

    // Migrate users.role_id if missing
    $col = $pdo->query("SHOW COLUMNS FROM users LIKE 'role_id'")->fetch();
    if (!$col) {
        // Ensure Guest role
        $pdo->prepare("INSERT IGNORE INTO roles (name) VALUES (?)")->execute(['Guest']);
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute(['Guest']);
        $guestId = (int)$stmt->fetchColumn();

        $pdo->exec("ALTER TABLE users ADD COLUMN role_id INT NOT NULL DEFAULT $guestId");
        $pdo->exec("ALTER TABLE users ADD CONSTRAINT fk_users_role FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE CASCADE");
    }

    // Seed Guest and Admin roles
    $pdo->prepare("INSERT IGNORE INTO roles (name) VALUES (?)")->execute(['Guest']);
    $pdo->prepare("INSERT IGNORE INTO roles (name) VALUES (?)")->execute(['Admin']);

    # Seed modules: Dashboard, Customers, DevTools, Admin
    $mods = ['Dashboard','Customers','DevTools','Admin'];
    $insMod = $pdo->prepare("INSERT IGNORE INTO modules (name) VALUES (?)");
    foreach ($mods as $m) {
        $insMod->execute([$m]);
    }

    // Seed users: guest and admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['guest']);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare("SELECT id FROM roles WHERE name = ?")->execute(['Guest']);
        $gId = (int)$pdo->query("SELECT id FROM roles WHERE name='Guest'")->fetchColumn();
        $pw = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username,password_hash,role_id) VALUES (?,?,?)")->execute(['guest',$pw,$gId]);
    }
    $stmt->execute(['admin']);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare("SELECT id FROM roles WHERE name = ?")->execute(['Admin']);
        $aId = (int)$pdo->query("SELECT id FROM roles WHERE name='Admin'")->fetchColumn();
        $hp = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username,password_hash,role_id) VALUES (?,?,?)")->execute(['admin',$hp,$aId]);
    }

    # Grant every role → Dashboard
    $stmt = $pdo->prepare("SELECT id FROM modules WHERE name=?");
    $stmt->execute(['Dashboard']);
    $dashId = (int)$stmt->fetchColumn();
    $roles = $pdo->query("SELECT id FROM roles")->fetchAll();
    $insRM = $pdo->prepare("INSERT IGNORE INTO role_module (role_id,module_id) VALUES (?,?)");
    foreach ($roles as $r) {
        $insRM->execute([(int)$r['id'],$dashId]);
    }

    # Grant Admin → all modules
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE name=?");
    $stmt->execute(['Admin']);
    $admId = (int)$stmt->fetchColumn();
    $modsAll = $pdo->query("SELECT id FROM modules")->fetchAll();
    foreach ($modsAll as $m) {
        $insRM->execute([$admId,(int)$m['id']]);
    }

    # Ensure session user_id valid
    if (!isset($_SESSION['user_id'])) {
        $uid = $pdo->query("SELECT id FROM users WHERE username='guest'")->fetchColumn();
        $_SESSION['user_id'] = (int)$uid;
    } else {
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id=?");
        $check->execute([$_SESSION['user_id']]);
        if ((int)$check->fetchColumn() === 0) {
            $uid = $pdo->query("SELECT id FROM users WHERE username='guest'")->fetchColumn();
            $_SESSION['user_id'] = (int)$uid;
        }
    }
}

# Return currently logged user or null
function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    static $cache = null;
    if ($cache !== null) return $cache;
    $pdo = require __DIR__ . '/db.php';
    $stmt = $pdo->prepare("SELECT u.id,u.username,u.role_id,r.name AS role_name
        FROM users u JOIN roles r ON u.role_id=r.id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $cache = $user ?: null;
}

# Check permission: moduleKey in modules table and role_module
function user_has_permission(string $moduleKey): bool {
    $user = current_user();
    if (!$user) return false;
    $pdo = require __DIR__ . '/db.php';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM role_module rm
        JOIN modules m ON rm.module_id=m.id
        WHERE rm.role_id=? AND m.name=?");
    $stmt->execute([$user['role_id'],$moduleKey]);
    return ((int)$stmt->fetchColumn()) > 0;
}
?>