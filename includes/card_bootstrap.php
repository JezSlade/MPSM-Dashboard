<?php declare(strict_types=1);
// /includes/card_bootstrap.php

// —————————————————————————————————————————————————————————
// 0) Enable debug logging + display to both file and page
// —————————————————————————————————————————————————————————
ini_set('display_errors', '1');
ini_set('log_errors',     '1');
ini_set('error_log',      __DIR__ . '/../logs/debug.log');
error_reporting(E_ALL);

// —————————————————————————————————————————————————————————
// 1) Start output buffering so we can set headers/cookies
// —————————————————————————————————————————————————————————
ob_start();

// —————————————————————————————————————————————————————————
// 2) Inject live-debug container into footer (and enlarge it)
// —————————————————————————————————————————————————————————
echo <<<HTML
<script>
document.addEventListener('DOMContentLoaded', function(){
  var footer = document.querySelector('footer');
  if (footer) {
    footer.style.minHeight = '300px';
    var dbg = document.createElement('pre');
    dbg.id = 'live-debug';
    dbg.style.cssText = [
      'background: rgba(0,0,0,0.8)',
      'color: #0f0',
      'padding: 10px',
      'margin: 0',
      'overflow: auto',
      'height: 100%',
      'font-family: monospace',
      'font-size: 12px'
    ].join(';');
    footer.appendChild(dbg);
  }
});
// JS helper to append messages
function appendDebug(msg){
  var dbg = document.getElementById('live-debug');
  if (dbg) {
    dbg.textContent += msg + "\\n";
    dbg.scrollTop = dbg.scrollHeight;
  }
}
</script>
HTML;
flush();

// —————————————————————————————————————————————————————————
// 3) Trace start
// —————————————————————————————————————————————————————————
echo "<script>appendDebug('BOOTSTRAP ▶ Starting card_bootstrap.php');</script>";
flush();

// —————————————————————————————————————————————————————————
// 4) Load API helpers & parse .env
// —————————————————————————————————————————————————————————
echo "<script>appendDebug('╸ Loading api_functions.php');</script>";
flush();
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');
echo "<script>appendDebug('✔ Parsed .env into \$config');</script>";
flush();

// —————————————————————————————————————————————————————————
// 5) Determine selected customer
// —————————————————————————————————————————————————————————
echo "<script>appendDebug('╸ Determining customerCode');</script>";
flush();
if (isset($_GET['customer'])) {
  $customerCode = $_GET['customer'];
  if (!headers_sent()) {
    setcookie('customer', $customerCode, time()+31536000, '/');
    echo "<script>appendDebug('→ Cookie set: customer={$customerCode}');</script>";
  }
  echo "<script>appendDebug('→ From GET: {$customerCode}');</script>";
} elseif (!empty($_COOKIE['customer'])) {
  $customerCode = $_COOKIE['customer'];
  echo "<script>appendDebug('→ From COOKIE: {$customerCode}');</script>";
} else {
  $customerCode = $config['DEALER_CODE'] ?? '';
  echo "<script>appendDebug('→ Default DEALER_CODE: {$customerCode}');</script>";
}
flush();

// —————————————————————————————————————————————————————————
// 6) Validate card metadata
// —————————————————————————————————————————————————————————
echo "<script>appendDebug('╸ Validating card metadata (path, cardTitle, columns)');</script>";
flush();
if (empty($path) || empty($cardTitle) || !is_array($columns)) {
  echo "<script>appendDebug('✖ ERROR: Missing path/cardTitle/columns');</script>";
  echo "<p class='error'>Card not configured properly.</p>";
  ob_end_flush();
  return;
}
echo "<script>appendDebug('✔ Metadata OK');</script>";
flush();

// —————————————————————————————————————————————————————————
// 7) Prepare payload
// —————————————————————————————————————————————————————————
echo "<script>appendDebug('╸ Preparing payload');</script>";
flush();
$payload = $payload ?? [];
echo "<script>appendDebug('→ Initial payload: ' + " . json_encode(json_encode($payload)) . ");</script>";
flush();
// Inject customer if expected
if (array_key_exists('CustomerCode', $payload) && !$payload['CustomerCode']) {
  $payload['CustomerCode'] = $customerCode;
  echo "<script>appendDebug('→ Injected CustomerCode: {$customerCode}');</script>";
  flush();
}

