<?php
/**
 * Force Cache Chunk Processing
 *
 * Bypasses CRON and directly processes one chunk via HTTP
 * FOR TESTING ONLY - CRON should be used in production
 */

require '../config.php';
require '../functions.php';

header('Content-Type: text/plain');

echo "=== FORCE CACHE CHUNK PROCESSING ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Execute chunked processor via CLI (bypass HTTP timeout)
$scriptPath = __DIR__ . '/refresh-cache-chunked.php';
$command = "/usr/bin/php {$scriptPath} process 2>&1";

echo "Command: {$command}\n";
echo str_repeat('-', 60) . "\n\n";

$output = shell_exec($command);
echo "Output:\n";
echo $output;

echo "\n" . str_repeat('-', 60) . "\n";
echo "Check status at: /cms/api/refresh-cache-chunked.php?action=status\n";
