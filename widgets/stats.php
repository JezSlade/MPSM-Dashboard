<?php
// widgets/stats.php

// Widget Name: Visitor Stats
// Widget Icon: fas fa-users
// Widget Width: 2.0
// Widget Height: 2.0

$_widget_config = [
    'name' => 'Visitor Stats',
    'icon' => 'users',
    'width' => 2,
    'height' => 2
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function render_stats_widget() {
    $logPath = __DIR__ . '/../logs/uuid_visits.log';
    $unique = 0;
    $total = 0;
    if (file_exists($logPath)) {
        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $uuids = [];
        foreach ($lines as $line) {
            [$uuid] = explode('|', $line);
            $uuids[$uuid] = true;
            $total++;
        }
        $unique = count($uuids);
    }

    ob_start();
    echo "<div class=\"compact-content\" style=\"display:none;\">";
    echo "  <div class=\"text-muted\">Unique: " . htmlspecialchars((string)$unique) . ", Total: " . htmlspecialchars((string)$total) . "</div>";
    echo "</div>";

    echo "<div class=\"expanded-content\" style=\"display:block;\">";
    echo "  <h3>Visitor Stats</h3>";
    echo "  <p><strong>Unique Visitors:</strong> " . htmlspecialchars((string)$unique) . "</p>";
    echo "  <p><strong>Total Visits:</strong> " . htmlspecialchars((string)$total) . "</p>";
    echo "</div>";
    return ob_get_clean();
}

?>
<?php echo render_stats_widget(); ?>
