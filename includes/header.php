<?php
declare(strict_types=1);
// /includes/header.php

// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

session_start();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard for <?php echo htmlspecialchars(DEALER_CODE, ENT_QUOTES, 'UTF-8'); ?></title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Global styles -->
  <link rel="stylesheet" href="/public/css/styles.css">

  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Render all feather icons
      feather.replace();

      // Theme toggle
      const themeToggle = document.getElementById('themeToggle');
      themeToggle.addEventListener('click', () => {
        const html = document.documentElement;
        const current = html.getAttribute('data-theme') || 'light';
        html.setAttribute('data-theme', current === 'light' ? 'dark' : 'light');
      });

      // Hard refresh (bypass cache)
      document.getElementById('hardRefresh').addEventListener('click', () => {
        location.reload(true);
      });

      // Clear session and reload
      document.getElementById('clearSession').addEventListener('click', () => {
        fetch('/api/clear_session.php', { method: 'POST' })
          .then(() => location.reload());
      });

      // View error log
      document.getElementById('viewErrorLog').addEventListener('click', () => {
        window.open('/logs/debug.log', '_blank');
      });
    });
  </script>
</head>
<body class="flex h-screen bg-bg-light text-text-light">
  <header class="flex items-center justify-between p-4 bg-bg-dark text-text-dark shadow-md">
    <div class="flex items-center space-x-4">
      <h1 class="text-2xl font-bold">
        Dashboard for <?php echo htmlspecialchars(DEALER_CODE, ENT_QUOTES, 'UTF-8'); ?>
      </h1>
    </div>
    <div class="flex items-center space-x-3">
      <button id="themeToggle" aria-label="Toggle theme" class="p-2 rounded hover:bg-bg-light/10">
        <i data-feather="sun"></i>
      </button>
      <button id="hardRefresh" aria-label="Hard refresh" class="p-2 rounded hover:bg-bg-light/10">
        <i data-feather="refresh-cw"></i>
      </button>
      <button id="clearSession" aria-label="Delete session" class="p-2 rounded hover:bg-bg-light/10">
        <i data-feather="trash-2"></i>
      </button>
      <button id="viewErrorLog" aria-label="Error log" class="p-2 rounded hover:bg-bg-light/10">
        <i data-feather="file-text"></i>
      </button>
    </div>
  </header>
  <!-- main content will be injected by the view -->
