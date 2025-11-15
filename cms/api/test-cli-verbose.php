<?php
/**
 * Verbose CLI Test with Error Capture
 *
 * Captures all possible errors including fatal errors
 */

header('Content-Type: text/plain');

echo "=== VERBOSE CLI TEST ===\n\n";

$scriptPath = __DIR__ . '/refresh-cache-chunked.php';
$command = "/usr/local/bin/php -d detect_unicode=0 -d display_errors=1 -d error_reporting=E_ALL {$scriptPath} process 2>&1";

echo "Command: {$command}\n";
echo str_repeat('-', 80) . "\n\n";

// Execute and capture ALL output (stdout + stderr)
$output = shell_exec($command);

echo "Raw Output Length: " . strlen($output) . " bytes\n";
echo str_repeat('-', 80) . "\n";
echo $output;
echo "\n" . str_repeat('-', 80) . "\n";

// Also try to get error log
$errorLogPath = ini_get('error_log');
echo "\nPHP Error Log Path: " . ($errorLogPath ?: 'default') . "\n";

// Check if we can see the refresh-cache log
$refreshLog = __DIR__ . '/../logs/cache-refresh-' . date('Y-m-d') . '.log';
echo "\nRefresh Cache Log: {$refreshLog}\n";
if (file_exists($refreshLog)) {
    echo "Last 10 lines:\n";
    $lines = file($refreshLog);
    $last10 = array_slice($lines, -10);
    echo implode('', $last10);
} else {
    echo "Log file does not exist\n";
}
