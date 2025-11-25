<?php
require '../config.php';
require '../functions.php';
requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;

try {
    $params = [
        'DealerCode' => $dealerCode,
        'PageNumber' => 1,
        'PageRows' => 500,
        'SortColumn' => 'Description',
        'SortOrder' => 'Asc'
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

    $statusCode = 0;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $headerLine) {
            if (preg_match('#^HTTP/\\d\\.\\d\\s+(\\d{3})#', $headerLine, $m)) {
                $statusCode = (int)$m[1];
                break;
            }
        }
    }

    $contentType = '';
    foreach ($http_response_header ?? [] as $headerLine) {
        if (stripos($headerLine, 'content-type:') === 0) {
            $contentType = trim(substr($headerLine, strlen('content-type:')));
            break;
        }
    }

    if ($contentType && stripos($contentType, 'application/json') === false) {
        $preview = substr($response, 0, 200);
        jsonError("Unexpected response format (Content-Type: {$contentType}) Preview: {$preview}", $statusCode ?: 502);
        exit;
    }

    if ($statusCode >= 400) {
        $decodedError = json_decode($response, true);
        $retryAfter = isset($decodedError['retry_after']) ? (int)$decodedError['retry_after'] : null;
        if ($statusCode === 429) {
            jsonResponse([
                'success' => false,
                'error' => $decodedError['error'] ?? 'Rate limit exceeded',
                'retry_after' => $retryAfter ?? 60
            ], 429);
        }
        $message = $decodedError['error'] ?? $decodedError['message'] ?? "Upstream error (HTTP {$statusCode})";
        jsonError($message, $statusCode);
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

/*
CHANGELOG
2025-11-28 Codex
- Added HTTP status/content-type validation and 429 retry_after propagation for customer lookup; retains success path while returning clearer upstream errors.
*/
