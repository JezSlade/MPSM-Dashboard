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
        echo '<div style="font-family: monospace; font-size: 12px; max-height: 100%; overflow-y: auto; padding-right: 10px; min-height: 100px;">';

        // Shared content for both views (time)
        $time_content = '<h4 style="color: var(--accent); margin-top: 10px; margin-bottom: 5px;">Current Time:</h4>';
        $time_content .= '<pre style="background: var(--bg-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); word-break: break-all; white-space: pre-wrap;">';
        $time_content .= htmlspecialchars(date('Y-m-d H:i:s'));
        $time_content .= '</pre>';

        // Session data content
        $session_content = '<h4 style="color: var(--accent); margin-top: 10px; margin-bottom: 5px;">$_SESSION Data:</h4>';
        $session_content .= '<pre style="background: var(--bg-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); word-break: break-all; white-space: pre-wrap;">';
        if (isset($_SESSION) && !empty($_SESSION)) {
            $display_session = $_SESSION;
            unset($display_session['PHPSESSID']); // Hide session ID
            $session_content .= htmlspecialchars(print_r($display_session, true));
        } else {
            $session_content .= 'Session is empty or not started.';
        }
        $session_content .= '</pre>';

        // POST data content
        $post_content = '<h4 style="color: var(--accent); margin-top: 20px; margin-bottom: 5px;">$_POST Data (Last Request):</h4>';
        $post_content .= '<pre style="background: var(--bg-secondary); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); word-break: break-all; white-space: pre-wrap;">';
        if (isset($_POST) && !empty($_POST)) {
            $post_content .= htmlspecialchars(print_r($_POST, true));
        } else {
            $post_content .= 'No POST data received in the last request.';
        }
        $post_content .= '</pre>';

        // Compact View (only session data + time)
        echo '<div class="compact-content">';
        echo $time_content;
        echo $session_content;
        echo '</div>'; // End compact-content

        // Expanded View (session data + post data + time)
        echo '<div class="expanded-content">';
        echo $time_content;
        echo $session_content;
        echo $post_content;
        echo '</div>'; // End expanded-content

        echo '</div>'; // End main wrapper div
    }
}

return 'render_debug_info_widget';
