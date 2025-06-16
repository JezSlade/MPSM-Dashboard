<?php
// --- Optional Debug ---
error_reporting(E_ALL);
ini_set('display_errors', '1');

// --- Auto-refresh cache once per day ---
$cachePath  = __DIR__ . '/cache/data.json';
$enginePath = __DIR__ . '/engine/cache_engine.php';
$needsRefresh = true;

if (file_exists($cachePath)) {
  $last = json_decode(file_get_contents($cachePath), true);
  $lastRun = strtotime($last['timestamp'] ?? '1970-01-01');
  $today = strtotime(date('Y-m-d') . ' 00:00:00');
  $needsRefresh = $lastRun < $today;
}

if ($needsRefresh) {
  // Run cache engine in its own sandboxed scope
  (function () use ($enginePath) {
    include $enginePath;
  })();
}

// ...rest of your render_view() or SPA logic here
