<?php
/**
 * index.php
 *
 * Main router. Dynamically picks a module from the modules table,
 * ensures a “guest” user and default roles/modules exist in DB,
 * and only loads modules the current user is permitted to see.
 */

session_start();
require __DIR__ . '/config/permissions.php';  // sets up user_has_permission(), current_user()
// and also loads APP_VERSION

$pdo = require __DIR__ . '/config/db.php';

// ────────────────────────────────────────────────────────────────────────────
// 1) On first load (or if tables are empty), create the necessary tables in DB.
//    Then seed default roles, modules, and a “guest” user so Dashboard can be seen.

// A) Create the required tables if they don’t already exist
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
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
  );
");

// B) If the “Guest” role does not exist, create it.
$stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
$stmt->execute(['Guest']);
if ((int)$stmt->fetchColumn() === 0) {
    $pdo->prepare("INSERT INTO roles (name) VALUES (?)")->execute(['Guest']);
}

// C) If the “guest” user does not exist, create it (with a random password).
//    Assign that user to the “Guest” role.
$stmt = $pdo->prepare("
  SELECT u.id
  FROM users u
  JOIN roles r ON u.role_id = r.id
  WHERE u.username = ?
");
$stmt->execute(['guest']);
if (! $stmt->fetch()) {
    // Get the role_id for “Guest”
    $roleId = $pdo->prepare("SELECT id FROM roles WHERE name = ?")->executeAndFetch(['Guest'], 'id');
    // Create a random placeholder password
    $pwHash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password_hash, role_id) VALUES (?, ?, ?)")
        ->execute(['guest', $pwHash, $roleId]);
}

// D) Seed modules: “Dashboard”, “Customers”, “DevTools”, “Admin” (if missing).
$allModuleNames = ['Dashboard','Customers','DevTools','Admin'];
foreach ($allModuleNames as $modName) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE name = ?");
    $stmt->execute([$modName]);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare("INSERT INTO modules (name) VALUES (?)")->execute([$modName]);
    }
}

// E) Ensure there is always a valid $_SESSION['user_id'].
//    If none is set (meaning nobody has “logged in” yet), assign “guest” (username) automatically.
if (! isset($_SESSION['user_id'])) {
    // Get guest user_id
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['guest']);
    $guestId = $stmt->fetchColumn();
    if ($guestId) {
        $_SESSION['user_id'] = $guestId;
    }
}
// ────────────────────────────────────────────────────────────────────────────

// 2) Load the list of modules from the database into $modules (name => path)
//    We’ll map each module name to its script under modules/<name>/<file>.php
$moduleRows = $pdo->query("SELECT name FROM modules ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
$modules = [];
foreach ($moduleRows as $modName) {
    // Map “Dashboard” → “modules/Dashboard/dashboard.php”
    // Map “Customers” → “modules/Customers/customers.php”
    // Map “DevTools” → “modules/DevTools/debug.php”
    // Map “Admin” → “modules/Admin/admin.php”
    $lower = strtolower($modName);
    if ($modName === 'DevTools') {
        $modules[$modName] = "modules/DevTools/debug.php";
    } elseif ($modName === 'Admin') {
        $modules[$modName] = "modules/Admin/admin.php";
    } else {
        // For Dashboard and Customers (and any future modules), assume a naming convention:
        $modules[$modName] = "modules/{$modName}/" . $lower . ".php";
    }
}

// 3) Determine which module to load; default to “Dashboard”
$module = isset($_GET['module']) ? $_GET['module'] : 'Dashboard';

// 4) If the requested module doesn’t exist in our $modules list, or the user lacks permission, 403
if (! array_key_exists($module, $modules) || ! user_has_permission($module)) {
    header('HTTP/1.1 403 Forbidden');
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>403 Forbidden</title></head><body>";
    echo "<h1>403 Forbidden</h1>";
    echo "<p>You do not have access to the <strong>" . htmlspecialchars($module) . "</strong> module.</p>";
    echo "</body></html>";
    exit;
}

// 5) Render the page
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
    <?php
      // Sidebar now reads $modules directly from DB
      include __DIR__ . '/views/partials/sidebar.php';
    ?>
    <main class="content">
      <?php include __DIR__ . '/' . $modules[$module]; ?>
    </main>
  </div>
</body>
</html>
