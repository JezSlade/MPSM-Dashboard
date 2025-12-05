<?php
/**
 * Test script to debug duplicate IP API issue
 * Simulates the API call with detailed diagnostic output
 */

require '../config.php';
require '../functions.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>Duplicate IP API Debug Test</h1>\n";
echo "<pre>\n";

// Step 1: Check if functions exist
echo "=== STEP 1: Function Availability Check ===\n";
echo "callMPSAPI() exists: " . (function_exists('callMPSAPI') ? 'YES' : 'NO') . "\n";
echo "extractDevicesFromResponse() exists: " . (function_exists('extractDevicesFromResponse') ? 'YES' : 'NO') . "\n";
echo "\n";

// Step 2: Check constants
echo "=== STEP 2: Constants Check ===\n";
echo "DEFAULT_DEALER_ID: " . (defined('DEFAULT_DEALER_ID') ? DEFAULT_DEALER_ID : 'NOT DEFINED') . "\n";
echo "DEFAULT_DEALER_CODE: " . (defined('DEFAULT_DEALER_CODE') ? DEFAULT_DEALER_CODE : 'NOT DEFINED') . "\n";
echo "\n";

// Step 3: Check MPS API credentials
echo "=== STEP 3: MPS API Credentials Check ===\n";
echo "MPS_API_BASE: " . MPS_API_BASE . "\n";
echo "MPS_CLIENT_ID: " . (empty(MPS_CLIENT_ID) ? 'EMPTY' : 'SET') . "\n";
echo "MPS_CLIENT_SECRET: " . (empty(MPS_CLIENT_SECRET) ? 'EMPTY' : 'SET') . "\n";
echo "MPS_USERNAME: " . (empty(MPS_USERNAME) ? 'EMPTY' : 'SET') . "\n";
echo "MPS_PASSWORD: " . (empty(MPS_PASSWORD) ? 'EMPTY' : 'SET') . "\n";
echo "\n";

// Step 4: Test getting OAuth token
echo "=== STEP 4: OAuth Token Test ===\n";
try {
    $token = getMPSToken();
    echo "Token obtained: " . (empty($token) ? 'FAILED (empty)' : 'SUCCESS (length: ' . strlen($token) . ')') . "\n";
    echo "Token preview: " . substr($token, 0, 50) . "...\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "\nCRITICAL ERROR: Cannot obtain OAuth token. API calls will fail.\n";
    echo "This is why fetchDevicesViaQuery() returns 0 devices.\n";
    echo "\nRoot Cause: MPS API credentials are empty in config.php\n";
    echo "</pre>";
    exit;
}
echo "\n";

// Step 5: Test Device/List API call
echo "=== STEP 5: Device/List API Call Test ===\n";
$testParams = [
    'FilterDealerId' => DEFAULT_DEALER_ID,
    'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
    'PageRows' => 10,
    'PageNumber' => 1,
    'SortColumn' => 'Id',
    'SortOrder' => 0,
];

// Remove null values
$testParams = array_filter($testParams, function($value) {
    return $value !== null;
});

echo "Request params:\n";
print_r($testParams);
echo "\n";

try {
    echo "Calling callMPSAPI('Device/List', params)...\n";
    $response = callMPSAPI('Device/List', $testParams);

    echo "Response received: " . (empty($response) ? 'EMPTY' : 'SUCCESS') . "\n";

    if (!empty($response)) {
        echo "Response structure:\n";
        echo "- Is array: " . (is_array($response) ? 'YES' : 'NO') . "\n";
        echo "- Keys: " . implode(', ', array_keys($response)) . "\n";

        if (isset($response['TotalRows'])) {
            echo "- TotalRows: " . $response['TotalRows'] . "\n";
        }

        echo "\nFull response (first 2000 chars):\n";
        echo substr(print_r($response, true), 0, 2000) . "\n...\n";
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "\nAPI call failed. This explains why fetchDevicesViaQuery() returns 0 devices.\n";
}
echo "\n";

// Step 6: Test extractDevicesFromResponse
echo "=== STEP 6: extractDevicesFromResponse() Test ===\n";
if (isset($response) && !empty($response)) {
    $devices = extractDevicesFromResponse($response);
    echo "Devices extracted: " . count($devices) . "\n";

    if (!empty($devices)) {
        echo "\nFirst device structure:\n";
        print_r($devices[0]);
    }
} else {
    echo "Skipped - no response data available\n";
}
echo "\n";

// Step 7: Summary
echo "=== STEP 7: Summary ===\n";
if (empty(MPS_CLIENT_ID) || empty(MPS_CLIENT_SECRET) || empty(MPS_USERNAME) || empty(MPS_PASSWORD)) {
    echo "ROOT CAUSE: MPS API credentials are EMPTY in cms/config.php\n";
    echo "Result: callMPSAPI() fails → fetchDevicesViaQuery() returns 0 devices\n";
    echo "Error message: 'No devices returned from cache or API'\n";
    echo "\nFIX REQUIRED:\n";
    echo "1. Add real MPS API credentials to cms/config.php\n";
    echo "2. Update these constants:\n";
    echo "   - MPS_CLIENT_ID\n";
    echo "   - MPS_CLIENT_SECRET\n";
    echo "   - MPS_USERNAME\n";
    echo "   - MPS_PASSWORD\n";
    echo "   - DEFAULT_DEALER_ID (should be a real dealer ID, not '1')\n";
    echo "   - DEFAULT_DEALER_CODE (should be a real dealer code, not 'TEST')\n";
} elseif (isset($devices) && count($devices) > 0) {
    echo "SUCCESS: API is working correctly\n";
    echo "Devices retrieved: " . count($devices) . "\n";
    echo "fetchDevicesViaQuery() should work properly\n";
} else {
    echo "API credentials are set but API call failed\n";
    echo "Check error messages above for details\n";
}

echo "</pre>";
