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

function customer_sendexplorerinvitation($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/SendEXplorerInvitation';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function customer_getcustomers($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/GetCustomers';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function customer_getcustomer($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/GetCustomer';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function customer_getcustomerbycode($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/GetCustomerByCode';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function customer_createcustomer($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/CreateCustomer';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function customer_getemailexplorerinstallationtocustomer($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/GetEmailExplorerInstallationToCustomer';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function customer_updatecustomer($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/UpdateCustomer';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

function customer_deletecustomer($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/DeleteCustomer';
    return executeHttpRequest($url, $payload, $headers, 'POST');
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

function customernotification_create($payload, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerNotification/Create';
    return executeHttpRequest($url, $payload, $headers, 'POST');
}

