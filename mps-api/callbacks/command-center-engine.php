<?php
declare(strict_types=1);

/**
 * Command Center Rules Engine
 *
 * Processes incoming panel messages against notification rules
 * and creates dashboard notifications when rules match
 */

if (!defined('MPS_ENGINE_ACCESS')) {
    exit('Access denied');
}

/*
CHANGELOG
2025-11-11 Codex
- Relaxed `processNotificationRules()` to accept `int|string` IDs before coercing them so PDO string IDs no longer trigger type errors.
2025-11-23 Codex
- Added deduplication for dashboard notifications to prevent repeat triggers from creating multiple active rows for the same rule/device/alert.
- Added device_identifier, device_model, alert_description template variables for human-readable notifications.
2025-11-24 Codex
- Added getAlertDisplayName() to look up alert codes from mpsm_alert_definitions table.
- Merged alert definitions lookup with device identifier and description template support.
*/

require_once __DIR__ . '/command-center-schema.php';

if (!function_exists('processNotificationRules')) {
    /**
     * Main entry point: Process panel message against all active rules
     *
     * @param PDO $pdo Database connection
     * @param int|string $messageId Panel message ID (allow strings from PDO)
     * @param array $messageData Panel message data
     */
    function processNotificationRules(PDO $pdo, int|string $messageId, array $messageData): void
    {
        try {
            $messageId = (int)$messageId;
            ensureCommandCenterTables($pdo);

            // Update alert aggregations first
            updateAlertAggregation($pdo, $messageData, $messageId);

            // Get all active notification rules
            $rules = getActiveNotificationRules($pdo);

            foreach ($rules as $rule) {
                if (ruleMatches($pdo, $rule, $messageData)) {
                    createDashboardNotification($pdo, $rule, $messageData, $messageId);
                    recordRuleMatch($pdo, $rule, $messageData, $messageId);
                }
            }

            // Clean up expired notifications
            expireOldNotifications($pdo);

        } catch (Throwable $e) {
            error_log("Command Center error: " . $e->getMessage());
        }
    }
}

/*
CHANGELOG
2025-11-25 Codex
- Added fallback alert display name lookup using docs/MPSM_Code_Descriptions.md so notifications still resolve when DB mappings are missing.
*/

if (!function_exists('updateAlertAggregation')) {
    /**
     * Update or create alert aggregation for frequency tracking
     */
    function updateAlertAggregation(PDO $pdo, array $messageData, int $messageId): void
    {
        $deviceSerial = $messageData['device_serial'] ?? null;
        $alertCode = $messageData['maintenance_alert_code'] ?? null;
        $customerCode = $messageData['customer_code'] ?? null;

        if (!$deviceSerial || !$alertCode) {
            return; // Need both for aggregation
        }

        $nyTime = getNYTimestamp();
        $table = DB_PREFIX . 'alert_aggregations';

        // Check if aggregation exists
        $sql = "SELECT * FROM {$table}
                WHERE device_serial = :device AND alert_code = :alert AND customer_code <=> :customer";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':device' => $deviceSerial,
            ':alert' => $alertCode,
            ':customer' => $customerCode
        ]);

        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing aggregation
            $newCount = $existing['occurrence_count'] + 1;

            // Calculate time window counts
            $count1h = calculateOccurrenceCount($pdo, $deviceSerial, $alertCode, $customerCode, 1);
            $count24h = calculateOccurrenceCount($pdo, $deviceSerial, $alertCode, $customerCode, 24);
            $count7d = calculateOccurrenceCount($pdo, $deviceSerial, $alertCode, $customerCode, 24 * 7);
            $count30d = calculateOccurrenceCount($pdo, $deviceSerial, $alertCode, $customerCode, 24 * 30);

            $updateSql = "UPDATE {$table}
                          SET last_occurrence_ny = :ny_time,
                              occurrence_count = :count,
                              count_1h = :count_1h,
                              count_24h = :count_24h,
                              count_7d = :count_7d,
                              count_30d = :count_30d,
                              latest_message_id = :message_id,
                              latest_payload = :payload
                          WHERE id = :id";

            $stmt = $pdo->prepare($updateSql);
            $stmt->execute([
                ':ny_time' => $nyTime,
                ':count' => $newCount,
                ':count_1h' => $count1h,
                ':count_24h' => $count24h,
                ':count_7d' => $count7d,
                ':count_30d' => $count30d,
                ':message_id' => $messageId,
                ':payload' => json_encode($messageData),
                ':id' => $existing['id']
            ]);

        } else {
            // Create new aggregation
            $insertSql = "INSERT INTO {$table}
                          (device_serial, alert_code, customer_code, first_occurrence_ny,
                           last_occurrence_ny, occurrence_count, count_1h, count_24h,
                           count_7d, count_30d, latest_message_id, latest_payload)
                          VALUES (:device, :alert, :customer, :first_ny, :last_ny, 1, 1, 1, 1, 1, :message_id, :payload)";

            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([
                ':device' => $deviceSerial,
                ':alert' => $alertCode,
                ':customer' => $customerCode,
                ':first_ny' => $nyTime,
                ':last_ny' => $nyTime,
                ':message_id' => $messageId,
                ':payload' => json_encode($messageData)
            ]);
        }
    }
}

