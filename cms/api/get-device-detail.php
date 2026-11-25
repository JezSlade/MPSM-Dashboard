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
require_once 'toner-catalog.php';

requireAuth();

$deviceId = $_GET['deviceId'] ?? '';
$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$fallbackBrand = is_string($_GET['brand'] ?? null) ? trim($_GET['brand']) : '';
$fallbackModel = is_string($_GET['model'] ?? null) ? trim($_GET['model']) : '';
$fallbackBrand = substr($fallbackBrand, 0, 100);
$fallbackModel = substr($fallbackModel, 0, 200);

if (!$deviceId) {
    jsonError('deviceId is required', 400);
}

try {
    $data = queryMpsApi('Device/Get', ['Id' => $deviceId], $_SESSION['user_id'], $dealerCode);
    $device = $data['data'] ?? null;
    $product = is_array($device['Product'] ?? null) ? $device['Product'] : [];
    $brand = $device['ProductBrand'] ?? $device['Brand'] ?? $product['Brand'] ?? $fallbackBrand;
    $model = $device['ProductModel'] ?? $device['Model'] ?? $product['Model'] ?? $fallbackModel;
    $tonerLookup = resolveTonerPartNumbers((string)$brand, (string)$model);

    jsonSuccess([
        'device' => $device,
        'tonerPartNumbers' => $tonerLookup['colors'],
        'tonerCatalogMatch' => [
            'matched' => $tonerLookup['matched'],
            'brand' => $tonerLookup['brand'],
            'model' => $tonerLookup['model'],
            'matchedAliases' => $tonerLookup['matchedAliases'],
        ],
    ]);
} catch (Exception $e) {
    jsonError('Failed to fetch device: ' . $e->getMessage());
}

/*
CHANGELOG
2026-11-25 Codex
- Enriched toner-alert device details with exact OEM part-number matches.
- Added alert brand/model fallbacks while preserving the existing device payload.
*/
