<?php
// /public/mpsm/modules/Customers/customers.php

// 1. If you already have an API helper (e.g. callGetCustomers()), require it here.
//    For now, we’ll stub $customerList as an empty array.
//
//    Once authentication is in place, replace this stub with your Working.php logic:
//      $token = getAccessToken();
//      $customerList = callGetCustomers($token);
//
//    (Do NOT forget to include or require the file containing getAccessToken() and callGetCustomers().)

$customerList = [];  // STUB: replace with real API data

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
      // When a customer is selected, you would load that customer’s devices here.
      // For now, just echo the chosen code. Replace with actual device‑loading logic.
      $chosen = htmlspecialchars($_POST['customerCode']);
      echo "<p>You chose customer: <strong>$chosen</strong></p>";
    ?>
  <?php endif; ?>
</div>
