<?php
/**
 * Get Integrations summary for dealer / customer.
 */

require '../config.php';
require '../functions.php';

requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$customerCode = $_GET['customerCode'] ?? null;

try {
    $params = [];
    if ($dealerCode) {
        $params['dealerCode'] = $dealerCode;
    }
    if ($customerCode) {
        $params['customerCode'] = $customerCode;
    }

    $payload = json_encode([
        'action' => 'Integrations/GetJoinedCustomers',
        'params' => $params
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
        jsonSuccess(['integrations' => $data['data']]);
    }

    throw new Exception($data['error'] ?? 'Unknown error from mps-api');

} catch (Exception $e) {
    jsonError('Failed to fetch integrations: ' . $e->getMessage());
}

