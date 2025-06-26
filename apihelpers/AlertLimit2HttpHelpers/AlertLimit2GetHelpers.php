<?php

/**
 * AlertLimit2 GET Helpers
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

function alertlimit2_getalllimits($dealerId, $customerId, $deviceId, $productId, $alertLimitSource, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/GetAllLimits'?dealerId=' . urlencode($dealerId)&customerId=' . urlencode($customerId)&deviceId=' . urlencode($deviceId)&productId=' . urlencode($productId)&alertLimitSource=' . urlencode($alertLimitSource);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function alertlimit2_dealer_getdefault($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Dealer/GetDefault'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function alertlimit2_dealer_getproductlist($code, $filterText, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Dealer/GetProductList'?code=' . urlencode($code)&filterText=' . urlencode($filterText);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function alertlimit2_dealer_getproduct($filterText, $dealerCode, $id, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Dealer/GetProduct'?filterText=' . urlencode($filterText)&dealerCode=' . urlencode($dealerCode)&id=' . urlencode($id);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function alertlimit2_customer_getdefault($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Customer/GetDefault'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function alertlimit2_customer_getproductlist($code, $filterText, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Customer/GetProductList'?code=' . urlencode($code)&filterText=' . urlencode($filterText);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function alertlimit2_customer_getproduct($filterText, $customerCode, $id, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Customer/GetProduct'?filterText=' . urlencode($filterText)&customerCode=' . urlencode($customerCode)&id=' . urlencode($id);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function alertlimit2_device_getdefault($id, $baseUrl, $headers = []) {
    $url = $baseUrl . '/AlertLimit2/Device/GetDefault'?id=' . urlencode($id);
    return executeHttpRequest($url, [], $headers, 'GET');
}

