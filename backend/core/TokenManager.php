<?php
require_once __DIR__ . '/EnvLoader.php';

class TokenManager
{
    private const CACHE_FILE = __DIR__ . '/../cache/token.json';

    public static function getToken(): string
    {
        $env = EnvLoader::load();
        $cached = self::readCache();

        if ($cached && isset($cached['access_token'], $cached['expires_at']) && time() < $cached['expires_at']) {
            return $cached['access_token'];
        }

        $newToken = self::requestToken($env);
        self::writeCache($newToken);
        return $newToken['access_token'];
    }

    private static function requestToken(array $env): array
    {
        $postData = http_build_query([
            'client_id' => $env['CLIENT_ID'],
            'client_secret' => $env['CLIENT_SECRET'],
            'grant_type' => 'password',
            'username' => $env['USERNAME'],
            'password' => $env['PASSWORD'],
            'scope' => $env['SCOPE']
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $env['TOKEN_URL'],
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new RuntimeException('cURL error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new RuntimeException("Token request failed: HTTP $httpCode. Response: $response");
        }

        $data = json_decode($response, true);
        if (!isset($data['access_token'], $data['expires_in'])) {
            throw new RuntimeException("Invalid token response: $response");
        }

        $data['expires_at'] = time() + (int) $data['expires_in'] - 30;
        return $data;
    }

    private static function readCache(): ?array
    {
        if (!file_exists(self::CACHE_FILE)) return null;
        return json_decode(file_get_contents(self::CACHE_FILE), true);
    }

    private static function writeCache(array $data): void
    {
        if (!is_dir(dirname(self::CACHE_FILE))) {
            mkdir(dirname(self::CACHE_FILE), 0777, true);
        }

        file_put_contents(self::CACHE_FILE, json_encode($data, JSON_PRETTY_PRINT));
    }
}
