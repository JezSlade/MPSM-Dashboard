<?php
// Removed `require_once __DIR__ . '/../includes/redis.php';` as redis.php content was not provided in the prompt.

// Enable detailed error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/debug.log'); // Logs errors to a file in the same directory as this script

/**
 * Loads environment variables from a .env file.
 * This function is extracted and adapted from `get_customers.php`.
 * It expects a .env file in the same directory as this script.
 *
 * @param string $path The path to the .env file.
 * @return array An associative array of environment variables.
 */
if (!function_exists('load_env')) {
    function load_env($path = __DIR__ . '/.env') {
        $env = [];
        if (!file_exists($path)) {
            // If .env file is not found, output an error and terminate
            echo json_encode(["error" => ".env file not found at " . $path]);
            exit;
        }
        // Read the .env file line by line
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            // Skip comments and empty lines
            if (str_starts_with(trim($line), '#')) continue;
            // Split the line into key and value
            [$key, $val] = explode('=', $line, 2);
            $env[trim($key)] = trim($val); // Store key-value pair
        }
        return $env;
    }
}

/**
 * Gets an access token from the API's token endpoint.
 * This function is extracted and adapted from `get_customers.php`.
 * It requires CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD, SCOPE, and TOKEN_URL
 * to be defined in the .env file.
 *
 * @param array $env Associative array of environment variables.
 * @return string The access token.
 */
if (!function_exists('get_token')) {
    function get_token($env) {
        // Define required environment variables for token request
        $required = ['CLIENT_ID', 'CLIENT_SECRET', 'USERNAME', 'PASSWORD', 'SCOPE', 'TOKEN_URL'];
        foreach ($required as $key) {
            if (empty($env[$key])) {
                // If any required key is missing, output error and terminate
                echo json_encode(["error" => "Missing $key in .env"]);
                exit;
            }
        }

        // Prepare POST fields for the token request
        $postFields = http_build_query([
            'client_id'     => $env['CLIENT_ID'],
            'client_secret' => $env['CLIENT_SECRET'],
            'grant_type'    => 'password',
            'username'      => $env['USERNAME'],
            'password'      => $env['PASSWORD'],
            'scope'         => $env['SCOPE']
        ]);

        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $env['TOKEN_URL']); // Set the URL for the token endpoint
        curl_setopt($ch, CURLOPT_POST, true); // Set request method to POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields); // Set POST data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ // Set HTTP headers
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch); // Execute cURL request
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
        curl_close($ch); // Close cURL session

        $json = json_decode($response, true); // Decode JSON response

        // Check for successful token acquisition
        if ($code !== 200 || !isset($json['access_token'])) {
            echo json_encode(["error" => "Token request failed", "details" => $json]);
            exit;
        }

        return $json['access_token']; // Return the access token
    }
}

/**
 * Helper function to make generic API calls.
 * This function is designed for POST requests with JSON payloads and Bearer token authentication.
 *
 * @param string $url The API endpoint URL.
 * @param string $token The Bearer access token.
 * @param array $payload The request body as an associative array, which will be JSON encoded.
 * @param string $method The HTTP method (default 'POST').
 * @return array The decoded JSON response from the API, or an error array.
 */
if (!function_exists('call_api')) {
    function call_api($url, $token, $payload, $method = 'POST') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); // Set the API endpoint URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ // Set HTTP headers
            "Authorization: Bearer $token", // Authorization header with token
            "Content-Type: application/json", // Request content type
            "Accept: application/json" // Expected response content type
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true); // Set request method to POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload)); // Set JSON encoded payload
        }
        // Note: This function is primarily set up for POST with body.
        // For GET requests, payload would typically be query parameters.
        // If GET requests with a body are needed (less common), this function would need adjustment.

        $response = curl_exec($ch); // Execute cURL request
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
        curl_close($ch); // Close cURL session

        $json = json_decode($response, true); // Decode JSON response

        // Check for non-200 HTTP status codes
        if ($code !== 200) {
            error_log("API Call Failed to $url with code $code: " . json_encode($json));
            return ["error" => "API call failed", "url" => $url, "code" => $code, "details" => $json];
        }

        return $json; // Return the decoded JSON response
    }
}

// Set the Content-Type header to application/json for the script's output
header('Content-Type: application/json');

// --- Main execution flow starts here ---

// 1. Load environment variables from the .env file
$env = load_env();

// 2. Obtain the access token required for subsequent API calls
$token = get_token($env);

// Retrieve hardcoded DEALER_CODE and DEALER_ID from environment variables
// These are assumed to be present in the .env file as per user's description.
$dealerCode = $env['DEALER_CODE'];
$dealerId = $env['DEALER_ID'];

// Initialize an array to store all collected data for the final JSON output
$output = [];

// --- Endpoint 1: Get Customers ---
// This is the first call as it provides 'customerid' (CustomerCode) for subsequent calls.
// Reference: get_customers.php
$customers_api_url = rtrim($env['API_BASE_URL'], '/') . '/Customer/GetCustomers';
$customers_payload = [
    'DealerCode'  => $dealerCode, // Requires hardcoded dealerCode
    'Code'        => null,
    'HasHpSds'    => null,
    'FilterText'  => null,
    'PageNumber'  => 1,
    'PageRows'    => 2147483647, // Set to max value to fetch all customers
    'SortColumn'  => 'Id',
    'SortOrder'   => 0 // 0 for ascending, 1 for descending
];

