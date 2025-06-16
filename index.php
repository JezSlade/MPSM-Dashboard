<?php
// --- DEBUG BLOCK ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/debug.log');

$cacheFile = __DIR__ . '/cache/data.json';
if (!file_exists($cacheFile)) {
  include __DIR__ . '/engine/cache_engine.php';
  clearstatcache();
}
// ✅ Auto-refresh cache if missing or outdated
$cachePath = __DIR__ . '/cache/data.json';
$enginePath = __DIR__ . '/engine/cache_engine.php';
$needsRefresh = true;

if (file_exists($cachePath)) {
  $last = json_decode(file_get_contents($cachePath), true);
  $lastRun = strtotime($last['timestamp'] ?? '1970-01-01');
  $today = strtotime(date('Y-m-d') . ' 00:00:00');
  $needsRefresh = $lastRun < $today;
}

if ($needsRefresh) {
  (function () use ($enginePath) {
    include $enginePath;
  })();
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

render_view('views/dashboard.php');

require_once __DIR__ . '/includes/footer.php';
?>
