<?php
/**
 * Comprehensive Dashboard API Fleet Coverage Test Suite
 * Tests all device-listing APIs to verify they're returning 5000+ devices
 *
 * Usage: php test_apis_fleet_coverage.php
 */

require 'cms/config.php';
require 'cms/functions.php';

// Test parameters
$testSecret = 'DEALER_API_2025';
$params = [
    'secret' => $testSecret,
    'force' => '1',
    'summaryOnly' => '1'
];

// Build test URLs
$baseUrl = 'http://localhost/cms/api';
$testApis = [
    [
        'name' => 'Duplicate IPs API',
        'endpoint' => '/get-duplicate-ips.php',
        'method' => 'GET',
        'field' => 'summary.totalValidDevices',
        'note' => 'Total devices analyzed for duplicate IPs'
    ],
    [
        'name' => 'Device Age Report API',
        'endpoint' => '/device-age-report.php',
        'method' => 'GET',
        'field' => 'total_devices_processed',
        'note' => 'Devices analyzed for age metrics'
    ],
    [
        'name' => 'Dealer Summary Hybrid',
        'endpoint' => '/get-dealer-summary.php',
        'method' => 'GET',
        'field' => 'summary.totalDevices',
        'note' => 'Total devices in dealer fleet'
    ],
    [
        'name' => 'Get Devices API',
        'endpoint' => '/get-devices.php',
        'method' => 'GET',
        'field' => 'total',
        'note' => 'Total count from single page request',
        'params' => array_merge($params, ['pageRows' => 5000])
    ],
    [
        'name' => 'Device List (CRUD)',
        'endpoint' => '/device-list.php',
        'method' => 'GET',
        'field' => 'total',
        'note' => 'Device list with pagination',
        'params' => array_merge($params, ['pageRows' => 100, 'page' => 1])
    ]
];

// Run tests
echo "=================================================================\n";
echo "MPSM Dashboard API Fleet Coverage Test Suite\n";
echo "Testing 5000+ device requirement across all APIs\n";
echo "=================================================================\n\n";

$results = [];
$now = date('Y-m-d H:i:s');

