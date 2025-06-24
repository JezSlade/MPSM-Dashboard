<?php
// File: includes/auth.php
// -------------------------------------------------------------------
// Handles MPS Monitor API client credentials flow.
// Expects CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD, SCOPE, TOKEN_URL.
// Caches token to .token_cache.json and auto-refreshes when expired.
// -------------------------------------------------------------------

/**
 * @return string Valid access token
 * @throws RuntimeException on any token-fetch error
 */
function get_bearer_token(): string
{
    $cacheFile = __DIR__ . '/../.token_cache.json';
    $tokenData = null;

    // Load from cache if present
    if (file_exists($cacheFile)) {
        $raw = file_get_contents($cacheFile);
        $tokenData = json_decode($raw, true);
    }

    // If cached and not expired, return it
    if (
        isset($tokenData['access_token'], $tokenData['expires_at']) &&
        time() < $tokenData['expires_at']
    ) {
        return $tokenData['access_token'];
    }

    // Build form fields
    $fields = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'username'      => USERNAME,
        'password'      => PASSWORD,
        'scope'         => SCOPE,
    ]);

    // Request new token
    $ch = curl_init(TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_POSTFIELDS     => $fields,
    ]);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        throw new RuntimeException('Token request cURL error: ' . $err);
    }

    $data = json_decode($resp, true);
    if (
        !isset($data['access_token']) ||
        !isset($data['expires_in']) ||
        !is_numeric($data['expires_in'])
    ) {
        throw new RuntimeException('Invalid token response: ' . $resp);
    }

    // Cache with a 30-second buffer
    $expiresAt = time() + (int)$data['expires_in'] - 30;
    $saveData  = [
        'access_token' => $data['access_token'],
        'expires_at'   => $expiresAt,
    ];
    file_put_contents($cacheFile, json_encode($saveData));

    return $data['access_token'];
}
