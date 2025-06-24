<?php
// includes/auth.php
// -------------------------------------------------------------------
// Handles MPS Monitor API client-credentials flow.
// Validates and sanitizes TOKEN_URL to avoid malformed-URL errors.
// -------------------------------------------------------------------

declare(strict_types=1);

/**
 * @return string Valid access token
 * @throws RuntimeException on any token-fetch or URL validation error
 */
function get_bearer_token(): string
{
    // 1) Sanitize and validate TOKEN_URL
    $rawUrl = TOKEN_URL;
    $url = trim($rawUrl, " \t\n\r\0\x0B\"'");  // strip whitespace and quotes
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new RuntimeException("Invalid TOKEN_URL: “{$rawUrl}”");
    }

    $cacheFile = __DIR__ . '/../.token_cache.json';
    $tokenData = null;

    // 2) Load from cache if present and not expired
    if (file_exists($cacheFile)) {
        $raw = file_get_contents($cacheFile);
        $tokenData = json_decode($raw, true);
    }
    if (
        isset($tokenData['access_token'], $tokenData['expires_at']) &&
        time() < $tokenData['expires_at']
    ) {
        return $tokenData['access_token'];
    }

    // 3) Build form fields
    $fields = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'username'      => USERNAME,
        'password'      => PASSWORD,
        'scope'         => SCOPE,
    ]);

    // 4) Request new token
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException("cURL init failed for TOKEN_URL: {$url}");
    }
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_POSTFIELDS     => $fields,
    ]);

    $resp = curl_exec($ch);
    if ($err = curl_error($ch)) {
        curl_close($ch);
        throw new RuntimeException('Token request cURL error: ' . $err);
    }
    curl_close($ch);

    $data = json_decode($resp, true);
    if (
        !isset($data['access_token']) ||
        !isset($data['expires_in']) ||
        !is_numeric($data['expires_in'])
    ) {
        throw new RuntimeException('Invalid token response: ' . $resp);
    }

    // 5) Cache with a 30-second buffer
    $expiresAt = time() + (int)$data['expires_in'] - 30;
    $saveData  = [
        'access_token' => $data['access_token'],
        'expires_at'   => $expiresAt,
    ];
    file_put_contents($cacheFile, json_encode($saveData));

    return $data['access_token'];
}
