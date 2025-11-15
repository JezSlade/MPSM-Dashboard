<?php
/**
 * CRON Diagnosis Tool
 *
 * Checks:
 * 1. Lock files that might block CRON
 * 2. Log files from last 3 days
 * 3. State file status
 * 4. Simulated CLI execution
 */

require '../config.php';
require '../functions.php';

header('Content-Type: text/plain');

echo "=== CRON DIAGNOSIS REPORT ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . " (EST)\n\n";

// Check lock files
echo "1. LOCK FILES\n";
echo str_repeat('-', 60) . "\n";
$locksDir = __DIR__ . '/../locks';
if (is_dir($locksDir)) {
    $locks = glob($locksDir . '/*.lock');
    if (empty($locks)) {
        echo "✓ No lock files found (good)\n";
    } else {
        foreach ($locks as $lock) {
            $age = time() - filemtime($lock);
            echo "⚠ Lock file: " . basename($lock) . " (age: {$age}s)\n";
            if ($age > 300) {
                echo "  → STALE (>5 min) - should be deleted\n";
            }
        }
    }
} else {
    echo "✗ Locks directory not found\n";
}
echo "\n";

// Check log files
echo "2. CRON LOG FILES (Last 3 days)\n";
echo str_repeat('-', 60) . "\n";
$logsDir = __DIR__ . '/../logs';
if (is_dir($logsDir)) {
    $logs = glob($logsDir . '/cron-router-*.log');
    rsort($logs); // Newest first
    $logs = array_slice($logs, 0, 3);

    foreach ($logs as $log) {
        $size = filesize($log);
        $modified = filemtime($log);
        $age = time() - $modified;
        echo "File: " . basename($log) . "\n";
        echo "  Size: {$size} bytes\n";
        echo "  Modified: " . date('Y-m-d H:i:s', $modified) . " ({$age}s ago)\n";

        // Show last 3 lines
        $lines = file($log);
        $lastLines = array_slice($lines, -3);
        echo "  Last 3 lines:\n";
        foreach ($lastLines as $line) {
            echo "    " . rtrim($line) . "\n";
        }
        echo "\n";
    }
} else {
    echo "✗ Logs directory not found\n";
}
echo "\n";

// Check state file
echo "3. CACHE REFRESH STATE FILE\n";
echo str_repeat('-', 60) . "\n";
$stateFile = $locksDir . '/cache-refresh-state.json';
if (file_exists($stateFile)) {
    $state = json_decode(file_get_contents($stateFile), true);
    $age = time() - filemtime($stateFile);
    echo "✓ State file exists\n";
    echo "  Modified: " . date('Y-m-d H:i:s', filemtime($stateFile)) . " ({$age}s ago)\n";
    echo "  Status: {$state['status']}\n";
    echo "  Current page: {$state['current_page']}\n";
    echo "  Last activity: {$state['last_activity']}\n";
} else {
    echo "✗ State file not found\n";
}
echo "\n";

// Test CLI execution
echo "4. SIMULATED CLI EXECUTION TEST\n";
echo str_repeat('-', 60) . "\n";
$scriptPath = __DIR__ . '/refresh-cache-chunked.php';
$command = "/usr/bin/php {$scriptPath} status 2>&1";
echo "Command: {$command}\n";
echo "Output:\n";
$output = shell_exec($command);
echo $output;
echo "\n";

echo "=== END DIAGNOSIS ===\n";
