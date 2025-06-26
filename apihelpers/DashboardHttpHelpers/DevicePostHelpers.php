<?php

/**
 * Device POST Helpers
 */

function executeHttpRequest($url, $data = [], $headers = [], $method = 'POST') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);
    return json_decode($response, true);
}

function customerdashboard_get($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerDashboard/Get';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function customerdashboard_devices($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerDashboard/Devices';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function customerdashboard_connectors($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerDashboard/Connectors';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

