<?php declare(strict_types=1);
// /includes/header.php

// Buffer output so later setcookie() or header() calls in views still work
ob_start();

// ——————————————————————————————————————————————————————————————
// GLOBAL HEADER (loaded on every page, before any HTML output)
// ——————————————————————————————————————————————————————————————

?><!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Heroicons -->
  <script src="https://unpkg.com/heroicons@2.0.13/dist/heroicons.min.js"></script>
  <!-- Your existing styles -->
  <link rel="stylesheet" href="<?= APP_BASE_URL ?>public/css/styles.css" />
</head>
<body class="flex flex-col h-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

  <header class="app-header flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow-md">
    <div class="flex items-center space-x-4">
      <!-- Status Light -->
      <span class="h-3 w-3 rounded-full bg-green-500"></span>
      <!-- Theme Toggle -->
      <button
        id="theme-toggle"
        class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400"
        title="Toggle Light/Dark"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m8-8h1M3 12H2m15.364 6.364l.707.707M6.343 6.343l-.707-.707"/>
        </svg>
      </button>
    </div>

    <div class="flex items-center space-x-4">
      <!-- Clear Session Cookies (Cyan Trash) -->
      <button
        onclick="clearSessionCookies()"
        class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-300"
        title="Clear Session Cookies"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyan-500" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 
                   0116.138 21H7.862a2 2 0
                   01-1.995-1.858L5 7m5-4h4
                   m-4 0a1 1 0 00-1 1v1h6V4a1 
                   1 0 00-1-1m-4 0h4"/>
        </svg>
      </button>

      <!-- Hard Refresh (Magenta Refresh) -->
      <button
        onclick="hardRefresh()"
        class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-300"
        title="Hard Refresh"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-pink-500" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582M20 20v-5h-.581M5.64 19.364A9 9 0 1119.364 5.64"/>
        </svg>
      </button>

      <!-- Debug Log (Yellow Bug) -->
      <button
        onclick="openDebugLog()"
        class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-300"
        title="View Debug Log"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
          <path d="M11.049 2.927c.3-.921 1.603-.921
                   1.902 0l1.286 3.963a1 1 0
                   00.95.69h4.162c.969 0
                   1.371 1.24.588 1.81l-3.37
                   2.448a1 1 0 00-.363
                   1.118l1.286 3.963c.3.921-.755
                   1.688-1.54 1.118l-3.37-2.448a1
                   1 0 00-1.176 0l-3.37
                   2.448c-.784.57-1.84-.197-1.54-1.118l1.286-3.963a1
                   1 0 00-.362-1.118L2.963
                   9.39c-.783-.57-.38-1.81.588-1.81h4.163a1
                   1 0 00.95-.69l1.285-3.963z"/>
        </svg>
      </button>
    </div>
  </header>

  <script>
    function clearSessionCookies() {
      document.cookie.split(";").forEach(c => {
        document.cookie = c.trim().replace(/=.*/, "=;expires=Thu,01 Jan 1970 00:00:00 UTC;path=/");
      });
      alert("Session cookies cleared.");
    }
    function hardRefresh() {
      window.location.reload(true);
    }
    function openDebugLog() {
      window.open('/components/debug-log.php','DebugLog','width=800,height=600');
    }
  </script>
