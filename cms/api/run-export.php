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

function readJsonRequestBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
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

    $requestBody = json_encode([
        'action' => $action,
        'params' => $params
    ]);

    if ($requestBody === false) {
        throw new Exception('Failed to encode request payload.');
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $requestBody,
            'timeout' => 60,
        ]
    ]);

    $endpoint = 'https://mpsm.resolutionsbydesign.us/mps-api/query';
    $response = file_get_contents($endpoint, false, $context);

    if ($response === false) {
        throw new Exception('Export request failed.');
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        throw new Exception('Invalid response from export service.');
    }

    if (empty($data['success'])) {
        $message = isset($data['error']) ? (string) $data['error'] : 'Export failed.';
        throw new Exception($message);
    }

    if (!empty($data['is_file']) && isset($data['file']) && is_array($data['file'])) {
        $file = $data['file'];
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
            ]
        ]);
        return;
    }

    // Fallback: return raw data payload if available (e.g., JSON exports)
    if (array_key_exists('data', $data)) {
        jsonSuccess([
            'data' => $data['data'],
            'meta' => $data['meta'] ?? null
        ]);
        return;
    }

    throw new Exception('Export response did not include downloadable content.');
} catch (Throwable $e) {
    jsonError('Failed to execute export: ' . $e->getMessage());
}
