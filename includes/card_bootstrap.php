<?php declare(strict_types=1);
// /includes/card_bootstrap.php

// ——————————————————————————————————————————————————
// 1) GLOBAL DEBUG (always at top)
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// ——————————————————————————————————————————————————
// 2) LOAD CACHE
$cachePath    = __DIR__ . '/../cache/data.json';
$customerCode = $_GET['customer'] ?? ($_ENV['DEALER_CODE'] ?? '');

if (! file_exists($cachePath)) {
    echo "<p class='error'>Cache file missing. Cannot load data.</p>";
    return;
}
$cache = json_decode(file_get_contents($cachePath), true);
if (! $cache || ! isset($cache['timestamp'])) {
    echo "<p class='error'>Invalid cache format.</p>";
    return;
}

// ——————————————————————————————————————————————————
// 3) PER-CARD DECLARATIONS (must be set in stub):
//    • $cacheSection (e.g. 'devices', 'alerts', 'counters')
//    • $cardTitle    (string for the <h3>)
//    • $columns      (assoc: data-key => column header)
if (empty($cacheSection) || empty($cardTitle) || empty($columns) || ! is_array($columns)) {
    echo "<p class='error'>Card not configured properly.</p>";
    return;
}

// Pull the dataset for this card
$dataSet = $cache[$cacheSection]['Result'] ?? [];
if (! is_array($dataSet) || count($dataSet) === 0) {
    echo "<p>No data found for “{$cardTitle}”.</p>";
    return;
}

// ——————————————————————————————————————————————————
// 4) RENDER CARD
?>
<div class="device-card">
  <div class="card-header compact-header">
    <h3><?= htmlspecialchars($cardTitle) ?></h3>
  </div>
  <div class="device-table-container">
    <table class="device-table">
      <thead><tr>
        <?php foreach ($columns as $key => $label): ?>
          <th><?= htmlspecialchars($label) ?></th>
        <?php endforeach; ?>
      </tr></thead>
      <tbody>
        <?php foreach ($dataSet as $item): ?>
          <tr>
            <?php foreach ($columns as $key => $_): ?>
              <td><?= htmlspecialchars($item[$key] ?? '') ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
