<?php
// includes/navigation.php
// … existing nav markup above …
?>
<nav class="bg-gray-800 p-4 flex items-center justify-between">
  <!-- … your existing nav items … -->

  <!-- === CUSTOMER SELECTOR === -->
  <div class="flex items-center space-x-2 text-gray-200">
    <span>Customer:</span>
    <?php
      // Use the same helper to let you pick a customer globally
      require_once __DIR__ . '/searchable_dropdown.php';
      renderSearchableDropdown(
        'nav_customer_search',      // input ID
        'nav_customer_datalist',    // datalist ID
        '/api/get_customers.php',   // endpoint URL
        'customer',                 // cookie name
        '— Select —',               // placeholder
        'text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-2 focus:outline-none focus:ring-2 focus:ring-cyan-500'
      );
    ?>
  </div>
</nav>
<?php
// … rest of navigation …
