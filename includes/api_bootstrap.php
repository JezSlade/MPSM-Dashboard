<?php declare(strict_types=1);
// /includes/api_bootstrap.php

// 0) Ensure debug.log exists
$logFile = __DIR__ . '/../logs/debug.log';
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0664);
}

// 1) Enable error logging & live-debug injection
ini_set('display_errors', '1');
ini_set('log_errors',     '1');
ini_set('error_log',      $logFile);
error_reporting(E_ALL);

// 2) Buffer output
ob_start();

// 3) Inject live-debug HTML+JS
if (php_sapi_name() !== 'cli') {
    echo <<<HTML
<style>
#debug-console {
  background: rgba(0,0,0,0.8);
  color: #0f0;
  padding: 5px;
  font-family: monospace;
  font-size: 11px;
  height: 150px;
  overflow-y: auto;
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  z-index: 9999;
  border-top: 1px solid #444;
}
</style>
<div id="debug-console"></div>
<script>
function appendDebug(msg){
  var c = document.getElementById('debug-console');
  if(c){ c.innerHTML += msg + '<br>'; c.scrollTop = c.scrollHeight; }
}
appendDebug('▶ api_bootstrap loaded');
</script>
HTML;
}

// 4) Log start
error_log("api_bootstrap ▶ start");

// 5) Load core functions
require_once __DIR__ . '/api_functions.php';
error_log("api_bootstrap ▶ api_functions loaded");

// 6) Load .env
$config = parse_env_file(__DIR__ . '/../.env');
error_log("api_bootstrap ▶ .env parsed");

// 7) Detect API & set JSON header
$isApi = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
if ($isApi) {
    header('Content-Type: application/json');
}
error_log("api_bootstrap ▶ isApi = " . ($isApi ? 'true' : 'false'));

// 8) Read raw input
$inputRaw = file_get_contents('php://input');
$input    = json_decode($inputRaw, true) ?: [];
error_log("api_bootstrap ▶ input = " . $inputRaw);

// 9) Validate requiredFields
if (!empty($requiredFields) && is_array($requiredFields)) {
  foreach ($requiredFields as $f) {
    if (empty($input[$f])) {
      http_response_code(400);
      echo json_encode(['error'=>"Missing required field: {$f}"]);
      error_log("api_bootstrap ▶ missing field: {$f}");
      ob_end_flush();
      exit;
    }
  }
}

// 10) Prepare caching
$method   = $method   ?? 'POST';
$useCache = $useCache ?? false;
$cacheKey = ($useCache && isset($cache))
    ? "{$path}:" . md5(serialize($input))
    : null;

// 11) Return cached if found
if ($cacheKey && isset($cache)) {
    if ($cached = $cache->get($cacheKey)) {
        echo $cached;
        error_log("api_bootstrap ▶ returned cached");
        ob_end_flush();
        exit;
    }
}

// 12) Call remote API
try {
    error_log("api_bootstrap ▶ calling API: {$path}");
    $resp = call_api($config, $method, $path, $input);
    $json = json_encode($resp, JSON_THROW_ON_ERROR);
    error_log("api_bootstrap ▶ API success");
} catch (\Throwable $e) {
    http_response_code(500);
    $err = $e->getMessage();
    echo json_encode(['error'=>$err]);
    error_log("api_bootstrap ▶ API ERROR: {$err}");
    ob_end_flush();
    exit;
}

// 13) Cache & output
if ($cacheKey && isset($cache)) {
    $cache->set($cacheKey, $json, $config['CACHE_TTL'] ?? 300);
    error_log("api_bootstrap ▶ cached response");
}
echo $json;
error_log("api_bootstrap ▶ output and flush");
ob_end_flush();
