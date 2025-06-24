<?php
// includes/header.php — Logo + neon icons
?>
<header class="global-header">
  <div class="flex items-center">
    <img src="/public/images/logo.png" alt="MPSM Logo" class="logo">
    <h1 class="app-title">MPSM Dashboard</h1>
  </div>
  <div class="flex items-center space-x-4">
    <button id="theme-toggle" class="icon-btn" aria-label="Toggle theme">
      <i data-feather="moon" class="text-cyan-400"></i>
    </button>
    <button id="refresh-all" class="icon-btn" aria-label="Refresh all cards">
      <i data-feather="refresh-ccw" class="text-magenta-400"></i>
    </button>
    <button id="help" class="icon-btn" aria-label="Help">
      <i data-feather="help-circle" class="text-yellow-400"></i>
    </button>
  </div>
</header>
