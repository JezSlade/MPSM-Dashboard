<?php
// widgets/debug_info.php

// Widget Name: Debug Info Stream
// Widget Icon: fas fa-bug
// Widget Width: 2.0
// Widget Height: 2.0

$_widget_config = [
    'name' => 'Debug Info Stream',
    'icon' => 'bug',
    'width' => 2,
    'height' => 2
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/logger.php';

function render_debug_info_widget() {
    $log_file = __DIR__ . '/../logs/debug.log';
    ob_start();

    echo "<div class=\"compact-content\" style=\"display:none;\">";
    echo "  <div class=\"text-muted\">Debug Info Stream (Compact)</div>";
    echo "</div>";

    echo "<div class=\"expanded-content\" style=\"display:block; height:100%; overflow:auto; padding:10px;\">";
    echo "  <pre style=\"font-size:0.9em; color:#ccc;\">";

    if (file_exists($log_file) && strpos($log_file, '.php') === false) {
        echo htmlspecialchars(file_get_contents($log_file));
    } else {
        echo "No debug log found or file path invalid.";
    }

    echo "  </pre>";
    echo "</div>";

    return ob_get_clean();
}

?>
<?= render_debug_info_widget() ?>
