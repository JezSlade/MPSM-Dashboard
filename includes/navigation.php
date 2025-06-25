<?php
/**
 * includes/navigation.php — Sidebar navigation component
 */
?>
<aside class="sidebar w-14 md:w-40 p-4 flex flex-col space-y-4 neumorphic">
  <button id="sidebarToggle" class="neu-btn mb-4" aria-label="Toggle sidebar">
    <i data-feather="menu"></i>
  </button>
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
      <i data-feather="printer"></i>
      <span class="hidden md:inline">Printers</span>
    </a>
    <a href="#" class="neu-btn flex items-center space-x-2">
      <i data-feather="settings"></i>
      <span class="hidden md:inline">Settings</span>
    </a>
  </nav>

  <!--
  Changelog:
  - No changes needed for navigation.
  - Verified id attributes and structure.
  -->
</aside>
