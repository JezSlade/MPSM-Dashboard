<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/debug.log');
// ----------------------------------------
$cacheFile = __DIR__ . '/../cache/data.json';
include __DIR__ . '/engine/cache_engine.php';

$needsUpdate = true;
if (file_exists($cacheFile)) {
  $meta = json_decode(file_get_contents($cacheFile), true);
  $lastRun = strtotime($meta['timestamp'] ?? '1970-01-01');
  $today = strtotime(date('Y-m-d') . ' 00:00:00');
  $needsUpdate = ($lastRun < $today);
}

if ($needsUpdate && php_sapi_name() !== 'cli') {
  include $engineFile;
}
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

render_view('views/dashboard.php');

require_once __DIR__ . '/includes/footer.php';
?>
