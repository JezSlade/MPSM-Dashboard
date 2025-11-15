<?php
/**
 * Check Cache Refresh Progress
 *
 * Simple status check with server time and fresh data
 */

header('Content-Type: text/plain');

echo "=== CACHE REFRESH PROGRESS CHECK ===\n\n";

echo "Server Time: " . date('Y-m-d H:i:s') . "\n\n";

// Check state file
$stateFile = __DIR__ . '/../locks/cache-refresh-state.json';

if (!file_exists($stateFile)) {
    echo "State File: NOT FOUND\n";
    echo "Status: No refresh in progress\n";
    exit;
}

echo "State File: EXISTS\n";
echo "Modified: " . date('Y-m-d H:i:s', filemtime($stateFile)) . "\n";
echo "Age: " . (time() - filemtime($stateFile)) . " seconds ago\n\n";

$state = json_decode(file_get_contents($stateFile), true);

if ($state) {
    echo "Status: {$state['status']}\n";
    echo "Started: {$state['started_at']}\n";
    echo "Last Activity: {$state['last_activity']}\n";
    echo "Current Page: {$state['current_page']}" . ($state['total_pages'] ? " / {$state['total_pages']}" : "") . "\n";
    echo "Devices Cached: {$state['devices_cached']}\n";
    echo "Drilldowns Cached: {$state['drilldowns_cached']}\n";

    if (!empty($state['errors'])) {
        echo "\nErrors:\n";
        foreach ($state['errors'] as $error) {
            echo "  - {$error}\n";
        }
    }
} else {
    echo "ERROR: Could not parse state file\n";
}

// Check log file
echo "\n" . str_repeat('-', 80) . "\n";
$logFile = __DIR__ . '/../logs/cache-refresh-' . date('Y-m-d') . '.log';
echo "Log File: {$logFile}\n";

if (file_exists($logFile)) {
    echo "Last 5 lines:\n";
    $lines = file($logFile);
    $last5 = array_slice($lines, -5);
    echo implode('', $last5);
} else {
    echo "Log file does not exist\n";
}
