<?php
/**
 * Test Device/List API Response Structure
 *
 * Shows what the MPS API actually returns
 */

require '../config.php';
require '../functions.php';

header('Content-Type: text/plain');

echo "=== DEVICE/LIST API TEST ===\n\n";

try {
    // Test API call
    $params = [
        'FilterDealerId' => DEFAULT_DEALER_ID,
        'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
        'PageNumber' => 1,
        'PageRows' => 5, // Just get 5 devices for testing
        'SortColumn' => 'Id',
        'SortOrder' => 0,
    ];

    $params = array_filter($params, static function ($value) {
        return $value !== null;
    });

    echo "Calling callMPSAPI('Device/List', params)...\n";
    echo "Params: " . json_encode($params, JSON_PRETTY_PRINT) . "\n\n";

    $response = callMPSAPI('Device/List', $params);

    echo "Response received!\n";
    echo str_repeat('-', 80) . "\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n" . str_repeat('-', 80) . "\n\n";

    // Analyze structure
    echo "Structure Analysis:\n";
    echo "Type: " . gettype($response) . "\n";

    if (is_array($response)) {
        echo "Top-level keys: " . implode(', ', array_keys($response)) . "\n";

        if (isset($response['data'])) {
            echo "✓ Has 'data' key\n";
        } else {
            echo "✗ NO 'data' key found\n";
        }

        if (isset($response['Result'])) {
            echo "✓ Has 'Result' key\n";
            if (is_array($response['Result'])) {
                echo "  Result count: " . count($response['Result']) . "\n";
            }
        }

        if (isset($response['Items'])) {
            echo "✓ Has 'Items' key\n";
            if (is_array($response['Items'])) {
                echo "  Items count: " . count($response['Items']) . "\n";
            }
        }

        if (isset($response['TotalCount'])) {
            echo "Total devices: {$response['TotalCount']}\n";
        }
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
