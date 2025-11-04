<?php
/**
 * Search Devices API
 * Server-side search using FilterText parameter
 * Searches ALL fields across ALL customers for the dealer
 * Supports wildcards if API supports them
 */

require '../config.php';
require '../functions.php';

requireAuth();

$query = $_GET['query'] ?? '';

if (empty($query) || strlen($query) < 2) {
    jsonSuccess([
        'devices' => [],
        'total' => 0,
        'query' => $query,
        'message' => 'Query too short (minimum 2 characters)'
    ]);
    exit;
}

try {
    // Search using Device/List with FilterText
    // This does SERVER-SIDE search across all device fields
    $payload = json_encode([
        'action' => 'Device/List',
        'params' => [
            'FilterDealerId' => DEFAULT_DEALER_ID,
            'FilterCustomerCodes' => null,  // All customers
            'ProductBrand' => null,
            'ProductModel' => null,
            'OfficeId' => null,
            'Status' => null,  // All devices
            'FilterText' => $query,  // ← SERVER-SIDE SEARCH!
            'PageNumber' => 1,
            'PageRows' => 100,  // Return up to 100 matches
            'SortColumn' => 'Id',
            'SortOrder' => 0
        ]
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

    $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

    if ($response === false) {
        throw new Exception("Failed to contact API");
    }

    $data = json_decode($response, true);

    if (!$data || !isset($data['success']) || !$data['success']) {
        throw new Exception("API error: " . ($data['error'] ?? 'Unknown'));
    }

    $raw = $data['data'] ?? [];

    // Extract devices from wrapped response
    $devices = [];
    if (isset($raw['Items']) && is_array($raw['Items'])) {
        $devices = $raw['Items'];
    } elseif (isset($raw['Result']) && is_array($raw['Result'])) {
        $devices = $raw['Result'];
    } elseif (is_array($raw)) {
        $devices = $raw;
    }

    jsonSuccess([
        'devices' => $devices,
        'total' => count($devices),
        'query' => $query
    ]);

} catch (Exception $e) {
    jsonError("Search failed: " . $e->getMessage());
}
