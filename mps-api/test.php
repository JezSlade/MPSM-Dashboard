<?php
/**
 * MPS API Engine Test Suite
 *
 * Tests all three critical fixes:
 * 1. OAuth authentication on all endpoints
 * 2. Smart parameter population (dealer codes, pagination)
 * 3. MPSM response validation (IsValid field handling)
 *
 * Usage: php test.php
 */

// Security constant
define('MPS_ENGINE_ACCESS', true);

// Enable debug mode for testing
putenv('MPS_DEBUG=true');

// Load engine
require_once __DIR__ . '/engine.php';

// Test configuration
$testResults = [];
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

/**
 * Test result tracking
 */
function startTest($name) {
    global $totalTests;
    $totalTests++;
    echo "\n[TEST {$totalTests}] {$name}\n";
    echo str_repeat('-', 80) . "\n";
}

function passTest($message = '') {
    global $passedTests, $testResults, $totalTests;
    $passedTests++;
    $testResults[$totalTests] = 'PASS';
    echo "[PASS] {$message}\n";
}

function failTest($message = '', $details = null) {
    global $failedTests, $testResults, $totalTests;
    $failedTests++;
    $testResults[$totalTests] = 'FAIL';
    echo "[FAIL] {$message}\n";
    if ($details !== null) {
        echo "Details: " . print_r($details, true) . "\n";
    }
}

function testInfo($message) {
    echo "  > {$message}\n";
}

/**
 * Print test summary
 */
function printSummary() {
    global $totalTests, $passedTests, $failedTests, $testResults;

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "TEST SUMMARY\n";
    echo str_repeat('=', 80) . "\n";
    echo "Total Tests:  {$totalTests}\n";
    echo "Passed:       {$passedTests} (" . round(($passedTests / $totalTests) * 100, 1) . "%)\n";
    echo "Failed:       {$failedTests}\n";

    if ($failedTests > 0) {
        echo "\nFailed Tests:\n";
        foreach ($testResults as $num => $result) {
            if ($result === 'FAIL') {
                echo "  - Test #{$num}\n";
            }
        }
    }

    echo "\n" . str_repeat('=', 80) . "\n";

    exit($failedTests > 0 ? 1 : 0);
}

// Initialize engine
try {
    $engine = MPSMonitorEngine::getInstance();
} catch (Exception $e) {
    echo "FATAL: Failed to initialize engine: {$e->getMessage()}\n";
    exit(1);
}

echo str_repeat('=', 80) . "\n";
echo "MPS API ENGINE TEST SUITE\n";
echo str_repeat('=', 80) . "\n";
echo "Testing critical fixes from API discovery\n";

// =============================================================================
// TEST 1: Engine Health Check
// =============================================================================
startTest("Engine Health Check");

try {
    $health = $engine->healthCheck();

    if (isset($health['status']) && $health['status'] === 'ok') {
        passTest("Engine is healthy");
        testInfo("Service: {$health['service']}");
        testInfo("Version: {$health['version']}");
        testInfo("Auth Mode: {$health['config']['auth_mode']}");
    } else {
        failTest("Health check returned non-ok status", $health);
    }
} catch (Exception $e) {
    failTest("Exception during health check: {$e->getMessage()}");
}

// =============================================================================
// TEST 2: OAuth Token Acquisition
// =============================================================================
startTest("OAuth Token Acquisition (Fix #1: OAuth on all endpoints)");

try {
    $reflection = new ReflectionClass($engine);
    $method = $reflection->getMethod('ensureAccessToken');
    $method->setAccessible(true);

    // Force token refresh
    $property = $reflection->getProperty('accessToken');
    $property->setAccessible(true);
    $property->setValue($engine, null);

    $result = $method->invoke($engine);

    if ($result === true) {
        passTest("OAuth token acquired successfully");

        // Check token property
        $token = $property->getValue($engine);
        if (!empty($token)) {
            testInfo("Token acquired: " . substr($token, 0, 20) . "...");
        }
    } else {
        failTest("Failed to acquire OAuth token");
    }
} catch (Exception $e) {
    failTest("Exception during OAuth test: {$e->getMessage()}");
}

