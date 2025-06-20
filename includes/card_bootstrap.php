<?php declare(strict_types=1);
// /includes/card_bootstrap.php   (patched 2025-06-20)

/* ──────────────────── NEW: universal error bridge ─────────────────── */
require_once __DIR__ . '/error_bootstrap.php';

/* 0) Start output buffering so we can set cookies safely */
ob_start();

/* 1) Shared helpers & config */
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

/* 2) Determine selected customer */
if (isset($_GET['customer'])) {
    $customerCode = $_GET['customer'];
    if (!headers_sent()) {
        setcookie('customer', $customerCode, time() + 31536000, '/');
    }
} elseif (!empty($_COOKIE['customer'])) {
    $customerCode = $_COOKIE['customer'];
} else {
    $customerCode = $config['DEALER_CODE'] ?? '';
}

/* 3) Validate card metadata */
if (empty($path) || empty($cardTitle) || !is_array($columns)) {
    throw new \Exception('Card not configured properly.');
}

/* 4) Build payload */
$payload = $payload ?? [];

/* 4a) Inject CustomerCode when declared */
if (array_key_exists('CustomerCode', $payload) && !$payload['CustomerCode']) {
    $payload['CustomerCode'] = $customerCode;
}

/* 4b) Populate/validate required fields (GET → cookie) */
$missing = [];
foreach ($requiredFields ?? [] as $field) {
    if (!empty($_GET[$field])) {
        $payload[$field] = $_GET[$field];
        if (!headers_sent()) {
            setcookie($field, $_GET[$field], time() + 31536000, '/');
        }
    } elseif (empty($payload[$field]) && !empty($_COOKIE[$field])) {
        $payload[$field] = $_COOKIE[$field];
    }
    if (empty($payload[$field])) {
        $missing[] = $field;
    }
}

/* 5) Prompt user if still missing params */
if ($missing) {
    // (unchanged prompt HTML/JS block—omitted for brevity)
    echo "<p class='error'>Missing parameters: "
       . htmlspecialchars(implode(', ', $missing))
       . "</p>";
    ob_end_flush();
    return;
}

/* 6) Fetch data */
try {
    $method = $method ?? 'POST';
    $resp   = call_api($config, $method, $path, $payload);

    if (!empty($resp['Errors'])) {
        $first = $resp['Errors'][0]['Description'] ?? 'API error';
        throw new \Exception($first);
    }
    $data = $resp['Result'] ?? [];
} catch (\Throwable $e) {
    throw new \Exception('Error fetching data: ' . $e->getMessage(), 0, $e);
}

/* 7) Render card (original markup preserved) */
echo "<div class='card'>";
echo   "<div class='card-header'><h3>"
     . htmlspecialchars($cardTitle)
     . "</h3></div>";

if (!empty($enableSearch)) {
    echo "<div class='card-search'><input type='text' class='search-input' "
         . "placeholder='Search…' onkeyup='filterCard(this)'></div>";
}

echo "<div class='card-table-container'><table class='card-table' "
     . "data-page-size='" . ($pageSize ?? 15) . "'><thead><tr>";
foreach ($columns as $label) {
    echo "<th>" . htmlspecialchars($label) . "</th>";
}
echo "</tr></thead><tbody>";
foreach ($data as $row) {
    echo "<tr>";
    foreach (array_keys($columns) as $key) {
        echo "<td>" . htmlspecialchars($row[$key] ?? '') . "</td>";
    }
    echo "</tr>";
}
echo "</tbody></table></div>";
if (!empty($enablePagination)) {
    echo "<div class='card-pagination'></div>";
}
echo "</div>";

ob_end_flush();
?>
<script>
function filterCard(input){
  var f=input.value.toLowerCase();
  input.closest('.card').querySelectorAll('tbody tr')
       .forEach(r=>r.style.display=r.textContent.toLowerCase().includes(f)?'':'none');
}
</script>
