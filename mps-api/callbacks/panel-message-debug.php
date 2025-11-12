<?php
declare(strict_types=1);

/**
 * MPSM Panel Message Debug Callback Receiver
 *
 * Enhanced version with comprehensive logging for diagnostics.
 * Logs EVERY incoming request with full details regardless of validity.
 */

define('MPS_ENGINE_ACCESS', true);

require_once dirname(__DIR__, 1) . '/config.php';
require_once dirname(__DIR__, 2) . '/cms/config.php';
require_once dirname(__DIR__, 2) . '/cms/functions.php';
require_once __DIR__ . '/panel-message-common.php';
require_once __DIR__ . '/payload-sanitizer.php';

$debugLogId = createPanelCallbackDebugLog();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Method Not Allowed', 405, null);
    respondError('Method Not Allowed', 405);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') === false) {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Invalid Content-Type', 415, null);
    respondError('Content-Type must be application/json', 415);
}

$rawBody = file_get_contents('php://input');
if ($rawBody === false || trim($rawBody) === '') {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Empty request body', 400, $rawBody ?: null);
    respondError('Empty request body');
}

$sanitizedBody = sanitizeRawPayload($rawBody);

try {
    $decoded = json_decode($sanitizedBody, true, 512, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (\JsonException $exception) {
    $errorMsg = 'Invalid JSON: ' . $exception->getMessage();
    logInvalidJsonPayloadSample($debugLogId, $rawBody, $sanitizedBody, $exception);
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', $errorMsg, 400, $rawBody);
    respondError($errorMsg);
}

if (!is_array($decoded)) {
    $errorMsg = 'Invalid JSON payload: Expected array/object';
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', $errorMsg, 400, $rawBody);
    respondError($errorMsg);
}

// Lightweight shared-secret validation
$providedSecret = $decoded['callbackSecret'] ?? $decoded['secret'] ?? null;
$expectedSecret = 'mpsm-panel-message-v1';
if ($providedSecret !== $expectedSecret) {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Unauthorized - invalid secret', 401, $rawBody);
    respondError('Unauthorized', 401);
}

try {
    $pdo = getDatabase();
    ensurePanelMessageTable($pdo);

    $insertSql = sprintf(
        'INSERT INTO %s (customer_code, customer_description, device_serial, maintenance_alert_code, maintenance_alert_id, panel_configuration, payload, source_ip)
         VALUES (:customer_code, :customer_description, :device_serial, :maintenance_alert_code, :maintenance_alert_id, :panel_configuration, :payload, :source_ip)',
        DB_PREFIX . 'panel_messages'
    );

    $stmt = $pdo->prepare($insertSql);
    $stmt->execute([
        ':customer_code' => truncateField($decoded['customer']['code'] ?? $decoded['Customer_Code'] ?? null, 100),
        ':customer_description' => truncateField($decoded['customer']['description'] ?? $decoded['Customer_Description'] ?? null, 255),
        ':device_serial' => truncateField(extractDeviceSerial($decoded), 150),
        ':maintenance_alert_code' => truncateField($decoded['maintenanceAlert']['code'] ?? $decoded['MaintenanceAlert_Code'] ?? null, 150),
        ':maintenance_alert_id' => truncateField($decoded['maintenanceAlert']['id'] ?? $decoded['MaintenanceAlert_Id'] ?? null, 150),
        ':panel_configuration' => truncateField($decoded['maintenanceAlert']['panelConfiguration'] ?? $decoded['PanelMessageConfiguration_Description'] ?? null, 255),
        ':payload' => json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
        ':source_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $messageId = $pdo->lastInsertId();

    updatePanelCallbackDebugLog($debugLogId, 'SUCCESS', "Message stored with ID {$messageId}", 200, $rawBody);
    logPanelMessage($decoded);
} catch (Throwable $exception) {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Database error: ' . $exception->getMessage(), 500, $rawBody);
    respondError('Internal Server Error: ' . $exception->getMessage(), 500);
}

respondSuccess(['stored' => true]);

function respondSuccess(array $data = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function respondError(string $message, int $status = 400): void
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/*
CHANGELOG
2025-11-11 Codex
- Reused the shared payload sanitizer so debug callbacks behave like production and capture invalid JSON snippets for replay.
- Swapped `json_decode` for an exception-driven decoder so multiline or badly encoded payloads now return the specific error that caused the rejection.
*/
