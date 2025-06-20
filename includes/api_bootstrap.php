<?php declare(strict_types=1);
// /includes/api_bootstrap.php   (patched 2025-06-20)

ob_start();

/* ──────────────────── NEW: universal error bridge ─────────────────── */
require_once __DIR__ . '/error_bootstrap.php';

/* 0) Shared helpers */
require_once __DIR__ . '/api_functions.php';

/* 1) Parse .env */
$config = parse_env_file(__DIR__ . '/../.env');

/* 2) Optional Redis cache */
try {
    require_once __DIR__ . '/redis.php';
    $cache = new RedisClient($config);
} catch (\Throwable $e) {
    $cache = null;
}

/* 3) Detect if we’re called via /api/ */
$isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0;
if ($isApi) {
    header('Content-Type: application/json');
}

/* 4) Read raw JSON input */
$input = json_decode(file_get_contents('php://input'), true) ?: [];

/* 5) Validate required fields */
if (!empty($requiredFields) && is_array($requiredFields)) {
    foreach ($requiredFields as $f) {
        if (empty($input[$f])) {
            $msg = "Missing required field: {$f}";
            if ($isApi || !defined('CARD_SANDBOX')) {
                http_response_code(400);
                echo json_encode(['error' => $msg]);
                ob_end_flush();
                exit;
            }
            throw new \Exception($msg);
        }
    }
}

/* 6) Cache key setup */
$method   = $method   ?? 'POST';
$useCache = $useCache ?? false;
$cacheKey = ($useCache && $cache)
    ? "{$path}:" . md5(serialize($input))
    : null;

/* 7) Serve from cache */
if ($cacheKey && $cache) {
    $cached = $cache->get($cacheKey);
    if ($cached) {
        echo $cached;
        ob_end_flush();
        return;
    }
}

/* 8) Call external API */
try {
    $resp = call_api($config, $method, $path, $input);
    $json = json_encode($resp, JSON_THROW_ON_ERROR);
} catch (\Throwable $e) {
    $msg = $e->getMessage();
    log_debug('API', $msg);
    if ($isApi || !defined('CARD_SANDBOX')) {
        http_response_code(500);
        echo json_encode(['error' => $msg]);
        ob_end_flush();
        exit;
    }
    throw $e;   // let sandbox catch and prettify
}

/* 9) Write cache & output */
if ($cacheKey && $cache) {
    $cache->set($cacheKey, $json, $config['CACHE_TTL'] ?? 300);
}
echo $json;
ob_end_flush();
