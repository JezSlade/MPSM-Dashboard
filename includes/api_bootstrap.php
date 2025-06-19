<?php declare(strict_types=1);
// /includes/api_bootstrap.php

// —————————————————————————————————————————————————————————
// 0) Enable debug logging
// —————————————————————————————————————————————————————————
ini_set('display_errors', '0');                                // don’t show errors to users
ini_set('log_errors',   '1');                                // enable error logging
ini_set('error_log',    __DIR__ . '/../logs/debug.log');    // point at your debug file
error_reporting(E_ALL);                                      // report everything

// —————————————————————————————————————————————————————————
// 1) Buffer all output so we can send headers later
// —————————————————————————————————————————————————————————
ob_start();

// 2) Load shared API helpers (defines parse_env_file, call_api, etc.)
require_once __DIR__ . '/api_functions.php';

// 3) Parse .env into $config
$config = parse_env_file(__DIR__ . '/../.env');

// 4) Optional: initialize Redis (fail-soft)
try {
    require_once __DIR__ . '/redis.php';
    $cache = new RedisClient($config);
} catch (\Throwable $e) {
    $cache = null;
}

// 5) Detect true API endpoints
$isApi = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;

// 6) Send JSON header if API
if ($isApi) {
    header('Content-Type: application/json');
}

// 7) Read raw input
$input = json_decode(file_get_contents('php://input'), true) ?: [];

// 8) Enforce requiredFields if declared
if (!empty($requiredFields) && is_array($requiredFields)) {
    foreach ($requiredFields as $f) {
        if (empty($input[$f])) {
            if ($isApi) {
                http_response_code(400);
                echo json_encode(['error'=>"Missing required field: {$f}"]);
            }
            ob_end_flush();
            exit;
        }
    }
}

// 9) Dispatch API call & cache
$method   = isset($method) ? $method : 'POST';
$useCache = isset($useCache) ? $useCache : false;
$cacheKey = ($useCache && $cache)
    ? "{$path}:" . md5(serialize($input))
    : null;

if ($cacheKey && $cache) {
    $cached = $cache->get($cacheKey);
    if ($cached) {
        echo $cached;
        ob_end_flush();
        exit;
    }
}

try {
    $resp = call_api($config, $method, $path, $input);
    $json = json_encode($resp, JSON_THROW_ON_ERROR);
} catch (\Throwable $e) {
    if ($isApi) {
        http_response_code(500);
        echo json_encode(['error'=>$e->getMessage()]);
    }
    ob_end_flush();
    exit;
}

// 10) Cache and output
if ($cacheKey && $cache) {
    $cache->set($cacheKey, $json, $config['CACHE_TTL'] ?? 300);
}
echo $json;

// 11) Flush buffer
ob_end_flush();
