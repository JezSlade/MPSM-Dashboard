<?php
/**
 * index.php
 *
 * Main router for MPSM. Dynamically picks a module from the database,
 * ensures required tables and columns exist (with automatic migrations),
 * seeds default roles/users/modules, and only loads modules the current
 * user is permitted to see.
 */

// Show all errors during development; comment out or remove in production
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require __DIR__ . '/config/permissions.php';  // sets up user_has_permission(), current_user()
$pdo = require_once __DIR__ . '/db.php';

// ────────────────────────────────────────────────────────────────────────────
// 1) Create necessary tables if they don’t already exist

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
// 2) Schema migration: ensure users.role_id exists; if not, add it (default to Guest)

try {
    // Check whether 'role_id' column exists in 'users'
    $col = $pdo
      ->query("SHOW COLUMNS FROM users LIKE 'role_id'")
      ->fetch();

    if (! $col) {
        // Fetch or create the Guest role to determine its id
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute(['Guest']);
        $guestId = (int)$stmt->fetchColumn();

        if (! $guestId) {
            // If Guest role does not exist yet, create it and get its id
            $pdo->prepare("INSERT INTO roles (name) VALUES (?)")
                ->execute(['Guest']);
            $guestId = (int)$pdo->lastInsertId();
        }

        // Add the 'role_id' column to users, defaulting existing rows to Guest
        $pdo->exec("
          ALTER TABLE users
          ADD COLUMN role_id INT NOT NULL DEFAULT {$guestId}
        ");

        // Add the foreign key constraint linking users.role_id → roles.id
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

// 3) Seed default roles and users

// 3A) Ensure 'Guest' role exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
$stmt->execute(['Guest']);
if ((int)$stmt->fetchColumn() === 0) {
    $pdo->prepare("INSERT INTO roles (name) VALUES (?)")
        ->execute(['Guest']);
}

// 3B) Ensure 'guest' user exists (with role 'Guest')
$stmt = $pdo->prepare("
  SELECT u.id
  FROM users u
  JOIN roles r ON u.role_id = r.id
  WHERE u.username = ?
");
$stmt->execute(['guest']);
if (! $stmt->fetch()) {
    // Fetch Guest role_id
    $stmt2 = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt2->execute(['Guest']);
    $guestRoleId = (int)$stmt2->fetchColumn();

    // Create 'guest' user with a random password
    $pwHash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
    $pdo->prepare("
      INSERT INTO users (username, password_hash, role_id)
      VALUES (?, ?, ?)
    ")->execute(['guest', $pwHash, $guestRoleId]);
}

// 3C) Ensure 'Admin' role exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
$stmt->execute(['Admin']);
if ((int)$stmt->fetchColumn() === 0) {
    $pdo->prepare("INSERT INTO roles (name) VALUES (?)")
        ->execute(['Admin']);
}

// 3D) Ensure 'admin' user exists (role 'Admin', password 'admin123')
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->execute(['admin']);
if ((int)$stmt->fetchColumn() === 0) {
    // Fetch Admin role_id
    $stmt2 = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt2->execute(['Admin']);
    $adminRoleId = (int)$stmt2->fetchColumn();

    // Create 'admin' user
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("
      INSERT INTO users (username, password_hash, role_id)
      VALUES (?, ?, ?)
    ")->execute(['admin', $hash, $adminRoleId]);
}

// ────────────────────────────────────────────────────────────────────────────
// 4) Seed default modules

$defaultModules = ['Dashboard', 'Customers', 'DevTools', 'Admin'];
foreach ($defaultModules as $modName) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE name = ?");
    $stmt->execute([$modName]);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare("INSERT INTO modules (name) VALUES (?)")
            ->execute([$modName]);
    }
}

// ────────────────────────────────────────────────────────────────────────────
// 5) Ensure a user is logged in (default to 'guest' if none)

if (! isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['guest']);
    $guestId = (int)$stmt->fetchColumn();
    if ($guestId) {
        $_SESSION['user_id'] = $guestId;
    }
}

// ────────────────────────────────────────────────────────────────────────────
// 6) Build the $modules array from database, mapping names → file paths

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
// 7) Determine which module to load; default to "Dashboard"

$module = isset($_GET['module']) ? $_GET['module'] : 'Dashboard';

// 8) Permission check: module must exist and user must have access

if (! array_key_exists($module, $modules) || ! user_has_permission($module)) {
    header('HTTP/1.1 403 Forbidden');
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>403 Forbidden</title></head><body>";
    echo "<h1>403 Forbidden</h1>";
    echo "<p>You do not have access to the <strong>" . htmlspecialchars($module) . "</strong> module.</p>";
    echo "</body></html>";
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// 9) Render the page

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
