<?php
// includes/header.php
// -------------------------------------------------------------------
// Global header: logo, title, feather‐icon buttons.
// -------------------------------------------------------------------
?>
<header class="flex items-center justify-between p-4
               bg-white bg-opacity-10 backdrop-blur-md
               border-b border-white border-opacity-20">
  <div class="flex items-center">
    <img src="/public/images/logo.png" alt="MPSM Logo" class="h-10 mr-3">
    <h1 class="text-2xl font-semibold text-white drop-shadow-lg">MPSM Dashboard</h1>
  </div>
  <div class="flex items-center space-x-4">
    <button id="theme-toggle" aria-label="Toggle theme"
      class="p-2 rounded-md bg-white bg-opacity-20 hover:bg-opacity-30 transition">
      <i data-feather="moon" class="text-cyan-400"></i>
    </button>
    <button id="refresh-all" aria-label="Refresh all cards"
      class="p-2 rounded-md bg-white bg-opacity-20 hover:bg-opacity-30 transition">
      <i data-feather="refresh-ccw" class="text-magenta-400"></i>
    </button>
    <button id="help" aria-label="Help"
      class="p-2 rounded-md bg-white bg-opacity-20 hover:bg-opacity-30 transition">
      <i data-feather="help-circle" class="text-yellow-400"></i>
    </button>
  </div>
</header>
