<?php declare(strict_types=1);
// /includes/header.php

// Start output buffering so setcookie() calls later won’t error
ob_start();
?><!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Tailwind CSS & Heroicons -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/heroicons@2.0.13/dist/heroicons.min.js"></script>
  <link rel="stylesheet" href="<?= APP_BASE_URL ?>public/css/styles.css" />
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

<header class="app-header relative flex items-center justify-end space-x-6 px-6 py-4 bg-gray-800 bg-opacity-75 backdrop-blur-md shadow-lg">
  <!-- Neon CMYK glow -->
  <div class="absolute inset-0 pointer-events-none" style="
       box-shadow:
         0 0 8px var(--cyan),
         0 0 12px var(--magenta),
         0 0 16px var(--yellow);
       opacity: 0.15;
     "></div>

  <div class="relative z-10 flex items-center space-x-6">
    <!-- Theme Toggle -->
    <button id="theme-toggle"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-400"
            title="Toggle Light/Dark">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-cyan-400" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 14a4 4 0 100-8 4 4 0 000 8zM4.22 4.22a.75.75 0 011.06 0l1.06 1.06a.75.75 0 11-1.06 1.06L4.22 5.28a.75.75 0 010-1.06zM14.66 5.28a.75.75 0 011.06 1.06l-1.06 1.06a.75.75 0 11-1.06-1.06l1.06-1.06zM2 10a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5A.75.75 0 012 10zM14.75 10a.75.75 0 110-1.5h1.5a.75.75 0 010 1.5h-1.5zM4.22 15.78a.75.75 0 00-1.06-1.06l-1.06 1.06a.75.75 0 001.06 1.06l1.06-1.06zM15.78 15.78a.75.75 0 00-1.06 0l-1.06 1.06a.75.75 0 001.06 1.06l1.06-1.06a.75.75 0 000-1.06z"/>
      </svg>
    </button>

    <!-- Open Debug Log -->
    <button onclick="openDebugLog()"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-magenta-400"
            title="Open Debug Log">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-magenta-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9.172 16.828a4 4 0 115.656-5.656l1.415-1.414a6 6 0 10-8.486 8.486l1.415-1.414z" />
      </svg>
    </button>

    <!-- Clear Session Cookies -->
    <button onclick="clearSessionCookies()"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400"
            title="Clear Session Cookies">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 4a2 2 0 00-2 2v1H4a2 2 0 000 4h2v6a2 2 0 002 2h8a2 2 0 002-2v-2h1a2 2 0 100-4h-1V6a2 2 0 00-2-2H8z" />
      </svg>
    </button>

    <!-- Hard Refresh -->
    <button onclick="hardRefresh()"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-black"
            title="Hard Refresh">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h5M20 20v-5h-5M5.629 18.364A9 9 0 1118.364 5.63" />
      </svg>
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
// Theme toggle listener remains in public/js/theme.js
</script>
