<?php
// =============================================================================
// CARD: Device Counters
// =============================================================================

// 1. Load environment and get an OAuth token exactly as in each API file.
//    (Manual .env parsing and token acquisition must live at the top of every card.)
$envFile = __DIR__ . '/../.env';
$env     = parseEnvFile($envFile);            // your existing .env parser
$token   = fetchOAuthToken($env);              // your existing token getter

// 2. Fetch all counters for this customer via the Device/GetCounters endpoint.
$customerCode = $_GET['customer'] ?? '';
$allCounters  = fetchDeviceCounters($env['BASE_URL'], $token, $customerCode);

// 3. Handle the search box input (same ID & class as printer_card.php for consistency).
$searchTerm = trim($_GET['search'] ?? '');

// 4. Filter devices by Equipment ID (formerly ExternalIdentifier) if a search term exists.
$filtered = array_filter($allCounters, function($c) use ($searchTerm) {
    // Search within the ExternalIdentifier field
    return $searchTerm === '' 
        || stripos($c['ExternalIdentifier'] ?? '', $searchTerm) !== false;
});

// 5. Pagination logic (identical defaults to printer_card.php: 15 items/page).
$currentPage   = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 15;
$totalItems    = count($filtered);
$totalPages    = (int)ceil($totalItems / $perPage);
$paginatedData = array_slice(
    array_values($filtered),              // reindex
    ($currentPage - 1) * $perPage,
    $perPage
);

// 6. Column definitions—**change** 'External ID' → 'Equipment ID'
$columns = [
    'Equipment ID',   // formerly 'External ID'
    'CounterName1',
    'CounterName2',
    // add your other counter columns here...
];

?>
<div class="device-card"
     data-card-id="card_device_counters"
     data-dashboard="<?= htmlspecialchars($_GET['dashboard'] ?? 'default') ?>"
     data-customer-code="<?= htmlspecialchars($customerCode) ?>">

  <!-- Card header with search box (compact style) -->
  <div class="card-header compact-header">
    <input
      type="text"
      id="device-search"
      name="search"
      value="<?= htmlspecialchars($searchTerm) ?>"
      placeholder="Search devices..."
      class="search-box" />
  </div>

  <?php if (empty($paginatedData)): ?>
    <!-- No data fallback -->
    <p>No devices found for this page.</p>
  <?php else: ?>
    <div class="device-table-container">
      <!-- Table structure matches printer_card.php exactly -->
      <table class="device-table" id="device-table">
        <thead>
          <tr>
            <?php foreach ($columns as $colLabel): ?>
              <th><?= htmlspecialchars($colLabel) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($paginatedData as $device): ?>
            <tr class="device-row">
              <?php foreach ($columns as $colLabel): ?>
                <?php if ($colLabel === 'Equipment ID'): ?>
                  <td>
                    <?php
                      // Show the ExternalIdentifier value or 'N/A' if empty
                      $value = $device['ExternalIdentifier'] ?? '';
                      echo htmlspecialchars($value !== '' ? $value : 'N/A');
                    ?>
                    <?php if (!empty($device['Id'])): ?>
                      <!-- Drill-down button replaces the tooltip -->
                      <button
                        class="drilldown-btn"
                        data-device-id="<?= htmlspecialchars($device['Id']) ?>"
                        title="View Details">
                        <span class="icon">🔍</span>
                      </button>
                    <?php endif; ?>
                  </td>
                <?php else: ?>
                  <!-- All other columns rendered normally -->
                  <td>
                    <?= htmlspecialchars($device[$colLabel] ?? '') ?>
                  </td>
                <?php endif; ?>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination nav (← Prev | Page X of Y | Next →) -->
    <div class="pagination-nav">
      <?php if ($currentPage > 1): ?>
        <a
          href="?customer=<?= urlencode($customerCode) ?>&search=<?= urlencode($searchTerm) ?>&page=<?= $currentPage - 1 ?>"
          class="page-link">← Prev</a>
      <?php endif; ?>

      <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>

      <?php if ($currentPage < $totalPages): ?>
        <a
          href="?customer=<?= urlencode($customerCode) ?>&search=<?= urlencode($searchTerm) ?>&page=<?= $currentPage + 1 ?>"
          class="page-link">Next →</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
