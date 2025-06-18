<?php
// Set PHP's execution time limit to unlimited.
// NOTE: This will NOT override server-level timeouts (e.g., Apache's TimeOut, Nginx's proxy_read_timeout).
// If you still experience timeouts, you MUST adjust your web server/proxy configuration.
set_time_limit(0);

// Start output buffering immediately to catch any unintended output (like warnings)
ob_start();

// Include the Redis cache helper functions
// Adjusted path: assuming redis.php is in an 'includes' folder relative to this script
require_once __DIR__ . '/includes/redis.php'; 

// Enable detailed error reporting for debugging purposes
// display_errors should ideally be 'Off' in a production environment
// and errors logged to a file. For debugging, 'On' is fine but will break JSON output.
// With ob_start, warnings will be buffered, then we can decide to discard them or not.
error_reporting(E_ALL);
ini_set('display_errors', '1'); // Keep this '1' for debugging to see if warnings occur
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
            // If .env file is not found, output an error JSON and terminate
            // This error will now be captured by ob_start
            header('Content-Type: application/json');
            echo json_encode(["error" => ".env file not found at " . $path]);
            ob_end_flush(); // Send buffered output and turn off buffering
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
 * The token is cached to reduce API calls.
 *
 * @param array $env Associative array of environment variables.
 * @return string The access token.
 */
