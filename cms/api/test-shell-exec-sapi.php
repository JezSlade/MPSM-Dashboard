<?php
/**
 * Test SAPI via shell_exec
 *
 * Calls echo-sapi.php via shell_exec to see what SAPI is detected
 */

header('Content-Type: text/plain');

echo "=== SHELL_EXEC SAPI TEST ===\n\n";

$script = __DIR__ . '/echo-sapi.php';
$command = "/usr/bin/php {$script} 2>&1";

echo "Command: {$command}\n";
echo "Output:\n";

$output = shell_exec($command);
echo $output;

echo "\nThis shows what SAPI name is detected when running via /usr/bin/php\n";
