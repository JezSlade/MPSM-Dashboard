<?php
class CacheManager
{
    private const CACHE_DIR = __DIR__ . '/../cache/';

    public static function get(string $key): mixed
    {
        $path = self::CACHE_DIR . $key . '.json';
        if (!file_exists($path)) return null;

        $data = json_decode(file_get_contents($path), true);
        if (!$data || time() > ($data['expires_at'] ?? 0)) {
            unlink($path);
            return null;
        }

        return $data['content'];
    }

    public static function put(string $key, $content, int $ttl = 300): void
    {
        $path = self::CACHE_DIR . $key . '.json';
        $data = [
            'expires_at' => time() + $ttl,
            'content' => $content
        ];
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }
}