if (!function_exists('calculateOccurrenceCount')) {
    /**
     * Calculate occurrence count within a time window
     */
    function calculateOccurrenceCount(
        PDO $pdo,
        string $deviceSerial,
        string $alertCode,
        ?string $customerCode,
        int $hours
    ): int {
        $table = DB_PREFIX . 'panel_messages';

        // Note: Can't bind $hours in INTERVAL clause, but it's already validated as int
        $sql = "SELECT COUNT(*) as count FROM {$table}
                WHERE device_serial = :device
                  AND maintenance_alert_code = :alert
                  AND customer_code <=> :customer
                  AND ny_received_at >= DATE_SUB(:now, INTERVAL {$hours} HOUR)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':device' => $deviceSerial,
            ':alert' => $alertCode,
            ':customer' => $customerCode,
            ':now' => getNYTimestamp()
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }
}

if (!function_exists('getActiveNotificationRules')) {
    /**
     * Get all enabled notification rules
     */
    function getActiveNotificationRules(PDO $pdo): array
    {
        $table = DB_PREFIX . 'notification_rules';

        $sql = "SELECT * FROM {$table} WHERE enabled = 1 ORDER BY severity DESC, id ASC";
        $stmt = $pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('ruleMatches')) {
    /**
     * Check if a rule matches the message data
     */
    function ruleMatches(PDO $pdo, array $rule, array $messageData): bool
    {
        $deviceSerial = $messageData['device_serial'] ?? '';
        $alertCode = $messageData['maintenance_alert_code'] ?? '';
        $customerCode = $messageData['customer_code'] ?? '';

        // Pattern matching (supports wildcards with %)
        if ($rule['alert_code_pattern'] && !matchesPattern($alertCode, $rule['alert_code_pattern'])) {
            return false;
        }

        if ($rule['device_serial_pattern'] && !matchesPattern($deviceSerial, $rule['device_serial_pattern'])) {
            return false;
        }

        if ($rule['customer_code_pattern'] && !matchesPattern($customerCode, $rule['customer_code_pattern'])) {
            return false;
        }

        // Frequency threshold matching
        if ($rule['frequency_count'] && $rule['frequency_window_hours']) {
            $actualCount = getFrequencyCount($pdo, $rule, $messageData);

            if ($actualCount < $rule['frequency_count']) {
                return false; // Threshold not met
            }
        }

        return true;
    }
}

if (!function_exists('matchesPattern')) {
    /**
     * Check if value matches pattern (supports SQL LIKE wildcards)
     */
    function matchesPattern(string $value, string $pattern): bool
    {
        // Convert SQL LIKE pattern to regex
        $pattern = str_replace('%', '.*', $pattern);
        $pattern = str_replace('_', '.', $pattern);
        $pattern = '/^' . $pattern . '$/i';

        return (bool)preg_match($pattern, $value);
    }
}

if (!function_exists('getFrequencyCount')) {
    /**
     * Get occurrence count based on rule's frequency type
     */
    function getFrequencyCount(PDO $pdo, array $rule, array $messageData): int
    {
        $deviceSerial = $messageData['device_serial'] ?? '';
        $alertCode = $messageData['maintenance_alert_code'] ?? '';
        $customerCode = $messageData['customer_code'] ?? '';
        $hours = (int)$rule['frequency_window_hours'];

        switch ($rule['frequency_type']) {
            case 'same_device':
                return calculateOccurrenceCount($pdo, $deviceSerial, $alertCode, $customerCode, $hours);

            case 'same_alert':
                // Count same alert across all devices
                $table = DB_PREFIX . 'panel_messages';
                $sql = "SELECT COUNT(*) as count FROM {$table}
                        WHERE maintenance_alert_code = :alert
                          AND ny_received_at >= DATE_SUB(:now, INTERVAL {$hours} HOUR)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':alert' => $alertCode,
                    ':now' => getNYTimestamp()
                ]);

                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return (int)($result['count'] ?? 0);

            case 'same_customer':
                // Count alerts for same customer
                $table = DB_PREFIX . 'panel_messages';
                $sql = "SELECT COUNT(*) as count FROM {$table}
                        WHERE customer_code = :customer
                          AND ny_received_at >= DATE_SUB(:now, INTERVAL {$hours} HOUR)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':customer' => $customerCode,
                    ':now' => getNYTimestamp()
                ]);

                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return (int)($result['count'] ?? 0);

            case 'any':
                // Count all alerts
                $table = DB_PREFIX . 'panel_messages';
                $sql = "SELECT COUNT(*) as count FROM {$table}
                        WHERE ny_received_at >= DATE_SUB(:now, INTERVAL {$hours} HOUR)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':now' => getNYTimestamp()
                ]);

                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return (int)($result['count'] ?? 0);

            default:
                return 0;
        }
    }
}

