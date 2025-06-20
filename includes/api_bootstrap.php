<?php declare(strict_types=1);
// /includes/api_bootstrap.php

// 0) Ensure debug.log exists
$logFile = __DIR__ . '/../logs/debug.log';
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0664);
}

// 1) Enable error logging (file only)
ini_set('display_errors', '0');
ini_set('log_errors',     '1');
ini_set('error_log',      $logFile);
error_reporting(E_ALL);

// 2) Buffer output
ob_start();

// 3) Detect API request and set JSON header
$isApi = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
if ($isApi) {
    header('Content-Type: application/json');
}
error_log("api_bootstrap ▶ start (isApi={$isApi})");

// 4) Load core helpers and .env
require_once __DIR__ . '/api_functions.php';
error_log("api_bootstrap ▶ api_functions loaded");
$config = parse_env_file(__DIR__ . '/../.env');
error_log("api_bootstrap ▶ .env parsed");

// 5) Read & validate input
$inputRaw = file_get_contents('php://input');
$input    = json_decode($inputRaw, true) ?: [];
error_log("api_bootstrap ▶ input: {$inputRaw}");
if (!empty($requiredFields) && is_array($requiredFields)) {
    foreach ($requiredFields as $f) {
        if (empty($input[$f])) {
            http_response_code(400);
            $msg = json_encode(['error'=>"Missing required field: {$f}"]);
            echo $msg;
            error_log("api_bootstrap ▶ missing field: {$f}");
            ob_end_flush();
            exit;
        }
    }
}

// 6) Prepare cache key if needed
$method   = $method   ?? 'POST';
$useCache = $useCache ?? false;
$cacheKey = ($useCache && isset($cache))
    ? "{$path}:" . md5(serialize($input))
    : null;

// 7) Return cached response
if ($cacheKey && isset($cache) && ($cached = $cache->get($cacheKey))) {
    echo $cached;
    error_log("api_bootstrap ▶ returned cached");
    ob_end_flush();
    exit;
}

// 8) Call remote API
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

// 9) Cache & output
if ($cacheKey && isset($cache)) {
    $cache->set($cacheKey, $json, $config['CACHE_TTL'] ?? 300);
    error_log("api_bootstrap ▶ cached response");
}
echo $json;
error_log("api_bootstrap ▶ output complete");
ob_end_flush();
