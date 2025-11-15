<?php
/**
 * Tail CRON Log - Show last 50 lines
 */

require '../config.php';

header('Content-Type: text/plain');

$logFile = __DIR__ . '/../logs/cron-router-' . date('Y-m-d') . '.log';

if (!file_exists($logFile)) {
    echo "Log file not found: $logFile\n";
    echo "\nChecking for other log files...\n";
    $logDir = dirname($logFile);
    $files = glob($logDir . '/cron-router-*.log');
    if ($files) {
        echo "Available logs:\n";
        foreach ($files as $file) {
            echo "  - " . basename($file) . " (" . filesize($file) . " bytes, modified: " . date('Y-m-d H:i:s', filemtime($file)) . ")\n";
        }
        echo "\nShowing latest log:\n";
        $logFile = end($files);
    } else {
        exit("No log files found.\n");
    }
}

echo "=== CRON Log: " . basename($logFile) . " ===\n";
echo "Last modified: " . date('Y-m-d H:i:s', filemtime($logFile)) . "\n";
echo "Size: " . filesize($logFile) . " bytes\n";
echo "\n=== LAST 50 LINES ===\n\n";

$lines = file($logFile);
$lastLines = array_slice($lines, -50);
echo implode('', $lastLines);
