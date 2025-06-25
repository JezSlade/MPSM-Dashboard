<?php
// includes/header.php — Logo + three action buttons
?>
<header class="flex items-center justify-between p-4
               bg-white/10 backdrop-blur-md border-b border-white/20">
  <div class="flex items-center">
    <img src="/public/images/logo.png" alt="" class="h-10 mr-3">
    <h1 class="text-2xl font-semibold text-white drop-shadow-lg">
      MPSM Dashboard
    </h1>
  </div>
  <div class="flex items-center space-x-4">
    <!-- Theme toggle -->
    <button id="theme-toggle"
      class="p-2 rounded-md bg-white/20 hover:bg-white/30 transition"
      aria-label="Toggle light/dark mode">
      <i data-feather="moon" class="text-cyan-400"></i>
    </button>

    <!-- Clear session cookies -->
    <button id="clear-session"
      class="p-2 rounded-md bg-white/20 hover:bg-white/30 transition"
      aria-label="Clear session cookies">
      <i data-feather="trash-2" class="text-yellow-400"></i>
    </button>

    <!-- Hard refresh -->
    <button id="refresh-all"
      class="p-2 rounded-md bg-white/20 hover:bg-white/30 transition"
      aria-label="Reload page">
      <i data-feather="refresh-cw" class="text-magenta-400"></i>
    </button>
  </div>
</header>
