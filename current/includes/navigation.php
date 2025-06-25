<?php
// includes/navigation.php
// -------------------------------------------------------------------
// Renders the top‐bar navigation with the currently selected customer.
// -------------------------------------------------------------------

?>
<nav class="w-full bg-gray-800/80 backdrop-blur-md border-b border-gray-700 px-6 py-3 flex items-center justify-between">
  <!-- Left: Logo / Title -->
  <div class="flex items-center space-x-4">
    <h1 class="text-2xl font-bold text-white">MPSM Dashboard</h1>
  </div>

  <!-- Right: Selected Customer only -->
  <div class="flex items-center space-x-2">
    <span class="text-gray-400">Customer:</span>
    <?php
      // Grab the same GET param your card uses
      $selectedCustomer = htmlspecialchars($_GET['customer'] ?? '', ENT_QUOTES);
    ?>
    <span class="px-3 py-1 bg-gray-700 text-white rounded-md">
      <?= $selectedCustomer ?: '—' ?>
    </span>
  </div>
</nav>
