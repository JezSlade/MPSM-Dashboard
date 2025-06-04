<?php
/**
 * index.php
 *
 * Main router for MPSM. Decide which module to load from ?module=…
 * and render the shared header + sidebar + the module’s content.
 */
session_start();
require __DIR__ . '/config/permissions.php';

$module = isset($_GET['module']) ? $_GET['module'] : 'Dashboard';

$modules = [
    'Dashboard' => 'modules/Dashboard/dashboard.php',
    'Customers' => 'modules/Customers/customers.php',
    'DevTools'  => 'modules/DevTools/debug.php'
];

if (! array_key_exists($module, $modules) || ! user_has_permission($module)) {
    header('HTTP/1.1 403 Forbidden');
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>403 Forbidden</title></head><body>";
    echo "<h1>403 Forbidden</h1>";
    echo "<p>You do not have access to the <strong>" . htmlspecialchars($module) . "</strong> module.</p>";
    echo "</body></html>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPSM Dashboard – <?= htmlspecialchars($module) ?></title>

  <!-- This must be exactly “assets/css/styles.css” (no leading slash) -->
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
