<?php
// widgets/session_vars.php

// MODIFIED: Use full Font Awesome class in metadata comment
// Widget Name: Session Variables
// Widget Icon: fas fa-bug
// Widget Width: 2.0
// Widget Height: 2.0

// The $_widget_config array is no longer directly used for metadata extraction
// by discover_widgets(). It's kept here for backward compatibility or other
// internal widget logic if needed. The metadata is now parsed from comments.
$_widget_config = [
    'name' => 'Session Variables',
    'icon' => 'bug', // This 'bug' will be overridden by the comment parsing
    'width' => 2,
    'height' => 2
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Config: ignore these dashboard/control variables
$excluded_prefixes = ['dashboard_', 'widget_', 'ui_', 'layout_', 'grid_', 'settings'];
$excluded_keys = ['active_widgets', 'widget_order', 'dashboard_config'];

/**
 * Determines whether a session key is considered runtime-relevant.
 */
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
