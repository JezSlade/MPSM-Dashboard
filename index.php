<?php
// /public/mpsm/index.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/permissions.php';

$module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
$validModules = ['dashboard', 'customers', 'developer'];

if (!in_array($module, $validModules) || !canViewModule($module)) {
    $module = 'dashboard';
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPSM Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

  <?php include __DIR__ . '/views/partials/header.php'; ?>
  <?php include __DIR__ . '/views/partials/sidebar.php'; ?>

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

  <script src="assets/js/main.js"></script>
</body>
</html>
