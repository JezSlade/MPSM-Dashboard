<?php
/**
 * navigation.php — Sidebar navigation component.
 * Collapsible, with primary nav items.
 */
?>
<aside class="sidebar w-14 md:w-40 p-4 flex flex-col space-y-4">
  <!-- Collapse/Expand toggle -->
  <button class="neu-btn mb-4" aria-label="Toggle sidebar">
    <i data-feather="menu"></i>
  </button>
  <!-- Navigation links -->
  <nav class="flex flex-col space-y-2">
    <a href="#" class="neu-btn flex items-center space-x-2">
      <i data-feather="home"></i>
      <span class="hidden md:inline">Home</span>
    </a>
    <a href="#" class="neu-btn flex items-center space-x-2">
      <i data-feather="bar-chart-2"></i>
      <span class="hidden md:inline">Analytics</span>
    </a>
    <a href="#" class="neu-btn flex items-center space-x-2">
      <i data-feather="settings"></i>
      <span class="hidden md:inline">Settings</span>
    </a>
  </nav>
</aside>
