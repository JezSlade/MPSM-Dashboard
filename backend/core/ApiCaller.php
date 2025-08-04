<?php
require_once __DIR__ . '/TokenManager.php';
require_once __DIR__ . '/EnvLoader.php';

class ApiCaller
{
    public static function request(string $method, string $endpoint, array $payload = [], array $query = []): array
    {
        $env = EnvLoader::load();
        $token = TokenManager::getToken();

        $url = rtrim($env['API_BASE_URL'], '/') . '/' . ltrim($endpoint, '/');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => in_array($method, ['POST', 'PUT']) ? json_encode($payload) : null
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new RuntimeException('cURL error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }
}
