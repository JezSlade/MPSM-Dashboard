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

// Increase limits for API calls that may take time
set_time_limit(45);
ini_set('max_execution_time', '45');

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
            'timeout' => 30,  // Increased from 15 to 30 seconds
            'ignore_errors' => true
        ]
    ]);

    $startTime = microtime(true);
    $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
    $responseTime = round((microtime(true) - $startTime) * 1000);

    if ($response === false) {
        $error = error_get_last();
        error_log("Search API failed for query '$query': " . ($error['message'] ?? 'Unknown error'));
        throw new Exception("Failed to contact API - timeout or network error");
    }

    // Check if response is empty
    if (empty($response)) {
        error_log("Search API returned empty response for query '$query'");
        throw new Exception("API returned empty response");
    }

    // Validate JSON before decoding
    $data = json_decode($response, true);
    $jsonError = json_last_error();

    if ($jsonError !== JSON_ERROR_NONE) {
        $preview = substr($response, 0, 200);
        error_log("Search API JSON decode error for query '$query': " . json_last_error_msg() . " | Response preview: $preview");
        throw new Exception("Invalid API response format (JSON parse error)");
    }

    if (!$data || !isset($data['success']) || !$data['success']) {
        $apiError = $data['error'] ?? 'Unknown error';
        error_log("Search API error for query '$query': $apiError");
        throw new Exception("API error: " . $apiError);
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

    // Log successful search
    error_log("Search successful for query '$query': " . count($devices) . " results in {$responseTime}ms");

    jsonSuccess([
        'devices' => $devices,
        'total' => count($devices),
        'query' => $query,
        'responseTime' => $responseTime
    ]);

} catch (Exception $e) {
    jsonError("Search failed: " . $e->getMessage());
}
