<?php
/**
 * Direct PHP API Test Suite - Tests APIs by including them directly
 * Bypasses HTTP layer for direct function testing
 */

require 'cms/config.php';
require 'cms/functions.php';

echo "=================================================================\n";
echo "MPSM Dashboard API Fleet Coverage Test Suite (Direct PHP)\n";
echo "Testing 5000+ device requirement across all APIs\n";
echo "=================================================================\n\n";

$results = [];
$now = date('Y-m-d H:i:s');

// Simulate request parameters
$_GET['secret'] = 'DEALER_API_2025';
$_GET['force'] = '1';
$_GET['summaryOnly'] = '1';

// Test 1: Duplicate IPs API
echo "Test 1: Duplicate IPs API\n";
echo "Expected: 5000+ total devices\n";

try {
    $file = 'cms/api/get-duplicate-ips.php';

    // Capture output
    ob_start();
    $_GET['secret'] = 'DEALER_API_2025';
    $_GET['force'] = '1';
    $_GET['summaryOnly'] = '1';

    // Include and capture
    include $file;
    $output = ob_get_clean();

    $data = json_decode($output, true);

    if ($data && isset($data['success']) && $data['success']) {
        $count = $data['summary']['totalValidDevices'] ?? 0;
        $source = $data['summary']['source'] ?? 'unknown';
        $status = $count >= 5000 ? 'PASS' : ($count > 1000 ? 'WARN' : 'FAIL');

        echo "Result: {$count} devices - {$status}\n";
        echo "Source: {$source}\n";
        echo "Cache age: " . ($data['summary']['cache_age_seconds'] ?? 'N/A') . " seconds\n";

        $results[] = [
            'api' => 'Duplicate IPs API',
            'status' => $status,
            'count' => $count,
            'source' => $source
        ];
    } else {
        echo "ERROR: " . ($data['error'] ?? 'Unknown error') . "\n";
        $results[] = [
            'api' => 'Duplicate IPs API',
            'status' => 'ERROR',
            'error' => $data['error'] ?? 'Invalid response'
        ];
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $results[] = [
        'api' => 'Duplicate IPs API',
        'status' => 'ERROR',
        'error' => $e->getMessage()
    ];
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $results[] = [
        'api' => 'Duplicate IPs API',
        'status' => 'ERROR',
        'error' => $e->getMessage()
    ];
}

echo "\n";

// Test 2: Device Age Report API
echo "Test 2: Device Age Report API\n";
echo "Expected: 5000+ total devices\n";

try {
    ob_start();
    $_GET['secret'] = 'DEALER_API_2025';
    $_GET['force'] = '1';
    $_GET['summaryOnly'] = '1';

    // Suppress headers
    if (!function_exists('header')) {
        function header($h) { }
    }

    include 'cms/api/device-age-report.php';
    $output = ob_get_clean();

    $data = json_decode($output, true);

    if ($data && isset($data['success']) && $data['success']) {
        $count = $data['total_devices_processed'] ?? 0;
        $source = $data['source'] ?? 'unknown';
        $status = $count >= 5000 ? 'PASS' : ($count > 1000 ? 'WARN' : 'FAIL');

        echo "Result: {$count} devices - {$status}\n";
        echo "Source: {$source}\n";
        echo "Cache age: " . ($data['cache_age_seconds'] ?? 'N/A') . " seconds\n";

        $results[] = [
            'api' => 'Device Age Report API',
            'status' => $status,
            'count' => $count,
            'source' => $source
        ];
    } else {
        echo "ERROR: " . ($data['error'] ?? 'Unknown error') . "\n";
        $results[] = [
            'api' => 'Device Age Report API',
            'status' => 'ERROR',
            'error' => $data['error'] ?? 'Invalid response'
        ];
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $results[] = [
        'api' => 'Device Age Report API',
        'status' => 'ERROR',
        'error' => $e->getMessage()
    ];
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $results[] = [
        'api' => 'Device Age Report API',
        'status' => 'ERROR',
        'error' => $e->getMessage()
    ];
}

echo "\n";

// Test 3: Dealer Summary API
echo "Test 3: Dealer Summary Hybrid API\n";
echo "Expected: 5000+ total devices\n";

try {
    ob_start();
    $_GET['secret'] = 'DEALER_API_2025';
    $_GET['force'] = '1';
    $_GET['summaryOnly'] = '1';

    include 'cms/api/get-dealer-summary.php';
    $output = ob_get_clean();

    $data = json_decode($output, true);

    if ($data && isset($data['success']) && $data['success']) {
        $count = $data['summary']['totalDevices'] ?? 0;
        $source = $data['summary']['_dataSource'] ?? 'unknown';
        $status = $count >= 5000 ? 'PASS' : ($count > 1000 ? 'WARN' : 'FAIL');

        echo "Result: {$count} devices - {$status}\n";
        echo "Source: {$source}\n";
        echo "Cached: " . ($data['cached'] ? 'yes' : 'no') . "\n";

        $results[] = [
            'api' => 'Dealer Summary API',
            'status' => $status,
            'count' => $count,
            'source' => $source
        ];
    } else {
        echo "ERROR: " . ($data['error'] ?? 'Unknown error') . "\n";
        $results[] = [
            'api' => 'Dealer Summary API',
            'status' => 'ERROR',
            'error' => $data['error'] ?? 'Invalid response'
        ];
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $results[] = [
        'api' => 'Dealer Summary API',
        'status' => 'ERROR',
        'error' => $e->getMessage()
    ];
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $results[] = [
        'api' => 'Dealer Summary API',
        'status' => 'ERROR',
        'error' => $e->getMessage()
    ];
}

echo "\n";

// Summary
echo "=================================================================\n";
echo "SUMMARY\n";
echo "=================================================================\n";
echo sprintf("%-35s %-10s %-10s\n", "API", "Status", "Count");
echo str_repeat("-", 55) . "\n";

foreach ($results as $result) {
    $status = $result['status'] ?? 'ERROR';
    $count = $result['count'] ?? 'N/A';
    echo sprintf("%-35s %-10s %-10s\n", $result['api'], $status, $count);
}

echo "\n";

// Save report
file_put_contents('/home/jez/projects/MPSM-Dashboard/test_apis_direct_report.json', json_encode([
    'timestamp' => $now,
    'results' => $results
], JSON_PRETTY_PRINT));

echo "Report saved to: test_apis_direct_report.json\n";
?>
