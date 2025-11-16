<?php
/**
 * Debug Refresh API Call
 *
 * Shows exactly what happens when we call Device/List
 */

require '../config.php';
require '../functions.php';

header('Content-Type: text/plain');

echo "=== DEBUG REFRESH API CALL ===\n\n";

try {
    $page = 1;
    $perPage = 100;

    $params = [
        'FilterDealerId' => DEFAULT_DEALER_ID,
        'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
        'FilterCustomerCodes' => null,
        'ProductBrand' => null,
        'ProductModel' => null,
        'OfficeId' => null,
        'Status' => null,
        'FilterText' => null,
        'PageNumber' => $page,
        'PageRows' => $perPage,
        'SortColumn' => 'Id',
        'SortOrder' => 0,
    ];

    $params = array_filter($params, static function ($value) {
        return $value !== null;
    });

    echo "Calling API...\n";
    $response = callMPSAPI('Device/List', $params);

    echo "Response received!\n\n";
    echo "Type: " . gettype($response) . "\n";

    if (!$response) {
        echo "ERROR: Response is empty/false/null\n";
        exit(1);
    }

    echo "Response is truthy\n";

    if (!is_array($response)) {
        echo "ERROR: Response is not an array\n";
        echo "Actual type: " . gettype($response) . "\n";
        echo "Value: " . var_export($response, true) . "\n";
        exit(1);
    }

    echo "Response is an array\n";
    echo "Keys: " . implode(', ', array_keys($response)) . "\n\n";

    if (!isset($response['Result'])) {
        echo "ERROR: 'Result' key not found\n";
        echo "Full response:\n";
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit(1);
    }

    echo "✓ 'Result' key found\n";

    $devices = $response['Result'];
    echo "Devices count: " . count($devices) . "\n";

    $totalRows = $response['TotalRows'] ?? 0;
    echo "Total rows: {$totalRows}\n";

    $totalPages = ($totalRows > 0) ? (int)ceil($totalRows / $perPage) : 1;
    echo "Total pages: {$totalPages}\n";

    if (count($devices) > 0) {
        $first = $devices[0];
        echo "\nFirst device:\n";
        echo "  Serial: " . ($first['SerialNumber'] ?? 'N/A') . "\n";
        echo "  Customer: " . ($first['CustomerCode'] ?? 'N/A') . "\n";
        echo "  Status: " . ($first['InstallStatus'] ?? 'N/A') . "\n";
    }

    echo "\n✓✓✓ SUCCESS - API call works correctly! ✓✓✓\n";

} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