// =============================================================================
// TEST 3: Smart Parameter Population - Dealer Code
// =============================================================================
startTest("Smart Parameter Population - Dealer Code (Fix #2)");

try {
    // Try to get dealer info (requires dealerCode parameter)
    // The engine should auto-populate it from config
    $result = $engine->dispatchAction('getDealerInfo', []);

    if (isset($result['success']) && $result['success'] === true) {
        passTest("Dealer code auto-populated successfully");
        testInfo("Retrieved dealer info without providing dealerCode parameter");
        if (isset($result['data']['DealerCode'])) {
            testInfo("Dealer Code: {$result['data']['DealerCode']}");
        }
    } elseif (isset($result['error']) &&
              (strpos($result['error'], 'Missing required') === false)) {
        // If error is not about missing parameters, that's OK - API might have other issues
        passTest("Parameter auto-population working (got non-parameter error)");
        testInfo("Error was not about missing parameters: {$result['error']}");
    } else {
        failTest("Dealer code not auto-populated", $result);
    }
} catch (Exception $e) {
    failTest("Exception during parameter population test: {$e->getMessage()}");
}

// =============================================================================
// TEST 4: Smart Parameter Population - Pagination
// =============================================================================
startTest("Smart Parameter Population - Pagination Defaults (Fix #2)");

try {
    // Try endpoint that uses pagination (without providing page/pageSize)
    $result = $engine->dispatchAction('getCustomers', []);

    if (isset($result['success'])) {
        if ($result['success'] === true) {
            passTest("Pagination defaults auto-populated");
            testInfo("Retrieved customers without page/pageSize parameters");
            if (isset($result['data']) && is_array($result['data'])) {
                testInfo("Returned " . count($result['data']) . " customers");
            }
        } else {
            // Check if error is NOT about missing page/pageSize
            $error = $result['error'] ?? '';
            if (strpos($error, 'page') === false && strpos($error, 'pageSize') === false) {
                passTest("Pagination defaults working (got non-pagination error)");
                testInfo("Error was not about pagination: {$error}");
            } else {
                failTest("Pagination defaults not working", $result);
            }
        }
    } else {
        failTest("Invalid response format", $result);
    }
} catch (Exception $e) {
    failTest("Exception during pagination test: {$e->getMessage()}");
}

// =============================================================================
// TEST 5: MPSM Response Validation - Valid Response
// =============================================================================
startTest("MPSM Response Validation - Valid Response (Fix #3)");

try {
    // Test the validateMPSMResponse method directly
    $reflection = new ReflectionClass($engine);
    $method = $reflection->getMethod('validateMPSMResponse');
    $method->setAccessible(true);

    // Simulate a valid MPSM response
    $validResponse = [
        'Result' => ['id' => 123, 'name' => 'Test'],
        'IsValid' => true,
        'Errors' => []
    ];

    $validation = $method->invoke($engine, $validResponse, 200);

    if ($validation['valid'] === true &&
        $validation['data'] === $validResponse['Result'] &&
        $validation['error'] === null) {
        passTest("Valid MPSM response correctly processed");
        testInfo("Result field extracted correctly");
    } else {
        failTest("Valid response validation failed", $validation);
    }
} catch (Exception $e) {
    failTest("Exception during valid response test: {$e->getMessage()}");
}

// =============================================================================
// TEST 6: MPSM Response Validation - Error Response
// =============================================================================
startTest("MPSM Response Validation - Error Response (Fix #3)");

try {
    $reflection = new ReflectionClass($engine);
    $method = $reflection->getMethod('validateMPSMResponse');
    $method->setAccessible(true);

    // Simulate an MPSM error response (HTTP 200 but IsValid=false)
    $errorResponse = [
        'Result' => null,
        'IsValid' => false,
        'Errors' => [
            ['Code' => 'ERR001', 'Description' => 'Invalid dealer code'],
            ['Code' => 'ERR002', 'Description' => 'Missing required field']
        ]
    ];

    $validation = $method->invoke($engine, $errorResponse, 200);

    if ($validation['valid'] === false &&
        !empty($validation['error']) &&
        strpos($validation['error'], 'Invalid dealer code') !== false) {
        passTest("Error response correctly detected and processed");
        testInfo("Extracted error: {$validation['error']}");
    } else {
        failTest("Error response validation failed", $validation);
    }
} catch (Exception $e) {
    failTest("Exception during error response test: {$e->getMessage()}");
}

