<?php
/**
 * Check Last CRON Execution
 *
 * Shows when CRON last ran by checking timestamp files
 */

header('Content-Type: text/plain');

echo "=== LAST CRON EXECUTION CHECK ===\n\n";

// Check heartbeat file
$heartbeatFile = dirname(__DIR__) . '/logs/cron-heartbeat.txt';
if (file_exists($heartbeatFile)) {
    $lines = file($heartbeatFile);
    echo "Heartbeat file exists (" . count($lines) . " entries)\n";
    echo "Last 5 entries:\n";
    foreach (array_slice($lines, -5) as $line) {
        echo "  " . rtrim($line) . "\n";
    }
} else {
    echo "No heartbeat file found (CRON never ran)\n";
}

echo "\n";

// Check simple timestamp file
$timestampFile = dirname(__DIR__) . '/last-cron-run.txt';
if (file_exists($timestampFile)) {
    $lastRun = file_get_contents($timestampFile);
    $age = time() - strtotime($lastRun);
    echo "Last CRON run: {$lastRun} ({$age} seconds ago)\n";
} else {
    echo "No timestamp file found\n";
}

echo "\n";

// Check CRON router log
$logFile = dirname(__DIR__) . '/logs/cron-router-' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $modified = filemtime($logFile);
    $age = time() - $modified;
    echo "Today's CRON log exists\n";
    echo "Last modified: " . date('Y-m-d H:i:s', $modified) . " ({$age}s ago)\n";

    $lines = file($logFile);
    echo "Total lines: " . count($lines) . "\n";
    echo "Last 3 lines:\n";
    foreach (array_slice($lines, -3) as $line) {
        echo "  " . rtrim($line) . "\n";
    }
} else {
    echo "No CRON log for today\n";
}
