<?php declare(strict_types=1);
// /includes/api_functions.php

/**
 * Parse a .env file into an associative array.
 */
if (!function_exists('parse_env_file')) {
    function parse_env_file(string $path): array {
        $env = [];
        if (!file_exists($path)) {
            return $env;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '' || str_starts_with($trim, '#') || !str_contains($trim, '=')) {
                continue;
            }
            list($key, $val) = explode('=', $trim, 2);
            $key = trim($key);
            $val = trim($val);
            // Strip quotes
            if ((str_starts_with($val, '"') && str_ends_with($val, '"'))
             || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }
            $env[$key] = $val;
        }
        return $env;
    }
}

/**
 * Fetch an OAuth token using password grant.
 */
if (!function_exists('get_token')) {
    function get_token(array $config): string {
        if (empty($config['TOKEN_URL'])) {
            throw new \Exception('TOKEN_URL not configured');
        }
        $post = [
            'client_id'     => $config['CLIENT_ID'] ?? '',
            'client_secret' => $config['CLIENT_SECRET'] ?? '',
            'username'      => $config['USERNAME'] ?? '',
            'password'      => $config['PASSWORD'] ?? '',
            'scope'         => $config['SCOPE'] ?? '',
            'grant_type'    => 'password',
        ];
        $ch = curl_init($config['TOKEN_URL']);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($post),
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $resp = curl_exec($ch);
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Token request failed: {$err}");
        }
        curl_close($ch);
        $data = json_decode($resp, true);
        if (!is_array($data) || empty($data['access_token'])) {
            throw new \Exception('Invalid token response');
        }
        return $data['access_token'];
    }
}

/**
 * Generic HTTP client for your API.
 * Now returns an ['error'=>...] array instead of throwing on JSON syntax errors.
 */
if (!function_exists('call_api')) {
    function call_api(array $config, string $method, string $path, array $body = []): array {
        if (empty($config['API_BASE_URL'])) {
            return ['error' => 'API_BASE_URL not configured'];
        }
        // Get token
        try {
            $token = get_token($config);
        } catch (\Throwable $e) {
            return ['error' => 'Auth error: ' . $e->getMessage()];
        }
        // Build URL
        $url = rtrim($config['API_BASE_URL'], '/') . '/' . ltrim($path, '/');
        $ch = curl_init($url);
        $headers = [
            "Authorization: Bearer {$token}",
            'Accept: application/json',
            'Content-Type: application/json',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['error' => "HTTP request failed: {$err}"];
        }
        curl_close($ch);

        // Decode JSON, but don’t throw on syntax errors
        $out = json_decode($resp, true);
        if ($out === null && json_last_error() !== JSON_ERROR_NONE) {
            // return raw response for debugging
            return [
                'error'      => 'Invalid JSON response: ' . json_last_error_msg(),
                'raw'        => $resp,
                'statusCode' => null
            ];
        }
        return is_array($out) ? $out : ['error' => 'Unexpected API response format'];
    }
}