if (!function_exists('createDashboardNotification')) {
    /**
     * Create a dashboard notification from a matched rule
     */
    function createDashboardNotification(PDO $pdo, array $rule, array $messageData, int $messageId): void
    {
        $deviceSerial = $messageData['device_serial'] ?? 'Unknown Device';
        $deviceIdentifier = $messageData['device_identifier'] ?? $deviceSerial;
        $alertCode = $messageData['maintenance_alert_code'] ?? 'Unknown Alert';
        // Look up human-readable description from alert_definitions or alert_code_descriptions table
        $alertDescription = $messageData['maintenance_alert_description']
            ?? lookupAlertCodeDescription($pdo, $alertCode);
        $customerCode = $messageData['customer_code'] ?? '';

        // Extract device metadata for display (equipment ID, model, location)
        $deviceMetadata = [
            'equipment_id' => $messageData['equipment_id'] ?? $messageData['EquipmentID'] ?? $messageData['EquipmentId'] ?? null,
            'model' => $messageData['device_model'] ?? $messageData['Model'] ?? $messageData['model'] ?? null,
            'location' => $messageData['device_location'] ?? $messageData['Location'] ?? $messageData['OfficeDescription'] ?? $messageData['Department'] ?? null,
            'device_identifier' => $deviceIdentifier
        ];

        // Get frequency data
        $frequencyCount = 1;
        $timeWindow = null;

        if ($rule['frequency_count'] && $rule['frequency_window_hours']) {
            $frequencyCount = getFrequencyCount($pdo, $rule, $messageData);
            $timeWindow = (int)$rule['frequency_window_hours'];
        }

        // Default templates use meaningful data
        // Title: Short summary with device and alert description
        // Message: More detail with customer context
        $defaultTitle = '{alert}';
        $defaultMessage = '{device} - {customer}';

        // Parse notification templates (pass $pdo to resolve alert display names)
        $title = parseNotificationTemplate(
            $rule['notification_title'] ?: $defaultTitle,
            $rule,
            $messageData,
            $frequencyCount,
            $timeWindow,
            $pdo
        );

        $message = parseNotificationTemplate(
            $rule['notification_message'] ?: $defaultMessage,
            $rule,
            $messageData,
            $frequencyCount,
            $timeWindow,
            $pdo
        );

        // Calculate expiration
        $nyTime = getNYTimestamp();
        $expiresAt = null;
        if ($rule['auto_dismiss_hours']) {
            $dt = new DateTime($nyTime, new DateTimeZone('America/New_York'));
            $dt->add(new DateInterval('PT' . $rule['auto_dismiss_hours'] . 'H'));
            $expiresAt = $dt->format('Y-m-d H:i:s');
        }

        // Determine icon and color based on severity
        $iconMap = [
            'info' => 'info-circle',
            'warning' => 'exclamation-triangle',
            'high' => 'exclamation-circle',
            'critical' => 'fire'
        ];

        $colorMap = [
            'info' => 'blue',
            'warning' => 'yellow',
            'high' => 'orange',
            'critical' => 'red'
        ];

        $icon = $iconMap[$rule['severity']] ?? 'bell';
        $color = $colorMap[$rule['severity']] ?? 'gray';

        // Priority: critical=100, high=75, warning=50, info=25
        $priorityMap = ['critical' => 100, 'high' => 75, 'warning' => 50, 'info' => 25];
        $priority = $priorityMap[$rule['severity']] ?? 0;

        $table = DB_PREFIX . 'dashboard_notifications';

        // Deduplicate: if an active notification already exists for this rule/device/alert, update it instead of inserting
        $existingStmt = $pdo->prepare("
            SELECT id, trigger_count, related_message_ids
            FROM {$table}
            WHERE status = 'active'
              AND rule_id = :rule_id
              AND device_serial = :device
              AND alert_code = :alert
            LIMIT 1
        ");
        $existingStmt->execute([
            ':rule_id' => $rule['id'],
            ':device' => $deviceSerial,
            ':alert' => $alertCode
        ]);
        $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $existingIds = array_filter(array_map('trim', explode(',', (string)$existing['related_message_ids'])));
            $existingIds[] = (string)$messageId;
            $newIds = implode(',', array_slice(array_unique($existingIds), 0, 50)); // cap length defensively

            $update = $pdo->prepare("
                UPDATE {$table}
                   SET title = :title,
                       message = :message,
                       trigger_count = :count,
                       time_window_hours = :window,
                       related_message_ids = :message_ids,
                       expires_at_ny = :expires_at,
                       priority = :priority,
                       created_at_ny = created_at_ny
                 WHERE id = :id
            ");
            $update->execute([
                ':title' => substr($title, 0, 255),
                ':message' => $message,
                ':count' => max((int)$existing['trigger_count'] + 1, $frequencyCount),
                ':window' => $timeWindow,
                ':message_ids' => $newIds,
                ':expires_at' => $expiresAt,
                ':priority' => $priority,
                ':id' => $existing['id']
            ]);
        } else {
            $sql = "INSERT INTO {$table}
                    (title, message, severity, rule_id, device_serial, alert_code, customer_code,
                     trigger_count, time_window_hours, related_message_ids, created_at_ny,
                     expires_at_ny, icon, color, priority, status, metadata)
                    VALUES (:title, :message, :severity, :rule_id, :device, :alert, :customer,
                            :count, :window, :message_ids, :created_at, :expires_at, :icon, :color, :priority, 'active', :metadata)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => substr($title, 0, 255),
                ':message' => $message,
                ':severity' => $rule['severity'],
                ':rule_id' => $rule['id'],
                ':device' => $deviceSerial,
                ':alert' => $alertCode,
                ':customer' => $customerCode,
                ':count' => $frequencyCount,
                ':window' => $timeWindow,
                ':message_ids' => (string)$messageId,
                ':created_at' => $nyTime,
                ':expires_at' => $expiresAt,
                ':icon' => $icon,
                ':color' => $color,
                ':priority' => $priority,
                ':metadata' => json_encode($deviceMetadata)
            ]);
        }

        // Update rule trigger tracking (using prepared statement to prevent SQL injection)
        $ruleTable = DB_PREFIX . 'notification_rules';
        $ruleUpdateStmt = $pdo->prepare("UPDATE {$ruleTable}
                    SET last_triggered_at = :ny_time,
                        trigger_count = trigger_count + 1
                    WHERE id = :rule_id");
        $ruleUpdateStmt->execute([
            ':ny_time' => $nyTime,
            ':rule_id' => (int)$rule['id']
        ]);
    }
}

