<?php declare(strict_types=1);
// /includes/api_bootstrap.php

try {
    // 1. Load your .env into a config array
    $config = parse_env_file(__DIR__ . '/../.env');

    // 2. Init Redis cache (fail softly)
    try {
        require_once __DIR__ . '/redis.php';
        $cache = new RedisClient($config);
    } catch (\Throwable $redisErr) {
        $cache = null;
    }

    // 3. Pull in all of your shared functions
    require_once __DIR__ . '/api_functions.php';

    // 4. Set JSON header for all responses
    header('Content-Type: application/json');

    // 5. Grab JSON input (if any)
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    // 5.1 Enforce per-endpoint “requiredFields” if set
    if (!empty($requiredFields) && is_array($requiredFields)) {
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: {$field}"]);
                exit;
            }
        }
    }

    // 6. Dispatch to API & cache if usable
    $useCache = $useCache ?? false;
    $cacheKey = ($useCache && $cache)
        ? $path . ':' . md5(serialize($input))
        : null;

    if ($useCache && $cache && ($cached = $cache->get($cacheKey))) {
        echo $cached;
        exit;
    }

    $resp = call_api($config, $method, $path, $input);
    $json = json_encode($resp, JSON_THROW_ON_ERROR);

    if ($useCache && $cache) {
        $cache->set($cacheKey, $json, $config['CACHE_TTL'] ?? 300);
    }

    echo $json;

} catch (\Throwable $e) {
    // Always return JSON, never a blank 500
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
