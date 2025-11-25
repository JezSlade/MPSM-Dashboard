<?php
/**
 * Get Customer Dashboard Data
 * Proxies request to mps-api backend
 * Following Engineering Standards: CMS = presentation, mps-api = API proxy
 */

require '../config.php';
require '../functions.php';

requireAuth();

$customerCode = $_GET['customerCode'] ?? DEFAULT_CUSTOMER_CODE;

try {
    // Call mps-api backend via /query endpoint
    $payload = json_encode([
        'action' => 'CustomerDashboard/Get',
        'params' => [
            'Code' => $customerCode
        ]
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 30,
            'ignore_errors' => true
        ]
    ]);

    $response = file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
    if ($response === false) {
        throw new Exception("Failed to contact mps-api backend");
    }

    $statusCode = 0;
    $contentType = '';
    if (isset($http_response_header)) {
        foreach ($http_response_header as $headerLine) {
            if (preg_match('#^HTTP/\\d\\.\\d\\s+(\\d{3})#', $headerLine, $m)) {
                $statusCode = (int)$m[1];
            }
            if (stripos($headerLine, 'content-type:') === 0) {
                $contentType = trim(substr($headerLine, strlen('content-type:')));
            }
        }
    }

    // Guard against HTML or non-JSON responses
    if ($contentType && stripos($contentType, 'application/json') === false) {
        $preview = substr($response, 0, 200);
        jsonError("Unexpected response format (Content-Type: {$contentType}) Preview: {$preview}", $statusCode ?: 502);
    }

    // Propagate rate limit with retry_after when present
    if ($statusCode === 429) {
        $decoded = json_decode($response, true);
        $retryAfter = isset($decoded['retry_after']) ? (int)$decoded['retry_after'] : 60;
        jsonResponse([
            'success' => false,
            'error' => $decoded['error'] ?? 'Rate limit exceeded',
            'retry_after' => $retryAfter
        ], 429);
    }

    $data = json_decode($response, true);
    if (!$data) {
        throw new Exception("Invalid response from mps-api backend");
    }

    // mps-api returns {success, data, action}
    if (isset($data['success']) && $data['success'] && isset($data['data'])) {
        jsonSuccess(['dashboard' => $data['data']]);
    } else {
        throw new Exception($data['error'] ?? 'Unknown error from mps-api');
    }

} catch (Exception $e) {
    jsonError("Failed to fetch dashboard: " . $e->getMessage());
}

/*
CHANGELOG
2025-11-28 Codex
- Added HTTP status/content-type validation and 429 retry_after propagation to prevent HTML/429 responses from breaking customer dashboard JSON parsing.
*/
