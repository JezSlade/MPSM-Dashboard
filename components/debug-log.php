<?php declare(strict_types=1);
// /components/debug-log.php

// Always return HTML
header('Content-Type: text/html; charset=utf-8');

// Look for debug.log in the project’s /logs folder (one level up)
// and as a fallback two levels up if your structure differs.
$possible = [
    __DIR__ . '/../logs/debug.log',
    __DIR__ . '/../../logs/debug.log',
];
$logFile = '';
foreach ($possible as $path) {
    if (is_readable($path)) {
        $logFile = $path;
        break;
    }
}

if (!$logFile) {
    $content = 'Debug log not found at any of: ' . implode(', ', $possible);
} else {
    // Read last 500 lines for performance
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $tail  = array_slice($lines, -500);
    $content = implode("\n", $tail);
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Debug Log</title>
  <style>
    body { background: #111; color: #eee; font-family: monospace; padding: 1em; }
    pre  { white-space: pre-wrap; word-break: break-word; }
  </style>
</head>
<body>
  <h1>Debug Log</h1>
  <pre><?= htmlspecialchars($content) ?></pre>
</body>
</html>
