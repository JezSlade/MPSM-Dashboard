<?php
/**
 * Test Dealer Summary API - Minimal Version
 */

require '../config.php';
require '../functions.php';

requireAuth();

header('Content-Type: application/json');

try {
    $pdo = getDatabase();

    // Test 1: Database connection
    $test1 = "Database connected";

    // Test 2: Check if cache table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'mpsm_cache_devices'");
    $tableExists = $stmt->rowCount() > 0;
    $test2 = $tableExists ? "mpsm_cache_devices exists" : "mpsm_cache_devices MISSING";

    // Test 3: Count devices
    $deviceCount = 0;
    if ($tableExists) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM mpsm_cache_devices WHERE is_uninstalled = 0");
        $deviceCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    }

    // Test 4: Try JSON extraction
    $jsonTest = "Not tested";
    if ($tableExists && $deviceCount > 0) {
        try {
            $stmt = $pdo->query("
                SELECT device_data->>'$.AssetNumber' as asset
                FROM mpsm_cache_devices
                WHERE is_uninstalled = 0
                LIMIT 1
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $jsonTest = "JSON extraction works: " . ($result['asset'] ?? 'null');
        } catch (Exception $e) {
            $jsonTest = "JSON extraction FAILED: " . $e->getMessage();
        }
    }

    // Test 5: Try file_get_contents to internal API
    $httpTest = "Not tested";
    try {
        $url = 'https://mpsm.resolutionsbydesign.us/cms/api/get-customers.php?dealerCode=' . DEFAULT_DEALER_CODE;
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'header' => 'Cookie: ' . $_SERVER['HTTP_COOKIE']
            ]
        ]);
        $response = @file_get_contents($url, false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            $customerCount = count($data['customers'] ?? []);
            $httpTest = "HTTP call works: $customerCount customers";
        } else {
            $httpTest = "HTTP call FAILED";
        }
    } catch (Exception $e) {
        $httpTest = "HTTP error: " . $e->getMessage();
    }

    echo json_encode([
        'success' => true,
        'tests' => [
            'database' => $test1,
            'table_exists' => $test2,
            'device_count' => $deviceCount,
            'json_extraction' => $jsonTest,
            'http_call' => $httpTest
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}

/*
CHANGELOG
2025-12-06 Codex
- Rebranded test harness description from Executive to Dealer and ensured changelog compliance.
*/