if (!function_exists('get_token')) {
    function get_token($env) {
        $cacheKey = 'mpsm:api:token';
        // Try to get token from cache first
        if ($cachedToken = getCache($cacheKey)) {
            return $cachedToken;
        }

        // Define required environment variables for token request
        $required = ['CLIENT_ID', 'CLIENT_SECRET', 'USERNAME', 'PASSWORD', 'SCOPE', 'TOKEN_URL'];
        foreach ($required as $key) {
            if (empty($env[$key])) {
                // If any required key is missing, output error JSON and terminate
                // This error will now be captured by ob_start
                header('Content-Type: application/json');
                echo json_encode(["error" => "Missing $key in .env"]);
                ob_end_flush(); // Send buffered output and turn off buffering
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
            // Output error JSON and terminate
            header('Content-Type: application/json');
            echo json_encode(["error" => "Token request failed", "details" => $json]);
            ob_end_flush(); // Send buffered output and turn off buffering
            exit;
        }

        // Cache the token for 3500 seconds (slightly less than typical 1 hour expiry)
        setCache($cacheKey, $json['access_token'], 3500);
        return $json['access_token']; // Return the access token
    }
}

/**
 * Helper function to make generic API calls with caching.
 * This function is designed for POST requests with JSON payloads and Bearer token authentication.
 * It's enhanced to ensure it always returns an array, even on cURL errors or invalid JSON.
 *
 * @param string $url The API endpoint URL.
 * @param string $token The Bearer access token.
 * @param array $payload The request body as an associative array, which will be JSON encoded.
 * @param string $method The HTTP method (default 'POST').
 * @param int $cacheTtl Time-to-live for cache in seconds (default 3600s = 1 hour). Set to 0 to disable caching.
 * @return array The decoded JSON response from the API, or an error array. Returns an empty array if API returns no data.
 */
if (!function_exists('call_api')) {
    function call_api($url, $token, $payload, $method = 'POST', $cacheTtl = 3600) { // Increased default cache TTL
        $cacheKey = 'mpsm:api:' . md5($url . json_encode($payload) . $method);

        // Try to get response from cache first
        if ($cacheTtl > 0 && ($cachedResponse = getCache($cacheKey))) {
            $decodedCache = json_decode($cachedResponse, true);
            // Ensure cached data is a valid array before returning
            if (is_array($decodedCache)) {
                return $decodedCache;
            } else {
                // Log corrupted cache entry and purge it to prevent future issues
                error_log("Corrupted cached data for key: $cacheKey. Purging and fetching new data.");
                purgeCache($cacheKey);
            }
        }

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

        $response = curl_exec($ch); // Execute cURL request
        $curlError = curl_error($ch); // Get any cURL error message
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
        curl_close($ch); // Close cURL session

        // Handle cURL errors (e.g., network issues, DNS problems)
        if ($curlError) {
             error_log("cURL error on $url: " . $curlError);
             return ["error" => "cURL error", "url" => $url, "details" => $curlError];
        }

        $json = json_decode($response, true); // Decode JSON response

        // If json_decode fails (e.g., non-JSON response, empty response), $json will be null or false.
        // Also explicitly check if the response is an empty string for robustness.
        if (!is_array($json) || empty($response)) {
            error_log("API Call Failed to $url: Invalid JSON response or empty body (HTTP Code: $code). Raw Response: " . (empty($response) ? "[EMPTY RESPONSE]" : $response));
            return ["error" => "API call failed: Invalid or empty response format", "url" => $url, "code" => $code, "raw_response" => $response];
        }

        // Check for non-200 HTTP status codes, even if JSON is valid
        if ($code !== 200) {
            error_log("API Call Failed to $url with non-200 HTTP code $code: " . json_encode($json));
            return ["error" => "API call failed", "url" => $url, "code" => $code, "details" => $json];
        }

        // Cache the response if successful and caching is enabled
        if ($cacheTtl > 0) {
            setCache($cacheKey, json_encode($json), $cacheTtl);
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
// Use null coalescing operator (??) to prevent undefined key errors if variables are missing
$dealerCode = $env['DEALER_CODE'] ?? null;
$dealerId = $env['DEALER_ID'] ?? null;

// Validate essential environment variables immediately
if ($dealerCode === null) {
    echo json_encode(["error" => "DEALER_CODE is not defined in .env"]);
    ob_end_flush();
    exit;
}
if ($dealerId === null) {
    echo json_encode(["error" => "DEALER_ID is not defined in .env"]);
    ob_end_flush();
    exit;
}

// Define a reasonable page size for API requests
const DEFAULT_PAGE_SIZE = 100; // Fetch 100 items per page
// Define the delay in seconds between API calls
const REQUEST_DELAY_SECONDS = 1; // 1 second delay

// Initialize an array to store all collected data for the final JSON output
$output = [];

// --- Endpoint 1: Get Customers with Pagination ---
// This is the first call as it provides 'customerid' (CustomerCode) for subsequent calls.
$customers_api_url = rtrim($env['API_BASE_URL'] ?? '', '/') . '/Customer/GetCustomers'; // Use ?? '' for API_BASE_URL
$allCustomers = [];
$pageNumber = 1;
$totalCustomersExpected = PHP_INT_MAX; // Initialize with a large number

$output['customers_fetch_status'] = ['message' => 'Attempting to fetch customers with pagination...'];

do {
    $customers_payload = [
        'DealerCode'  => $dealerCode,
        'Code'        => null,
        'HasHpSds'    => null,
        'FilterText'  => null,
        'PageNumber'  => $pageNumber,
        'PageRows'    => DEFAULT_PAGE_SIZE,
        'SortColumn'  => 'Id',
        'SortOrder'   => 0
    ];

    $customers_response = call_api($customers_api_url, $token, $customers_payload);

    if (isset($customers_response['error'])) {
        $output['customers_fetch_status'] = $customers_response;
        // Clear any output already buffered from warnings/notices, then flush the JSON error
        ob_clean();
        echo json_encode($output, JSON_PRETTY_PRINT);
        ob_end_flush();
        exit;
    }

    $currentCustomers = $customers_response['Result'] ?? [];
    $totalCustomersExpected = $customers_response['TotalRows'] ?? 0;

    $allCustomers = array_merge($allCustomers, $currentCustomers);
    $pageNumber++;

    // Add a delay after fetching each page of customers
    if (count($currentCustomers) > 0) { // Only delay if data was actually fetched
        sleep(REQUEST_DELAY_SECONDS);
    }

    // Continue loop if we haven't fetched all customers and there are more pages
} while (count($allCustomers) < $totalCustomersExpected && count($currentCustomers) === DEFAULT_PAGE_SIZE);

$output['customers_fetch_status'] = [
    'message'           => 'Successfully fetched customers',
    'total_customers_found' => count($allCustomers)
];
$output['customer_data'] = [];

// Iterate through each customer to fetch their devices, counters, and alerts
foreach ($allCustomers as $customer) {
    if (!is_array($customer)) {
        error_log("Skipping non-array customer entry encountered: " . json_encode($customer));
        continue;
    }

    $customerCode = $customer['Code'] ?? null;

    if ($customerCode === null) {
        error_log("Skipping customer due to missing 'Code' in response: " . json_encode($customer));
        continue;
    }

    $output['customer_data'][$customerCode] = [
        'customer_name' => $customer['Description'] ?? 'N/A',
        'devices'       => []
    ];

    // Add a small delay before fetching devices for the next customer
    sleep(REQUEST_DELAY_SECONDS);

    // --- Endpoint 2: Get Devices for the current customer with Pagination ---
    $devices_api_url = rtrim($env['API_BASE_URL'] ?? '', '/') . '/Device/List';
    $allDevices = [];
    $pageNumber = 1;
    $totalDevicesExpected = PHP_INT_MAX;

    do {
        $devices_payload = [
            'FilterDealerId'      => $dealerId,
            'FilterCustomerCodes' => [$customerCode],
            'ProductBrand'        => null,
            'ProductModel'        => null,
            'OfficeId'            => null,
            'Status'              => 1,
            'FilterText'          => null,
            'PageNumber'          => $pageNumber,
            'PageRows'            => DEFAULT_PAGE_SIZE,
            'SortColumn'          => 'Id',
            'SortOrder'           => 0
        ];

        $devices_response = call_api($devices_api_url, $token, $devices_payload);

        if (isset($devices_response['error'])) {
            $output['customer_data'][$customerCode]['devices_fetch_error'] = $devices_response;
            break; // Break pagination loop for this customer's devices
        }

        $currentDevices = $devices_response['Result'] ?? [];
        $totalDevicesExpected = $devices_response['TotalRows'] ?? 0;

        $allDevices = array_merge($allDevices, $currentDevices);
        $pageNumber++;

        // Add a delay after fetching each page of devices
        if (count($currentDevices) > 0) { // Only delay if data was actually fetched
            sleep(REQUEST_DELAY_SECONDS);
        }

    } while (count($allDevices) < $totalDevicesExpected && count($currentDevices) === DEFAULT_PAGE_SIZE);

    $output['customer_data'][$customerCode]['total_devices_found'] = count($allDevices);
    
    // Iterate through each device to fetch its counters and alerts
    foreach ($allDevices as $device) {
        if (!is_array($device)) {
            error_log("Skipping non-array device entry for customer $customerCode: " . json_encode($device));
            continue;
        }

        $serialNumber = $device['SerialNumber'] ?? null;
        $assetNumber = $device['AssetNumber'] ?? null;

        if ($serialNumber === null && $assetNumber === null) {
            error_log("Skipping device due to missing 'SerialNumber' and 'AssetNumber' in response for customer $customerCode: " . json_encode($device));
            continue;
        }
        $deviceKey = $serialNumber ?? $assetNumber;

        $output['customer_data'][$customerCode]['devices'][$deviceKey] = [
            'description'   => $device['Description'] ?? 'N/A',
            'asset_number'  => $assetNumber,
            'serial_number' => $serialNumber,
            'counters'      => null,
            'alerts'        => null
        ];

        // Add a small delay before fetching data for the next device
        sleep(REQUEST_DELAY_SECONDS);

        // --- Endpoint 3: Get Device Counters for the current device (with pagination) ---
        $device_counters_api_url = rtrim($env['API_BASE_URL'] ?? '', '/') . '/Counter/ListDetailed';
        $allCounters = [];
        $pageNumber = 1;
        $totalCountersExpected = PHP_INT_MAX;

        do {
            $device_counters_payload = [
                'DealerCode'         => $dealerCode,
                'CustomerCode'       => $customerCode,
                'SerialNumber'       => $serialNumber,
                'AssetNumber'        => $assetNumber,
                'CounterDetaildTags' => null,
                'PageNumber'         => $pageNumber,
                'PageRows'           => DEFAULT_PAGE_SIZE
            ];
            $counters_response = call_api($device_counters_api_url, $token, $device_counters_payload);

            if (isset($counters_response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['counters_fetch_error'] = $counters_response;
                break; // Break pagination loop for this device's counters
            }
            
            $currentCounters = $counters_response['Result'] ?? [];
            $totalCountersExpected = $counters_response['TotalRows'] ?? 0;
            
            $allCounters = array_merge($allCounters, $currentCounters);
            $pageNumber++;

            // Add a delay after fetching each page of counters
            if (count($currentCounters) > 0) { // Only delay if data was actually fetched
                sleep(REQUEST_DELAY_SECONDS);
            }

        } while (count($allCounters) < $totalCountersExpected && count($currentCounters) === DEFAULT_PAGE_SIZE);

        $output['customer_data'][$customerCode]['devices'][$deviceKey]['counters'] = [
            'total_counters' => count($allCounters),
            'data'           => $allCounters
        ];

        // Add a small delay before fetching alerts for the current device (or next device)
        sleep(REQUEST_DELAY_SECONDS);

        // --- Endpoint 4: Get Device Alerts for the current customer (with pagination) ---
        // Note: get_device_alerts.php and Swagger indicate this endpoint primarily filters by CustomerCode,
        // so we fetch all alerts for the customer and include them under each of their devices.
        $device_alerts_api_url = rtrim($env['API_BASE_URL'] ?? '', '/') . '/SupplyAlert/List';
        $allAlerts = [];
        $pageNumber = 1;
        $totalAlertsExpected = PHP_INT_MAX;

        do {
            $device_alerts_payload = [
                'CustomerCode' => $customerCode,
                'PageNumber'   => $pageNumber,
                'PageRows'     => DEFAULT_PAGE_SIZE,
                'SortColumn'   => 'CreationDate',
                'SortOrder'    => 1
            ];
            $alerts_response = call_api($device_alerts_api_url, $token, $device_alerts_payload);

            if (isset($alerts_response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['alerts_fetch_error'] = $alerts_response;
                break; // Break pagination loop for this customer's alerts
            }

            $currentAlerts = $alerts_response['Result'] ?? [];
            $totalAlertsExpected = $alerts_response['TotalRows'] ?? 0;
            
            $allAlerts = array_merge($allAlerts, $currentAlerts);
            $pageNumber++;

            // Add a delay after fetching each page of alerts
            if (count($currentAlerts) > 0) { // Only delay if data was actually fetched
                sleep(REQUEST_DELAY_SECONDS);
            }

        } while (count($allAlerts) < $totalAlertsExpected && count($currentAlerts) === DEFAULT_PAGE_SIZE);

        $output['customer_data'][$customerCode]['devices'][$deviceKey]['alerts'] = [
            'total_alerts' => count($allAlerts),
            'data'         => $allAlerts
        ];
    }
}

// Clear any buffered output (warnings, notices, etc.) before echoing the final JSON
ob_clean();
// Output the final collected data as a pretty-printed JSON response
echo json_encode($output, JSON_PRETTY_PRINT);

// End output buffering and send the content to the browser
ob_end_flush();
?>
