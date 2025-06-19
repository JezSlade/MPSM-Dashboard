<?php declare(strict_types=1);
// /includes/card_bootstrap.php

// 1) Load shared API helpers & config
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 2) Determine selected customer
$customerCode = $_GET['customer'] ?? $config['DEALER_CODE'] ?? '';

// 3) Validate card metadata
if (empty($path) || empty($cardTitle) || !is_array($columns)) {
    echo "<p class='error'>Card not configured properly.</p>";
    return;
}

// 4) Inject customerCode if needed
if (array_key_exists('CustomerCode', $payload) && !$payload['CustomerCode']) {
    $payload['CustomerCode'] = $customerCode;
}

// 5) Pull in any GET params for declared requiredFields
if (!empty($requiredFields) && is_array($requiredFields)) {
    foreach ($requiredFields as $field) {
        if (( !isset($payload[$field]) || $payload[$field] === '' )
            && isset($_GET[$field])) {
            $payload[$field] = $_GET[$field];
        }
    }
}

// 6) Build list of still-missing fields
$missing = [];
if (!empty($requiredFields) && is_array($requiredFields)) {
    foreach ($requiredFields as $field) {
        if (empty($payload[$field])) {
            $missing[] = $field;
        }
    }
}

// 7) If anything’s missing, render a prompt and bail out
if (!empty($missing)) {
    echo "<div class='card'>";
    echo   "<div class='card-header'><h3>"
         . htmlspecialchars($cardTitle)
         . "</h3></div>";
    echo   "<div class='card-body'>";
    echo     "<form method='GET'>";
    // preserve existing query params (customer, etc)
    foreach ($_GET as $gk => $gv) {
        echo "<input type='hidden' name='"
             . htmlspecialchars($gk)
             . "' value='"
             . htmlspecialchars($gv)
             . "'>";
    }
    echo     "<p>Please enter:</p>";
    foreach ($missing as $field) {
        echo "<label for='{$field}'>"
             . htmlspecialchars($field)
             . ":</label> ";
        echo "<input type='text' id='{$field}' name='{$field}'><br>";
    }
    echo     "<button type='submit'>Load “"
             . htmlspecialchars($cardTitle)
             . "”</button>";
    echo   "</form>";
    echo "</div></div>";
    return;
}

// 8) All set → fetch data
try {
    $method = $method ?? 'POST';
    $resp   = call_api($config, $method, $path, $payload);

    // surface any API-level Errors
    if (!empty($resp['Errors']) && is_array($resp['Errors'])) {
        $first = $resp['Errors'][0];
        throw new \Exception($first['Description'] ?? 'API error');
    }

    $data = $resp['Result'] ?? [];

} catch (\Throwable $e) {
    echo "<p class='error'>Error fetching data: "
         . htmlspecialchars($e->getMessage())
         . "</p>";
    return;
}

// 9) Render the card (search / table / pagination)
echo "<div class='card'>";
echo   "<div class='card-header'><h3>"
     . htmlspecialchars($cardTitle)
     . "</h3></div>";

// search box
if (!empty($enableSearch)) {
    echo "<div class='card-search'>"
         . "<input type='text' class='search-input'"
         . " placeholder='Search…' onkeyup='filterCard(this)'>"
         . "</div>";
}

// table
echo "<div class='card-table-container'>";
echo   "<table class='card-table' data-page-size='"
     . ($pageSize ?? 15)
     . "'>";
echo     "<thead><tr>";
foreach ($columns as $key => $label) {
    echo "<th>"
         . htmlspecialchars($label)
         . "</th>";
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

// pagination
if (!empty($enablePagination)) {
    echo "<div class='card-pagination'></div>";
}

echo "</div>";
?>

<script>
// client‐side search
function filterCard(input) {
    var filter = input.value.toLowerCase();
    var rows   = input.closest('.card')
                      .querySelectorAll('tbody tr');
    rows.forEach(function(r) {
        r.style.display = r.textContent
            .toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>
