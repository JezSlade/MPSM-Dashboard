<?php
/**
 * run-export.php
 *
 * Executes a catalogued export endpoint via the internal mps-api proxy and returns
 * the downloadable payload (base64 encoded) to the browser.
 */

require '../config.php';
require '../functions.php';

requireAuth();

require_once dirname(__DIR__, 2) . '/.canonical/EndpointCatalog.php';

if (!defined('MPSM_EXPORT_ENDPOINT')) {
    define('MPSM_EXPORT_ENDPOINT', 'https://mpsm.resolutionsbydesign.us/mps-api/query');
}

function readJsonRequestBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Perform a raw export call against the mps-api proxy.
 *
 * @param string $action
 * @param array<string,mixed> $params
 * @return array<string,mixed>
 * @throws Exception
 */
function dispatchExportRequest(string $action, array $params): array
{
    $requestBody = json_encode([
        'action' => $action,
        'params' => $params
    ], JSON_UNESCAPED_SLASHES);

    if ($requestBody === false) {
        throw new Exception('Failed to encode request payload.');
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $requestBody,
            'timeout' => 90,
            'ignore_errors' => true // Capture error bodies for diagnostics
        ]
    ]);

    $response = @file_get_contents(MPSM_EXPORT_ENDPOINT, false, $context);
    $statusCode = 0;
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('#^HTTP/\\d+\\.\\d+\\s+(\\d{3})#i', $header, $matches)) {
                $statusCode = (int) $matches[1];
                break;
            }
        }
    }

    if ($response === false) {
        $error = error_get_last();
        $message = $error['message'] ?? 'Export request failed.';
        if ($statusCode >= 400) {
            $message = "HTTP {$statusCode}: {$message}";
        }
        throw new Exception($message);
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        $snippet = substr($response, 0, 2000);
        $prefix = $statusCode ? "HTTP {$statusCode}: " : '';
        throw new Exception($prefix . 'Invalid response from export service. Body: ' . $snippet);
    }

    if ($statusCode) {
        $decoded['http_status'] = $statusCode;
    }

    return $decoded;
}

/**
 * Normalize a structured export payload (Base64Content + MimeType + FileName) into a file array.
 *
 * @param array<string,mixed> $payload
 * @param string $defaultName
 * @return array<string,mixed>|null
 */
function extractBase64File(array $payload, string $defaultName): ?array
{
    $candidates = [
        'base64content', 'Base64Content', 'base64_content', 'data', 'content'
    ];
    $mimeCandidates = [
        'MimeType', 'mimeType', 'content_type', 'ContentType', 'contentType'
    ];
    $nameCandidates = [
        'FileName', 'fileName', 'name', 'filename'
    ];

    $base64 = null;
    foreach ($candidates as $key) {
        if (isset($payload[$key]) && is_string($payload[$key]) && $payload[$key] !== '') {
            $base64 = $payload[$key];
            break;
        }
    }

    if (!$base64) {
        return null;
    }

    $mime = 'application/octet-stream';
    foreach ($mimeCandidates as $key) {
        if (!empty($payload[$key]) && is_string($payload[$key])) {
            $mime = $payload[$key];
            break;
        }
    }

    $name = $defaultName;
    foreach ($nameCandidates as $key) {
        if (!empty($payload[$key]) && is_string($payload[$key])) {
            $name = $payload[$key];
            break;
        }
    }

    return [
        'name' => $name,
        'content_type' => $mime,
        'size' => isset($payload['size']) ? (int)$payload['size'] : null,
        'data' => $base64
    ];
}

/**
 * Attempt to adjust export parameters based on an error message.
 *
 * @param string $errorMessage
 * @param array<string,mixed> $params
 * @return bool true when an adjustment was applied
 */
function applyExportHeuristics(string $errorMessage, array &$params): bool
{
    $normalized = strtolower($errorMessage);

    $ensure = function (string $key, $value) use (&$params) {
        if (!array_key_exists($key, $params)) {
            $params[$key] = $value;
            return true;
        }
        return false;
    };

    if (strpos($normalized, 'pagerows') !== false || strpos($normalized, 'page rows') !== false) {
        return $ensure('pageRows', 1000);
    }

    if (strpos($normalized, 'pagenumber') !== false || strpos($normalized, 'page number') !== false) {
        return $ensure('pageNumber', 1);
    }

    if (strpos($normalized, 'pagesize') !== false) {
        return $ensure('pageSize', 1000);
    }

    if (strpos($normalized, 'rowsperpage') !== false) {
        return $ensure('rowsPerPage', 1000);
    }

    if (strpos($normalized, 'startdate') !== false || strpos($normalized, 'start date') !== false) {
        return $ensure('startDate', date('Y-m-d\T00:00:00', strtotime('-30 days')));
    }

    if (strpos($normalized, 'enddate') !== false || strpos($normalized, 'end date') !== false) {
        return $ensure('endDate', date('Y-m-d\T23:59:59'));
    }

    if (strpos($normalized, 'fromdate') !== false || strpos($normalized, 'from date') !== false) {
        return $ensure('fromDate', date('Y-m-d\T00:00:00', strtotime('-30 days')));
    }

    if (strpos($normalized, 'todate') !== false || strpos($normalized, 'to date') !== false) {
        return $ensure('toDate', date('Y-m-d\T23:59:59'));
    }

    if (strpos($normalized, 'exportformat') !== false || strpos($normalized, ' format') !== false) {
        return $ensure('exportFormat', 'Excel');
    }

    if (strpos($normalized, 'exporttocsv') !== false) {
        return $ensure('exportToCsv', false);
    }

    if (strpos($normalized, 'format') !== false) {
        return $ensure('format', 'Excel');
    }

    return false;
}

