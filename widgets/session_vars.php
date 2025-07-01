<?php
// widgets/session_vars.php

$_widget_config = [
    'name' => 'Session Variables',
    'icon' => 'bug',
    'width' => 2,
    'height' => 2
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="widget-body">
    <h3 class="widget-section-title">Session Variables</h3>
    <?php if (!empty($_SESSION)): ?>
        <pre><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>
    <?php else: ?>
        <p><em>No session variables set.</em></p>
    <?php endif; ?>

    <h3 class="widget-section-title">Cookies</h3>
    <?php if (!empty($_COOKIE)): ?>
        <pre><?= htmlspecialchars(print_r($_COOKIE, true)) ?></pre>
    <?php else: ?>
        <p><em>No cookies set.</em></p>
    <?php endif; ?>

    <h3 class="widget-section-title">Environment Variables</h3>
    <?php
        $env = getenv();
        if (!empty($env)):
    ?>
        <pre><?= htmlspecialchars(print_r($env, true)) ?></pre>
    <?php else: ?>
        <p><em>No environment variables available.</em></p>
    <?php endif; ?>
</div>
