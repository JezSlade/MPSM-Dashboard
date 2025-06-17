<?php
// =============================================================================
// CARD: Device Counters
// =============================================================================

// Enable full PHP error reporting and log to your unified debug file
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// Load your .env values and get an OAuth token
require_once __DIR__ . '/../includes/config.php';
$env   = load_env(__DIR__ . '/../.env');
$token = get_token($env);

// Determine current customer and search/page inputs
$customerCode = $_GET['customer'] ?? '';
$searchTerm   = trim($_GET['search'] ?? '');
$currentPage  = max(1, (int)($_GET['page'] ?? 1));

// === Fetch raw data via your internal APIs ===
$scheme        = $_SERVER['REQUEST_SCHEME'] ?? 'https';
$host          = $_SERVER['HTTP_HOST'];
// Device counters list
$countersUrl   = "$scheme://$host/api/get_device_counters.php?customer=" . urlencode($customerCode);
$countersResp  = json_decode(file_get_contents($countersUrl), true);
$allCounters   = $countersResp['Result'] ?? [];
// Device lookup for ExternalIdentifier → Equipment ID
$deviceUrl     = "$scheme://$host/api/get_devices.php?customer=" . urlencode($customerCode);
$devicesResp   = json_decode(file_get_contents($deviceUrl), true);
$deviceLookup  = [];
foreach ($devicesResp['Result'] ?? [] as $dev) {
    $deviceLookup[$dev['Id']] = $dev['Equipment ID'] ?? 'N/A';
}

// === Filter by search term on ExternalIdentifier ===
$filtered = array_filter($allCounters, function($c) use ($searchTerm) {
    return $searchTerm === ''
        || stripos($c['ExternalIdentifier'] ?? '', $searchTerm) !== false;
});

// === Pagination (15 items per page) ===
$perPage      = 15;
$totalItems   = count($filtered);
$totalPages   = (int)ceil($totalItems / $perPage);
$paginated    = array_slice(array_values($filtered), ($currentPage - 1) * $perPage, $perPage);

// === Column definitions ===
$columns = [
    'Equipment ID',    // was External ID
    'Mono',
    'Color',
    'MonoA3',
    'ColorA3',
    'Fax'
];

?>
<div class="device-card"
     data-card-id="card_device_counters"
     data-dashboard="<?= htmlspecialchars($_GET['dashboard'] ?? 'default') ?>"
     data-customer-code="<?= htmlspecialchars($customerCode) ?>">

  <!-- Header + Search Box -->
  <div class="card-header compact-header">
    <input
      type="text"
      id="device-search"
      name="search"
      value="<?= htmlspecialchars($searchTerm) ?>"
      placeholder="Search devices..."
      class="search-box" />
  </div>

  <?php if (empty($paginated)): ?>
    <p>No devices found for this page.</p>
  <?php else: ?>
    <div class="device-table-container">
      <table class="device-table" id="device-table">
        <thead>
          <tr>
            <?php foreach ($columns as $col): ?>
              <th><?= htmlspecialchars($col) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($paginated as $row): ?>
            <tr class="device-row">
              <?php foreach ($columns as $col): ?>
                <?php if ($col === 'Equipment ID'): ?>
                  <td>
                    <?php
                      $ext = $row['ExternalIdentifier'] ?? '';
                      echo htmlspecialchars($ext !== '' ? $ext : 'N/A');
                    ?>
                    <?php if (!empty($row['Id'])): ?>
                      <button
                        class="drilldown-btn"
                        data-device-id="<?= htmlspecialchars($row['Id']) ?>"
                        title="View Details">
                        <span class="icon">🔍</span>
                      </button>
                    <?php endif; ?>
                  </td>
                <?php else: ?>
                  <td><?= htmlspecialchars($row[$col] ?? '') ?></td>
                <?php endif; ?>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-nav">
      <?php if ($currentPage > 1): ?>
        <a href="?customer=<?= urlencode($customerCode) ?>&search=<?= urlencode($searchTerm) ?>&page=<?= $currentPage - 1 ?>" class="page-link">← Prev</a>
      <?php endif; ?>

      <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>

      <?php if ($currentPage < $totalPages): ?>
        <a href="?customer=<?= urlencode($customerCode) ?>&search=<?= urlencode($searchTerm) ?>&page=<?= $currentPage + 1 ?>" class="page-link">Next →</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<link rel="stylesheet" href="/public/css/styles.css">
<script>
  // Drill-down modal loader (reuses your shared component)
  document.querySelectorAll('.drilldown-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-device-id');
      fetch(`/components/drilldown-modal.php?id=${encodeURIComponent(id)}`)
        .then(r => r.text())
        .then(html => {
          const modal = document.createElement('div');
          modal.innerHTML = html;
          document.body.appendChild(modal);
        });
    });
  });
</script>
