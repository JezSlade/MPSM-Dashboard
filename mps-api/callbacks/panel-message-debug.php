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

// Create debug log entry IMMEDIATELY
$debugLogId = createDebugLog();

// Continue with normal processing
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    updateDebugLog($debugLogId, 'ERROR', 'Method Not Allowed', 405);
    respondError('Method Not Allowed', 405);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') === false) {
    updateDebugLog($debugLogId, 'ERROR', 'Invalid Content-Type', 415);
    respondError('Content-Type must be application/json', 415);
}

$rawBody = file_get_contents('php://input');
if ($rawBody === false || trim($rawBody) === '') {
    updateDebugLog($debugLogId, 'ERROR', 'Empty request body', 400);
    respondError('Empty request body');
}

$decoded = json_decode($rawBody, true);
if (!is_array($decoded)) {
    updateDebugLog($debugLogId, 'ERROR', 'Invalid JSON payload', 400, $rawBody);
    respondError('Invalid JSON payload');
}

// Lightweight shared-secret validation
$providedSecret = $decoded['callbackSecret'] ?? $decoded['secret'] ?? null;
$expectedSecret = 'mpsm-panel-message-v1';
if ($providedSecret !== $expectedSecret) {
    updateDebugLog($debugLogId, 'ERROR', 'Unauthorized - invalid secret', 401, $rawBody);
    respondError('Unauthorized', 401);
}

try {
    $pdo = getDatabase();
    ensurePanelMessageTable($pdo);

    $insertSql = sprintf(
        'INSERT INTO %s (customer_code, customer_description, device_serial, maintenance_alert_code, maintenance_alert_id, panel_configuration, payload, source_ip) VALUES (:customer_code, :customer_description, :device_serial, :maintenance_alert_code, :maintenance_alert_id, :panel_configuration, :payload, :source_ip)',
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

    updateDebugLog($debugLogId, 'SUCCESS', "Message stored with ID $messageId", 200, $rawBody);
    logPanelMessage($decoded);
} catch (Throwable $exception) {
    updateDebugLog($debugLogId, 'ERROR', 'Database error: ' . $exception->getMessage(), 500, $rawBody);
    respondError('Internal Server Error: ' . $exception->getMessage(), 500);
}

respondSuccess(['stored' => true]);

/**
 * Create initial debug log entry with all request details
 */
function createDebugLog(): int
{
    try {
        $pdo = getDatabase();
        ensureDebugLogTable($pdo);

        // Capture all headers
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('HTTP_', '', $key);
                $header = str_replace('_', '-', $header);
                $headers[$header] = $value;
            }
        }

        $sql = sprintf(
            'INSERT INTO %s (timestamp, ip_address, http_method, content_type, user_agent, headers, status)
             VALUES (:timestamp, :ip_address, :http_method, :content_type, :user_agent, :headers, :status)',
            DB_PREFIX . 'panel_callback_debug'
        );

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':timestamp' => date('Y-m-d H:i:s'),
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':http_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            ':content_type' => $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? 'not set',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'not set',
            ':headers' => json_encode($headers),
            ':status' => 'PROCESSING'
        ]);

        return (int)$pdo->lastInsertId();
    } catch (Throwable $e) {
        error_log("Failed to create debug log: " . $e->getMessage());
        return 0;
    }
}

/**
 * Update debug log entry with results
 */
function updateDebugLog(int $id, string $status, string $message, int $httpCode, string $rawBody = ''): void
{
    if ($id === 0) {
        return;
    }

    try {
        $pdo = getDatabase();

        $sql = sprintf(
            'UPDATE %s SET status = :status, message = :message, http_code = :http_code, raw_body = :raw_body WHERE id = :id',
            DB_PREFIX . 'panel_callback_debug'
        );

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':status' => $status,
            ':message' => $message,
            ':http_code' => $httpCode,
            ':raw_body' => $rawBody ? substr($rawBody, 0, 65000) : null
        ]);
    } catch (Throwable $e) {
        error_log("Failed to update debug log: " . $e->getMessage());
    }
}

/**
 * Ensure debug log table exists
 */
function ensureDebugLogTable(PDO $pdo): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    $table = DB_PREFIX . 'panel_callback_debug';
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            timestamp DATETIME NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            http_method VARCHAR(10) NOT NULL,
            content_type VARCHAR(255) NULL,
            user_agent VARCHAR(500) NULL,
            headers JSON NULL,
            raw_body TEXT NULL,
            status VARCHAR(20) NOT NULL,
            message VARCHAR(500) NULL,
            http_code INT NULL,
            INDEX idx_timestamp (timestamp),
            INDEX idx_ip_address (ip_address),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $ensured = true;
}

/**
 * Ensure storage table exists
 */
function ensurePanelMessageTable(PDO $pdo): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    $table = DB_PREFIX . 'panel_messages';
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            customer_code VARCHAR(100) NULL,
            customer_description VARCHAR(255) NULL,
            device_serial VARCHAR(150) NULL,
            maintenance_alert_code VARCHAR(150) NULL,
            maintenance_alert_id VARCHAR(150) NULL,
            panel_configuration VARCHAR(255) NULL,
            source_ip VARCHAR(45) NULL,
            payload JSON NOT NULL,
            processed TINYINT(1) DEFAULT 0,
            INDEX idx_received_at (received_at),
            INDEX idx_customer_code (customer_code),
            INDEX idx_device_serial (device_serial),
            INDEX idx_processed (processed)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $ensured = true;
}

/**
 * Derive a serial number or system identifier from possible payload locations
 */
function extractDeviceSerial(array $payload): ?string
{
    if (isset($payload['installedProduct']['serialNumber'])) {
        return (string)$payload['installedProduct']['serialNumber'];
    }
    if (isset($payload['InstalledProduct_SerialNumber'])) {
        return (string)$payload['InstalledProduct_SerialNumber'];
    }
    return null;
}

/**
 * Soft length guard for nullable string columns
 */
function truncateField($value, int $length): ?string
{
    if ($value === null) {
        return null;
    }
    $string = trim((string)$value);
    if ($string === '') {
        return null;
    }
    return mb_substr($string, 0, $length);
}

/**
 * Append a summary line to the panel message log
 */
function logPanelMessage(array $payload): void
{
    $logDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/panel-message-' . date('Y-m-d') . '.log';
    $summary = [
        'time' => date('Y-m-d H:i:s'),
        'customer' => $payload['customer']['code'] ?? $payload['Customer_Code'] ?? null,
        'serial' => extractDeviceSerial($payload),
        'alert_code' => $payload['maintenanceAlert']['code'] ?? $payload['MaintenanceAlert_Code'] ?? null,
    ];

    $line = json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

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
