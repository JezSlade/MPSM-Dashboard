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

$params = ['id' => $deviceId];

try {
    $response = callMPSQuery('Device/Delete', $params);

    if ($response === null) {
        logDeviceCrudAction('delete', $params, null, 'error', 'Device/Delete returned null');
        jsonError('Device deletion failed via upstream API', 502);
    }

    cacheClear('all-devices-dealer-' . DEFAULT_DEALER_CODE);
    logDeviceCrudAction('delete', $params, ['deleted' => true]);

    jsonSuccess([
        'deleted' => true,
        'message' => 'Device deleted successfully',
    ]);
} catch (Throwable $exception) {
    logDeviceCrudAction('delete', $params, null, 'error', $exception->getMessage());
    jsonError('Failed to delete device: ' . $exception->getMessage());
}
