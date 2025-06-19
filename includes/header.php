<?php declare(strict_types=1);
// /includes/header.php

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
  <!-- Global Styles -->
  <link rel="stylesheet" href="<?= APP_BASE_URL ?>public/css/styles.css" />
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

<header class="app-header relative flex items-center justify-between px-4 py-2 bg-gray-800 bg-opacity-75 backdrop-blur-md shadow-lg">
  <!-- CMYK neon glow -->
  <div class="absolute inset-0 pointer-events-none" style="
       box-shadow: 0 0 6px var(--cyan), 0 0 8px var(--magenta), 0 0 10px var(--yellow);
       opacity: 0.1;
     "></div>

  <!-- Left: Searchable customer combobox -->
  <div class="relative z-10 flex-1 max-w-xs">
    <label for="customer-search" class="sr-only">Customer</label>
    <input
      id="customer-search"
      list="customer-list"
      placeholder="— choose a customer —"
      class="w-full text-sm bg-gray-800 text-white border border-gray-700 rounded-md py-1 px-2 focus:outline-none focus:ring-1 focus:ring-cyan-400 focus:border-cyan-400"
    />
    <datalist id="customer-list" class="hidden"></datalist>
  </div>

  <!-- Right: Utility icons -->
  <div class="relative z-10 flex items-center space-x-6">
    <button id="theme-toggle" class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-400" title="Toggle Light/Dark">
      <i data-feather="sun" class="h-5 w-5 text-cyan-400"></i>
    </button>
    <button onclick="openDebugLog()" class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-magenta-400" title="Open Debug Log">
      <i data-feather="terminal" class="h-5 w-5 text-magenta-400"></i>
    </button>
    <button onclick="clearSessionCookies()" class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400" title="Clear Session Cookies">
      <i data-feather="trash-2" class="h-5 w-5 text-yellow-400"></i>
    </button>
    <button onclick="hardRefresh()" class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-black" title="Hard Refresh">
      <i data-feather="refresh-cw" class="h-5 w-5 text-black"></i>
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

// On DOM ready
document.addEventListener('DOMContentLoaded', () => {
  // Render Feather icons
  if (window.feather) feather.replace();

  // --- Customer combobox setup ---
  const datalist = document.getElementById('customer-list');
  const input    = document.getElementById('customer-search');
  // Get current cookie
  const cookieMatch = document.cookie.match(/(?:^|; )customer=([^;]+)/);
  const currentCode = cookieMatch ? decodeURIComponent(cookieMatch[1]) : '';

  // Fetch customers
  fetch('/api/get_customers.php')
    .then(res => res.json())
    .then(customers => {
      // Populate datalist
      datalist.innerHTML = '';
      customers.forEach(c => {
        const opt = document.createElement('option');
        opt.value       = `${c.Description || c.Name}`;
        opt.dataset.code= c.Code;
        datalist.appendChild(opt);
      });
      // Pre-fill input if we have a cookie match
      if (currentCode) {
        const match = Array.from(datalist.options)
                           .find(o => o.dataset.code === currentCode);
        if (match) input.value = match.value;
      }
    })
    .catch(err => {
      console.error('Error loading customers:', err);
    });

  // On selection, set cookie & reload
  input.addEventListener('change', () => {
    const match = Array.from(datalist.options)
                       .find(o => o.value === input.value);
    const code  = match ? match.dataset.code : '';
    if (code) {
      document.cookie = `customer=${encodeURIComponent(code)};path=/;max-age=${60*60*24*365}`;
      window.location.reload();
    }
  });

  // --- Theme toggle logic ---
  const themeBtn = document.getElementById('theme-toggle');
  themeBtn.addEventListener('click', () => {
    const isDark = document.documentElement.classList.toggle('dark');
    const icon   = themeBtn.querySelector('i');
    icon.setAttribute('data-feather', isDark ? 'moon' : 'sun');
    if (window.feather) feather.replace();
  });
});
</script>
