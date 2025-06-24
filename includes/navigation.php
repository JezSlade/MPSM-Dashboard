<?php declare(strict_types=1);
// /includes/navigation.php
// Expects $customers (array of ['Code'=>…,'Description'=>…]) and $customerCode
?>
<form method="get" action="" class="flex items-center">
  <label for="customer-select" class="sr-only">Select Customer</label>
  <select id="customer-select"
          name="customer"
          onchange="this.form.submit()"
          class="bg-gray-700 text-gray-100 px-3 py-1 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-400">
    <option value="">All Customers</option>
    <?php foreach ($customers as $c): 
      $code = $c['Code'] ?? '';
      $label = $c['Description'] ?? $c['Name'] ?? $code;
    ?>
      <option value="<?= htmlspecialchars($code) ?>"
        <?= $code === ($customerCode ?? '') ? 'selected' : '' ?>>
        <?= htmlspecialchars($label) ?>
      </option>
    <?php endforeach; ?>
  </select>
</form>
