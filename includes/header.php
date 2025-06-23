<?php declare(strict_types=1);
// /includes/header.php

// Start output buffering so any setcookie() calls later won’t fail
ob_start();
?><!DOCTYPE html>
<html lang="en" class="h-full dark">
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
  <!-- Neon CMYK glow overlay -->
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

    <!-- Debug Log -->
    <button onclick="openDebugLog()"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-magenta-400"
            title="Open Debug Log">
      <i data-feather="terminal" class="h-8 w-8 text-magenta-400"></i>
    </button>

    <!-- Clear Session Cookies -->
    <button onclick="clearSessionCookies()"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400"
            title="Clear Session Cookies">
      <i data-feather="trash-2" class="h-8 w-8 text-yellow-400"></i>
    </button>

    <!-- Hard Refresh -->
    <button onclick="hardRefresh()"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-black"
            title="Hard Refresh">
      <i data-feather="refresh-cw" class="h-8 w-8 text-black"></i>
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

// On DOM ready: render Feather icons and wire up theme toggle
document.addEventListener('DOMContentLoaded', () => {
  if (window.feather) feather.replace();

  const themeBtn = document.getElementById('theme-toggle');
  themeBtn.addEventListener('click', () => {
    const isDark = document.documentElement.classList.toggle('dark');
    const icon   = themeBtn.querySelector('i');
    icon.setAttribute('data-feather', isDark ? 'moon' : 'sun');
    if (window.feather) feather.replace();
  });
});
</script>