foreach ($testApis as $api) {
    $apiName = $api['name'];
    $endpoint = $api['endpoint'];
    $field = $api['field'];
    $note = $api['note'];

    // Build query string
    $queryParams = $api['params'] ?? $params;
    $url = $baseUrl . $endpoint . '?' . http_build_query($queryParams);

    echo "Testing: {$apiName}\n";
    echo "URL: {$url}\n";
    echo "Field: {$field}\n";
    echo "Note: {$note}\n";

    try {
        // Make request
        $context = stream_context_create([
            'http' => [
                'method' => $api['method'] ?? 'GET',
                'timeout' => 60,
                'ignore_errors' => true
            ]
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            $error = error_get_last();
            throw new Exception('Failed to contact API: ' . ($error['message'] ?? 'Unknown error'));
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            throw new Exception('Invalid JSON response');
        }

        // Check for success
        if (!($data['success'] ?? false)) {
            $error = $data['error'] ?? 'Unknown error';
            throw new Exception("API returned error: {$error}");
        }

        // Extract device count from nested field
        $count = getNestedValue($data, $field);

        if ($count === null) {
            throw new Exception("Field '{$field}' not found in response");
        }

        $count = (int)$count;

        // Determine status
        $status = 'PASS';
        $color = 'GREEN';
        if ($count < 1000) {
            $status = 'FAIL - Limited subset';
            $color = 'RED';
        } elseif ($count < 5000) {
            $status = 'WARN - Below expected';
            $color = 'YELLOW';
        }

        // Check data source and cache info
        $source = getNestedValue($data, 'source') ?? getNestedValue($data, 'summary.source') ?? 'unknown';
        $cacheAge = getNestedValue($data, 'cache_age_seconds') ?? getNestedValue($data, 'summary.cache_age_seconds') ?? null;

        $result = [
            'api' => $apiName,
            'endpoint' => $endpoint,
            'status' => $status,
            'count' => $count,
            'expected' => '5000+',
            'source' => $source,
            'cache_age_seconds' => $cacheAge,
            'timestamp' => $now
        ];

        $results[] = $result;

        // Display result
        echo "Result: {$count} devices - {$status}\n";
        echo "Source: {$source}\n";
        if ($cacheAge !== null) {
            echo "Cache Age: {$cacheAge} seconds\n";
        }
        echo "\n";

    } catch (Exception $e) {
        $result = [
            'api' => $apiName,
            'endpoint' => $endpoint,
            'status' => 'ERROR',
            'error' => $e->getMessage(),
            'timestamp' => $now
        ];

        $results[] = $result;
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }
}

// Print summary
echo "=================================================================\n";
echo "TEST SUMMARY\n";
echo "=================================================================\n";
echo sprintf("%-40s %-10s %-10s %-20s\n", "API", "Status", "Count", "Source");
echo str_repeat("-", 80) . "\n";

foreach ($results as $result) {
    $status = $result['status'] ?? 'ERROR';
    $count = $result['count'] ?? 'N/A';
    $source = $result['source'] ?? $result['error'] ?? 'Unknown';
    echo sprintf("%-40s %-10s %-10s %-20s\n",
        substr($result['api'], 0, 38),
        $status,
        $count,
        substr($source, 0, 18)
    );
}

echo "\n";
echo "=================================================================\n";
echo "ANALYSIS\n";
echo "=================================================================\n";

$passCount = count(array_filter($results, fn($r) => strpos($r['status'] ?? '', 'PASS') === 0));
$warnCount = count(array_filter($results, fn($r) => strpos($r['status'] ?? '', 'WARN') === 0));
$failCount = count(array_filter($results, fn($r) => strpos($r['status'] ?? '', 'FAIL') === 0));
$errorCount = count(array_filter($results, fn($r) => $r['status'] === 'ERROR'));

echo "PASS:  {$passCount}\n";
echo "WARN:  {$warnCount}\n";
echo "FAIL:  {$failCount}\n";
echo "ERROR: {$errorCount}\n\n";

// Check for broken sources
$brokenSources = ['live-query', 'mps-api/query'];
$apisBySource = [];
foreach ($results as $result) {
    $source = $result['source'] ?? 'unknown';
    if (!isset($apisBySource[$source])) {
        $apisBySource[$source] = [];
    }
    $apisBySource[$source][] = $result;
}

echo "APIS BY DATA SOURCE:\n";
foreach ($apisBySource as $source => $apis) {
    $isBroken = in_array($source, $brokenSources);
    $broken = $isBroken ? ' [BROKEN ENDPOINT]' : '';
    echo "  {$source}{$broken}: " . count($apis) . " api(s)\n";
    foreach ($apis as $api) {
        $count = $api['count'] ?? 'N/A';
        echo "    - {$api['api']}: {$count} devices\n";
    }
}

// Generate JSON report
$report = [
    'timestamp' => $now,
    'test_duration' => date('Y-m-d H:i:s'),
    'total_apis_tested' => count($results),
    'results' => $results,
    'summary' => [
        'pass' => $passCount,
        'warn' => $warnCount,
        'fail' => $failCount,
        'error' => $errorCount
    ]
];

$reportJson = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents('/home/jez/projects/MPSM-Dashboard/test_apis_fleet_coverage_report.json', $reportJson);

echo "\nJSON Report saved to: test_apis_fleet_coverage_report.json\n";

/**
 * Helper: Get nested value from array using dot notation
 */
function getNestedValue($array, $path) {
    $keys = explode('.', $path);
    $value = $array;
    foreach ($keys as $key) {
        if (is_array($value) && isset($value[$key])) {
            $value = $value[$key];
        } else {
            return null;
        }
    }
    return $value;
}
?>
