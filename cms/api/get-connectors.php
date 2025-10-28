<?php
/**
 * Get Connectors Summary API
 * Retrieves connector statistics for a specific customer.
 */

require '../config.php';
require '../functions.php';

requireAuth();

$customerCode = $_GET['customerCode'] ?? DEFAULT_CUSTOMER_CODE;

try {
    $payload = json_encode([
        'action' => 'CustomerDashboard/Connectors',
        'params' => [
            'Code' => $customerCode
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
        throw new Exception('Failed to contact mps-api backend');
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        throw new Exception('Invalid response from mps-api backend');
    }

    if (!empty($data['success']) && isset($data['data'])) {
        jsonSuccess(['connectors' => $data['data']]);
    }

    throw new Exception($data['error'] ?? 'Unknown error from mps-api');

} catch (Exception $e) {
    jsonError('Failed to fetch connector data: ' . $e->getMessage());
}

