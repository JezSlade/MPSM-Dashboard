<?php
// --- DEBUG BLOCK ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/debug.log');

// 🔍 Log start
file_put_contents(__DIR__ . '/logs/cache_debug.log', "START index.php\n", FILE_APPEND);

$cacheFile  = __DIR__ . '/cache/data.json';
$enginePath = __DIR__ . '/engine/cache_engine.php';

// ✅ Step 1: Try to create cache if missing
if (!file_exists($cacheFile)) {
  file_put_contents(__DIR__ . '/logs/cache_debug.log', "CACHE FILE MISSING. Running cache_engine.php\n", FILE_APPEND);
  include $enginePath;
  clearstatcache();

  if (file_exists($cacheFile)) {
    file_put_contents(__DIR__ . '/logs/cache_debug.log', "✅ Cache created successfully.\n", FILE_APPEND);
  } else {
    file_put_contents(__DIR__ . '/logs/cache_debug.log', "❌ Cache still missing after build.\n", FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Cache file not found after rebuild']);
    exit;
  }
}

// ✅ Step 2: Auto-refresh if cache is older than today
$needsRefresh = true;

if (file_exists($cacheFile)) {
  $last = json_decode(file_get_contents($cacheFile), true);
  $lastRun = strtotime($last['timestamp'] ?? '1970-01-01');
  $today = strtotime(date('Y-m-d') . ' 00:00:00');
  $needsRefresh = $lastRun < $today;
}

if ($needsRefresh) {
  file_put_contents(__DIR__ . '/logs/cache_debug.log', "CACHE is stale. Running refresh.\n", FILE_APPEND);
  (function () use ($enginePath) {
    include $enginePath;
    clearstatcache();
  })();
} else {
  file_put_contents(__DIR__ . '/logs/cache_debug.log', "CACHE is current. Skipping refresh.\n", FILE_APPEND);
}

// ✅ Proceed to normal rendering
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

render_view('views/dashboard.php');

require_once __DIR__ . '/includes/footer.php';
