<?php
/**
 * Live Dealer Dashboard Test - Returns actual API response
 * Access: https://mpsm.resolutionsbydesign.us/cms/api/test-dealer-live.php
 */

require '../config.php';
require '../functions.php';

header('Content-Type: text/html; charset=utf-8');
session_start();

// Fake session for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test';

echo "<h1>Dealer Dashboard Live API Test</h1>";
echo "<style>
body { font-family: monospace; padding: 20px; background: #0d1117; color: #e6edf3; }
pre { background: #161b22; padding: 15px; border: 1px solid #30363d; border-radius: 6px; overflow: auto; }
h2 { color: #1f6feb; margin-top: 30px; }
.success { color: #3fb950; }
.error { color: #f85149; }
.warning { color: #d29922; }
</style>";

// Test get-dealer-summary.php
echo "<h2>Test 1: get-dealer-summary.php</h2>";
ob_start();
try {
    include 'get-dealer-summary.php';
    $response = ob_get_clean();
    $data = json_decode($response, true);

    if ($data && isset($data['success'])) {
        if ($data['success']) {
            echo "<p class='success'>✓ API returned success</p>";
            $summary = $data['summary'] ?? [];
            echo "<p>Total Customers: <strong>{$summary['totalCustomers']}</strong></p>";
            echo "<p>Total Devices: <strong>{$summary['totalDevices']}</strong></p>";
            echo "<p>Data Source: <strong>{$summary['_dataSource']}</strong></p>";
            if (isset($summary['_note'])) {
                echo "<p class='warning'>Note: {$summary['_note']}</p>";
            }
            echo "<details><summary>Full Response</summary><pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre></details>";
        } else {
            echo "<p class='error'>✗ API returned error: " . htmlspecialchars($data['error'] ?? 'Unknown') . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Invalid JSON response</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "<p class='error'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test get-customer-portfolio.php
echo "<h2>Test 2: get-customer-portfolio.php</h2>";
ob_start();
try {
    include 'get-customer-portfolio.php';
    $response = ob_get_clean();
    $data = json_decode($response, true);

    if ($data && isset($data['success'])) {
        if ($data['success']) {
            echo "<p class='success'>✓ API returned success</p>";
            $customers = $data['customers'] ?? [];
            echo "<p>Total Customers: <strong>" . count($customers) . "</strong></p>";
            if (count($customers) > 0) {
                echo "<p>Sample customer:</p>";
                echo "<pre>" . json_encode($customers[0], JSON_PRETTY_PRINT) . "</pre>";
            }
        } else {
            echo "<p class='error'>✗ API returned error: " . htmlspecialchars($data['error'] ?? 'Unknown') . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Invalid JSON response</p>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "<p class='error'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Direct MPS API test
echo "<h2>Test 3: Direct MPS API Call</h2>";
try {
    $dealerCode = DEFAULT_DEALER_CODE;
    echo "<p>Testing with dealer code: <strong>$dealerCode</strong></p>";

    $customers = callMPSQuery('Customer/GetCustomers', [
        'DealerCode' => $dealerCode,
        'FilterDealerCodes' => [$dealerCode],
        'PageNumber' => 1,
        'PageRows' => 10,
        'SortColumn' => 'Code'
    ]);

    if ($customers && is_array($customers)) {
        echo "<p class='success'>✓ Retrieved " . count($customers) . " customers from MPS API</p>";
        if (count($customers) > 0) {
            $sample = $customers[0];
            echo "<p>Sample: {$sample['Description']} ({$sample['Code']})</p>";

            // Test dashboard call
            $dashboard = callMPSQuery('CustomerDashboard/Get', [
                'Code' => $sample['Code'],
                'DealerCode' => $dealerCode
            ]);

            if ($dashboard && is_array($dashboard)) {
                echo "<p class='success'>✓ Retrieved dashboard for {$sample['Code']}</p>";
                echo "<p>Devices: <strong>{$dashboard['TotalManagedDevices']}</strong></p>";
                echo "<pre>" . json_encode($dashboard, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<p class='error'>✗ Failed to retrieve dashboard</p>";
            }
        }
    } else {
        echo "<p class='error'>✗ No customers returned from MPS API</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><p><a href='../dealer.php'>Back to Dealer Dashboard</a> | <a href='test-dealer-live.php'>Refresh Test</a></p>";
