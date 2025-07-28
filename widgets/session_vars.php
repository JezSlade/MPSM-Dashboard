<?php
// PATCHED: session_vars.php v2.3 - Token Display and Global Exposure
// ------------------------------------------------------
// • Displays runtime session variables, cookies, and token info.
// • Ensures $token is globally accessible after loading.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Include the global token handler
require_once dirname(__DIR__) . '/mps_monitor/includes/global_token_handler.php';
global $token;
$token = get_valid_mps_token();

// Display Token Info
if (is_array($token)) {
    echo "<h3>🔐 MPS Token</h3>";
    echo "<pre>" . print_r($token, true) . "</pre>";
} else {
    echo "<p style='color:red;'>⚠️ Token unavailable or invalid</p>";
}

// Display all runtime $_SESSION variables (excluding dashboard controls)
$excluded_prefixes = ['dashboard_', 'widget_', 'ui_', 'layout_', 'grid_', 'settings'];
$excluded_keys = ['active_widgets', 'widget_order', 'dashboard_config'];

function is_runtime_variable($key, $excluded_keys, $excluded_prefixes) {
    if (in_array($key, $excluded_keys, true)) return false;
    foreach ($excluded_prefixes as $prefix) {
        if (stripos($key, $prefix) === 0) return false;
    }
    return true;
}

echo "<h3>📦 Session Variables</h3><pre>";
foreach ($_SESSION as $key => $value) {
    if (is_runtime_variable($key, $excluded_keys, $excluded_prefixes)) {
        echo "$key => ";
        print_r($value);
        echo "\n";
    }
}
echo "</pre>";
?>
