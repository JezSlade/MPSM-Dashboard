<?php
/**
 * View CRON Heartbeat
 *
 * Shows when CRON last executed
 */

header('Content-Type: text/plain');

echo "=== CRON HEARTBEAT STATUS ===\n\n";
echo "Server Time: " . date('Y-m-d H:i:s') . "\n\n";

$logFile = __DIR__ . '/../logs/cron-heartbeat.log';

if (!file_exists($logFile)) {
    echo "No heartbeat log found.\n";
    echo "CRON has never executed the heartbeat logger.\n\n";
    echo "To enable, update CRON command to:\n";
    echo "/usr/local/bin/php -d detect_unicode=0 /home/resolut7/public_html/mpsm.resolutionsbydesign.us/cms/api/cron-heartbeat.php && /usr/local/bin/php -d detect_unicode=0 /home/resolut7/public_html/mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php process\n";
    exit;
}

echo "Heartbeat log exists.\n";
echo "Last modified: " . date('Y-m-d H:i:s', filemtime($logFile)) . "\n";
echo "Age: " . (time() - filemtime($logFile)) . " seconds ago\n\n";

echo "Last 10 heartbeats:\n";
echo str_repeat('-', 80) . "\n";

$lines = file($logFile);
$last10 = array_slice($lines, -10);
echo implode('', $last10);

echo str_repeat('-', 80) . "\n";

$lastLine = trim(end($lines));
if (preg_match('/\[(.*?)\]/', $lastLine, $matches)) {
    $lastExecution = strtotime($matches[1]);
    $age = time() - $lastExecution;

    echo "\nLast execution: {$matches[1]}\n";
    echo "Time since last execution: {$age} seconds\n";

    if ($age < 90) {
        echo "✓ CRON is running (executed within last 90 seconds)\n";
    } else {
        echo "✗ CRON may not be running (last execution was {$age} seconds ago)\n";
    }
}
