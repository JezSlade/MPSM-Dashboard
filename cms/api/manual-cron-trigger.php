<?php
/**
 * Manual CRON Trigger
 *
 * Simulates CRON execution via HTTP for testing
 * This bypasses cPanel CRON and runs the router directly via CLI
 */

require '../config.php';

header('Content-Type: text/plain');

echo "=== MANUAL CRON TRIGGER ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$cronScript = dirname(__DIR__) . '/cron-router.php';

if (!file_exists($cronScript)) {
    die("ERROR: CRON router not found at: {$cronScript}\n");
}

echo "Executing: /usr/bin/php {$cronScript}\n";
echo str_repeat('-', 60) . "\n\n";

// Execute CRON router via CLI
$command = "/usr/bin/php {$cronScript} 2>&1";
$output = shell_exec($command);

echo "CRON Output:\n";
echo $output;
echo "\n";

echo str_repeat('-', 60) . "\n";
echo "Execution complete. Check logs for details.\n";
