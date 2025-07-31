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

    echo "<div class='compact-content' style='display:none;'>";
    echo "  <div class='text-muted'>Debug Info Stream (Compact)</div>";
    echo "</div>";

    echo "<div class='expanded-content' style='display:block; height:100%; overflow:auto; padding:10px; background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:12px; box-shadow: var(--shadow-dark); font-size:0.85em; color:var(--text-secondary); font-family:monospace;'>";
    echo "  <div style='display:flex; flex-direction:column-reverse; height:100%; overflow-y:scroll;'>";

    if (file_exists($log_file) && strpos($log_file, '.php') === false) {
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_reverse($lines) as $line) {
            $line = preg_replace("/\[(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2})\]/", "[$2]", $line); // condense timestamp
            echo "<div style='padding:2px 4px; border-bottom:1px solid rgba(255,255,255,0.05);'>" . htmlspecialchars($line) . "</div>";
        }
    } else {
        echo "<div>No debug log found or file path invalid.</div>";
    }

    echo "  </div>";
    echo "</div>";

    return ob_get_clean();
}

?>
<?= render_debug_info_widget() ?>
