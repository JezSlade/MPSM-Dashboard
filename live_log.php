<?php
/**
 * Simple live log stream output for dashboard.log
 * Displays last 200 lines in reverse order (newest at top)
 */

// PHP Debugging Lines - START
// Enable all error reporting for development purposes.
// This helps in identifying and debugging issues quickly.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

$logFile = __DIR__ . '/../logs/dashboard.log';

if (!file_exists($logFile)) {
    echo "<div style='color:red;'>Log file not found.</div>";
    exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$lines = array_reverse(array_slice($lines, -200));

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>
    body { font-family: monospace; background: #111; color: #0f0; padding: 10px; margin: 0; }
    .log-line { padding: 2px 0; border-bottom: 1px solid #222; }
</style><meta http-equiv='refresh' content='2'></head><body>";

foreach ($lines as $line) {
    echo "<div class='log-line'>" . htmlspecialchars($line) . "</div>";
}

echo "</body></html>";
?>
