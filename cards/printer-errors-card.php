<?php
$cachePath = __DIR__ . '/../cache/data.json';
$customerCode = $_GET['customer'] ?? 'W9OPXL0YDK';

if (!file_exists($cachePath)) {
    echo "<p class='error'>Cache file missing. Cannot load data.</p>";
    return;
}

$cache = json_decode(file_get_contents($cachePath), true);
if (!$cache || !isset($cache['timestamp'])) {
    echo "<p class='error'>Invalid cache format.</p>";
    return;
}
?>

    <div class="device-card">
      <div class="card-header compact-header">
        <h3>Printer Errors</h3>
      </div>
      <div class="device-table-container">
        <?php
          $dataSet = $cache['alerts']['Result'] ?? []; // default
    if (!$dataSet || !is_array($dataSet)) {
        echo "<p>No data found for this customer.</p>";
        return;
    }

    echo "<table class='device-table'><thead><tr>";
    echo "<th>Equipment ID</th><th>Department</th><th>Model</th>";
    echo "</tr></thead><tbody>";

    foreach ($dataSet as $item) {
        $id = htmlspecialchars($item['ExternalIdentifier'] ?? 'n/a');
        $dept = htmlspecialchars($item['Department'] ?? '—');
        $model = htmlspecialchars($item['Model'] ?? '—');
        echo "<tr><td>$id</td><td>$dept</td><td>$model</td></tr>";
    }

    echo "</tbody></table>";
    ?>
  </div>
</div>
