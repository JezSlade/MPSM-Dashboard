<?php declare(strict_types=1);
// /includes/header.php

ob_start();
?><!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/heroicons@2.0.13/dist/heroicons.min.js"></script>
  <link rel="stylesheet" href="<?= APP_BASE_URL ?>public/css/styles.css" />
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

<header class="app-header relative flex items-center justify-end space-x-6 px-6 py-4 bg-gray-800 bg-opacity-75 backdrop-blur-md shadow-lg">
  <div class="absolute inset-0 pointer-events-none" style="
       box-shadow:
         0 0 8px var(--cyan),
         0 0 12px var(--magenta),
         0 0 16px var(--yellow);
       opacity: 0.15;
     "></div>
  <div class="relative z-10 flex items-center space-x-6">
    <!-- Theme Toggle -->
    <button id="theme-toggle" class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-400" title="Toggle Light/Dark">
      <!-- SVG omitted for brevity -->
    </button>

    <!-- Debug Log -->
    <button onclick="openDebugLog()" class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-magenta-400" title="Open Debug Log">
      <!-- SVG omitted -->
    </button>

    <!-- Clear Cookies -->
    <button onclick="clearSessionCookies()" class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400" title="Clear Session Cookies">
      <!-- SVG omitted -->
    </button>

    <!-- Hard Refresh -->
    <button onclick="hardRefresh()" class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-black" title="Hard Refresh">
      <!-- SVG omitted -->
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

// **Attach the Preferences‐Modal toggle** to the gear button when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const gear = document.getElementById('preferences-toggle');
  const modal = document.getElementById('preferences-modal');
  if (gear && modal) {
    gear.addEventListener('click', () => {
      modal.classList.toggle('hidden');
    });
  }
});
// Theme toggle listener remains in public/js/theme.js
</script>
