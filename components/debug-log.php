<?php declare(strict_types=1);
// /components/debug-log.php

// Always serve HTML
header('Content-Type: text/html; charset=utf-8');

// Locate the debug log
$logFile = __DIR__ . '/../logs/debug.log';
if (!file_exists($logFile)) {
    $content = "Debug log not found at {$logFile}";
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
    pre  { white-space: pre-wrap; word-wrap: break-word; }
  </style>
</head>
<body>
  <h1>Debug Log</h1>
  <pre><?= htmlspecialchars($content) ?></pre>
</body>
</html>
