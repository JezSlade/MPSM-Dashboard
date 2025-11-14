<?php
/**
 * Count ALL Devices from API - Simple Version
 * Uses the internal /mps-api/query endpoint
 */

set_time_limit(300);

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once dirname(__DIR__) . '/functions.php';
requireAuth();

header('Content-Type: text/plain; charset=utf-8');

echo "=== COUNTING ALL DEVICES FROM API ===\n\n";

try {
    $totalDevices = 0;
    $pageNumber = 1;
    $maxPages = 500;

    echo "Querying Device/List API...\n\n";

    while ($pageNumber <= $maxPages) {
        // Call API through internal endpoint
        $payload = json_encode([
            'action' => 'Device/List',
            'params' => [
                'PageNumber' => $pageNumber,
                'PageRows' => 50,
                'SortColumn' => 'Id',
                'SortOrder' => 0
                // NO filters - get everything
            ]
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $payload,
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

        if (!$response) {
            echo "Page $pageNumber: Failed to get response\n";
            break;
        }

        $decoded = json_decode($response, true);

        if (!$decoded || !isset($decoded['success']) || !$decoded['success']) {
            echo "Page $pageNumber: API error - " . ($decoded['error'] ?? 'Unknown') . "\n";
            break;
        }

        $devices = $decoded['data']['Devices'] ?? [];
        $deviceCount = count($devices);
        $totalDevices += $deviceCount;

        if ($pageNumber <= 5 || $pageNumber % 10 == 0 || $deviceCount < 50) {
            echo "Page $pageNumber: $deviceCount devices (Total: $totalDevices)\n";
        }

        if ($deviceCount < 50) {
            echo "\nLast page at $pageNumber with $deviceCount devices\n";
            break;
        }

        $pageNumber++;
        usleep(100000); // 100ms
    }

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "TOTAL DEVICES IN API: $totalDevices\n";
    echo "Pages fetched: " . ($pageNumber - 1) . "\n";
    echo str_repeat('=', 80) . "\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
