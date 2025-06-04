<?php
$pageTitle = 'MPSM Dashboard ‐ Customers';
require_once __DIR__ . '/header.php';
?>
  <section class="customers-section">
    <h1 class="section-title">Customers</h1>
    <div class="card" style="margin-top: 1rem;">
      <h2 class="sub-title">All Customers</h2>
      <div style="margin-top: 1rem;">
        <table class="table">
          <thead>
            <tr>
              <th>Customer Code</th>
              <th>Name</th>
              <th>Location</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            require_once __DIR__ . '/api_functions.php';
            $dealerCode = 'SZ13qRwU5GtFLj0i_CbEgQ2';
            try {
                $customers = getCustomers($dealerCode);
            } catch (Exception $e) {
                echo '<tr><td colspan="4" style="color: #ff9000;">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                $customers = [];
            }
            foreach ($customers as $cust) {
              echo '<tr>';
              echo '<td>' . htmlspecialchars($cust['Code'] ?? '') . '</td>';
              echo '<td>' . htmlspecialchars($cust['Description'] ?? '') . '</td>';
              echo '<td>' . htmlspecialchars($cust['Location'] ?? '') . '</td>';
              echo '<td>' . (isset($cust['IsActive']) && $cust['IsActive'] ? 'Active' : 'Inactive') . '</td>';
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
<?php
require_once __DIR__ . '/footer.php';
?>