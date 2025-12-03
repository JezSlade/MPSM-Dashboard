<?php
/**
 * Simple Dealer API Diagnostic
 * No authentication required for debugging
 */

require '../config.php';
require '../functions.php';

header('Content-Type: application/json');

$result = [];

// Test 1: Database connection
try {
    $pdo = getDatabase();
    $result['database'] = 'Connected';
} catch (Exception $e) {
    $result['database'] = 'Error: ' . $e->getMessage();
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Test 2: Check cache table
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $result['tables'] = $tables;

    $hasCacheDevices = in_array('mpsm_cache_devices', $tables);
    $result['has_cache_devices_table'] = $hasCacheDevices;

    if ($hasCacheDevices) {
        $count = $pdo->query("SELECT COUNT(*) FROM mpsm_cache_devices")->fetchColumn();
        $result['cache_devices_count'] = $count;

        $installed = $pdo->query("SELECT COUNT(*) FROM mpsm_cache_devices WHERE is_uninstalled = 0")->fetchColumn();
        $result['installed_devices_count'] = $installed;
    }
} catch (Exception $e) {
    $result['cache_check_error'] = $e->getMessage();
}

// Test 3: Try live API call
try {
    $dealerCode = DEFAULT_DEALER_CODE;
    $result['dealer_code'] = $dealerCode;

    $customers = callMPSQuery('Customer/GetCustomers', ['FilterDealerCodes' => [$dealerCode]]);

    if ($customers && isset($customers['Customers'])) {
        $result['live_api_customers_count'] = count($customers['Customers']);
        $result['live_api_sample'] = array_slice($customers['Customers'], 0, 3);
    } else {
        $result['live_api_response'] = $customers;
    }
} catch (Exception $e) {
    $result['live_api_error'] = $e->getMessage();
}

// Test 4: Test dealer summary endpoint
try {
    $url = 'https://mpsm.resolutionsbydesign.us/cms/api/get-dealer-summary.php';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $result['dealer_summary_response'] = $data;
} catch (Exception $e) {
    $result['dealer_summary_error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
