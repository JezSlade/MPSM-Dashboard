<?php

/**
 * Device GET Helpers
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

function customerdashboard($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerDashboard'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customerdashboard_pages($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerDashboard/Pages'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function sdsaction_getdeviceactionsdashboard($deviceId, $dealerId, $group, $state, $isOpen, $severity, $actionType, $isPredictive, $filterText, $baseUrl, $headers = []) {
    $url = $baseUrl . '/SdsAction/GetDeviceActionsDashboard'?deviceId=' . urlencode($deviceId)&dealerId=' . urlencode($dealerId)&group=' . urlencode($group)&state=' . urlencode($state)&isOpen=' . urlencode($isOpen)&severity=' . urlencode($severity)&actionType=' . urlencode($actionType)&isPredictive=' . urlencode($isPredictive)&filterText=' . urlencode($filterText);
    return executeHttpRequest($url, [], $headers, 'GET');
}

