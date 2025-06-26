<?php

/**
 * Counter POST Helpers
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

function counter_list($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/List';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function counter_listdetailed($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/ListDetailed';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function counter_listblended($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/ListBlended';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function counter_catalog_export($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/Catalog/Export';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function counter_catalog_list($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/Catalog/List';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function counter_device_update($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/Device/Update';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function counter_device_updatecounterdetailtag($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/Device/UpdateCounterDetailTag';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function counter_uploadofflinedevicecounters($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/UploadOfflineDeviceCounters';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

