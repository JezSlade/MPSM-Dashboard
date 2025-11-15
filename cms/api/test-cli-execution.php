<?php
/**
 * Test CLI Execution of refresh-cache-chunked.php
 *
 * Simulates what CRON does to help diagnose execution issues
 */

header('Content-Type: text/plain');

echo "=== CLI EXECUTION TEST ===\n\n";

$scriptPath = __DIR__ . '/refresh-cache-chunked.php';
$command = "/usr/local/bin/php -d detect_unicode=0 {$scriptPath} process 2>&1";

echo "Command: {$command}\n";
echo str_repeat('-', 80) . "\n\n";

echo "Executing...\n\n";

$output = shell_exec($command);

echo "Output:\n";
echo str_repeat('-', 80) . "\n";
echo $output;
echo "\n" . str_repeat('-', 80) . "\n";

// Try to parse as JSON
$data = json_decode($output, true);
if ($data) {
    echo "\nParsed JSON:\n";
    print_r($data);
} else {
    echo "\nNOTE: Output is not valid JSON\n";
}

echo "\n\nCheck state file after this execution:\n";
echo "URL: /cms/api/refresh-cache-chunked.php?action=status\n";
