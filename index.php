<?php
/**
 * index.php
 *
 * Main router for MPSM. Ensures DB schema is up‐to‐date (adding missing columns if needed),
 * seeds default roles/users/modules, grants every role access to Dashboard,
 * grants Admin access to all modules, and only loads modules the current user is permitted to see.
 */

// 1) Show errors during development (remove or comment out in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// 2) Immediately ensure $_SESSION['user_id'] is set to 'guest' if it’s not already
$pdo = require __DIR__ . '/config/db.php';
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute(['guest']);
$guestId = (int)$stmt->fetchColumn();
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $guestId;
}

// 3) Load permissions functions (defines user_has_permission() and current_user())
require __DIR__ . '/config/permissions.php';

// 4) Create or retrieve a PDO connection (again, for schema operations)
$pdo = require __DIR__ . '/config/db.php';

// ────────────────────────────────────────────────────────────────────────────
// 5) Ensure all required tables exist (roles, modules, role_module, users)

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
    -- role_id column may be added via migration below if missing
  );
");

// ────────────────────────────────────────────────────────────────────────────
// 6) Schema migration: ensure users.role_id exists; if not, add it (default to Guest)

try {
    $col = $pdo
      ->query("SHOW COLUMNS FROM users LIKE 'role_id'")
      ->fetch();

    if (!$col) {
        // Fetch or create the Guest role to determine its id
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute(['Guest']);
        $guestRoleId = (int)$stmt->fetchColumn();
        if (!$guestRoleId) {
            $pdo->prepare("INSERT INTO roles (name) VALUES (?)")->execute(['Guest']);
            $guestRoleId = (int)$pdo->lastInsertId();
        }

        // Add 'role_id' column to users, defaulting existing rows to Guest
        $pdo->exec("
          ALTER TABLE users
          ADD COLUMN role_id INT NOT NULL DEFAULT {$guestRoleId}
        ");

        // Add foreign key constraint
        $pdo->exec("
          ALTER TABLE users
          ADD CONSTRAINT fk_users_role
          FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
        ");
    }
} catch (PDOException $e) {
    error_log("Schema migration error (users.role_id): " . $e->getMessage());
}
// ────────────────────────────────────────────────────────────────────────────
// 7) Seed default roles and users

// 7A) Ensure 'Guest' role exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
$stmt->execute(['Guest']);
if ((int)$stmt->fetchColumn() === 0) {
    $pdo->prepare("INSERT INTO roles (name) VALUES (?)")->execute(['Guest']);
}

// 7B) Ensure 'guest' user exists (with role 'Guest')
$stmt = $pdo->prepare("
  SELECT u.id
  FROM users u
  JOIN roles r ON u.role_id = r.id
  WHERE u.username = ?
");
$stmt->execute(['guest']);
if (!$stmt->fetch()) {
    $stmt2 = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt2->execute(['Guest']);
    $guestRoleId = (int)$stmt2->fetchColumn();
    $pwHash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
    $pdo->prepare("
      INSERT INTO users (username, password_hash, role_id)
      VALUES (?, ?, ?)
    ")->execute(['guest', $pwHash, $guestRoleId]);
}

// 7C) Ensure 'Admin' role exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
$stmt->execute(['Admin']);
if ((int)$stmt->fetchColumn() === 0) {
    $pdo->prepare("INSERT INTO roles (name) VALUES (?)")->execute(['Admin']);
}

// 7D) Ensure 'admin' user exists (role 'Admin', password 'admin123')
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->execute(['admin']);
if ((int)$stmt->fetchColumn() === 0) {
    $stmt2 = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt2->execute(['Admin']);
    $adminRoleId = (int)$stmt2->fetchColumn();
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("
      INSERT INTO users (username, password_hash, role_id)
      VALUES (?, ?, ?)
    ")->execute(['admin', $hash, $adminRoleId]);
}

// ────────────────────────────────────────────────────────────────────────────
// 8) Seed default modules

$defaultModules = ['Dashboard', 'Customers', 'DevTools', 'Admin'];
foreach ($defaultModules as $modName) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE name = ?");
    $stmt->execute([$modName]);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare("INSERT INTO modules (name) VALUES (?)")->execute([$modName]);
    }
}

// ────────────────────────────────────────────────────────────────────────────
// 9) Grant EVERY role access to Dashboard

try {
    $stmt = $pdo->prepare("SELECT id FROM modules WHERE name = ?");
    $stmt->execute(['Dashboard']);
    $dashboardId = (int)$stmt->fetchColumn();

    $stmtRoles = $pdo->query("SELECT id FROM roles");
    $insertStmt = $pdo->prepare("
      INSERT IGNORE INTO role_module (role_id, module_id)
      VALUES (?, ?)
    ");
    while ($r = $stmtRoles->fetch(PDO::FETCH_ASSOC)) {
        $roleId = (int)$r['id'];
        $insertStmt->execute([$roleId, $dashboardId]);
    }
} catch (PDOException $e) {
    error_log("Error granting Dashboard access: " . $e->getMessage());
}

// ────────────────────────────────────────────────────────────────────────────
// 10) Grant Admin role access to ALL modules

try {
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt->execute(['Admin']);
    $adminRoleId = (int)$stmt->fetchColumn();

    if ($adminRoleId) {
        $stmt2 = $pdo->query("SELECT id FROM modules");
        $insertAll = $pdo->prepare("
          INSERT IGNORE INTO role_module (role_id, module_id)
          VALUES (?, ?)
        ");
        while ($m = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $insertAll->execute([$adminRoleId, (int)$m['id']]);
        }
    }
} catch (PDOException $e) {
    error_log("Error granting Admin access to all modules: " . $e->getMessage());
}

// ────────────────────────────────────────────────────────────────────────────
// 11) Ensure a user is logged in (default to 'guest' if none)

if (!isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['guest']);
    $_SESSION['user_id'] = (int)$stmt->fetchColumn();
}

// ────────────────────────────────────────────────────────────────────────────
// 12) Build the $modules array from database, mapping names → file paths

$moduleRows = $pdo->query("SELECT name FROM modules ORDER BY name ASC")
                  ->fetchAll(PDO::FETCH_COLUMN);

$modules = [];
foreach ($moduleRows as $modName) {
    if ($modName === 'DevTools') {
        $modules[$modName] = "modules/DevTools/debug.php";
    } elseif ($modName === 'Admin') {
        $modules[$modName] = "modules/Admin/admin.php";
    } else {
        $lower = strtolower($modName);
        $modules[$modName] = "modules/{$modName}/{$lower}.php";
    }
}

// ────────────────────────────────────────────────────────────────────────────
// 13) Determine which module to load; default to "Dashboard"

$module = isset($_GET['module']) ? $_GET['module'] : 'Dashboard';

// 14) Permission check: module must exist and user must have access

if (!array_key_exists($module, $modules) || !user_has_permission($module)) {
    header('HTTP/1.1 403 Forbidden');
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>403 Forbidden</title></head><body>";
    echo "<h1>403 Forbidden</h1>";
    echo "<p>You do not have access to the <strong>" . htmlspecialchars($module) . "</strong> module.</p>";
    echo "</body></html>";
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// 15) Render the page

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
