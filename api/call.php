<?php
require_once __DIR__ . '/../auth/token.php';

function callApi(string $endpoint, array $payload = [], string $method = 'post') {
    $url = rtrim(env('BASE_URL'), '/') . '/' . ltrim($endpoint, '/');
    $token = getToken();

    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if (strtoupper($method) !== 'GET') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $responseBody = curl_exec($ch);
    $status       = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $status, 'body' => json_decode($responseBody, true)];
}
