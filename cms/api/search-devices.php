<?php
/**
 * Search Devices API
 * Fast-paths through the MySQL cache (mpsm_cache_devices) and only falls back to the upstream API if nothing is found.
 * Uses indexed fields (serial_number, customer_code) plus key JSON attributes for partial matching.
 */

require '../config.php';
require '../functions.php';

requireAuth();

set_time_limit(30);
ini_set('max_execution_time', '30');

$query = $_GET['query'] ?? '';

if (empty($query) || strlen($query) < 2) {
    jsonSuccess([
        'devices' => [],
        'total' => 0,
        'query' => $query,
        'message' => 'Query too short (minimum 2 characters)'
    ]);
    exit;
}

function searchCache(PDO $pdo, string $query, int $limit = 25): array
{
    $prefix = DB_PREFIX;
    $like = '%' . $query . '%';

    $sql = "
        SELECT
            device_data,
            is_uninstalled
        FROM {$prefix}cache_devices
        WHERE serial_number LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.EquipmentId')) LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.EquipmentID')) LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.DeviceEquipmentId')) LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.DeviceEquipmentID')) LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.AssetNumber')) LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.ExternalIdentifier')) LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Product.Model')) LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.IpAddress')) LIKE :like
        LIMIT :limit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':like', $like, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $devices = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $decoded = json_decode($row['device_data'] ?? '', true);
        if (is_array($decoded)) {
            // Ensure uninstalled flag is present for UI badges
            if (!isset($decoded['IsUninstalled'])) {
                $decoded['IsUninstalled'] = (bool)($row['is_uninstalled'] ?? false);
            }
            $devices[] = $decoded;
        }
    }

    return $devices;
}

function searchUpstream(string $query): array
{
    $payload = json_encode([
        'action' => 'Device/List',
        'params' => [
            'FilterDealerId' => DEFAULT_DEALER_ID,
            'FilterCustomerCodes' => null,
            'ProductBrand' => null,
            'ProductModel' => null,
            'OfficeId' => null,
            'Status' => null,
            'FilterText' => $query,
            'PageNumber' => 1,
            'PageRows' => 100,
            'SortColumn' => 'Id',
            'SortOrder' => 0
        ]
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 20,
            'ignore_errors' => true
        ]
    ]);

    $startTime = microtime(true);
    $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
    $responseTime = round((microtime(true) - $startTime) * 1000);

    if ($response === false || empty($response)) {
        $error = error_get_last();
        throw new Exception("Failed to contact API - " . ($error['message'] ?? 'timeout or network error'));
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $preview = substr($response, 0, 200);
        throw new Exception("Invalid API response format (JSON parse error) | Preview: $preview");
    }

    if (!$data || !isset($data['success']) || !$data['success']) {
        $apiError = $data['error'] ?? 'Unknown error';
        throw new Exception("API error: " . $apiError);
    }

    $raw = $data['data'] ?? [];
    if (isset($raw['Items']) && is_array($raw['Items'])) {
        $devices = $raw['Items'];
    } elseif (isset($raw['Result']) && is_array($raw['Result'])) {
        $devices = $raw['Result'];
    } elseif (is_array($raw)) {
        $devices = $raw;
    } else {
        $devices = [];
    }

    return [
        'devices' => $devices,
        'responseTime' => $responseTime
    ];
}

try {
    $pdo = getDatabase();
    $started = microtime(true);
    $source = 'cache';
    $devices = [];
    $cacheError = null;

    try {
        $devices = searchCache($pdo, $query);
    } catch (Throwable $cacheEx) {
        $cacheError = $cacheEx;
        error_log("[search-devices] Cache search failed: " . $cacheEx->getMessage());
    }

    $responseTime = round((microtime(true) - $started) * 1000);

    // Fallback to upstream API if cache misses or cache unavailable
    if (count($devices) === 0) {
        $upstream = searchUpstream($query);
        $devices = $upstream['devices'] ?? [];
        $responseTime = $upstream['responseTime'] ?? $responseTime;
        $source = $cacheError ? 'api_fallback_no_cache' : 'api';
    }

    jsonSuccess([
        'devices' => $devices,
        'total' => count($devices),
        'query' => $query,
        'responseTime' => $responseTime,
        'source' => $source
    ]);

} catch (Exception $e) {
    jsonError("Search failed: " . $e->getMessage());
}

/*
CHANGELOG
2025-11-28 Codex
- Reworked search to read from the local cache (mpsm_cache_devices) with JSON field matching and only fall back to the upstream API on cache misses to eliminate multi-minute waits.
*/
