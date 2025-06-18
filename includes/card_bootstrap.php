<?php declare(strict_types=1);
// /includes/card_bootstrap.php

// 1) Load shared API helpers & config
require_once __DIR__ . '/api_functions.php';
$config       = parse_env_file(__DIR__ . '/../.env');

// 2) Determine selected customer (URL param → default)
$customerCode = $_GET['customer'] ?? $config['DEALER_CODE'] ?? '';

// 3) Validate per‐card settings
if (empty($path) || empty($cardTitle) || !is_array($columns)) {
    echo "<p class='error'>Card not configured properly.</p>";
    return;
}

try {
    // 4) Inject customer code if this card uses it
    if (array_key_exists('CustomerCode', $payload) && !$payload['CustomerCode']) {
        $payload['CustomerCode'] = $customerCode;
    }

    // 5) Call the API
    $method = $method ?? 'POST';
    $resp   = call_api($config, $method, $path, $payload);

    // 6) Handle API‐level errors
    if (!empty($resp['Errors']) && is_array($resp['Errors'])) {
        $first = $resp['Errors'][0];
        throw new \Exception($first['Description'] ?? 'Unknown API error');
    }

    $data = $resp['Result'] ?? [];

} catch (\Throwable $e) {
    echo "<p class='error'>Error fetching data: " 
       . htmlspecialchars($e->getMessage()) 
       . "</p>";
    return;
}

// 7) Render the card
echo "<div class='card'>";
echo   "<div class='card-header'><h3>" 
     . htmlspecialchars($cardTitle) 
     . "</h3></div>";

// 8) Search box
if (!empty($enableSearch)) {
    echo "<div class='card-search'>";
    echo   "<input type='text' class='search-input' "
         . "placeholder='Search…' onkeyup='filterCard(this)'>";
    echo "</div>";
}

// 9) Table
echo "<div class='card-table-container'>";
echo   "<table class='card-table' data-page-size='" 
     . ($pageSize ?? 15) 
     . "'>";
echo     "<thead><tr>";
foreach ($columns as $key => $label) {
    echo "<th>" . htmlspecialchars($label) . "</th>";
}
echo     "</tr></thead>";
echo     "<tbody>";
foreach ($data as $row) {
    echo "<tr>";
    foreach ($columns as $key => $_) {
        echo "<td>" 
           . htmlspecialchars($row[$key] ?? '') 
           . "</td>";
    }
    echo "</tr>";
}
echo     "</tbody>";
echo   "</table>";
echo "</div>";

// 10) Pagination
if (!empty($enablePagination)) {
    echo "<div class='card-pagination'></div>";
}

echo "</div>";
?>

<script>
function filterCard(input) {
    var filter = input.value.toLowerCase();
    var rows   = input.closest('.card')
                     .querySelectorAll('tbody tr');
    rows.forEach(function(row) {
        row.style.display = row.textContent
            .toLowerCase()
            .includes(filter) ? '' : 'none';
    });
}
// pagination logic should be initialized globally
</script>
