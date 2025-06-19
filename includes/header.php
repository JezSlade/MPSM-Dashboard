<?php declare(strict_types=1);
// /includes/header.php

// Buffer at start so setcookie() later won’t break
ob_start();

// --------------------------------------------------------------------------------
// GLOBAL HEADER (dark mode by default, CMYK‐themed icons rethought)
// --------------------------------------------------------------------------------
?><!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Tailwind CSS + Heroicons -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/heroicons@2.0.13/dist/heroicons.min.js"></script>
  <link rel="stylesheet" href="<?= APP_BASE_URL ?>public/css/styles.css" />
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

  <header class="app-header flex items-center justify-between px-6 py-4 bg-gray-800 bg-opacity-75 backdrop-blur-md shadow-lg">
    <div class="flex items-center space-x-4">
      <!-- Status Light -->
      <span class="h-3 w-3 rounded-full bg-green-400"></span>
      <!-- Theme Toggle -->
      <button
        id="theme-toggle"
        class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400"
        title="Toggle Light/Dark"
      >
        <!-- Sun/Moon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m8-8h1M3 12H2m15.364
                   6.364l.707.707M6.343 6.343l-.707-.707"/>
        </svg>
      </button>
    </div>

    <div class="flex items-center space-x-4">
      <!-- Printer -->
      <button
        onclick="clearSessionCookies()"
        class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400"
        title="Printer"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyan-400" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2
                   2 0 012-2h16a2 2 0 012 2v5a2 2 0
                   01-2 2h-2M6 14h12v8H6v-8z"/>
        </svg>
      </button>

      <!-- Device -->
      <button
        onclick="hardRefresh()"
        class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-magenta-400"
        title="Device"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-magenta-400" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 16V4a1 1 0 011-1h8a1 1 0 011
                   1v12M8 20h8m-4-4v4"/>
        </svg>
      </button>

      <!-- Remote -->
      <button
        onclick="openDebugLog()"
        class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400"
        title="Remote"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 10l4-4m0 0l-4-4m4 4H9
                   m6 14l-4-4m0 0l4-4m-4 4h10"/>
        </svg>
      </button>

      <!-- Dashboard -->
      <button
        onclick="openDebugLog()"
        class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400"
        title="Dashboard"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h18v18H3V3zm6 6h6v6H9V9z"/>
        </svg>
      </button>
    </div>
  </header>

  <script>
    function clearSessionCookies() {
      document.cookie.split(";").forEach(c =>
        document.cookie = c.trim().replace(/=.*/, "=;expires=Thu,01 Jan 1970 00:00:00 UTC;path=/")
      );
      alert("Session cookies cleared.");
    }
    function hardRefresh() {
      window.location.reload(true);
    }
    function openDebugLog() {
      window.open('/components/debug-log.php','DebugLog','width=800,height=600');
    }
    // theme-toggle listener remains elsewhere
  </script>
