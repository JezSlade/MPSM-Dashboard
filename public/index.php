<?php
/**
 * index.php
 *
 * Entry point for the MPS Monitor Dashboard POC.
 * Implements a simple “Views” system rather than
 * dynamic card loading.
 */

require __DIR__ . '/src/config.php';
require __DIR__ . '/src/DebugPanel.php';

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPS Monitor Dashboard</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Minimalist/neumorphic-inspired base styles */
    body {
      margin: 0;
      background: #1e1e1e;
      color: #e4e4e4;
      font-family: sans-serif;
    }
    nav.view-menu {
      background: #2a2a2a;
      padding: 10px;
      display: flex;
    }
    nav.view-menu a {
      color: #e4e4e4;
      text-decoration: none;
      margin-right: 20px;
      padding: 6px 12px;
      border-radius: 4px;
    }
    nav.view-menu a.active {
      background: #3a3a3a;
    }
    .content {
      padding: 20px;
    }
  </style>
</head>
<body>
  <?php DebugPanel::log('Rendering index.php, loading view system'); ?>

  <nav class="view-menu">
    <?php
      // Determine which view to load (fallback: developer)
      $currentView = $_GET['view'] ?? 'developer';

      // List of available views (add more filenames here as you build them)
      $views = ['developer'];

      foreach ($views as $viewName) {
          $isActive = ($viewName === $currentView) ? ' active' : '';
          echo "<a href=\"?view={$viewName}\" class=\"{$isActive}\">"
               . ucfirst($viewName)
               . "</a>";
      }
    ?>
  </nav>

  <div class="content">
    <?php
      // Securely build the path to the requested view
      $safeView = basename($currentView);
      $viewFile = __DIR__ . "/views/{$safeView}.php";

      if (is_readable($viewFile)) {
          include $viewFile;
      } else {
          echo "<p>View “{$safeView}” not found.</p>";
          DebugPanel::log("Missing view file: {$safeView}", ['path' => $viewFile]);
      }
    ?>
  </div>
</body>
</html>
