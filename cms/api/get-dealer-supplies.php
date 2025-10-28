<?php
/**
 * Get Dealer Supply Catalog Snapshot
 * Wraps DealerSupply/List for the configured dealer.
 */

require '../config.php';
require '../functions.php';

requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$pageNumber = isset($_GET['pageNumber']) ? (int) $_GET['pageNumber'] : 1;
$pageRows = isset($_GET['pageRows']) ? (int) $_GET['pageRows'] : 50;
$sortColumn = $_GET['sortColumn'] ?? 'Description';
$sortOrder = $_GET['sortOrder'] ?? 'Asc';
$filterText = $_GET['filterText'] ?? '';

if ($pageNumber < 1) {
    $pageNumber = 1;
}

if ($pageRows < 1) {
    $pageRows = 1;
} elseif ($pageRows > 200) {
    $pageRows = 200;
}

$sortOrder = strtoupper($sortOrder) === 'DESC' ? 'Desc' : 'Asc';

try {
    $params = [
        'code' => $dealerCode,
        'pageNumber' => $pageNumber,
        'pageRows' => $pageRows,
        'sortColumn' => $sortColumn,
        'sortOrder' => $sortOrder
    ];

    if ($filterText !== '') {
        $params['filterText'] = $filterText;
    }

    $payload = json_encode([
        'action' => 'DealerSupply/List',
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
        jsonSuccess(['supplies' => $data['data']]);
    }

    throw new Exception($data['error'] ?? 'Unknown error from mps-api');

} catch (Exception $e) {
    jsonError('Failed to fetch dealer supplies: ' . $e->getMessage());
}

