<?php declare(strict_types=1);
// /includes/api_bootstrap.php

// Start: Load shared API helpers (defines parse_env_file, call_api, etc.)
require_once __DIR__ . '/api_functions.php';

// Parse .env into $config
$config = parse_env_file(__DIR__ . '/../.env');

// Optional: initialize Redis (fail-soft)
try {
    require_once __DIR__ . '/redis.php';
    $cache = new RedisClient($config);
} catch (\Throwable $e) {
    $cache = null;
}

// Always respond JSON
header('Content-Type: application/json');

// Read raw input
$input = json_decode(file_get_contents('php://input'), true) ?: [];

// Enforce requiredFields if declared
if (!empty($requiredFields) && is_array($requiredFields)) {
    foreach ($requiredFields as $f) {
        if (empty($input[$f])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: {$f}"]);
            exit;
        }
    }
}

// Determine HTTP method and caching flag
$method   = $method   ?? 'POST';
$useCache = $useCache ?? false;

// Compute cache key if needed
$cacheKey = ($useCache && isset($cache))
    ? "{$path}:" . md5(serialize($input))
    : null;

// Attempt to serve from cache
if ($cacheKey && $cache) {
    $cached = $cache->get($cacheKey);
    if ($cached) {
        echo $cached;
        exit;
    }
}

// Perform the API call
try {
    $resp = call_api($config, $method, $path, $input);
    $json = json_encode($resp, JSON_THROW_ON_ERROR);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Save to cache if configured
if ($cacheKey && $cache) {
    $cache->set($cacheKey, $json, $config['CACHE_TTL'] ?? 300);
}

// Output the JSON
echo $json;
