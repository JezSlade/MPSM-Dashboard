<?php declare(strict_types=1);
// /includes/header.php

// Buffer at start so setcookie() later won’t break
ob_start();
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

<header class="app-header relative flex items-center justify-center space-x-4 px-6 py-4 bg-gray-800 bg-opacity-75 backdrop-blur-md shadow-lg">
  <!-- Neon CMYK glow overlay -->
  <div class="absolute inset-0 pointer-events-none" style="
       box-shadow:
         0 0 8px var(--cyan),
         0 0 12px var(--magenta),
         0 0 16px var(--yellow);
       opacity: 0.15;
     "></div>

  <div class="relative z-10 flex items-center space-x-6">
    <!-- Devices (Printer icon, Cyan) -->
    <button onclick="viewDevices()" class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-400" title="Devices">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 9V2h12v7M6 22h12v-7H6v7zM6 14h12M6 18h12" />
      </svg>
    </button>

    <!-- Alerts (Bell icon, Magenta) -->
    <button onclick="viewAlerts()" class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-magenta-400" title="Alerts">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-magenta-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11
                 a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5
                 m6 0a3 3 0 11-6 0h6z" />
      </svg>
    </button>

    <!-- Supplies (Cube icon, Yellow) -->
    <button onclick="viewSupplies()" class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400" title="Supplies">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 16V8a2 2 0 00-1-1.732l-8-4.62L3 6.268
                 A2 2 0 002 8v8a2 2 0 001 1.732l8 4.62
                 8-4.62A2 2 0 0021 16z" />
      </svg>
    </button>

    <!-- Preferences (Gear icon, Black) -->
    <button onclick="togglePreferencesModal(true)" class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-black" title="Preferences">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11.049 2.927c.3-.92 1.603-.92 1.902 0
                 a1.03 1.03 0 00.95.69h2.091
                 a1.03 1.03 0 00.64-1.788l-1.578-1.578
                 a1.03 1.03 0 000-1.414l1.578-1.578
                 a1.03 1.03 0 01.64 1.788h-2.091
                 a1.03 1.03 0 00-.95-.69c-.3-.92-1.603-.92-1.902 0
                 a1.03 1.03 0 00-.95.69H8.96
                 a1.03 1.03 0 01-.64 1.788l1.578 1.578
                 a1.03 1.03 0 000 1.414L8.32 4.405
                 a1.03 1.03 0 01.64-1.788h1.438
                 c.44 0 .84-.308.951-.69z" />
      </svg>
    </button>

    <!-- Theme Toggle (Sun/Moon) -->
    <button id="theme-toggle" class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-400" title="Toggle Light/Dark">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 2a.75.75 0 01.75.75v1.5
                 a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2z
                 M10 14a4 4 0 100-8 4 4 0 000 8z
                 M4.22 4.22a.75.75 0 011.06 0l1.06 1.06
                 a.75.75 0 11-1.06 1.06L4.22 5.28
                 a.75.75 0 010-1.06z
                 M14.66 5.28a.75.75 0 011.06 1.06l-1.06 1.06
                 a.75.75 0 11-1.06-1.06l1.06-1.06z
                 M2 10a.75.75 0 01.75-.75h1.5
                 a.75.75 0 010 1.5h-1.5A.75.75 0 012 10z
                 M14.75 10a.75.75 0 110-1.5h1.5
                 a.75.75 0 010 1.5h-1.5z
                 M4.22 15.78a.75.75 0 00-1.06-1.06
                 l-1.06 1.06a.75.75 0 001.06 1.06
                 l1.06-1.06z
                 M15.78 15.78a.75.75 0 00-1.06 0l-1.06 1.06
                 a.75.75 0 001.06 1.06l1.06-1.06
                 a.75.75 0 000-1.06z"/>
      </svg>
    </button>
  </div>
</header>

<script>
// Navigation helpers (you can adjust URLs as needed)
function viewDevices() {
  window.location.href = '/views/dashboard.php?view=devices';
}
function viewAlerts() {
  window.location.href = '/views/dashboard.php?view=alerts';
}
function viewSupplies() {
  window.location.href = '/views/dashboard.php?view=supplies';
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
function openDebugLog() {
  window.open('/components/debug-log.php','DebugLog','width=800,height=600');
}
// Theme toggle listener is still in public/js/theme.js
</script>
