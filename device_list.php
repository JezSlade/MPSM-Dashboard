<?php
// File: device_list.php
// ----------------------
// Displays a paginated list of devices for the selected customer code.
// Assumes that CSS is already loaded via header.php.

$pageTitle = 'MPSM Dashboard ‐ Devices';
require_once __DIR__ . '/header.php';
?>

  <!-- ========== Begin device_list.php content ========== -->
  <section class="devices-section">
    <h1 class="section-title">Devices</h1>

    <div class="card" style="margin-top: 1rem;">
      <h2 class="sub-title">All Devices</h2>

      <div style="margin-top: 1rem;">
        <!-- Example pagination controls (could be generated via PHP) -->
        <div class="pagination" style="margin-bottom: 1rem;">
          <button class="btn-outline" disabled>Prev</button>
          <span style="color: #e3e6ea; margin: 0 0.5rem;">Page 1 / 10</span>
          <button class="btn-neon">Next</button>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th>SEID</th>
              <th>Brand</th>
              <th>Model</th>
              <th>Serial Number</th>
              <th>IP Address</th>
              <th>Alerts</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Example placeholder data. In practice, call /Device/List with the selected CustomerCode.
            $devices = [
              [
                'SEID'       => 'DEV12345',
                'Brand'      => 'BrandX',
                'Model'      => 'ModelA',
                'Serial'     => 'SN-00123',
                'IpAddress'  => '192.168.1.10',
                'Alerts'     => 2
              ],
              [
                'SEID'       => 'DEV67890',
                'Brand'      => 'BrandY',
                'Model'      => 'ModelB',
                'Serial'     => 'SN-00456',
                'IpAddress'  => '192.168.1.11',
                'Alerts'     => 0
              ],
              // … more device rows …
            ];

            foreach ($devices as $device) {
              echo '<tr>';
              
              // SEID is clickable to drill down:
              echo '<td><a href="/device_detail.php?id=' . urlencode($device['SEID']) . '">'
                   . htmlspecialchars($device['SEID'])
                   . '</a></td>';
              
              echo '<td>' . htmlspecialchars($device['Brand']) . '</td>';
              echo '<td>' . htmlspecialchars($device['Model']) . '</td>';
              echo '<td>' . htmlspecialchars($device['Serial']) . '</td>';
              echo '<td>' . htmlspecialchars($device['IpAddress']) . '</td>';
              
              echo '<td>';
              if ($device['Alerts'] > 0) {
                echo '<span class="icon-warning">⚠️</span> ' . intval($device['Alerts']);
              } else {
                echo '—';
              }
              echo '</td>';
              
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <!-- ========== End device_list.php content ========== -->

<?php
require_once __DIR__ . '/footer.php';
