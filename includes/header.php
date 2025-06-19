<?php declare(strict_types=1);
// /includes/header.php

// Start buffering so setcookie() calls won’t error
ob_start();
?><!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Tailwind CSS only -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= APP_BASE_URL ?>public/css/styles.css" />
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

<header class="app-header relative flex items-center justify-end space-x-8 px-6 py-4 bg-gray-800 bg-opacity-75 backdrop-blur-md shadow-lg">
  <!-- CMYK Neon Glow -->
  <div class="absolute inset-0 pointer-events-none" style="
       box-shadow:
         0 0 8px var(--cyan),
         0 0 12px var(--magenta),
         0 0 16px var(--yellow);
       opacity: 0.15;
     "></div>

  <div class="relative z-10 flex items-center space-x-8 text-3xl">
    <!-- Tech Monitor (desktop icon) as Light/Dark toggle -->
    <button id="theme-toggle"
            class="focus:outline-none focus:ring-2 focus:ring-cyan-400"
            title="Toggle Light/Dark">
      <span class="text-cyan-400">🖥️</span>
    </button>

    <!-- Debug Log (terminal icon) -->
    <button onclick="openDebugLog()"
            class="focus:outline-none focus:ring-2 focus:ring-magenta-400"
            title="Open Debug Log">
      <span class="text-magenta-400">💻</span>
    </button>

    <!-- Clear Session Cookies (broom icon) -->
    <button onclick="clearSessionCookies()"
            class="focus:outline-none focus:ring-2 focus:ring-yellow-400"
            title="Clear Session Cookies">
      <span class="text-yellow-400">🧹</span>
    </button>

    <!-- Hard Refresh -->
    <button onclick="hardRefresh()"
            class="focus:outline-none focus:ring-2 focus:ring-black"
            title="Hard Refresh">
      <span class="text-black">🔄</span>
    </button>
  </div>
</header>

<script>
// Utility functions
function openDebugLog() {
  window.open('/components/debug-log.php','DebugLog','width=800,height=600');
}
function clearSessionCookies() {
  document.cookie.split(';').forEach(c =>
    document.cookie = c.trim().replace(/=.*/, '=;expires=Thu,01 Jan 1970 00:00:00 UTC;path=/')
  );
  alert('Session cookies cleared.');
}
function hardRefresh() {
  window.location.reload(true);
}

// Theme toggle hook (you can wire your theme.js here too)
document.getElementById('theme-toggle').addEventListener('click', () => {
  document.documentElement.classList.toggle('dark');
});
</script>
