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

<style>
    .widget-session-debug {
        font-family: monospace;
        font-size: 0.85em;
        padding: 1rem;
        border-radius: 1rem;
        background: rgba(40, 44, 52, 0.6);
        box-shadow:
            inset 2px 2px 4px rgba(255, 255, 255, 0.05),
            inset -2px -2px 4px rgba(0, 0, 0, 0.2),
            0 4px 30px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(6px);
        color: #eee;
        overflow-y: auto;
        max-height: 100%;
    }

    .widget-session-debug h3 {
        font-size: 1rem;
        margin-top: 1em;
        margin-bottom: 0.5em;
        color: #a0d8ef;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 0.3em;
    }

    .widget-session-debug pre {
        white-space: pre-wrap;
        word-break: break-word;
        background: rgba(0, 0, 0, 0.2);
        padding: 0.5em;
        border-radius: 0.5em;
    }

    .widget-session-debug p {
        margin: 0.5em 0;
        color: #bbb;
    }
</style>

<div class="widget-session-debug">
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
