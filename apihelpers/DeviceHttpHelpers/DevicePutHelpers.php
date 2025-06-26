<?php

/**
 * Device PUT Helpers
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

function assignOfficeToDevicesByDeviceId($baseUrl, $payload, $headers = []) {
    return executeHttpRequest($baseUrl . '/Device/AssignOfficeToDevicesByDeviceId', $payload, $headers, 'PUT');
}

function assignOfficeToDevicesBySerialNumber($baseUrl, $payload, $headers = []) {
    return executeHttpRequest($baseUrl . '/Device/AssignOfficeToDevicesBySerialNumber', $payload, $headers, 'PUT');
}

function updateDevicesBySerialNumbers($baseUrl, $payload, $headers = []) {
    return executeHttpRequest($baseUrl . '/Device/UpdateDevicesBySerialNumbers', $payload, $headers, 'PUT');
}

function restoreDeletedDevice($baseUrl, $payload, $headers = []) {
    return executeHttpRequest($baseUrl . '/Device/Deleted/Restore', $payload, $headers, 'PUT');
}

