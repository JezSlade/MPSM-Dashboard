<?php
// includes/redis.php
// Simple Redis cache helper with extension check and failover handling

function getRedisClient() {
    static $redis = null;
    if ($redis === null) {
        if (!class_exists('Redis')) {
            throw new Exception('The Redis extension is not installed or enabled.');
        }
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
    }
    return $redis;
}

function getCache($key) {
    try {
        return getRedisClient()->get($key);
    } catch (Exception $e) {
        // Treat as cache miss on failure
        return false;
    }
}

function setCache($key, $value, $ttl = 60) {
    try {
        getRedisClient()->set($key, $value, $ttl);
    } catch (Exception $e) {
        // Ignore cache write failures
    }
}

function purgeCache($pattern) {
    try {
        $r = getRedisClient();
        $keys = $r->keys($pattern);
        foreach ($keys as $key) {
            $r->del($key);
        }
    } catch (Exception $e) {
        // Ignore purge failures
    }
}
