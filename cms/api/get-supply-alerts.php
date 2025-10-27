<?php
/**
 * Get Supply Alerts API
 * Proxies request to mps-api backend for supply/toner alerts
 * Following Engineering Standards: CMS = presentation, mps-api = API proxy
 */

require '../config.php';
require '../functions.php';

requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$customerCode = $_GET['customerCode'] ?? null; // Optional - null returns all customers
$pageNumber = isset($_GET['pageNumber']) ? (int)$_GET['pageNumber'] : 1;
$pageRows = isset($_GET['pageRows']) ? (int)$_GET['pageRows'] : 50;

try {
    // Call mps-api backend via /query endpoint
    $payload = json_encode([
        'action' => 'SupplyAlert/List',
        'params' => [
            'DealerCode' => $dealerCode,
            'CustomerCode' => $customerCode,
            'SupplyType' => null, // null = all types
            'ManageOption' => null, // null = all options
            'PageNumber' => $pageNumber,
            'PageRows' => $pageRows,
            'SortColumn' => 'InitialDate',
            'SortOrder' => 1 // 1 = Descending (most recent first)
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
        jsonSuccess(['alerts' => $data['data']]);
    } else {
        throw new Exception($data['error'] ?? 'Unknown error from mps-api');
    }

} catch (Exception $e) {
    jsonError("Failed to fetch supply alerts: " . $e->getMessage());
}