/**
 * Execute an export with heuristic retries for common validation errors.
 *
 * @param string $action
 * @param array<string,mixed> $initialParams
 * @return array<string,mixed>
 * @throws Exception
 */
function performExportWithRecovery(string $action, array $initialParams): array
{
    $attempts = 0;
    $params = $initialParams;
    $lastError = 'Export failed.';

    while ($attempts < 5) {
        $attempts++;

        $result = dispatchExportRequest($action, $params);
        if (!empty($result['success'])) {
            $result['attempts'] = $attempts;
            $result['params_used'] = $params;
            return $result;
        }

        $errorMessage = (string)($result['error'] ?? $result['message'] ?? $result['detail'] ?? '');
        if ($errorMessage === '') {
            $lastError = 'Export failed without additional details.';
            break;
        }

        $lastError = $errorMessage;
        $adjusted = applyExportHeuristics($errorMessage, $params);

        if (!$adjusted) {
            break;
        }
    }

    throw new Exception($lastError);
}

try {
    $payload = readJsonRequestBody();

    $action = isset($payload['action']) ? trim((string) $payload['action']) : '';
    if ($action === '') {
        throw new Exception('Action is required.');
    }

    $params = isset($payload['params']) && is_array($payload['params']) ? $payload['params'] : [];

    EndpointCatalog::init();
    $exportCandidates = EndpointCatalog::getAllEndpoints(false);

    $allowed = array_filter($exportCandidates, function ($entry) use ($action) {
        return isset($entry['action']) && strcasecmp($entry['action'], $action) === 0;
    });

    if (empty($allowed)) {
        throw new Exception('Requested export action is not registered in the catalog.');
    }

    $exportResponse = performExportWithRecovery($action, $params);

    // Structured payloads that return Base64Content + MimeType + FileName
    $structuredFile = extractBase64File($exportResponse, $action . '.bin');
    if (!$structuredFile && isset($exportResponse['data']) && is_array($exportResponse['data'])) {
        $structuredFile = extractBase64File($exportResponse['data'], $action . '.bin');
    }

    if ($structuredFile) {
        jsonSuccess([
            'file' => $structuredFile,
            'attempts' => $exportResponse['attempts'] ?? 1,
            'params_used' => $exportResponse['params_used'] ?? $params,
            'duration_ms' => $exportResponse['duration_ms'] ?? null,
            'catalog_hint' => $exportResponse['catalog_hint'] ?? null
        ]);
        return;
    }

    if (!empty($exportResponse['is_file']) && isset($exportResponse['file']) && is_array($exportResponse['file'])) {
        $file = $exportResponse['file'];
        $fileData = $file['data'] ?? null;
        if (!$fileData) {
            throw new Exception('Export completed but no file content was returned.');
        }

        jsonSuccess([
            'file' => [
                'name' => $file['name'] ?? ($action . '.bin'),
                'content_type' => $file['content_type'] ?? 'application/octet-stream',
                'size' => isset($file['size']) ? (int) $file['size'] : null,
                'data' => $fileData
            ],
            'attempts' => $exportResponse['attempts'] ?? 1,
            'params_used' => $exportResponse['params_used'] ?? $params,
            'duration_ms' => $exportResponse['duration_ms'] ?? null,
            'catalog_hint' => $exportResponse['catalog_hint'] ?? null
        ]);
        return;
    }

    // Fallback: return raw data payload if available (e.g., JSON exports)
    if (array_key_exists('data', $exportResponse)) {
        jsonSuccess([
            'data' => $exportResponse['data'],
            'meta' => $exportResponse['meta'] ?? null,
            'attempts' => $exportResponse['attempts'] ?? 1,
            'params_used' => $exportResponse['params_used'] ?? $params,
            'duration_ms' => $exportResponse['duration_ms'] ?? null,
            'catalog_hint' => $exportResponse['catalog_hint'] ?? null
        ]);
        return;
    }

    throw new Exception('Export response did not include downloadable content.');
} catch (Throwable $e) {
    jsonError('Failed to execute export: ' . $e->getMessage());
}

/*
CHANGELOG
2025-11-25 Codex
- Enabled export proxy to read HTTP error bodies (ignore_errors) and include status codes so export failures return actionable details instead of stream errors.
2025-11-26 Codex
- Added structured export normalization so Base64Content + MimeType payloads are delivered as downloadable files instead of JSON.
*/
