<?php
declare(strict_types=1);

/**
 * Debug Log Viewer
 * ------------------------------------------------------------------
 * • Always points to /logs/debug.log
 * • If the file is missing, it creates an empty one so subsequent
 *   requests succeed and the user sees “Log is empty.” instead of
 *   “Log not found.”
 */

$logFile = realpath(__DIR__ . '/../logs') . '/debug.log';
if ($logFile === '/debug.log') {                 // realpath failed
    $logFile = __DIR__ . '/../logs/debug.log';
}

header('Content-Type: text/plain');

// Create the file if it doesn’t exist yet
if (!file_exists($logFile)) {
    if (touch($logFile)) {
        echo "Log is empty. ($logFile)";
        exit;
    }
    echo "Unable to create log file at $logFile";
    exit;
}

// Show content or placeholder
$contents = file_get_contents($logFile);
echo $contents !== '' ? $contents : "Log is empty. ($logFile)";