$output['customers_fetch_status'] = ['message' => 'Attempting to fetch customers...'];
$customers_response = call_api($customers_api_url, $token, $customers_payload);

// Check if customer fetching failed
if (isset($customers_response['error'])) {
    $output['customers_fetch_status'] = $customers_response;
    echo json_encode($output, JSON_PRETTY_PRINT); // Output current status and exit
    exit;
}

// Store customer fetch success details and data
$output['customers_fetch_status'] = ['message' => 'Successfully fetched customers', 'total_customers_found' => $customers_response['TotalRows']];
$customers = $customers_response['Result'];
$output['customer_data'] = []; // Initialize array to hold detailed customer data

// Iterate through each customer to fetch their devices, counters, and alerts
foreach ($customers as $customer) {
    $customerCode = $customer['Code'];
    $output['customer_data'][$customerCode] = [
        'customer_name' => $customer['Description'],
        'devices'       => [] // Initialize array for devices under this customer
    ];

    // --- Endpoint 2: Get Devices for the current customer ---
    // Requires 'customerid' (CustomerCode) from the previous customers response.
    // Reference: get_devices.php
    $devices_api_url = rtrim($env['API_BASE_URL'], '/') . '/Device/List';
    $devices_payload = [
        'FilterDealerId'      => $dealerId, // Requires hardcoded DEALER_ID
        'FilterCustomerCodes' => [$customerCode], // Uses customerCode from current iteration
        'ProductBrand'        => null,
        'ProductModel'        => null,
        'OfficeId'            => null,
        'Status'              => 1, // Filter for active devices (Status 1)
        'FilterText'          => null,
        'PageNumber'          => 1,
        'PageRows'            => 2147483647, // Set to max value to fetch all devices for this customer
        'SortColumn'          => 'Id',
        'SortOrder'           => 0
    ];

    $devices_response = call_api($devices_api_url, $token, $devices_payload);

    // Check if device fetching failed for this customer
    if (isset($devices_response['error'])) {
        $output['customer_data'][$customerCode]['devices_fetch_error'] = $devices_response;
        continue; // Skip to the next customer if devices fetch fails
    }

    // Store device fetch success details and data
    $output['customer_data'][$customerCode]['total_devices_found'] = $devices_response['TotalRows'];
    $devices = $devices_response['Result'];

    // Iterate through each device to fetch its counters and alerts
    foreach ($devices as $device) {
        $serialNumber = $device['SerialNumber'];
        $assetNumber = $device['AssetNumber']; // AssetNumber can be an alternative identifier

        $output['customer_data'][$customerCode]['devices'][$serialNumber] = [
            'description'  => $device['Description'],
            'asset_number' => $assetNumber,
            'counters'     => null, // Placeholder for counters data
            'alerts'       => null  // Placeholder for alerts data
        ];

        // --- Endpoint 3: Get Device Counters for the current device ---
        // Requires 'customerid' (CustomerCode) and 'deviceid' (SerialNumber/AssetNumber) from previous responses.
        // Reference: get_device_counters.php
        $device_counters_api_url = rtrim($env['API_BASE_URL'], '/') . '/Counter/ListDetailed';
        $device_counters_payload = [
            'DealerCode'         => $dealerCode,
            'CustomerCode'       => $customerCode,
            'SerialNumber'       => $serialNumber, // Uses serialNumber from current device iteration
            'AssetNumber'        => $assetNumber,  // Also pass assetNumber if available
            'CounterDetaildTags' => null // No specific tags for this iteration
        ];
        $counters_response = call_api($device_counters_api_url, $token, $device_counters_payload);

        // Store counters data or error
        if (isset($counters_response['error'])) {
            $output['customer_data'][$customerCode]['devices'][$serialNumber]['counters_fetch_error'] = $counters_response;
        } else {
            $output['customer_data'][$customerCode]['devices'][$serialNumber]['counters'] = [
                'total_counters' => $counters_response['TotalRows'],
                'data'           => $counters_response['Result']
            ];
        }

        // --- Endpoint 4: Get Device Alerts for the current customer/device ---
        // Requires 'customerid' (CustomerCode). Note that get_device_alerts.php uses CustomerCode in its payload.
        // Reference: get_device_alerts.php, Swagger Pretty.json (for SupplyAlert/List)
        $device_alerts_api_url = rtrim($env['API_BASE_URL'], '/') . '/SupplyAlert/List';
        $device_alerts_payload = [
            'CustomerCode' => $customerCode, // Uses customerCode from current iteration
            'PageNumber'   => 1,
            'PageRows'     => 2147483647,
            'SortColumn'   => 'CreationDate',
            'SortOrder'    => 1 // 1 for descending (newest first)
        ];
        $alerts_response = call_api($device_alerts_api_url, $token, $device_alerts_payload);

        // Store alerts data or error
        if (isset($alerts_response['error'])) {
            $output['customer_data'][$customerCode]['devices'][$serialNumber]['alerts_fetch_error'] = $alerts_response;
        } else {
            $output['customer_data'][$customerCode]['devices'][$serialNumber]['alerts'] = [
                'total_alerts' => $alerts_response['TotalRows'],
                'data'         => $alerts_response['Result']
            ];
        }
    }
}

// Output the final collected data as a pretty-printed JSON response
echo json_encode($output, JSON_PRETTY_PRINT);
?>
