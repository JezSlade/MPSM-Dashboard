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

function customer_alertsettings_get($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/AlertSettings/Get'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customer_accessories_get($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/Accessories/Get'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customer_advancedoptions_get($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/AdvancedOptions/Get'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customer_customerservicesstatus_get($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/CustomerServicesStatus/Get'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customer_explorersettings_get($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/eXplorerSettings/Get'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customer_epsonsettings_get($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/EpsonSettings/Get'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customer_epsonusbcustomerid_get($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/Customer/EpsonUSBCustomerId/Get'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customerdashboard($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerDashboard'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customerdashboard_pages($code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerDashboard/Pages'?code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customernotification_list($notificationType, $notificationMode, $language, $isActive, $code, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerNotification/List'?notificationType=' . urlencode($notificationType)&notificationMode=' . urlencode($notificationMode)&language=' . urlencode($language)&isActive=' . urlencode($isActive)&code=' . urlencode($code);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customernotification_get($id, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerNotification/Get'?id=' . urlencode($id);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customernotification_getsamplenotification($id, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerNotification/GetSampleNotification'?id=' . urlencode($id);
    return executeHttpRequest($url, [], $headers, 'GET');
}

function customernotification_getnotificationplaceholders($notificationType, $notificationMode, $baseUrl, $headers = []) {
    $url = $baseUrl . '/CustomerNotification/GetNotificationPlaceholders'?notificationType=' . urlencode($notificationType)&notificationMode=' . urlencode($notificationMode);
    return executeHttpRequest($url, [], $headers, 'GET');
}

