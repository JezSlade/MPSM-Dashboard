<?php declare(strict_types=1);
// /includes/api_functions.php

/**
 * Parse a .env file into an associative array.
 */
function parse_env_file(string $path): array {
    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env   = [];
    if ($lines) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            list($key, $val) = explode('=', $line, 2);
            $env[trim($key)] = trim($val);
        }
    }
    return $env;
}

/**
 * Obtain an OAuth bearer token, with simple file-based caching.
 */
function get_oauth_token(array $config): string {
    $cacheFile = __DIR__ . '/../logs/token_cache.json';
    $now       = time();

    if (is_readable($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if (!empty($data['token']) && !empty($data['expires_at']) && $data['expires_at'] > $now + 30) {
            return $data['token'];
        }
    }

    $post = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => $config['CLIENT_ID'],
        'client_secret' => $config['CLIENT_SECRET'],
        'username'      => $config['USERNAME'],
        'password'      => $config['PASSWORD'],
        'scope'         => $config['SCOPE'],
    ]);

    $ch = curl_init($config['TOKEN_URL']);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        throw new Exception('Token request failed: ' . curl_error($ch));
    }
    curl_close($ch);

    $obj = json_decode($resp, true);
    if (empty($obj['access_token']) || empty($obj['expires_in'])) {
        throw new Exception('Invalid token response: ' . $resp);
    }

    $token     = $obj['access_token'];
    $expiresAt = $now + (int)$obj['expires_in'];

    @file_put_contents($cacheFile, json_encode([
        'token'      => $token,
        'expires_at' => $expiresAt,
    ]));

    return $token;
}

/**
 * Perform an API request.
 */
function call_api(array $config, string $method, string $endpoint, array $payload = []): array {
    if (empty($config['API_BASE_URL'])) {
        throw new Exception('API_BASE_URL not set in .env');
    }
    $url = rtrim($config['API_BASE_URL'], '/') . '/' . ltrim($endpoint, '/');

    $token = get_oauth_token($config);

    $ch = curl_init();
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
        'Content-Type: application/json',
    ];

    $opts = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
    ];

    if (strtoupper($method) === 'GET' && !empty($payload)) {
        $url = $opts[CURLOPT_URL] . '?' . http_build_query($payload);
        $opts[CURLOPT_URL] = $url;
    } else {
        $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
        if (!empty($payload)) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
        }
    }

    curl_setopt_array($ch, $opts);
    $resp = curl_exec($ch);
    if ($resp === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL error fetching {$endpoint}: {$err}");
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON response ({$httpCode}): {$resp}");
    }
    if ($httpCode < 200 || $httpCode >= 300) {
        $msg = $data['error_description'] ?? ($data['message'] ?? 'Unknown API error');
        throw new Exception("API error ({$httpCode}): {$msg}");
    }

    return $data;
}
