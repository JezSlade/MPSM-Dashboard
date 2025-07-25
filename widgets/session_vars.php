<?php
// PATCHED: session_vars.php v2.2 - Final Unified Version
// ------------------------------------------------------
// • Displays runtime session variables, cookies, and token info.
// • Integrated with global_token_handler.php v3.4 for auto-refresh.
// • No direct config include – relies solely on session and token handler.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Include the global token handler
require_once dirname(__DIR__) . '/mps_monitor/includes/global_token_handler.php';
$token = get_valid_mps_token();

// Config: ignore these dashboard/control variables
$excluded_prefixes = ['dashboard_', 'widget_', 'ui_', 'layout_', 'grid_', 'settings'];
$excluded_keys = ['active_widgets', 'widget_order', 'dashboard_config'];

function is_runtime_variable($key, $excluded_keys, $excluded_prefixes) {
    if (in_array($key, $excluded_keys, true)) {
        return false;
    }
    foreach ($excluded_prefixes as $prefix) {
        if (stripos($key, $prefix) === 0) {
            return false;
        }
    }
    return true;
}

// Filter session for runtime variables only
$runtime_session = [];
foreach ($_SESSION as $key => $value) {
    if (is_runtime_variable($key, $excluded_keys, $excluded_prefixes)) {
        $runtime_session[$key] = $value;
    }
}
?>

<div class="widget-body">
    <h3 class="widget-section-title">Runtime Session Variables</h3>
    <?php if (!empty($runtime_session)): ?>
        <pre><?= htmlspecialchars(print_r($runtime_session, true)) ?></pre>
    <?php else: ?>
        <p><em>No runtime session variables are set.</em></p>
    <?php endif; ?>

    <h3 class="widget-section-title">Cookies</h3>
    <?php if (!empty($_COOKIE)): ?>
        <pre><?= htmlspecialchars(print_r($_COOKIE, true)) ?></pre>
    <?php else: ?>
        <p><em>No cookies set.</em></p>
    <?php endif; ?>

    <h3 class="widget-section-title">Tokens</h3>
    <?php
        if (!empty($token['access_token'])) {
            echo "<strong>Access Token:</strong><br><code>" . htmlspecialchars($token['access_token']) . "</code><br>";
            echo "<strong>Refresh Token:</strong><br><code>" . htmlspecialchars($token['refresh_token'] ?? 'N/A') . "</code><br>";
            echo "<strong>Expires In:</strong> " . htmlspecialchars($token['expires_in'] ?? 'N/A') . " seconds<br>";
            echo "<strong>Fetched At:</strong> " . htmlspecialchars($token['fetched_at'] ?? date('Y-m-d H:i:s')) . "<br>";
        } else {
            echo "<p><em>No valid token available.</em></p>";
        }
    ?>
</div>
