<?php
/**
 * index.php
 *
 * Main router for MPSM. Determines which module to load based on `?module=…`,
 * and includes the appropriate PHP file under /modules/.
 */

session_start();

// Load user permissions and any autoloaders
require __DIR__ . '/config/permissions.php';

// Determine which module to load (default “Dashboard”)
$module = isset($_GET['module']) ? $_GET['module'] : 'Dashboard';

// Define available modules (key = module name, value = file path)
$modules = [
    'Dashboard' => 'modules/Dashboard/dashboard.php',
    'Customers' => 'modules/Customers/customers.php',
    'DevTools'  => 'modules/DevTools/debug.php'
];

// If user lacks permission for the requested module, show “Access Denied”
if (! array_key_exists($module, $modules) || ! user_has_permission($module)) {
    header('HTTP/1.1 403 Forbidden');
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>403 Forbidden</title></head><body>";
    echo "<h1>403 Forbidden</h1>";
    echo "<p>You do not have access to the <strong>" . htmlspecialchars($module) . "</strong> module.</p>";
    echo "</body></html>";
    exit;
}

// Include the site header + sidebar (shared across all modules)
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
      <?php
        // Finally, include the requested module’s PHP file.
        include __DIR__ . '/' . $modules[$module];
      ?>
    </main>
  </div>
</body>
</html>
