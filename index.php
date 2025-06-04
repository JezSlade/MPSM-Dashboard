<?php
/**
 * index.php
 *
 * Main router. Dynamically picks a module (Dashboard, Customers, DevTools),
 * ensures there is always a valid default role in the session,
 * and only loads modules the user is permitted to see.
 */

session_start();
require __DIR__ . '/config/permissions.php';  // loads $permissions and user_has_permission()

// ────────────────────────────────────────────────────────────────────────────
// 1) Ensure there is always a valid role in $_SESSION['role']
//    If none is set (or if it was set to something invalid), assign the first role
//    from the $permissions array as the default.
if (! isset($_SESSION['role']) || ! array_key_exists($_SESSION['role'], $permissions)) {
    // Grab all the role names (keys of $permissions), and pick the first one
    $allRoles = array_keys($permissions);
    $_SESSION['role'] = reset($allRoles);
}
// ────────────────────────────────────────────────────────────────────────────

// 2) Define which modules exist (key = module name, value = path to file)
$modules = [
    'Dashboard' => 'modules/Dashboard/dashboard.php',
    'Customers' => 'modules/Customers/customers.php',
    'DevTools'  => 'modules/DevTools/debug.php'
];

// 3) Determine which module to load; default to “Dashboard”
$module = isset($_GET['module']) ? $_GET['module'] : 'Dashboard';

// 4) If module not found in our $modules list or the user lacks permission, show 403
if (! array_key_exists($module, $modules) || ! user_has_permission($module)) {
    header('HTTP/1.1 403 Forbidden');
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>403 Forbidden</title></head><body>";
    echo "<h1>403 Forbidden</h1>";
    echo "<p>You do not have access to the <strong>" . htmlspecialchars($module) . "</strong> module.</p>";
    echo "</body></html>";
    exit;
}

// 5) Render the page header (with version + role switch) and sidebar
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
      // Sidebar will loop over $modules and highlight the current $module
      include __DIR__ . '/views/partials/sidebar.php';
    ?>
    <main class="content">
      <?php include __DIR__ . '/' . $modules[$module]; ?>
    </main>
  </div>
</body>
</html>
