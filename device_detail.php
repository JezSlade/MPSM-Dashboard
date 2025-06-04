<?php
$pageTitle = 'MPSM Dashboard ‐ Device Detail';
require_once __DIR__ . '/header.php';
?>
  <section class="device-detail-section">
    <h1 class="section-title">Device Details</h1>
    <?php
    require_once __DIR__ . '/api_functions.php';
    $deviceId = isset($_GET['id']) ? $_GET['id'] : '';
    $dealerCode = 'SZ13qRwU5GtFLj0i_CbEgQ2';
    try {
        $detail = getDeviceDetail($dealerCode, $deviceId);
    } catch (Exception $e) {
        echo '<p style="color: #ff9000;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        $detail = [];
    }
    ?>
    <div class="card" style="margin-top: 1rem;">
      <h2 class="sub-title">Basic Info</h2>
      <table class="table" style="margin-top: 1rem;">
        <tbody>
          <tr><td>SEID</td><td><?php echo htmlspecialchars($detail['SEID'] ?? ''); ?></td></tr>
          <tr><td>Brand</td><td><?php echo htmlspecialchars($detail['Brand'] ?? ''); ?></td></tr>
          <tr><td>Model</td><td><?php echo htmlspecialchars($detail['Model'] ?? ''); ?></td></tr>
          <tr><td>Serial Number</td><td><?php echo htmlspecialchars($detail['SerialNumber'] ?? ''); ?></td></tr>
          <tr><td>IP Address</td><td><?php echo htmlspecialchars($detail['IpAddress'] ?? ''); ?></td></tr>
          <tr><td>Status</td><td><?php echo htmlspecialchars($detail['Status'] ?? ''); ?></td></tr>
          <tr><td>Last Seen</td><td><?php echo htmlspecialchars($detail['LastSeen'] ?? ''); ?></td></tr>
        </tbody>
      </table>
    </div>
    <div class="card" style="margin-top: 1.5rem;">
      <h2 class="sub-title">Supply Alerts</h2>
      <table class="table" style="margin-top: 1rem;">
        <thead>
          <tr>
            <th>Supply Type</th>
            <th>Color</th>
            <th>Alerts</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ($detail['SupplyAlerts'] ?? [] as $sa) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($sa['SupplyType'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($sa['ColorType'] ?? '') . '</td>';
            echo '<td>';
            if (isset($sa['ManagedAlerts']) && $sa['ManagedAlerts'] > 0) {
              echo '<span class="icon-warning">⚠️</span> ' . intval($sa['ManagedAlerts']);
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
    <div class="card" style="margin-top: 1.5rem; margin-bottom: 2rem;">
      <h2 class="sub-title">Counters (Last 7 Days)</h2>
      <table class="table" style="margin-top: 1rem;">
        <thead>
          <tr>
            <th>Counter Name</th>
            <th>Value</th>
            <th>Last Update</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ($detail['CountersLast7d'] ?? [] as $cnt) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($cnt['CounterName'] ?? '') . '</td>';
            echo '<td>' . intval($cnt['Value'] ?? 0) . '</td>';
            echo '<td>' . htmlspecialchars($cnt['LastUpdate'] ?? '') . '</td>';
            echo '</tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </section>
<?php
require_once __DIR__ . '/footer.php';
?>