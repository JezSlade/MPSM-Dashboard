<?php
/**
 * index.php
 *
 * Entry point for MPS Monitor Dashboard POC.
 * Now lives in project root—no subfolders required.
 *
 * Responsibilities:
 *  - Load config & debug utilities from src/
 *  - Render a simple “Views” menu
 *  - Include the requested view from views/
 */

require __DIR__ . '/src/config.php';       // loads .env vars & constants
require __DIR__ . '/src/DebugPanel.php';    // debug logging utility

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MPS Monitor Dashboard</title>

  <!-- Load your CSS from root/styles.css -->
  <link rel="stylesheet" href="/styles.css">

  <!-- Fallback inline styles if styles.css is missing -->
  <style>
    body { margin:0; background:#1e1e1e; color:#e4e4e4; font-family:sans-serif; }
    nav.view-menu { background:#2a2a2a; padding:10px; display:flex; }
    nav.view-menu a {
      color:#e4e4e4; text-decoration:none; margin-right:20px;
      padding:6px 12px; border-radius:4px;
    }
    nav.view-menu a.active { background:#3a3a3a; }
    .content { padding:20px; }
  </style>
</head>
<body>
  <?php DebugPanel::log('Root index.php loaded'); ?>

  <!-- VIEW SELECTION -->
  <nav class="view-menu">
    <?php
      // Pick view from ?view= query (default 'developer')
      $current = $_GET['view'] ?? 'developer';
      // Whitelist available views
      $views = ['developer'];
      foreach ($views as $v) {
          $cls = $v === $current ? 'active' : '';
          echo "<a href=\"?view={$v}\" class=\"{$cls}\">" . ucfirst($v) . "</a>";
      }
    ?>
  </nav>

  <!-- VIEW CONTENT -->
  <div class="content">
    <?php
      // Securely include the view file from /views/
      $safe = basename($current);
      $file = __DIR__ . "/views/{$safe}.php";

      if (is_readable($file)) {
          include $file;
      } else {
          echo "<p><em>View “{$safe}” not found.</em></p>";
          DebugPanel::log("Missing view file", ['path' => $file]);
      }
    ?>
  </div>
</body>
</html>
