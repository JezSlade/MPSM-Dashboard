<?php declare(strict_types=1);
// /includes/card_bootstrap.php

// 1) LOAD CONFIG AND HELPERS
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 2) DETERMINE SELECTED CUSTOMER
$customerCode = $_GET['customer'] ?? $config['DEALER_CODE'] ?? '';

// 3) PER-CARD SETTINGS (must be set in stub):
//    • $path             : API endpoint path
//    • $payload          : request body array
//    • $cardTitle        : display name for the card
//    • $columns          : assoc array of column key => display label
//    • $enableSearch     : bool
//    • $enablePagination : bool
//    • $pageSize         : int

if (empty($path) || empty($cardTitle) || empty($columns) || !is_array($columns)) {
    echo "<p class='error'>Card not configured properly.</p>";
    return;
}

// 4) FETCH DATA
try {
    // inject customer code if needed
    if (isset($payload['CustomerCode']) && !$payload['CustomerCode']) {
        $payload['CustomerCode'] = $customerCode;
    }
    $resp = call_api($config, 'POST', $path, $payload);
    $data = $resp['Result'] ?? [];
} catch (\Throwable $e) {
    echo "<p class='error'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</p>";
    return;
}

// 5) RENDER CARD
echo "<div class='card'>";
echo "<div class='card-header'><h3>" . htmlspecialchars($cardTitle) . "</h3></div>";

// search bar
if (!empty($enableSearch)) {
    echo "<div class='card-search'>";
    echo "<input type='text' class='search-input' placeholder='Search…' onkeyup='filterCard(this)'>";
    echo "</div>";
}

// table
echo "<div class='card-table-container'>";
echo "<table class='card-table' data-page-size='" . ($pageSize ?? 15) . "'>";
echo "<thead><tr>";
foreach ($columns as $key => $label) {
    echo "<th>" . htmlspecialchars($label) . "</th>";
}
echo "</tr></thead>";
echo "<tbody>";
foreach ($data as $row) {
    echo "<tr>";
    foreach ($columns as $key => $label) {
        echo "<td>" . htmlspecialchars($row[$key] ?? '') . "</td>";
    }
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo "</div>";

// pagination
if (!empty($enablePagination)) {
    echo "<div class='card-pagination'></div>";
}

echo "</div>";

// 6) CLIENT‐SIDE SCRIPT (search & pagination)
?>
<script>
function filterCard(input) {
    var filter = input.value.toLowerCase();
    var tbl = input.closest('.card').querySelector('tbody');
    tbl.querySelectorAll('tr').forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
// Pagination logic can be added here
</script>
