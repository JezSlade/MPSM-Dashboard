<?php
/**
 * Debug Action Flow
 *
 * Shows what action is detected and where the script logic goes
 */

header('Content-Type: text/plain');

echo "=== ACTION FLOW DEBUG ===\n\n";

$script = __DIR__ . '/refresh-cache-chunked.php';
$command = "/usr/bin/php {$script} process 2>&1";

echo "Command: {$command}\n";
echo "Expected: argv[1] = 'process'\n\n";

echo "Testing via shell_exec:\n";
echo str_repeat('-', 60) . "\n";

$output = shell_exec($command);
echo $output;

echo "\n" . str_repeat('-', 60) . "\n";

// Also show relevant lines from the script
echo "\nScript action detection (lines 100-120):\n";
$lines = file($script);
for ($i = 99; $i < min(120, count($lines)); $i++) {
    printf("%3d: %s", $i + 1, $lines[$i]);
}
