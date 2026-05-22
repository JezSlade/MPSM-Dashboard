<?php
/**
 * Device Lifecycle - Delete
 * Deletes a device via mps-api proxy.
 */

require '../config.php';
require '../functions.php';

requireAuth();
ensureDeviceCrudEnabled();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    jsonError('Invalid JSON payload', 400);
}

$deviceId = trim($data['deviceId'] ?? '');
if ($deviceId === '') {
    jsonError('deviceId is required', 422);
}
$serialNumber = trim((string)($data['serialNumber'] ?? ''));
$customerCode = trim((string)($data['customerCode'] ?? ''));

$params = ['id' => $deviceId];

try {
    $response = callMPSQuery('Device/Delete', $params);

    if ($response === null) {
        logDeviceCrudAction('delete', $params, null, 'error', 'Device/Delete returned null');
        jsonError('Device deletion failed via upstream API', 502);
    }

    $cacheRowsAffected = 0;
    try {
        $pdo = getDatabase();
        $where = [];
        $cacheParams = [];

        if ($serialNumber !== '') {
            $where[] = 'serial_number = :serial_number';
            $cacheParams[':serial_number'] = $serialNumber;
        }
        if ($deviceId !== '') {
            $where[] = "JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Id')) = :device_id_1";
            $where[] = "JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IdInstalledProduct')) = :device_id_2";
            $where[] = "JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.DeviceId')) = :device_id_3";
            $cacheParams[':device_id_1'] = $deviceId;
            $cacheParams[':device_id_2'] = $deviceId;
            $cacheParams[':device_id_3'] = $deviceId;
        }

        if ($where) {
            $sql = 'UPDATE ' . DB_PREFIX . 'cache_devices
                    SET is_uninstalled = 1, cached_at = NOW()
                    WHERE (' . implode(' OR ', $where) . ')';
            if ($customerCode !== '') {
                $sql .= ' AND customer_code = :customer_code';
                $cacheParams[':customer_code'] = $customerCode;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($cacheParams);
            $cacheRowsAffected = $stmt->rowCount();
        }
    } catch (Throwable $cacheException) {
        error_log('Device delete cache update failed: ' . $cacheException->getMessage());
    }

    cacheClear('all-devices-dealer-' . DEFAULT_DEALER_CODE);
    cacheClear('duplicate-ips-analysis-*');
    logDeviceCrudAction('delete', array_merge($params, [
        'serialNumber' => $serialNumber,
        'customerCode' => $customerCode,
    ]), [
        'deleted' => true,
        'cache_rows_affected' => $cacheRowsAffected,
    ]);

    jsonSuccess([
        'deleted' => true,
        'message' => 'Device deleted successfully',
        'cache_updated' => $cacheRowsAffected > 0,
        'cache_rows_affected' => $cacheRowsAffected,
    ]);
} catch (Throwable $exception) {
    logDeviceCrudAction('delete', $params, null, 'error', $exception->getMessage());
    jsonError('Failed to delete device: ' . $exception->getMessage());
}