// —————————————————————————————————————————————————————————
// 8) Handle requiredFields
// —————————————————————————————————————————————————————————
$missing = [];
foreach ($requiredFields ?? [] as $field) {
  echo "<script>appendDebug('╸ Checking required field: {$field}');</script>";
  flush();
  if (!empty($_GET[$field])) {
    $payload[$field] = $_GET[$field];
    echo "<script>appendDebug('→ From GET: {$field}={$_GET[$field]}');</script>";
    if (!headers_sent()) {
      setcookie($field, $_GET[$field], time()+31536000, '/');
      echo "<script>appendDebug('→ Cookie set: {$field}={$_GET[$field]}');</script>";
    }
  } elseif (empty($payload[$field]) && !empty($_COOKIE[$field])) {
    $payload[$field] = $_COOKIE[$field];
    echo "<script>appendDebug('→ From COOKIE: {$field}={$_COOKIE[$field]}');</script>";
  }
  if (empty($payload[$field])) {
    $missing[] = $field;
    echo "<script>appendDebug('→ Missing: {$field}');</script>";
  }
  flush();
}
echo "<script>appendDebug('→ Missing fields: " . json_encode($missing) . "');</script>";
flush();

if (!empty($missing)) {
  echo "<script>appendDebug('✚ Rendering prompt for missing fields');</script>";
  flush();
  echo "<form class='card form-card'>";
  echo "<h3>" . htmlspecialchars($cardTitle) . "</h3>";
  foreach ($missing as $f) {
    echo "<label for='{$f}'>{$f}</label>";
    echo "<input type='text' id='{$f}' name='{$f}' />";
  }
  echo "<button type='submit'>Submit</button>";
  echo "</form>";
  ob_end_flush();
  return;
}

// —————————————————————————————————————————————————————————
// 9) Fetch data from API
// —————————————————————————————————————————————————————————
echo "<script>appendDebug('╸ Calling API (method=".($method ?? 'POST').", path={$path})');</script>";
flush();
try {
  $data = call_api($config, $method ?? 'POST', $path, $payload);
  echo "<script>appendDebug('✔ API call success, data received');</script>";
  flush();
} catch (\Throwable $e) {
  echo "<script>appendDebug('✖ API call FAILED: " . $e->getMessage() . "');</script>";
  echo "<p class='error'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</p>";
  ob_end_flush();
  return;
}

// —————————————————————————————————————————————————————————
// 10) Render card HTML
// —————————————————————————————————————————————————————————
echo "<script>appendDebug('╸ Rendering card HTML');</script>";
flush();
echo "<div class='card'>";
echo "<h3>" . htmlspecialchars($cardTitle) . "</h3>";
if (!empty($description)) {
  echo "<p class='description'>" . htmlspecialchars($description) . "</p>";
}
echo "<table><thead><tr>";
foreach ($columns as $colKey => $colTitle) {
  echo "<th>" . htmlspecialchars($colTitle) . "</th>";
}
echo "</tr></thead><tbody>";
foreach ($data as $row) {
  echo "<tr>";
  foreach ($columns as $colKey => $colTitle) {
    $val = $row[$colKey] ?? '';
    echo "<td>" . htmlspecialchars((string)$val) . "</td>";
  }
  echo "</tr>";
}
echo "</tbody></table>";
if (!empty($data['_pagination'])) {
  echo "<div class='pagination'>{$data['_pagination']}</div>";
}
echo "</div>";
echo "<script>appendDebug('✔ Card rendered');</script>";
flush();

// —————————————————————————————————————————————————————————
// 11) End of bootstrap
// —————————————————————————————————————————————————————————
echo "<script>appendDebug('► End of card_bootstrap.php');</script>";
flush();
ob_end_flush();
?>
