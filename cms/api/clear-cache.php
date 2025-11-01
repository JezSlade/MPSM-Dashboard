<?php
/**
 * Clear Device Cache
 * Deletes the cached device data to force a refresh
 */

require '../config.php';
require '../functions.php';

requireAuth();

$cacheFile = __DIR__ . '/cache/device-cache.json';

try {
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
        jsonSuccess(['message' => 'Cache cleared successfully']);
    } else {
        jsonSuccess(['message' => 'No cache file found']);
    }
} catch (Exception $e) {
    jsonError("Failed to clear cache: " . $e->getMessage());
}