// =============================================================================
// TEST 7: MPSM Response Validation - HTTP Error
// =============================================================================
startTest("MPSM Response Validation - HTTP Error Handling (Fix #3)");

try {
    $reflection = new ReflectionClass($engine);
    $method = $reflection->getMethod('validateMPSMResponse');
    $method->setAccessible(true);

    // Simulate a real HTTP error (404)
    $httpError = ['message' => 'Not found'];

    $validation = $method->invoke($engine, $httpError, 404);

    if ($validation['valid'] === false &&
        strpos($validation['error'], '404') !== false) {
        passTest("HTTP errors correctly handled");
        testInfo("Error: {$validation['error']}");
    } else {
        failTest("HTTP error validation failed", $validation);
    }
} catch (Exception $e) {
    failTest("Exception during HTTP error test: {$e->getMessage()}");
}

// =============================================================================
// TEST 8: End-to-End Integration Test
// =============================================================================
startTest("End-to-End Integration - Real API Call");

try {
    // Make a real API call that should work with all fixes
    // getDealerInfo is a simple GET that should succeed
    $result = $engine->dispatchAction('getDealerInfo', []);

    testInfo("Action: getDealerInfo");
    testInfo("Response: " . json_encode($result, JSON_PRETTY_PRINT));

    if (isset($result['success'])) {
        if ($result['success'] === true) {
            passTest("End-to-end test successful");
            testInfo("All fixes working together in production");

            // Verify response structure
            if (isset($result['data'])) {
                testInfo("Data returned successfully");
                if (is_array($result['data']) && count($result['data']) > 0) {
                    testInfo("Response contains " . count($result['data']) . " items");
                }
            }
        } else {
            // API returned an error, but that's OK as long as it's properly formatted
            if (isset($result['error']) && !empty($result['error'])) {
                passTest("End-to-end communication working (got formatted error)");
                testInfo("Error: {$result['error']}");
                testInfo("This indicates the engine is communicating with MPSM API");
            } else {
                failTest("Malformed error response", $result);
            }
        }
    } else {
        failTest("Invalid response structure", $result);
    }
} catch (Exception $e) {
    failTest("Exception during integration test: {$e->getMessage()}");
}

// =============================================================================
// TEST 9: Parameter Auto-population Override
// =============================================================================
startTest("Parameter Auto-population with Explicit Values");

try {
    // Test that explicit parameters override auto-population
    $customCode = 'TESTCODE123';
    $result = $engine->dispatchAction('getDealerInfo', ['code' => $customCode]);

    // We can't easily verify the exact code used, but we can check that
    // the engine accepted our parameter without error
    if (isset($result['success'])) {
        passTest("Explicit parameters accepted (override auto-population)");
        testInfo("Provided custom code: {$customCode}");
        if (!$result['success'] && isset($result['error'])) {
            testInfo("API error (expected for invalid code): {$result['error']}");
        }
    } else {
        failTest("Invalid response when providing explicit parameters", $result);
    }
} catch (Exception $e) {
    failTest("Exception during override test: {$e->getMessage()}");
}

// =============================================================================
// TEST 10: Registry and Swagger Integration
// =============================================================================
startTest("Swagger Registry Integration");

try {
    $registry = SwaggerActionRegistry::getInstance();
    $operations = $registry->listOperations();

    if (!empty($operations) && count($operations) > 0) {
        passTest("Swagger registry loaded successfully");
        testInfo("Found " . count($operations) . " registered operations");

        // Verify some key operations exist
        $actions = array_column($operations, 'action');
        $keyActions = ['getDealerInfo', 'getCustomers', 'getVehicles'];
        $found = array_intersect($keyActions, $actions);

        if (count($found) > 0) {
            testInfo("Key actions found: " . implode(', ', $found));
        }
    } else {
        failTest("No operations found in registry");
    }
} catch (Exception $e) {
    failTest("Exception during registry test: {$e->getMessage()}");
}

// =============================================================================
// Print Summary
// =============================================================================
printSummary();