if (!function_exists('getAlertDisplayName')) {
    function lookupDocAlertName(string $alertCode): ?string
    {
        static $map = null;

        if ($map === null) {
            $map = [];
            $file = dirname(__DIR__, 2) . '/docs/MPSM_Code_Descriptions.md';
            if (is_readable($file)) {
                $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $trim = trim($line);
                    if ($trim === '' || stripos($trim, 'code') === 0) {
                        continue;
                    }
                    $parts = preg_split('/\s+/', $trim, 2);
                    if (count($parts) === 2) {
                        $map[$parts[0]] = $parts[1];
                    }
                }
            }
        }

        return $map[$alertCode] ?? null;
    }

    /**
     * Look up alert display name from alert_definitions table
     * Returns the display_name if found, otherwise returns the original alert code
     */
    function getAlertDisplayName(PDO $pdo, string $alertCode): string
    {
        static $cache = [];

        // Use cache to avoid repeated DB lookups
        if (isset($cache[$alertCode])) {
            return $cache[$alertCode];
        }

        try {
            $table = DB_PREFIX . 'alert_definitions';
            $stmt = $pdo->prepare("SELECT display_name FROM {$table} WHERE alert_code = :code AND enabled = 1 LIMIT 1");
            $stmt->execute([':code' => $alertCode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $displayName = $result['display_name'] ?? null;
            if (!$displayName) {
                $docName = lookupDocAlertName($alertCode);
                if ($docName) {
                    $displayName = $docName;
                }
            }

            $displayName = $displayName ?: $alertCode;
            $cache[$alertCode] = $displayName;

            return $displayName;
        } catch (Throwable $e) {
            error_log("Error looking up alert display name: " . $e->getMessage());
            return $alertCode;
        }
    }
}

if (!function_exists('parseNotificationTemplate')) {
    /**
     * Parse notification template with variable substitution
     * Uses alert display names from alert_definitions table
     */
    function parseNotificationTemplate(
        string $template,
        array $rule,
        array $messageData,
        int $count,
        ?int $windowHours,
        ?PDO $pdo = null
    ): string {
        $deviceSerial = $messageData['device_serial'] ?? 'Unknown Device';
        $deviceIdentifier = $messageData['device_identifier'] ?? $deviceSerial;
        $deviceModel = trim($messageData['device_model'] ?? '');
        $alertCode = $messageData['maintenance_alert_code'] ?? 'Unknown Alert';
        $alertDescription = $messageData['maintenance_alert_description'] ?? '';
        $customerCode = $messageData['customer_code'] ?? '';
        $customerDescription = $messageData['customer_description'] ?? 'Unknown Customer';

        // Look up display name for alert code if PDO connection is available
        $alertDisplayName = $alertCode;
        if ($pdo && $alertCode !== 'Unknown Alert') {
            $alertDisplayName = getAlertDisplayName($pdo, $alertCode);
        }

        // Time window formatting
        $windowText = '';
        if ($windowHours !== null) {
            if ($windowHours < 24) {
                $windowText = $windowHours . ' hour' . ($windowHours != 1 ? 's' : '');
            } else {
                $days = $windowHours / 24;
                $windowText = $days . ' day' . ($days != 1 ? 's' : '');
            }
        }

        // Use human-readable device name (identifier or model, fallback to serial)
        $deviceDisplay = $deviceIdentifier;
        if ($deviceDisplay === $deviceSerial && !empty($deviceModel)) {
            $deviceDisplay = $deviceModel;
        }

        // Use alert display name from definitions, fall back to description, then code
        $alertDisplay = $alertDisplayName;
        if ($alertDisplay === $alertCode && !empty($alertDescription)) {
            $alertDisplay = $alertDescription;
        }
        if ($alertDisplay === $alertCode) {
            $alertDisplay = "Alert {$alertCode}";
        }

        $replacements = [
            '{severity}' => ucfirst($rule['severity']),
            '{device}' => $deviceDisplay,
            '{device_serial}' => $deviceSerial,
            '{device_id}' => $deviceIdentifier,
            '{device_model}' => $deviceModel,
            '{alert}' => $alertDisplay,
            '{alert_code}' => $alertCode,
            '{alert_description}' => $alertDescription,
            '{customer}' => $customerDescription ?: $customerCode,
            '{customer_code}' => $customerCode,
            '{count}' => $count,
            '{window}' => $windowText,
            '{rule_name}' => $rule['name'] ?? 'Alert Rule'
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}

if (!function_exists('recordRuleMatch')) {
    /**
     * Record rule match in history
     */
    function recordRuleMatch(PDO $pdo, array $rule, array $messageData, int $messageId): void
    {
        $table = DB_PREFIX . 'rule_match_history';
        $nyTime = getNYTimestamp();

        $sql = "INSERT INTO {$table}
                (rule_id, panel_message_id, matched_at_ny, device_serial, alert_code,
                 customer_code, rule_name, rule_severity)
                VALUES (:rule_id, :message_id, :ny_time, :device, :alert, :customer, :rule_name, :severity)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':rule_id' => $rule['id'],
            ':message_id' => $messageId,
            ':ny_time' => $nyTime,
            ':device' => $messageData['device_serial'] ?? null,
            ':alert' => $messageData['maintenance_alert_code'] ?? null,
            ':customer' => $messageData['customer_code'] ?? null,
            ':rule_name' => $rule['name'],
            ':severity' => $rule['severity']
        ]);
    }
}

if (!function_exists('expireOldNotifications')) {
    /**
     * Mark expired notifications as expired
     */
    function expireOldNotifications(PDO $pdo): void
    {
        $table = DB_PREFIX . 'dashboard_notifications';
        $nyTime = getNYTimestamp();

        $sql = "UPDATE {$table}
                SET status = 'expired'
                WHERE status = 'active'
                  AND expires_at_ny IS NOT NULL
                  AND expires_at_ny < :ny_time";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':ny_time' => $nyTime]);
    }
}

if (!function_exists('lookupAlertCodeDescription')) {
    /**
     * Look up human-readable description for an alert code
     * Checks both alert_definitions (primary) and alert_code_descriptions (fallback)
     */
    function lookupAlertCodeDescription(PDO $pdo, string $alertCode): string
    {
        static $cache = [];

        if (isset($cache[$alertCode])) {
            return $cache[$alertCode];
        }

        // First try alert_definitions table (primary)
        try {
            $table = DB_PREFIX . 'alert_definitions';
            $stmt = $pdo->prepare("SELECT display_name, description FROM {$table} WHERE alert_code = :code AND enabled = 1");
            $stmt->execute([':code' => $alertCode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && !empty($result['display_name'])) {
                $cache[$alertCode] = $result['display_name'];
                return $result['display_name'];
            }
        } catch (PDOException $e) {
            // Table doesn't exist yet, continue to fallback
        }

        // Fallback to alert_code_descriptions table (legacy)
        try {
            $table = DB_PREFIX . 'alert_code_descriptions';
            $stmt = $pdo->prepare("SELECT description FROM {$table} WHERE alert_code = :code AND is_active = 1");
            $stmt->execute([':code' => $alertCode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $description = $result ? $result['description'] : '';
            $cache[$alertCode] = $description;

            return $description;
        } catch (PDOException $e) {
            // Table doesn't exist yet, return empty
            return '';
        }
    }
}
