<?php
// includes/header.php — Global header with neon‐accent icon buttons
?>
<header class="flex items-center justify-between p-4
               bg-white bg-opacity-10 backdrop-blur-md
               border-b border-white border-opacity-20">
  <div class="flex items-center">
    <img src="/public/images/logo.png" alt="MPSM Logo" class="h-10 mr-3">
    <h1 class="text-2xl font-semibold text-white drop-shadow-lg">
      MPSM Dashboard
    </h1>
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
