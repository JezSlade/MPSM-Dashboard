<?php declare(strict_types=1);
// /includes/card_bootstrap.php

// —————————————————————————————————————————————————————————
// 0) Enable debug logging
// —————————————————————————————————————————————————————————
ini_set('display_errors', '0');
ini_set('log_errors',   '1');
ini_set('error_log',    __DIR__ . '/../logs/debug.log');
error_reporting(E_ALL);

// Start output buffering so we can set cookies/headers safely
ob_start();

// 1) Load shared API helpers & config
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 2) Determine selected customer (URL → cookie → default)
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

// 3) Validate card metadata
if (empty($path) || empty($cardTitle) || !is_array($columns)) {
    echo "<p class='error'>Card not configured properly.</p>";
    ob_end_flush();
    return;
}

// 4) Ensure payload exists
$payload = $payload ?? [];

// 4a) Inject customerCode if needed
if (array_key_exists('CustomerCode', $payload) && !$payload['CustomerCode']) {
    $payload['CustomerCode'] = $customerCode;
}

// 4b) Populate any requiredFields from GET → cookie
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

// 5) If any required fields still missing, render prompt...
if (!empty($missing)) {
    echo "<form class='card form-card'>";
    echo "<h3>{$cardTitle}</h3>";
    foreach ($missing as $f) {
        echo "<label for='{$f}'>{$f}</label>";
        echo "<input type='text' id='{$f}' name='{$f}' />";
    }
    echo "<button type='submit'>Submit</button>";
    echo "</form>";
    ob_end_flush();
    return;
}

// 6) Fetch data
try {
    $data = call_api($config, $method ?? 'POST', $path, $payload);
} catch (\Throwable $e) {
    echo "<p class='error'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</p>";
    ob_end_flush();
    return;
}

// 7) Render card
echo "<div class='card'>";
echo "<h3>{$cardTitle}</h3>";
if (!empty($description)) {
    echo "<p class='description'>{$description}</p>";
}
echo "<table>";
// Header
echo "<thead><tr>";
foreach ($columns as $col) {
    echo "<th>{$col}</th>";
}
echo "</tr></thead>";
// Body
echo "<tbody>";
foreach ($data as $row) {
    echo "<tr>";
    foreach ($columns as $colKey => $colTitle) {
        $val = $row[$colKey] ?? '';
        echo "<td>" . htmlspecialchars((string)$val) . "</td>";
    }
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
// Pagination (if applicable)
if (!empty($data['_pagination'])) {
    echo "<div class='pagination'>{$data['_pagination']}</div>";
}
echo "</div>";

// Flush the buffer now that all setcookie calls are done
ob_end_flush();
?>

<script>
// client-side search
function filterCard(input) {
    var filter = input.value.toLowerCase();
    var rows   = input.closest('.card').querySelectorAll('tbody tr');
    rows.forEach(function(r) {
        r.style.display = r.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>
