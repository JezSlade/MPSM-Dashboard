<?php
// --- DEBUG MODE ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/debug.log');

echo "<!-- Start index.php -->\n";

// --- CACHE ENGINE AUTO-TRIGGER ---
$cachePath  = __DIR__ . '/cache/data.json';
$enginePath = __DIR__ . '/engine/cache_engine.php';
$needsRefresh = true;

if (file_exists($cachePath)) {
  $last = json_decode(file_get_contents($cachePath), true);
  $lastRun = strtotime($last['timestamp'] ?? '1970-01-01');
  $today = strtotime(date('Y-m-d') . ' 00:00:00');
  $needsRefresh = $lastRun < $today;
}

echo "<!-- Cache Refresh Needed? " . ($needsRefresh ? 'YES' : 'NO') . " -->\n";

if ($needsRefresh) {
  try {
    (function () use ($enginePath) {
      echo "<!-- Running cache_engine.php -->\n";
      include $enginePath;
    })();
  } catch (Throwable $e) {
    echo "<!-- Cache Engine Error -->\n";
    error_log("[CACHE ENGINE FAIL] " . $e->getMessage());
  }
} else {
  echo "<!-- Using existing cache -->\n";
}

// --- LOAD VIEW ---
$view = $_GET['view'] ?? 'dashboard';
$viewFile = __DIR__ . '/views/' . basename($view) . '.php';

echo "<!-- View file: $viewFile -->\n";

if (file_exists($viewFile)) {
  include $viewFile;
  echo "<!-- View loaded successfully -->\n";
} else {
  echo "<div class='card error'><h2>View Not Found</h2><p>The view '$view' does not exist.</p></div>";
  echo "<!-- View not found fallback shown -->\n";
}

echo "<!-- End of index.php -->";
