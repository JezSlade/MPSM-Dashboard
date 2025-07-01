<?php
// widgets/session_vars.php

$_widget_config = [
    'name' => 'Session Variables',
    'icon' => 'bug',
    'width' => 2,
    'height' => 2
];

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Keys to hide (dashboard or irrelevant internal ones)
$excluded_keys = ['dashboard_config', 'active_widgets', 'widget_order'];
?>

<div class="widget-body">
    <h3 class="widget-section-title">Session Variables</h3>
    <?php
    $filtered_session = array_diff_key($_SESSION, array_flip($excluded_keys));
    if (!empty($filtered_session)):
    ?>
        <pre><?= htmlspecialchars(print_r($filtered_session, true)) ?></pre>
    <?php else: ?>
        <p><em>No relevant session variables set.</em></p>
    <?php endif; ?>

    <h3 class="widget-section-title">Cookies</h3>
    <?php if (!empty($_COOKIE)): ?>
        <pre><?= htmlspecialchars(print_r($_COOKIE, true)) ?></pre>
    <?php else: ?>
        <p><em>No cookies set.</em></p>
    <?php endif; ?>

    <h3 class="widget-section-title">Token Overview</h3>
    <?php
        $token_keys = ['access_token', 'refresh_token'];
        $found = false;
        foreach ($token_keys as $key) {
            if (isset($_SESSION[$key]) || isset($_COOKIE[$key])) {
                $found = true;
                echo "<strong>$key:</strong><br>";
                echo "<code>" . htmlspecialchars($_SESSION[$key] ?? $_COOKIE[$key]) . "</code><br><br>";
            }
        }
        if (!$found) {
            echo "<p><em>No access or refresh tokens found.</em></p>";
        }
    ?>
</div>
