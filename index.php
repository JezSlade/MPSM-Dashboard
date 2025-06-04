<?php
/**
 * index.php
 *
 * Main router for MPSM.  
 * - Ensures DB schema is up-to-date (adds missing columns if needed).  
 * - Seeds default roles/users/modules.  
 * - Grants every role access to Dashboard.  
 * - Grants Admin role access to all modules (including Admin itself).  
 * - Renders only those modules the current user is permitted to see.
 *
 * Version displayed is pulled from config/permissions.php.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// ────────────────────────────────────────────────────────────────────────────
// [A] Establish PDO and ensure guest login before loading permissions

$pdo = require __DIR__ . '/config/db.php';

// Ensure guest user exists and set $_SESSION['user_id'] = guest if it’s not already set
function ensureGuestSession(PDO $pdo): void {
    // Create guest role if needed
    $pdo->exec("INSERT IGNORE INTO roles (name) VALUES ('Guest')");
    // Create guest user (with random pw) if needed
    $stmt = $pdo->prepare("
      SELECT u.id 
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE u.username = 'guest'
    ");
    $stmt->execute();
    if (!$stmt->fetch()) {
        // Fetch Guest role id
        $stmt2 = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt2->execute(['Guest']);
        $gid = (int)$stmt2->fetchColumn();
        $pw = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $pdo->prepare("
          INSERT INTO users (username, password_hash, role_id)
          VALUES (?, ?, ?)
        ")->execute(['guest', $pw, $gid]);
    }
    // Now set session to that guest ID if not already set
    if (!isset($_SESSION['user_id'])) {
        $stmt3 = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt3->execute(['guest']);
        $_SESSION['user_id'] = (int)$stmt3->fetchColumn();
    } else {
        // If session user_id is invalid, reset to guest
        $stmt4 = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
        $stmt4->execute([$_SESSION['user_id']]);
        if ((int)$stmt4->fetchColumn() === 0) {
            $stmt5 = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt5->execute(['guest']);
            $_SESSION['user_id'] = (int)$stmt5->fetchColumn();
        }
    }
}

ensureGuestSession($pdo);

// Now load permission helpers
require __DIR__ . '/config/permissions.php';

// ────────────────────────────────────────────────────────────────────────────
// [B] Ensure tables exist and migrate schema

function ensureTablesAndSchema(PDO $pdo): void {
    // Create tables if not exist
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
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
        FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
      );
    ");
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL
        -- role_id may be added below
      );
    ");

    // If users.role_id is missing, add it (default existing to Guest)
    $col = $pdo->query("SHOW COLUMNS FROM users LIKE 'role_id'")->fetch();
    if (!$col) {
        // Ensure Guest role exists
        $pdo->exec("INSERT IGNORE INTO roles (name) VALUES ('Guest')");
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute(['Guest']);
        $guestId = (int)$stmt->fetchColumn();

        $pdo->exec("
          ALTER TABLE users
          ADD COLUMN role_id INT NOT NULL DEFAULT {$guestId}
        ");
        $pdo->exec("
          ALTER TABLE users
          ADD CONSTRAINT fk_users_role
          FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
        ");
    }
}

ensureTablesAndSchema($pdo);

// ────────────────────────────────────────────────────────────────────────────
// [C] Seed default roles and users (Guest and Admin)

function seedRolesAndUsers(PDO $pdo): void {
    // Guest role & user done in ensureGuestSession()
    // Now ensure Admin role and user
    $pdo->exec("INSERT IGNORE INTO roles (name) VALUES ('Admin')");

    // Ensure 'admin' user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    if ((int)$stmt->fetchColumn() === 0) {
        // Fetch Admin role id
        $stmt2 = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt2->execute(['Admin']);
        $adminRoleId = (int)$stmt2->fetchColumn();

        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("
          INSERT INTO users (username, password_hash, role_id)
          VALUES (?, ?, ?)
        ")->execute(['admin', $hash, $adminRoleId]);
    }
}

seedRolesAndUsers($pdo);

// ────────────────────────────────────────────────────────────────────────────
// [D] Seed default modules

function seedModules(PDO $pdo): void {
    $defaults = ['Dashboard', 'Customers', 'DevTools', 'Admin'];
    $insert = $pdo->prepare("INSERT IGNORE INTO modules (name) VALUES (?)");
    foreach ($defaults as $m) {
        $insert->execute([$m]);
    }
}

seedModules($pdo);

// ────────────────────────────────────────────────────────────────────────────
// [E] Grant EVERY role access to Dashboard

function grantDashboardAllRoles(PDO $pdo): void {
    // Fetch Dashboard module_id
    $stmt = $pdo->prepare("SELECT id FROM modules WHERE name = ?");
    $stmt->execute(['Dashboard']);
    $dashboardId = (int)$stmt->fetchColumn();

    $stmtRoles = $pdo->query("SELECT id FROM roles");
    $insert = $pdo->prepare("
      INSERT IGNORE INTO role_module (role_id, module_id)
      VALUES (?, ?)
    ");
    while ($r = $stmtRoles->fetch(PDO::FETCH_ASSOC)) {
        $insert->execute([(int)$r['id'], $dashboardId]);
    }
}

grantDashboardAllRoles($pdo);

// ────────────────────────────────────────────────────────────────────────────
// [F] Grant Admin role access to ALL modules (including “Admin”)

function grantAdminAllModules(PDO $pdo): void {
    // Fetch Admin role_id
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt->execute(['Admin']);
    $adminRoleId = (int)$stmt->fetchColumn();
    if (!$adminRoleId) {
        return;
    }

    $stmtMods = $pdo->query("SELECT id FROM modules");
    $insert = $pdo->prepare("
      INSERT IGNORE INTO role_module (role_id, module_id)
      VALUES (?, ?)
    ");
    while ($m = $stmtMods->fetch(PDO::FETCH_ASSOC)) {
        $insert->execute([$adminRoleId, (int)$m['id']]);
    }
}

grantAdminAllModules($pdo);

// ────────────────────────────────────────────────────────────────────────────
// [G] Build the list of available modules (name → path)

$stmtMods = $pdo->query("SELECT name FROM modules ORDER BY name ASC");
$moduleRows = $stmtMods->fetchAll(PDO::FETCH_COLUMN);

$modules = [];
foreach ($moduleRows as $m) {
    switch ($m) {
        case 'DevTools':
            $modules[$m] = "modules/DevTools/debug.php";
            break;
        case 'Admin':
            $modules[$m] = "modules/Admin/admin.php";
            break;
        default:
            $lower = strtolower($m);
            $modules[$m] = "modules/{$m}/{$lower}.php";
            break;
    }
}

// ────────────────────────────────────────────────────────────────────────────
// [H] Determine current module (default to Dashboard) and check permissions

$module = $_GET['module'] ?? 'Dashboard';

if (!array_key_exists($module, $modules) || !user_has_permission($module)) {
    header('HTTP/1.1 403 Forbidden');
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>403 Forbidden</title></head><body>";
    echo "<h1>403 Forbidden</h1>";
    echo "<p>You do not have access to the <strong>" . htmlspecialchars($module) . "</strong> module.</p>";
    echo "</body></html>";
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// [I] Render HTML (Header, Sidebar, Selected Module)

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPSM Dashboard – <?= htmlspecialchars($module) ?></title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <script src="assets/js/main.js" defer></script>
</head>
<body>
  <?php include __DIR__ . '/views/partials/header.php'; ?>

  <div class="main-wrapper">
    <?php include __DIR__ . '/views/partials/sidebar.php'; ?>

    <main class="content">
      <?php include __DIR__ . '/' . $modules[$module]; ?>
    </main>
  </div>
</body>
</html>
