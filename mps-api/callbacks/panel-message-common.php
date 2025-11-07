<?php
declare(strict_types=1);

/**
 * Shared helper functions for panel message callbacks.
 */

if (!defined('MPS_ENGINE_ACCESS')) {
    exit('Access denied');
}

if (!function_exists('ensurePanelCallbackDebugTable')) {
    /**
     * Ensure the panel callback debug table exists and is up to date.
     */
    function ensurePanelCallbackDebugTable(PDO $pdo): void
    {
        static $ensuredDebug = false;
        if ($ensuredDebug) {
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
                unique_source VARCHAR(255) NULL,
                forwarded_for VARCHAR(255) NULL,
                headers JSON NULL,
                raw_body LONGTEXT NULL,
                status VARCHAR(20) NOT NULL,
                message VARCHAR(500) NULL,
                http_code INT NULL,
                completed_at DATETIME NULL,
                INDEX idx_timestamp (timestamp),
                INDEX idx_ip_address (ip_address),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Ensure newer columns exist when upgrading from earlier schema versions.
        $columnExists = static function (string $column) use ($pdo, $table) {
            $stmt = $pdo->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
            return $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
        };

        if (!$columnExists('unique_source')) {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN unique_source VARCHAR(255) NULL AFTER user_agent");
        }

        if (!$columnExists('forwarded_for')) {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN forwarded_for VARCHAR(255) NULL AFTER unique_source");
        }

        if (!$columnExists('completed_at')) {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN completed_at DATETIME NULL AFTER http_code");
        }

        $rawBodyColumn = $columnExists('raw_body');
        if (!$rawBodyColumn) {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN raw_body LONGTEXT NULL AFTER headers");
        } elseif (stripos((string)($rawBodyColumn['Type'] ?? ''), 'longtext') === false) {
            $pdo->exec("ALTER TABLE {$table} MODIFY COLUMN raw_body LONGTEXT NULL");
        }

        $ensuredDebug = true;
    }
}

if (!function_exists('createPanelCallbackDebugLog')) {
    /**
     * Create a debug log entry for the incoming request.
     */
    function createPanelCallbackDebugLog(): int
    {
        try {
            $pdo = getDatabase();
            ensurePanelCallbackDebugTable($pdo);

            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace('HTTP_', '', $key);
                    $header = str_replace('_', '-', $header);
                    $headers[$header] = $value;
                }
            }

            $forwardedFor = null;
            foreach (['X-FORWARDED-FOR', 'X_FORWARDED_FOR', 'FORWARDED', 'FORWARDED-FOR'] as $candidate) {
                if (isset($headers[$candidate]) && $headers[$candidate] !== '') {
                    $forwardedFor = $headers[$candidate];
                    break;
                }
            }

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'not set';

            $sourceParts = [$ipAddress];
            if ($forwardedFor) {
                $sourceParts[] = "via {$forwardedFor}";
            }
            if ($userAgent && $userAgent !== 'not set') {
                $sourceParts[] = $userAgent;
            }

            $uniqueSource = implode(' | ', array_filter($sourceParts));

            $sql = sprintf(
                'INSERT INTO %s (timestamp, ip_address, http_method, content_type, user_agent, unique_source, forwarded_for, headers, status)
                 VALUES (:timestamp, :ip_address, :http_method, :content_type, :user_agent, :unique_source, :forwarded_for, :headers, :status)',
                DB_PREFIX . 'panel_callback_debug'
            );

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':timestamp' => getNYTimestamp(), // NY local time
                ':ip_address' => $ipAddress,
                ':http_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                ':content_type' => $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? 'not set',
                ':user_agent' => $userAgent,
                ':unique_source' => $uniqueSource ?: null,
                ':forwarded_for' => $forwardedFor,
                ':headers' => json_encode($headers, JSON_UNESCAPED_UNICODE),
                ':status' => 'PROCESSING'
            ]);

            return (int)$pdo->lastInsertId();
        } catch (Throwable $e) {
            error_log('Failed to create panel callback debug log: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('updatePanelCallbackDebugLog')) {
    /**
     * Update the debug log entry with the final status.
     */
    function updatePanelCallbackDebugLog(
        int $id,
        string $status,
        string $message,
        int $httpCode,
        ?string $rawBody = null
    ): void {
        if ($id === 0) {
            return;
        }

        try {
            $pdo = getDatabase();
            ensurePanelCallbackDebugTable($pdo);

            $truncatedBody = null;
            if ($rawBody !== null) {
                $truncatedBody = mb_strcut($rawBody, 0, 65000, 'UTF-8');
            }

            $sql = sprintf(
                'UPDATE %s
                 SET status = :status,
                     message = :message,
                     http_code = :http_code,
                     raw_body = :raw_body,
                     completed_at = NOW()
                 WHERE id = :id',
                DB_PREFIX . 'panel_callback_debug'
            );

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':status' => $status,
                ':message' => $message,
                ':http_code' => $httpCode,
                ':raw_body' => $truncatedBody
            ]);
        } catch (Throwable $e) {
            error_log('Failed to update panel callback debug log: ' . $e->getMessage());
        }
    }
}

