<?php
// includes/auth.php

/**
 * Retrieves a valid bearer token, using a simple file cache.
 * Cache file stores JSON: { access_token, expires_at }
 */

function get_bearer_token(): string
{
    $cacheFile = __DIR__ . '/../.token_cache.json';
    $tokenData = null;

    // load cache if it exists
    if (file_exists($cacheFile)) {
        $raw = file_get_contents($cacheFile);
        $tokenData = json_decode($raw, true);
    }

    // if valid cache, return it
    if (
        isset($tokenData['access_token'], $tokenData['expires_at']) &&
        time() < $tokenData['expires_at']
    ) {
        return $tokenData['access_token'];
    }

    // otherwise, request a new token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $postFields = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'username'      => USERNAME,
        'password'      => PASSWORD,
        'scope'         => SCOPE,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        throw new RuntimeException('Token request failed: ' . $err);
    }
    $data = json_decode($resp, true);
    if (!isset($data['access_token'], $data['expires_in'])) {
        throw new RuntimeException('Invalid token response: ' . $resp);
    }

    // calculate expiry (buffer 30s)
    $expiresAt = time() + (int)$data['expires_in'] - 30;
    $tokenData = [
        'access_token' => $data['access_token'],
        'expires_at'   => $expiresAt,
    ];
    file_put_contents($cacheFile, json_encode($tokenData));

    return $data['access_token'];
}
