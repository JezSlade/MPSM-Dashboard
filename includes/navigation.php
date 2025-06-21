<?php declare(strict_types=1);
// includes/navigation.php
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) return;
require_once __DIR__ . '/searchable_dropdown.php';
?>
<nav class="p-4 neon-glass-nav flex justify-center">
  <?php renderSearchableDropdown(
    'nav-customer-combobox',
    'nav-customer-list',
    '/api/get_customers.php',
    'customer',
    '— Choose Customer —',
    'w-64 text-white bg-transparent border border-white border-opacity-50 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-400'
  ); ?>
</nav>
