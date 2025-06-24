<?php
// /views/dashboard.php
// Dashboard View: renders all cards in a responsive grid

// (Header, navigation, and debug setup are handled in index.php)

// Capture any selected customer from query string
$selectedCustomer = $_GET['customer'] ?? null;
?>
<main>
  <!-- Responsive card grid -->
  <div
    id="cardGrid"
    class="card-grid"
    style="
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      padding: 20px;
    "
  >
    <!-- Customer selector card (table rows inside this will be clickable) -->
    <?php include __DIR__ . '/../cards/CustomersCard.php'; ?>

    <!-- Example of other cards; add or remove as needed -->
    <?php include __DIR__ . '/../cards/DeviceCountersCard.php'; ?>
    <?php include __DIR__ . '/../cards/SupplyAlertsCard.php'; ?>
  </div>
</main>

<!-- Client‐side behavior for sorting, expand/collapse, drilldown, and customer‐row clicks -->
<script src="/public/js/card-interactions.js"></script>
