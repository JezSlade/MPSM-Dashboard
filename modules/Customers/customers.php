<?php
// /public/mpsm/modules/Customers/customers.php

$customerList = [];  // Replace with real API data once integrated
?>
<div class="module-container">
  <h2>Customers</h2>
  <form action="index.php?module=customers" method="POST">
    <label for="customerSelect">Select Customer:</label>
    <select id="customerSelect" name="customerCode">
      <option value="">-- Choose a Customer --</option>
      <?php foreach ($customerList as $cust): ?>
        <option value="<?= htmlspecialchars($cust['code']) ?>">
          <?= htmlspecialchars($cust['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Load Devices</button>
  </form>

  <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['customerCode'])): ?>
    <?php
      $chosen = htmlspecialchars($_POST['customerCode']);
      echo "<p>You chose customer: <strong>$chosen</strong></p>";
    ?>
  <?php endif; ?>
</div>
