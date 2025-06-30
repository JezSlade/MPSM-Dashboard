<?php
// widgets/debug_info.php

// Widget configuration
$_widget_config = [
    'name' => 'Debug Info Stream',
    'icon' => 'bug', // Font Awesome icon for bugs
    'width' => 2, // This widget might need more space
    'height' => 2
];

// Widget content rendering function
// Ensure this function is only declared once
if (!function_exists('render_debug_info_widget')) {
    function render_debug_info_widget() {
        echo '<div style="font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto; padding-right: 10px; min-height: 100px; /* Ensure visibility */">';

        echo '<h4 style="color: var(--accent); margin-top: 10px; margin-bottom: 5px;">$_SESSION Data:</h4>';
        echo '<pre style="background: var(--bg-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); word-break: break-all; white-space: pre-wrap;">';
        if (isset($_SESSION) && !empty($_SESSION)) {
            // Hide session ID from display for security/tidiness
            $display_session = $_SESSION;
            unset($display_session['PHPSESSID']);
            echo htmlspecialchars(print_r($display_session, true));
        } else {
            echo 'Session is empty or not started.';
        }
        echo '</pre>';

        echo '<h4 style="color: var(--accent); margin-top: 20px; margin-bottom: 5px;">$_POST Data (Last Request):</h4>';
        echo '<pre style="background: var(--bg-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); word-break: break-all; white-space: pre-wrap;">';
        if (isset($_POST) && !empty($_POST)) {
            echo htmlspecialchars(print_r($_POST, true));
        } else {
            echo 'No POST data received in the last request.';
        }
        echo '</pre>';

        echo '<h4 style="color: var(--accent); margin-top: 20px; margin-bottom: 5px;">Time:</h4>';
        echo '<pre style="background: var(--bg-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); word-break: break-all; white-space: pre-wrap;">';
        echo htmlspecialchars(date('Y-m-d H:i:s'));
        echo '</pre>';

        echo '</div>';
    }
}

return 'render_debug_info_widget';
