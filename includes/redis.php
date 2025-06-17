<?php
// includes/redis.php
// Simple Redis cache helper

function getRedisClient() {
    static $redis = null;
    if ($redis === null) {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
    }
    return $redis;
}

function getCache($key) {
    $r = getRedisClient();
    return $r->get($key);
}

function setCache($key, $value, $ttl = 60) {
    $r = getRedisClient();
    $r->set($key, $value, $ttl);
}

function purgeCache($pattern) {
    $r = getRedisClient();
    $keys = $r->keys($pattern);
    foreach ($keys as $key) {
        $r->del($key);
    }
}
