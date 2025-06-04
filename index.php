<?php
// /public/mpsm/index.php

// 1. PHP error reporting (for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Include permissions and helper functions
require_once __DIR__ . '/config/permissions.php';

// 3. Determine which module to load (default = dashboard)
$module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';

// 4. Whitelist valid modules to avoid arbitrary includes
$validModules = ['dashboard', 'customers', 'developer'];

// 5. If the requested module isn’t valid or isn’t permitted, fall back to dashboard
if (!in_array($module, $validModules) || !canViewModule($module)) {
    $module = 'dashboard';
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPSM Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Link to compiled CSS -->
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

  <!-- 6. Header -->
  <?php include __DIR__ . '/views/partials/header.php'; ?>

  <!-- 7. Sidebar -->
  <?php include __DIR__ . '/views/partials/sidebar.php'; ?>

  <!-- 8. Main Content Wrapper -->
  <main class="app-content">
    <?php
      switch ($module) {
        case 'customers':
          include __DIR__ . '/modules/Customers/customers.php';
          break;

        case 'developer':
          echo "<div class='module-placeholder'><h2>Developer Tools (Coming Soon)</h2></div>";
          break;

        default:
          echo "<div class='module-placeholder'><h2>Welcome to the Dashboard</h2>
                <p>Select a module from the sidebar.</p></div>";
          break;
      }
    ?>
  </main>

  <!-- 9. JS -->
  <script src="assets/js/main.js"></script>
</body>
</html>
