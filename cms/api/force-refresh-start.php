<?php
/**
 * Force Refresh Start - Manual Trigger
 *
 * Manually trigger cache refresh via CLI execution
 */

header('Content-Type: text/plain');

echo "=== MANUAL CACHE REFRESH TRIGGER ===\n\n";

$scriptPath = __DIR__ . '/refresh-cache-chunked.php';

// First: Start a new refresh
echo "Step 1: Starting new refresh...\n";
$command = "/usr/local/bin/php -d detect_unicode=0 {$scriptPath} start 2>&1";
echo "Command: {$command}\n\n";

$output = shell_exec($command);
echo "Output:\n{$output}\n";
echo str_repeat('-', 80) . "\n\n";

// Wait a moment
sleep(2);

// Second: Process one chunk
echo "Step 2: Processing first chunk...\n";
$command = "/usr/local/bin/php -d detect_unicode=0 {$scriptPath} process 2>&1";
echo "Command: {$command}\n\n";

$output = shell_exec($command);
echo "Output:\n{$output}\n";
echo str_repeat('-', 80) . "\n\n";

// Check status
echo "Step 3: Checking status...\n";
$stateFile = __DIR__ . '/../locks/cache-refresh-state.json';
if (file_exists($stateFile)) {
    $state = json_decode(file_get_contents($stateFile), true);
    echo "Status: {$state['status']}\n";
    echo "Devices Cached: {$state['devices_cached']}\n";
    echo "Current Page: {$state['current_page']}" . ($state['total_pages'] ? " / {$state['total_pages']}" : "") . "\n";
    echo "Last Activity: {$state['last_activity']}\n";

    if (!empty($state['errors'])) {
        echo "\nErrors:\n";
        print_r($state['errors']);
    }
} else {
    echo "State file not found\n";
}

echo "\n" . str_repeat('-', 80) . "\n";
echo "Check log: /cms/logs/cache-refresh-" . date('Y-m-d') . ".log\n";
