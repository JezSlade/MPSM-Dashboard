<?php
declare(strict_types=1);

/**
 * Debug Log Viewer
 * ------------------------------------------------------------------
 * Shows the unified /logs/debug.log created by includes/debug.php.
 * Handles three states gracefully:
 *   1. File exists & readable   → prints content (latest at bottom)
 *   2. File exists but empty    → “Log is empty.”
 *   3. File missing             → “Log not found.” (with resolved path)
 */

// Always resolve against project root → /logs/debug.log
$logFile = realpath(__DIR__ . '/../logs/debug.log');

// Fallback if realpath fails (directory may exist but file missing)
if ($logFile === false) {
    $logFile = __DIR__ . '/../logs/debug.log';
}

header('Content-Type: text/plain');

if (!is_file($logFile)) {
    echo 'Log not found. (expecting ' . $logFile . ')';
    exit;
}

$contents = file_get_contents($logFile);
if ($contents === '') {
    echo 'Log is empty. (' . $logFile . ')';
    exit;
}

echo $contents;
