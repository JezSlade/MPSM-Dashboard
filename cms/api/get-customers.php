<?php
require '../config.php';
require '../functions.php';
requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;

try {
    $params = [
        'DealerCode' => $dealerCode,
        'PageNumber' => 1,
        'PageRows' => 500
    ];

    $payload = json_encode([
        'action' => 'Customer/GetCustomers',
        'params' => $params
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 15,
            'ignore_errors' => true
        ]
    ]);

    $response = file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

    if ($response === false) {
        jsonError('Failed to connect to MPS API');
        exit;
    }

    $data = json_decode($response, true);

    if (!$data || !isset($data['success'])) {
        jsonError('Invalid API response');
        exit;
    }

    if (!$data['success']) {
        jsonError($data['error'] ?? 'API request failed');
        exit;
    }

    $customers = $data['data']['Items'] ?? $data['data'] ?? [];

    jsonSuccess([
        'customers' => $customers,
        'total' => count($customers)
    ]);

} catch (Exception $e) {
    jsonError("Failed to fetch customers: " . $e->getMessage());
}
