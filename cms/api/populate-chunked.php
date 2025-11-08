<?php
/**
 * CHUNKED DEVICE POPULATION
 * Populates devices in batches to avoid timeouts
 * Can be called multiple times - resumes from where it left off
 */

set_time_limit(300); // 5 minutes max
ini_set('memory_limit', '512M');

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once dirname(__DIR__) . '/functions.php';

if (php_sapi_name() !== 'cli') {
    requireAuth();
    header('Content-Type: text/plain; charset=utf-8');
}

$CHUNK_SIZE = 10; // Process 10 pages (1000 devices) per run
$startTime = microtime(true);

echo "=== CHUNKED DEVICE POPULATION ===\n";
echo "Chunk size: {$CHUNK_SIZE} pages (up to " . ($CHUNK_SIZE * 100) . " devices)\n\n";

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    // Get current state
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_devices");
    $currentCount = (int)$stmt->fetchColumn();
    echo "Current devices in DB: {$currentCount}\n";

    // Calculate starting page (rough estimate based on 100 devices per page)
    $startPage = max(1, floor($currentCount / 100) + 1);
    $endPage = $startPage + $CHUNK_SIZE - 1;

    echo "Processing pages {$startPage} to {$endPage}\n\n";

    $stats = [
        'fetched' => 0,
        'stored' => 0,
        'errors' => 0,
        'pages' => 0
    ];

    for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++) {
        echo "Page {$pageNumber}... ";
        flush();

        $params = [
            'PageNumber' => $pageNumber,
            'PageRows' => 50,
            'SortColumn' => 'Id',
            'SortOrder' => 0
        ];

        try {
            $response = callMPSMAPI('Device/List', $params);
        } catch (Exception $e) {
            echo "API error: {$e->getMessage()}\n";
            break;
        }

        if (!$response || !is_array($response)) {
            echo "Empty response\n";
            break;
        }

        $deviceCount = count($response);
        $stats['fetched'] += $deviceCount;
        $stats['pages']++;

        echo "{$deviceCount} devices... ";

        // Store devices
        foreach ($response as $device) {
            $serialNumber = $device['AssetNumber'] ?? $device['SerialNumber'] ?? null;
            $customerCode = $device['CustomerCode'] ?? null;

            if (!$serialNumber) {
                $stats['errors']++;
                continue;
            }

            try {
                $sql = "INSERT INTO {$prefix}cache_devices
                        (serial_number, device_data, customer_code, is_uninstalled, cached_at)
                        VALUES (:serial, :data, :customer, 0, NOW())
                        ON DUPLICATE KEY UPDATE
                        device_data = :data2,
                        customer_code = :customer2,
                        cached_at = NOW()";

                $stmt = $pdo->prepare($sql);
                $deviceJson = json_encode($device, JSON_UNESCAPED_UNICODE);

                $stmt->execute([
                    ':serial' => $serialNumber,
                    ':data' => $deviceJson,
                    ':customer' => $customerCode,
                    ':data2' => $deviceJson,
                    ':customer2' => $customerCode
                ]);

                $stats['stored']++;

            } catch (Exception $e) {
                $stats['errors']++;
            }
        }

        echo "stored {$stats['stored']}\n";

        // Check if this was the last page
        if ($deviceCount < 100) {
            echo "\nLast page detected (< 100 devices)\n";
            break;
        }

        usleep(50000); // 50ms delay between pages
    }

    $duration = round(microtime(true) - $startTime, 2);

    // Get final count
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}cache_devices");
    $finalCount = (int)$stmt->fetchColumn();

    echo "\n" . str_repeat('=', 60) . "\n";
    echo "CHUNK COMPLETE\n";
    echo str_repeat('=', 60) . "\n";
    echo "Pages processed: {$stats['pages']}\n";
    echo "Devices fetched: {$stats['fetched']}\n";
    echo "Devices stored: {$stats['stored']}\n";
    echo "Errors: {$stats['errors']}\n";
    echo "Duration: {$duration}s\n";
    echo "Total in DB: {$finalCount}\n";

    if ($deviceCount >= 100) {
        echo "\nMORE PAGES AVAILABLE - Run again to continue\n";
        http_response_code(206); // Partial Content
    } else {
        echo "\nPOPULATION COMPLETE\n";
        http_response_code(200);
    }

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    http_response_code(500);
}
