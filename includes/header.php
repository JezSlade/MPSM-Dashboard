<?php declare(strict_types=1);
ob_start();
ini_set('display_errors','0');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
?><!DOCTYPE html>
<html lang="en" class="h-full" data-theme="light">
<head>
  <meta charset="UTF-8"/>
  <title><?=htmlspecialchars($pageTitle ?? 'MPS Monitor Dashboard')?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <link rel="stylesheet" href="/public/css/styles.css"/>
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

<header class="relative flex justify-end items-center p-4 glass glass-cmyk space-x-4" role="banner">
  <button id="theme-toggle" class="tooltip p-2 rounded" title="Toggle Light/Dark Theme">
    <i data-feather="sun" class="h-6 w-6"></i>
  </button>
  <button id="debug-toggle" class="tooltip p-2 rounded" title="Open Debug Log">
    <i data-feather="terminal" class="h-6 w-6"></i>
  </button>
  <button id="clear-cookies" class="tooltip p-2 rounded" title="Clear Session Cookies">
    <i data-feather="trash-2" class="h-6 w-6"></i>
  </button>
  <button id="hard-refresh" class="tooltip p-2 rounded" title="Hard Refresh">
    <i data-feather="refresh-cw" class="h-6 w-6"></i>
  </button>
</header>

<script>
// Theme toggling with glassmorphic CMYK neon effect
const root = document.documentElement;
const themeBtn = document.getElementById('theme-toggle');
function applyTheme(theme) {
  root.classList.toggle('dark', theme==='dark');
  root.setAttribute('data-theme', theme);
  localStorage.setItem('theme', theme);
  const icon = themeBtn.querySelector('i');
  icon.setAttribute('data-feather', theme==='dark' ? 'moon' : 'sun');
  feather.replace();
}
document.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('theme') 
    || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  applyTheme(saved);
  themeBtn.addEventListener('click', () => {
    applyTheme(root.classList.contains('dark') ? 'light' : 'dark');
  });
  feather.replace();
  document.getElementById('debug-toggle').addEventListener('click', () => {
    window.open('https://mpsm.resolutionsbydesign.us/components/debug-log.php','DebugLog','width=800,height=600');
  });
  document.getElementById('clear-cookies').addEventListener('click', () => {
    document.cookie.split(';').forEach(c => {
      document.cookie = c.trim().replace(/=.*/, '=;expires=Thu,01 Jan 1970 00:00:00 UTC;path=/');
    });
    alert('Session cookies cleared.');
  });
  document.getElementById('hard-refresh').addEventListener('click', () => {
    location.reload(true);
  });
});
</script>
