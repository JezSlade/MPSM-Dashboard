<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

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
  try {
    (function () use ($enginePath) {
      include $enginePath;
    })();
  } catch (Throwable $e) {
    error_log("[CACHE ERROR] " . $e->getMessage());
  }
}

// --- Your regular SPA logic here ---
$view = $_GET['view'] ?? 'dashboard';
$viewFile = __DIR__ . '/views/' . basename($view) . '.php';

if (file_exists($viewFile)) {
  include $viewFile;
} else {
  echo "<div class='card error'><h2>View Not Found</h2><p>The view '$view' does not exist.</p></div>";
}
