<?php declare(strict_types=1);
// /includes/header.php

// Start buffering so setcookie() calls won’t break
ob_start();
?><!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Feather Icons -->
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <!-- Global Stylesheet -->
  <link rel="stylesheet" href="<?= APP_BASE_URL ?>public/css/styles.css" />
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

<header class="app-header relative flex items-center justify-end space-x-8 px-6 py-4 bg-gray-800 bg-opacity-75 backdrop-blur-md shadow-lg">
  <!-- CMYK neon glow overlay -->
  <div class="absolute inset-0 pointer-events-none" style="
       box-shadow:
         0 0 8px var(--cyan),
         0 0 12px var(--magenta),
         0 0 16px var(--yellow);
       opacity: 0.15;
     "></div>

  <div class="relative z-10 flex items-center space-x-8">
    <!-- Light/Dark Toggle -->
    <button id="theme-toggle"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-400"
            title="Toggle Light/Dark">
      <i data-feather="sun" class="h-8 w-8 text-cyan-400"></i>
    </button>

    <!-- Open Debug Log -->
    <button id="debug-toggle"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-magenta-400"
            title="Open Debug Log">
      <i data-feather="terminal" class="h-8 w-8 text-magenta-400"></i>
    </button>

    <!-- Clear Session Cookies -->
    <button id="clear-cookies"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400"
            title="Clear Session Cookies">
      <i data-feather="trash-2" class="h-8 w-8 text-yellow-400"></i>
    </button>

    <!-- Hard Refresh -->
    <button id="hard-refresh"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-black"
            title="Hard Refresh">
      <i data-feather="refresh-cw" class="h-8 w-8 text-black"></i>
    </button>
  </div>
</header>

<script>
// Utility functions
function openDebugLog() {
  const url = window.location.origin + '<?= APP_BASE_URL ?>components/debug-log.php';
  window.open(url, 'DebugLog', 'width=800,height=600');
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

document.addEventListener('DOMContentLoaded', () => {
  const root = document.documentElement;
  // Initialize theme from localStorage or system preference
  const saved = localStorage.getItem('theme');
  if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    root.classList.add('dark');
  } else {
    root.classList.remove('dark');
  }

  // Replace icons
  if (window.feather) feather.replace();

  // Theme toggle
  const themeBtn = document.getElementById('theme-toggle');
  themeBtn.addEventListener('click', () => {
    const isNowDark = root.classList.toggle('dark');
    localStorage.setItem('theme', isNowDark ? 'dark' : 'light');
    const icon = themeBtn.querySelector('i');
    icon.setAttribute('data-feather', isNowDark ? 'moon' : 'sun');
    if (window.feather) feather.replace();
  });

  // Debug log
  document.getElementById('debug-toggle').addEventListener('click', openDebugLog);
  // Clear cookies
  document.getElementById('clear-cookies').addEventListener('click', clearSessionCookies);
  // Hard refresh
  document.getElementById('hard-refresh').addEventListener('click', hardRefresh);
});
</script>
