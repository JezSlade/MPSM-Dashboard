<?php

/**
 * AlertLimit2 POST Helpers
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

function alertlimit2_disablealertlimits($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/DisableAlertLimits';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function alertlimit2_dealer_createdefault($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Dealer/CreateDefault';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function alertlimit2_dealer_createproduct($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Dealer/CreateProduct';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function alertlimit2_customer_createdefault($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Customer/CreateDefault';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function alertlimit2_customer_createproduct($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Customer/CreateProduct';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function alertlimit2_device_createdefault($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Device/CreateDefault';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

