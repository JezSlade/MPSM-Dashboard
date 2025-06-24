<?php
// includes/api_client.php
// -------------------------------------------------------------------
// Central HTTP client—no headers, with file-based JSON cache.
// -------------------------------------------------------------------

require_once __DIR__ . '/auth.php';

function api_request(string $path, array $body): array {
    // ---- File-based cache setup ----
    $cacheTTL  = 300; // seconds
    $cacheDir  = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    $keySource = $path . '|' . json_encode($body);
    $cacheFile = $cacheDir . '/' . sha1($keySource) . '.json';

    // Serve from cache if fresh
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
        $raw = @file_get_contents($cacheFile);
        $cached = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($cached['status'], $cached['data'])) {
            return $cached;
        }
        // fall through if cache corrupt
    }

    // ---- Upstream fetch with retry on 401 ----
    for ($i = 1; $i <= 2; $i++) {
        $t  = get_bearer_token();
        $ch = curl_init(API_BASE_URL . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $t,
            ],
            CURLOPT_POSTFIELDS     => json_encode($body),
        ]);

        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $st   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            throw new RuntimeException($err);
        }
        if ($st === 401 && $i === 1) {
            @unlink(__DIR__ . '/../.token_cache.json');
            continue;
        }

        $j = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON');
        }

        $result = ['status' => $st, 'data' => $j];

        // Write to cache
        @file_put_contents($cacheFile, json_encode($result));

        return $result;
    }

    throw new RuntimeException('Unknown API error');
}
