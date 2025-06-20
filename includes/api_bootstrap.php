<?php declare(strict_types=1);
// /includes/api_bootstrap.php

// 0) Ensure debug.log exists for PHP error_log()
$logFile = __DIR__ . '/../logs/debug.log';
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0664);
}

// 1) Turn on error logging (but don’t display to users)
ini_set('display_errors', '0');
ini_set('log_errors',     '1');
ini_set('error_log',      $logFile);
error_reporting(E_ALL);

// 2) Buffer output so headers can still be sent
ob_start();

// 3) Load helpers and env
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 4) Detect if this is an API call
$isApi = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
if ($isApi) {
    header('Content-Type: application/json');
}

// 5) Read raw JSON input
$inputRaw = file_get_contents('php://input');
$input    = json_decode($inputRaw, true) ?: [];

// 6) Validate required fields (if set)
if (!empty($requiredFields) && is_array($requiredFields)) {
    foreach ($requiredFields as $f) {
        if (empty($input[$f])) {
            http_response_code(400);
            echo json_encode(['error'=>"Missing required field: {$f}"]);
            ob_end_flush();
            exit;
        }
    }
}

// 7) Prepare cache (optional)
$method   = $method   ?? 'POST';
$useCache = $useCache ?? false;
$cacheKey = ($useCache && isset($cache))
    ? "{$path}:" . md5(serialize($input))
    : null;

// 8) Return cached response if available
if ($cacheKey && isset($cache) && ($cached = $cache->get($cacheKey))) {
    echo $cached;
    ob_end_flush();
    exit;
}

// 9) Call the remote API
try {
    $resp = call_api($config, $method, $path, $input);
    $json = json_encode($resp, JSON_THROW_ON_ERROR);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
    ob_end_flush();
    exit;
}

// 10) Cache & output
if ($cacheKey && isset($cache)) {
    $cache->set($cacheKey, $json, $config['CACHE_TTL'] ?? 300);
}
echo $json;

// 11) Flush everything
ob_end_flush();
