<?php
// widgets/session_vars.php

$_widget_config = [
    'name' => 'Session Variables',
    'icon' => 'bug',
    'width' => 2,
    'height' => 2
];

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Exclude UI/dashboard config junk
$excluded_prefixes = ['dashboard_', 'widget_', 'ui_', 'layout_', 'grid_', 'settings'];
$excluded_keys = ['active_widgets', 'widget_order', 'dashboard_config'];

// Helper: Filter out keys based on prefix or name
function is_runtime_variable($key) {
    global $excluded_prefixes, $excluded_keys;
    foreach ($excluded_prefixes as $prefix) {
        if (stripos($key, $prefix) === 0) return false;
    }
    return !in_array($key, $excluded_keys, true);
}

// Filter session data
$runtime_session = array_filter($_SESSION, function($k) {
    return is_runtime_variable($k);
}, ARRAY_FILTER_USE_KEY);
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
        $token_keys = ['access_token', 'refresh_token', 'mps_token'];
        $tokens_found = false;
        foreach ($token_keys as $key) {
            $value = $_SESSION[$key] ?? $_COOKIE[$key] ?? null;
            if ($value) {
                echo "<strong>$key:</strong><br><code>" . htmlspecialchars($value) . "</code><br><br>";
                $tokens_found = true;
            }
        }
        if (!$tokens_found) {
            echo "<p><em>No tokens found in session or cookies.</em></p>";
        }
    ?>
</div>
