<?php declare(strict_types=1);
// includes/header.php
ob_start();
ini_set('display_errors','0');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
?>
<!DOCTYPE html>
<html lang="en" class="h-full" data-theme="light">
<head>
  <meta charset="UTF-8"/>
  <title><?= htmlspecialchars($pageTitle ?? 'MPS Monitor Dashboard') ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <link rel="stylesheet" href="/public/css/styles.css"/>
</head>
<body class="flex flex-col h-full text-white">

<header class="flex justify-between items-center p-4 neon-glass-header">
  <div class="flex items-center space-x-4">
    <button id="theme-toggle" class="neon-btn neon-cyan tooltip" title="Toggle Light/Dark Theme">
      <i data-feather="sun" class="h-6 w-6"></i>
    </button>
    <button id="preferences-toggle" class="neon-btn neon-magenta tooltip" title="Open Preferences">
      <i data-feather="settings" class="h-6 w-6"></i>
    </button>
  </div>
  <div class="flex items-center space-x-4">
    <button id="debug-toggle" class="neon-btn neon-yellow tooltip" title="Debug Log">
      <i data-feather="terminal" class="h-6 w-6"></i>
    </button>
    <button id="clear-cookies" class="neon-btn neon-cyan tooltip" title="Clear Cookies">
      <i data-feather="trash-2" class="h-6 w-6"></i>
    </button>
    <button id="hard-refresh" class="neon-btn neon-magenta tooltip" title="Hard Refresh">
      <i data-feather="refresh-cw" class="h-6 w-6"></i>
    </button>
  </div>
</header>

<script>
// Theme toggling
const root = document.documentElement;
const themeBtn = document.getElementById('theme-toggle');
function applyTheme(t) {
  root.classList.toggle('dark', t==='dark');
  root.setAttribute('data-theme', t);
  localStorage.setItem('theme', t);
  themeBtn.querySelector('i').setAttribute('data-feather', t==='dark'?'moon':'sun');
  feather.replace();
}
document.addEventListener('DOMContentLoaded',()=>{
  const saved = localStorage.getItem('theme')
    || (window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');
  applyTheme(saved);
  themeBtn.onclick = ()=> applyTheme(root.classList.contains('dark')?'light':'dark');
  feather.replace();

  document.getElementById('debug-toggle').onclick = ()=> {
    window.open('/components/debug-log.php','DebugLog','width=800,height=600');
  };
  document.getElementById('clear-cookies').onclick = ()=> {
    document.cookie.split(';').forEach(c=>
      document.cookie = c.trim().replace(/=.*/,'=;expires=Thu,01 Jan 1970 00:00:00 UTC;path=/'));
    alert('Session cookies cleared');
  };
  document.getElementById('hard-refresh').onclick = ()=> location.reload(true);
});
</script>
