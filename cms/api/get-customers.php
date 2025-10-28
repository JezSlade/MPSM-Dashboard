<?php
/**
 * Get Customers API
 * Proxies Customer/GetCustomers through the mps-api backend.
 */

require '../config.php';
require '../functions.php';

requireAuth();

$userId = $_SESSION['user_id'];
$preferences = getUserPreferences($userId);

$dealerCode = $preferences['dealerCode'] ?? DEFAULT_DEALER_CODE;

$pageNumber = isset($_GET['pageNumber']) ? (int) $_GET['pageNumber'] : 1;
$pageRows = isset($_GET['pageRows']) ? (int) $_GET['pageRows'] : 100;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($pageNumber < 1) {
    $pageNumber = 1;
}

if ($pageRows < 10) {
    $pageRows = 10;
} elseif ($pageRows > 200) {
    $pageRows = 200;
}

try {
    $params = [
        'DealerCode' => $dealerCode,
        'PageNumber' => $pageNumber,
        'PageRows' => $pageRows,
        'SortColumn' => 'Description',
        'SortOrder' => 'Asc',
    ];

    if ($search !== '') {
        $params['FilterText'] = $search;
    }

    $payload = json_encode([
        'action' => 'Customer/GetCustomers',
        'params' => $params,
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 30,
        ],
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
        $customers = is_array($data['data']) ? $data['data'] : [];
        jsonSuccess(['customers' => $customers]);
    }

    throw new Exception($data['error'] ?? 'Unknown error from mps-api');

} catch (Exception $e) {
    jsonError('Failed to fetch customers: ' . $e->getMessage());
}

