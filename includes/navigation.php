<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="px-6 py-4 bg-gray-800 bg-opacity-50 backdrop-blur-sm flex items-center">
  <form method="get" action="">
    <label for="customer" class="sr-only">Customer</label>
    <select id="customer" name="customer"
            onchange="this.form.submit()"
            class="bg-gray-700 text-gray-100 p-2 rounded">
      <option value="">All Customers</option>
      <?php foreach ($customers as $c): ?>
        <option value="<?= htmlspecialchars($c['Code']) ?>"
          <?= ($c['Code'] ?? '') === $customerCode ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['Description'] ?? $c['Name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>
</nav>
