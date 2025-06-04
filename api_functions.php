<?php
require_once __DIR__ . '/working_token.php';

function getCustomers($dealerCode, $pageNumber = 1, $pageRows = 10, $sortColumn = 'Code', $sortOrder = 'Asc') {
    $token = getAccessToken();
    if (!$token) {
        throw new Exception("No API token available.");
    }
    $url = 'https://api.abassetmanagement.com/api3/Customer/GetCustomers';
    $payload = [
        'DealerCode' => $dealerCode,
        'PageNumber' => $pageNumber,
        'PageRows'   => $pageRows,
        'SortColumn' => $sortColumn,
        'SortOrder'  => $sortOrder
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        throw new Exception("cURL Error in getCustomers(): $err");
    } elseif ($httpCode !== 200) {
        throw new Exception("getCustomers() HTTP $httpCode: $response");
    }
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("getCustomers() invalid JSON: $response");
    }
    return $data['Result'] ?? [];
}

function getDevices($dealerCode, $pageNumber = 1, $pageRows = 10, $sortColumn = 'SEID', $sortOrder = 'Asc') {
    $token = getAccessToken();
    if (!$token) {
        throw new Exception("No API token available.");
    }
    $url = 'https://api.abassetmanagement.com/api3/Device/List';
    $payload = [
        'DealerCode' => $dealerCode,
        'PageNumber' => $pageNumber,
        'PageRows'   => $pageRows,
        'SortColumn' => $sortColumn,
        'SortOrder'  => $sortOrder
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        throw new Exception("cURL Error in getDevices(): $err");
    } elseif ($httpCode !== 200) {
        throw new Exception("getDevices() HTTP $httpCode: $response");
    }
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("getDevices() invalid JSON: $response");
    }
    return $data['Result'] ?? [];
}

function getDeviceDetail($dealerCode, $seid) {
    $token = getAccessToken();
    if (!$token) {
        throw new Exception("No API token available.");
    }
    $url = 'https://api.abassetmanagement.com/api3/Device/GetDetailedInformations';
    $payload = [
        'DealerCode' => $dealerCode,
        'SEID'       => $seid
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        throw new Exception("cURL Error in getDeviceDetail(): $err");
    } elseif ($httpCode !== 200) {
        throw new Exception("getDeviceDetail() HTTP $httpCode: $response");
    }
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("getDeviceDetail() invalid JSON: $response");
    }
    return $data['Result'] ?? [];
}
?>