if (!function_exists('ensurePanelMessageTable')) {
    /**
     * Ensure the panel message table exists with NY timezone support.
     * MISSION CRITICAL: ny_received_at stores NY local time for sorting/filtering/display
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
                ny_received_at DATETIME NOT NULL COMMENT 'NY local time (America/New_York)',
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
                INDEX idx_ny_received_at (ny_received_at),
                INDEX idx_customer_code (customer_code),
                INDEX idx_device_serial (device_serial),
                INDEX idx_processed (processed)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Add ny_received_at column if upgrading from old schema
        $columnExists = function (string $column) use ($pdo, $table) {
            $stmt = $pdo->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
            return $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
        };

        if (!$columnExists('ny_received_at')) {
            $pdo->exec("
                ALTER TABLE {$table}
                ADD COLUMN ny_received_at DATETIME NOT NULL COMMENT 'NY local time (America/New_York)' AFTER received_at,
                ADD INDEX idx_ny_received_at (ny_received_at)
            ");

            // Backfill existing rows by converting received_at to NY time
            $pdo->exec("UPDATE {$table} SET ny_received_at = received_at WHERE ny_received_at IS NULL OR ny_received_at = '0000-00-00 00:00:00'");
        }

        $ensured = true;
    }
}

if (!function_exists('extractDeviceSerial')) {
    /**
     * Derive the device serial number from possible payload locations.
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
}

if (!function_exists('truncateField')) {
    /**
     * Soft length guard for nullable string columns.
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
}

if (!function_exists('getNYTimestamp')) {
    /**
     * Get current timestamp in New York timezone (America/New_York)
     * Returns format: YYYY-MM-DD HH:MM:SS
     *
     * MISSION CRITICAL: All timestamps MUST be in NY local time
     */
    function getNYTimestamp(): string
    {
        $nyTimezone = new DateTimeZone('America/New_York');
        $now = new DateTime('now', $nyTimezone);
        return $now->format('Y-m-d H:i:s');
    }
}

if (!function_exists('convertToNYTime')) {
    /**
     * Convert any timestamp to New York local time
     *
     * @param string|int $timestamp Unix timestamp or date string
     * @return string Formatted as YYYY-MM-DD HH:MM:SS in NY time
     */
    function convertToNYTime($timestamp): string
    {
        $nyTimezone = new DateTimeZone('America/New_York');

        if (is_numeric($timestamp)) {
            $dt = new DateTime('@' . $timestamp);
        } else {
            $dt = new DateTime($timestamp);
        }

        $dt->setTimezone($nyTimezone);
        return $dt->format('Y-m-d H:i:s');
    }
}

if (!function_exists('formatNYTimestamp')) {
    /**
     * Format NY timestamp for display
     *
     * @param string $nyTimestamp NY timestamp in Y-m-d H:i:s format
     * @param string $format Output format (default: M d, Y g:i A)
     * @return string Formatted timestamp
     */
    function formatNYTimestamp(string $nyTimestamp, string $format = 'M d, Y g:i A'): string
    {
        $nyTimezone = new DateTimeZone('America/New_York');
        $dt = new DateTime($nyTimestamp, $nyTimezone);
        return $dt->format($format);
    }
}

if (!function_exists('logPanelMessage')) {
    /**
     * Append a summary line to the panel message log file.
     * Uses New York local time for all timestamps.
     */
    function logPanelMessage(array $payload): void
    {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $nyTime = getNYTimestamp();
        $logFile = $logDir . '/panel-message-' . substr($nyTime, 0, 10) . '.log';

        $summary = [
            'time' => $nyTime,
            'timezone' => 'America/New_York',
            'customer' => $payload['customer']['code'] ?? $payload['Customer_Code'] ?? null,
            'serial' => extractDeviceSerial($payload),
            'alert_code' => $payload['maintenanceAlert']['code'] ?? $payload['MaintenanceAlert_Code'] ?? null,
        ];

        $line = json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
