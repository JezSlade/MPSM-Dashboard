<?php
/**
 * Get Device Detail API
 * Live device drill-down for the toner alerts page. Deliberately not the
 * cache-backed get-device-deep-dive.php - that cache only covers the
 * default dealer's devices, and this needs to work for any dealer the
 * user has added a credential profile for.
 */
require '../config.php';
require '../functions.php';

requireAuth();

$deviceId = $_GET['deviceId'] ?? '';
$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;

if (!$deviceId) {
    jsonError('deviceId is required', 400);
}

try {
    $data = queryMpsApi('Device/Get', ['Id' => $deviceId], $_SESSION['user_id'], $dealerCode);
    jsonSuccess(['device' => $data['data'] ?? null]);
} catch (Exception $e) {
    jsonError('Failed to fetch device: ' . $e->getMessage());
}
