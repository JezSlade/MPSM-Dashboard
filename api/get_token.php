<?php
require_once __DIR__ . '/../includes/redis.php';

// Redis cache wrapper
$cacheKey = 'mpsm:api:get_token.php:' . md5(json_encode($_REQUEST));
if ($cached = getCache($cacheKey)) {
    echo $cached;
    exit;
}

ob_start();

echo json_encode(['status' => 'placeholder']); ?>

$output = ob_get_clean();
setCache($cacheKey, $output, 60);
echo $output;
