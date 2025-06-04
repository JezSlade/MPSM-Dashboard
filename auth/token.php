<?php
require_once __DIR__ . '/../config/env.php';

function getToken(): string {
    $tokenFile = __DIR__ . '/token_cache.json';
    if (file_exists($tokenFile)) {
        $data = json_decode(file_get_contents($tokenFile), true);
        if (time() < $data['expires']) return $data['access_token'];
    }

    $post = [
        'grant_type' => 'password',
        'username'   => env('USERNAME'),
        'password'   => env('PASSWORD'),
        'scope'      => env('SCOPE'),
    ];

    $ch = curl_init(env('TOKEN_URL'));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($post),
        CURLOPT_USERPWD        => env('CLIENT_ID') . ':' . env('CLIENT_SECRET'),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);

    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!isset($response['access_token'])) {
        die('Error obtaining access token.');
    }

    file_put_contents($tokenFile, json_encode([
        'access_token' => $response['access_token'],
        'expires'      => time() + $response['expires_in'] - 60,
    ]));

    return $response['access_token'];
}
