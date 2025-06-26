<?php

/**
 * Counter GET Helpers
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

function counter_catalog_suggestions(, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/Catalog/Suggestions';
    return executeHttpRequest($url, [], $headers, 'GET');
}

function counter_device_list($fromDate, $toDate, $id, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/Device/List'?fromDate=' . urlencode($fromDate)&toDate=' . urlencode($toDate)&id=' . urlencode($id);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function counter_device_export($exportToCsv, $fromDate, $toDate, $id, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/Device/Export'?exportToCsv=' . urlencode($exportToCsv)&fromDate=' . urlencode($fromDate)&toDate=' . urlencode($toDate)&id=' . urlencode($id);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function counter_listmaintenancekitcounters($id, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Counter/ListMaintenanceKitCounters'?id=' . urlencode($id);
    return executeHttpRequest($url, [], $headers, 'GET');
}

