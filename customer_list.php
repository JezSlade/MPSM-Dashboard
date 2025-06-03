<?php
// File: customer_list.php
// ------------------------
// Lists all customers fetched from the API.
// We ensure CSS is already loaded via header.php.

$pageTitle = 'MPSM Dashboard ‐ Customers';
require_once __DIR__ . '/header.php';
?>

  <!-- ========== Begin customer_list.php content ========== -->
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
            // Example PHP loop to render rows.
            // In reality, fetch $customers from the /Customer/GetCustomers API endpoint.
            $customers = [
              ['code' => 'CUST001', 'name' => 'Acme Corp',     'location' => 'Raleigh, NC',     'status' => 'Active'],
              ['code' => 'CUST002', 'name' => 'Beta Co',      'location' => 'Charlotte, NC',   'status' => 'Inactive'],
              ['code' => 'CUST003', 'name' => 'Gamma Industries', 'location' => 'Fayetteville, NC', 'status' => 'Active'],
              // … additional customer rows …
            ];

            foreach ($customers as $cust) {
              echo '<tr>';
              echo '<td>' . htmlspecialchars($cust['code']) . '</td>';
              echo '<td>' . htmlspecialchars($cust['name']) . '</td>';
              echo '<td>' . htmlspecialchars($cust['location']) . '</td>';
              echo '<td>' . htmlspecialchars($cust['status']) . '</td>';
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <!-- ========== End customer_list.php content ========== -->

<?php
require_once __DIR__ . '/footer.php';
