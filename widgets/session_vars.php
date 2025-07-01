<?php
// widgets/session_vars.php

$_widget_config = [
    'name' => 'Session Debug',
    'icon' => 'bug',
    'width' => 2,
    'height' => 2
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<div style="font-family: monospace; font-size: 0.85em;">
    <h3>Session Variables</h3>
    <?php if (!empty($_SESSION)): ?>
        <pre><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>
    <?php else: ?>
        <p><em>No session variables set.</em></p>
    <?php endif; ?>

    <h3>Cookies</h3>
    <?php if (!empty($_COOKIE)): ?>
        <pre><?= htmlspecialchars(print_r($_COOKIE, true)) ?></pre>
    <?php else: ?>
        <p><em>No cookies set.</em></p>
    <?php endif; ?>

    <h3>Environment Variables</h3>
    <?php
        $env = getenv();
        if (!empty($env)):
    ?>
        <pre><?= htmlspecialchars(print_r($env, true)) ?></pre>
    <?php else: ?>
        <p><em>No environment variables available.</em></p>
    <?php endif; ?>
</div>
