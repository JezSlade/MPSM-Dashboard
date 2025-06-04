<?php
$pageTitle = 'MPSM Dashboard ‐ Devices';
require_once __DIR__ . '/header.php';
?>
  <section class="devices-section">
    <h1 class="section-title">Devices</h1>
    <div class="card" style="margin-top: 1rem;">
      <h2 class="sub-title">All Devices</h2>
      <div style="margin-top: 1rem;">
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
            require_once __DIR__ . '/api_functions.php';
            $dealerCode = 'SZ13qRwU5GtFLj0i_CbEgQ2';
            try {
                $devices = getDevices($dealerCode);
            } catch (Exception $e) {
                echo '<tr><td colspan="6" style="color: #ff9000;">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                $devices = [];
            }
            foreach ($devices as $device) {
              echo '<tr>';
              echo '<td><a href="/device_detail.php?id=' . urlencode($device['SEID'] ?? '') . '">' . htmlspecialchars($device['SEID'] ?? '') . '</a></td>';
              echo '<td>' . htmlspecialchars($device['Brand'] ?? '') . '</td>';
              echo '<td>' . htmlspecialchars($device['Model'] ?? '') . '</td>';
              echo '<td>' . htmlspecialchars($device['SerialNumber'] ?? '') . '</td>';
              echo '<td>' . htmlspecialchars($device['IpAddress'] ?? '') . '</td>';
              echo '<td>';
              if (isset($device['Alerts']) && $device['Alerts'] > 0) {
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
<?php
require_once __DIR__ . '/footer.php';
?>