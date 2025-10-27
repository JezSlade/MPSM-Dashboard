<?php
/**
 * Get Devices API
 * Proxies request to mps-api backend
 * Following Engineering Standards: CMS = presentation, mps-api = API proxy
 */

require '../config.php';
require '../functions.php';

requireAuth();

$customerCode = $_GET['customerCode'] ?? DEFAULT_CUSTOMER_CODE;
$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;

try {
    // Call mps-api backend via /query endpoint
    $payload = json_encode([
        'action' => 'Device/List',
        'params' => [
            'FilterDealerId' => DEFAULT_DEALER_ID,
            'FilterCustomerCode' => $customerCode,
            'pageNumber' => 1,
            'pageRows' => 100
        ]
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 30
        ]
    ]);

    $response = file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
    if ($response === false) {
        throw new Exception("Failed to contact mps-api backend");
    }

    $data = json_decode($response, true);
    if (!$data) {
        throw new Exception("Invalid response from mps-api backend");
    }

    // mps-api returns {success, data, action}
    if (isset($data['success']) && $data['success'] && isset($data['data'])) {
        jsonSuccess(['devices' => $data['data']]);
    } else {
        throw new Exception($data['error'] ?? 'Unknown error from mps-api');
    }

} catch (Exception $e) {
    jsonError("Failed to fetch devices: " . $e->getMessage());
}
