<?php
/**
 * Database Monitor API
 * Provides high-level insight into cache, panel message, and payload logging tables.
 */

require '../config.php';
require '../functions.php';

define('MPS_ENGINE_ACCESS', true);
require_once dirname(__DIR__, 2) . '/mps-api/callbacks/panel-message-common.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    ensurePanelCallbackDebugTable($pdo);

    $stats = [];

    // Cache devices
    $stmt = $pdo->query("SELECT COUNT(*) AS count, MIN(cached_at) AS oldest, MAX(cached_at) AS newest FROM {$prefix}cache_devices");
    $stats['cache_devices'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['count' => 0, 'oldest' => null, 'newest' => null];

    // Cache drilldown
    $stmt = $pdo->query("SELECT COUNT(*) AS count, MIN(cached_at) AS oldest, MAX(cached_at) AS newest FROM {$prefix}cache_device_drilldown");
    $stats['cache_drilldown'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['count' => 0, 'oldest' => null, 'newest' => null];

    // Panel messages
    $stmt = $pdo->query("SELECT COUNT(*) AS count, MIN(received_at) AS oldest, MAX(received_at) AS newest FROM {$prefix}panel_messages");
    $stats['panel_messages'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['count' => 0, 'oldest' => null, 'newest' => null];

    // Payload debugger
    $stmt = $pdo->query("SELECT COUNT(*) AS count, MIN(timestamp) AS oldest, MAX(timestamp) AS newest FROM {$prefix}panel_callback_debug");
    $stats['payload_debugger'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['count' => 0, 'oldest' => null, 'newest' => null];

    $deviceCount = (int) ($stats['cache_devices']['count'] ?? 0);
    $drilldownCount = (int) ($stats['cache_drilldown']['count'] ?? 0);
    $coverage = $deviceCount > 0 ? round(($drilldownCount / $deviceCount) * 100, 1) : 0;

    // Missing drill-down devices sample
    $missingCountStmt = $pdo->query("
        SELECT COUNT(*)
        FROM {$prefix}cache_devices
        WHERE serial_number NOT IN (
            SELECT serial_number FROM {$prefix}cache_device_drilldown
        )
    ");
    $missingTotal = $missingCountStmt ? (int)$missingCountStmt->fetchColumn() : 0;

    $missingStmt = $pdo->query("
        SELECT serial_number, cached_at
        FROM {$prefix}cache_devices
        WHERE serial_number NOT IN (
            SELECT serial_number FROM {$prefix}cache_device_drilldown
        )
        ORDER BY cached_at DESC
        LIMIT 20
    ");
    $missingDevices = $missingStmt ? $missingStmt->fetchAll(PDO::FETCH_ASSOC) : [];

    // Refresh lock status
    $lockFile = __DIR__ . '/cache/enhanced-refresh.lock';
    $refreshLock = [
        'active' => file_exists($lockFile),
        'age_seconds' => null,
        'created_at' => null
    ];
    if ($refreshLock['active']) {
        $mtime = filemtime($lockFile);
        if ($mtime !== false) {
            $refreshLock['age_seconds'] = time() - $mtime;
            $refreshLock['created_at'] = date('Y-m-d H:i:s', $mtime);
        }
    }

    $samples = [
        'cache_devices' => fetchSample($pdo, "
            SELECT serial_number, customer_code, is_uninstalled, cached_at
            FROM {$prefix}cache_devices
            ORDER BY cached_at DESC
            LIMIT 15
        "),
        'cache_device_drilldown' => fetchSample($pdo, "
            SELECT serial_number, has_alerts, has_supplies, cached_at
            FROM {$prefix}cache_device_drilldown
            ORDER BY cached_at DESC
            LIMIT 15
        "),
        'panel_messages' => fetchSample($pdo, "
            SELECT device_serial, customer_code, maintenance_alert_code, received_at
            FROM {$prefix}panel_messages
            ORDER BY received_at DESC
            LIMIT 15
        "),
        'panel_callback_debug' => fetchSample($pdo, "
            SELECT timestamp, status, http_code, unique_source
            FROM {$prefix}panel_callback_debug
            ORDER BY timestamp DESC
            LIMIT 15
        "),
    ];

    jsonSuccess([
        'tables' => $stats,
        'coverage' => [
            'device_count' => $deviceCount,
            'drilldown_count' => $drilldownCount,
            'drilldown_coverage_percent' => $coverage,
            'missing_total' => $missingTotal,
            'missing_sample' => $missingDevices
        ],
        'refresh_lock' => $refreshLock,
        'samples' => $samples,
        'generated_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    jsonError('Failed to retrieve database metrics: ' . $e->getMessage(), 500);
}

function fetchSample(PDO $pdo, string $query): array
{
    try {
        $stmt = $pdo->query($query);
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return array_map(static function (array $row): array {
            return array_map(static function ($value) {
                if (is_numeric($value)) {
                    return $value + 0;
                }
                return $value;
            }, $row);
        }, $rows);
    } catch (Throwable $e) {
        error_log('Database monitor sample fetch failed: ' . $e->getMessage());
        return [];
    }
}
