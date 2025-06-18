<?php
// Start output buffering immediately to capture any unintended output (like warnings or notices).
// This prevents such output from corrupting the final JSON response.
ob_start();

// Set PHP's execution time limit to unlimited.
// IMPORTANT: This only affects PHP's internal timeout. Server-level timeouts (e.g., Apache, Nginx, load balancers)
// must be configured separately by your hosting provider if you still experience "Request Timeout" errors when accessing via HTTP.
set_time_limit(0);

// Include the Redis cache helper functions.
// This path assumes 'redis.php' is located in an 'includes' subdirectory relative to this script.
require_once __DIR__ . '/includes/redis.php'; 

// Configure PHP error reporting and logging.
// For production, 'display_errors' should ideally be '0' to prevent information disclosure.
error_reporting(E_ALL);
ini_set('display_errors', '1'); // Set to '0' for production environments.
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/debug.log'); // Errors will be logged to 'debug.log' in the script's directory.

/**
 * Loads environment variables from the .env file.
 * This function is derived from the provided `get_customers.php` file.
 * It expects the `.env` file to be located in the same directory as this script.
 *
 * @param string $path The full path to the .env file.
 * @return array An associative array of environment variables.
 */
if (!function_exists('load_env')) {
    function load_env($path = __DIR__ . '/.env') {
        $env = [];
        if (!file_exists($path)) {
            // If the .env file is not found, output a structured JSON error and terminate.
            header('Content-Type: application/json');
            echo json_encode(["error" => ".env file not found at " . $path]);
            ob_end_flush(); // Flush any buffered output and disable buffering.
            exit;
        }
        // Read the .env file line by line, ignoring empty lines and newlines.
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            // Skip comment lines (lines starting with '#').
            if (str_starts_with(trim($line), '#')) continue;
            // Split the line into a key and a value at the first '='.
            [$key, $val] = explode('=', $line, 2);
            $env[trim($key)] = trim($val); // Store the trimmed key-value pair in the environment array.
        }
        return $env;
    }
}

/**
 * Retrieves an access token from the API's token endpoint.
 * This function incorporates caching using Redis to reduce redundant API calls.
 * It's adapted from the token logic found in the provided `get_customers.php` file.
 *
 * @param array $env Associative array containing environment variables (CLIENT_ID, CLIENT_SECRET, etc.).
 * @return string The access token string.
 */
if (!function_exists('get_token')) {
    function get_token($env) {
        $cacheKey = 'mpsm:api:token';
        // 1. Attempt to retrieve the token from Redis cache first.
        if ($cachedToken = getCache($cacheKey)) {
            error_log("Token retrieved from cache.");
            return $cachedToken;
        }
        error_log("Token not in cache, fetching new token.");

        // Define the essential environment variables required for the token request.
        $required = ['CLIENT_ID', 'CLIENT_SECRET', 'USERNAME', 'PASSWORD', 'SCOPE', 'TOKEN_URL'];
        foreach ($required as $key) {
            if (empty($env[$key])) {
                // If any required environment variable is missing, output error and exit.
                header('Content-Type: application/json');
                echo json_encode(["error" => "Missing $key in .env for token request"]);
                ob_end_flush();
                exit;
            }
        }

        // Prepare the POST fields for the OAuth 2.0 token request.
        $postFields = http_build_query([
            'client_id'     => $env['CLIENT_ID'],
            'client_secret' => $env['CLIENT_SECRET'],
            'grant_type'    => 'password',
            'username'      => $env['USERNAME'],
            'password'      => $env['PASSWORD'],
            'scope'         => $env['SCOPE']
        ]);

        // Initialize cURL session for the token request.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $env['TOKEN_URL']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string.
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch); // Execute the cURL request.
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the HTTP status code.
        curl_close($ch); // Close the cURL session.

        $json = json_decode($response, true); // Decode the JSON response.

        // Check if the token request was successful (HTTP 200 and 'access_token' present).
        if ($code !== 200 || !isset($json['access_token'])) {
            header('Content-Type: application/json');
            echo json_encode(["error" => "Token request failed", "http_code" => $code, "details" => $json]);
            ob_end_flush();
            exit;
        }

        // 2. Cache the obtained token in Redis. Token TTL is 3500 seconds (just under 1 hour).
        setCache($cacheKey, $json['access_token'], 3500);
        error_log("New token fetched and cached.");
        return $json['access_token'];
    }
}

/**
 * Generic helper function to make API calls to the MPS Monitor API.
 * This function integrates Redis caching and is designed to be robust against
 * cURL errors, invalid JSON, and non-200 HTTP codes.
 * It ensures the response is cached before returning (due to synchronous setCache).
 *
 * @param string $url The API endpoint URL to call.
 * @param string $token The Bearer access token for authorization.
 * @param array $payload The associative array representing the request body.
 * @param string $method The HTTP method for the request (default 'POST').
 * @param int $cacheTtl Time-to-live for the cached response in seconds (default 3600s = 1 hour).
 * Set to 0 to disable caching for a specific call.
 * @return array The decoded JSON response from the API, or a structured error array.
 */
if (!function_exists('call_api')) {
    function call_api($url, $token, $payload, $method = 'POST', $cacheTtl = 3600) {
        // Generate a unique cache key based on URL, payload, and method.
        $cacheKey = 'mpsm:api:' . md5($url . json_encode($payload) . $method);

        // 1. Attempt to retrieve the response from Redis cache first if caching is enabled.
        if ($cacheTtl > 0 && ($cachedResponse = getCache($cacheKey))) {
            $decodedCache = json_decode($cachedResponse, true);
            // Validate that the cached data is a valid array.
            if (is_array($decodedCache)) {
                error_log("API response for $url (payload hash: " . md5(json_encode($payload)) . ") retrieved from cache.");
                return $decodedCache;
            } else {
                // Log and purge corrupted cache entries to ensure fresh data fetch next time.
                error_log("Corrupted cached data for key: $cacheKey. Purging and fetching new data.");
                purgeCache($cacheKey);
            }
        }
        error_log("API response for $url (payload hash: " . md5(json_encode($payload)) . ") not in cache, fetching new data.");

        // Initialize cURL session for the API call.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string.
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ // Set required HTTP headers.
            "Authorization: Bearer $token",
            "Content-Type: application/json",
            "Accept: application/json"
        ]);

        // Configure for POST request if specified.
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response = curl_exec($ch); // Execute the cURL request.
        $curlError = curl_error($ch); // Capture any cURL errors.
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the HTTP status code.
        curl_close($ch); // Close the cURL session.

        // Handle cURL-specific errors (e.g., network connectivity issues).
        if ($curlError) {
             error_log("cURL error on $url: " . $curlError);
             return ["error" => "cURL error", "url" => $url, "details" => $curlError];
        }

        $json = json_decode($response, true); // Decode the API response JSON.

        // Check if JSON decoding failed or if the response body was empty.
        if (!is_array($json) || empty($response)) {
            error_log("API Call Failed to $url: Invalid JSON response or empty body (HTTP Code: $code). Raw Response: " . (empty($response) ? "[EMPTY RESPONSE]" : $response));
            return ["error" => "API call failed: Invalid or empty response format", "url" => $url, "http_code" => $code, "raw_response" => $response];
        }

        // Check for non-200 HTTP status codes, indicating an API-specific error.
        if ($code !== 200) {
            error_log("API Call Failed to $url with non-200 HTTP code $code: " . json_encode($json));
            return ["error" => "API call failed", "url" => $url, "http_code" => $code, "details" => $json];
        }

        // 2. If the API call was successful and caching is enabled, cache the response.
        // This is synchronous, so the cache operation completes before the function returns.
        if ($cacheTtl > 0) {
            setCache($cacheKey, json_encode($json), $cacheTtl);
            error_log("API response for $url (payload hash: " . md5(json_encode($payload)) . ") cached successfully.");
        }

        return $json; // Return the successfully decoded JSON response array.
    }
}

// Set the Content-Type header to application/json for the script's final output.
header('Content-Type: application/json');

// --- Main execution flow starts here ---

// 1. Load environment variables from the .env file.
$env = load_env();
error_log("Environment variables loaded.");

// 2. Obtain the access token for API authorization.
$token = get_token($env);
error_log("API token obtained and/or retrieved from cache. Proceeding.");

// Retrieve critical configuration variables from .env.
$dealerCode = $env['DEALER_CODE'] ?? null;
$dealerId = $env['DEALER_ID'] ?? null;
$apiBaseUrl = rtrim($env['API_BASE_URL'] ?? '', '/'); // Ensure trailing slash is removed.

// Validate that essential environment variables are defined.
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
error_log("DealerCode and DealerID validated.");

// Define configuration constants for API calls and delays.
const DEFAULT_PAGE_SIZE = 100;      // Number of items to fetch per API page.
const REQUEST_DELAY_SECONDS = 1;    // Delay in seconds between API calls to prevent overwhelming server.

// Initialize an array to store all collected data for the final JSON output.
$output = [];

// --- STEP 1: Fetch Customers with Pagination ---
// This is the first call, providing 'CustomerCode' for subsequent steps.
error_log("Initiating fetch for Customers.");
$customers_api_url = $apiBaseUrl . '/Customer/GetCustomers';
$allCustomers = [];
$pageNumber = 1;
$totalCustomersExpected = PHP_INT_MAX; 

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
        'SortOrder'   => 0 // Ascending
    ];

    $customers_response = call_api($customers_api_url, $token, $customers_payload);
    // CRITICAL: call_api ensures caching (or cache retrieval) is done before it returns.

    // Check for API errors during customer fetching.
    if (isset($customers_response['error'])) {
        $output['customers_fetch_status'] = $customers_response;
        error_log("Customer fetch error: " . json_encode($customers_response));
        ob_clean(); // Clear any buffered output (warnings/notices) and then flush the JSON error.
        echo json_encode($output, JSON_PRETTY_PRINT);
        ob_end_flush();
        exit;
    }

    $currentCustomers = $customers_response['Result'] ?? []; 
    $totalCustomersExpected = $customers_response['TotalRows'] ?? 0;

    $allCustomers = array_merge($allCustomers, $currentCustomers);
    $pageNumber++;

    error_log("Fetched Customer Page $pageNumber-1. Total customers so far: " . count($allCustomers) . ". Expected: " . $totalCustomersExpected);

    // Add a delay after fetching each page of customers to space out requests.
    if (count($currentCustomers) > 0) { 
        sleep(REQUEST_DELAY_SECONDS);
    }

} while (count($allCustomers) < $totalCustomersExpected && count($currentCustomers) === DEFAULT_PAGE_SIZE);

$output['customers_fetch_status'] = [
    'message'             => 'Successfully fetched customers',
    'total_customers_found' => count($allCustomers)
];
$output['customer_data'] = [];
error_log("Finished fetching all customers. Total: " . count($allCustomers));

// --- STEP 2: Iterate through Customers and Fetch Devices ---
foreach ($allCustomers as $customer) {
    if (!is_array($customer)) {
        error_log("Skipping non-array customer entry: " . json_encode($customer));
        continue;
    }

    $customerCode = $customer['Code'] ?? null;

    if ($customerCode === null) {
        error_log("Skipping customer due to missing 'Code': " . json_encode($customer));
        continue;
    }

    $output['customer_data'][$customerCode] = [
        'customer_name' => $customer['Description'] ?? 'N/A',
        'devices'       => []
    ];
    error_log("Processing Customer: $customerCode - " . ($customer['Description'] ?? 'N/A'));

    // Add a delay before fetching devices for this customer.
    sleep(REQUEST_DELAY_SECONDS);

    // Fetch Devices for the current customer with Pagination.
    $devices_api_url = $apiBaseUrl . '/Device/List';
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
        // CRITICAL: call_api ensures caching (or cache retrieval) is done before it returns.

        if (isset($devices_response['error'])) {
            $output['customer_data'][$customerCode]['devices_fetch_error'] = $devices_response;
            error_log("Device fetch error for customer $customerCode: " . json_encode($devices_response));
            break; // Break pagination loop for this customer's devices.
        }

        $currentDevices = $devices_response['Result'] ?? [];
        $totalDevicesExpected = $devices_response['TotalRows'] ?? 0;

        $allDevices = array_merge($allDevices, $currentDevices);
        $pageNumber++;
        error_log("  Fetched Device Page $pageNumber-1 for customer $customerCode. Total devices so far: " . count($allDevices) . ". Expected: " . $totalDevicesExpected);

        // Add a delay after fetching each page of devices.
        if (count($currentDevices) > 0) { 
            sleep(REQUEST_DELAY_SECONDS);
        }

    } while (count($allDevices) < $totalDevicesExpected && count($currentDevices) === DEFAULT_PAGE_SIZE);

    $output['customer_data'][$customerCode]['total_devices_found'] = count($allDevices);
    error_log("  Finished fetching all devices for customer $customerCode. Total: " . count($allDevices));
    
    // --- STEP 3: Iterate through Devices and Fetch Counters/Alerts ---
    foreach ($allDevices as $device) {
        if (!is_array($device)) {
            error_log("  Skipping non-array device entry for customer $customerCode: " . json_encode($device));
            continue;
        }

        $serialNumber = $device['SerialNumber'] ?? null;
        $assetNumber = $device['AssetNumber'] ?? null;

        if ($serialNumber === null && $assetNumber === null) {
            error_log("  Skipping device due to missing 'SerialNumber' and 'AssetNumber' for customer $customerCode: " . json_encode($device));
            continue;
        }
        $deviceKey = $serialNumber ?? $assetNumber; // Use SerialNumber or AssetNumber as unique key.

        $output['customer_data'][$customerCode]['devices'][$deviceKey] = [
            'description'   => $device['Description'] ?? 'N/A',
            'asset_number'  => $assetNumber,
            'serial_number' => $serialNumber,
            'counters'      => null,
            'alerts'        => null
        ];
        error_log("  Processing Device: $deviceKey - " . ($device['Description'] ?? 'N/A') . " for customer $customerCode.");

        // Add a delay before fetching data for this specific device.
        sleep(REQUEST_DELAY_SECONDS);

        // Fetch Device Counters for the current device with Pagination.
        $device_counters_api_url = $apiBaseUrl . '/Counter/ListDetailed';
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
            // CRITICAL: call_api ensures caching (or cache retrieval) is done before it returns.

            if (isset($counters_response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['counters_fetch_error'] = $counters_response;
                error_log("    Counters fetch error for device $deviceKey: " . json_encode($counters_response));
                break; // Break pagination loop for this device's counters.
            }
            
            $currentCounters = $counters_response['Result'] ?? [];
            $totalCountersExpected = $counters_response['TotalRows'] ?? 0;
            
            $allCounters = array_merge($allCounters, $currentCounters);
            $pageNumber++;
            error_log("      Fetched Counter Page $pageNumber-1 for device $deviceKey. Total counters so far: " . count($allCounters) . ". Expected: " . $totalCountersExpected);

            // Add a delay after fetching each page of counters.
            if (count($currentCounters) > 0) { 
                sleep(REQUEST_DELAY_SECONDS);
            }

        } while (count($allCounters) < $totalCountersExpected && count($currentCounters) === DEFAULT_PAGE_SIZE);

        $output['customer_data'][$customerCode]['devices'][$deviceKey]['counters'] = [
            'total_counters' => count($allCounters),
            'data'           => $allCounters
        ];
        error_log("    Finished fetching all counters for device $deviceKey. Total: " . count($allCounters));

        // Add a delay before fetching alerts for the current device.
        sleep(REQUEST_DELAY_SECONDS);

        // Fetch Device Alerts for the current customer (with pagination).
        // The API filters alerts by CustomerCode, so we fetch all alerts for the customer.
        $device_alerts_api_url = $apiBaseUrl . '/SupplyAlert/List';
        $allAlerts = [];
        $pageNumber = 1;
        $totalAlertsExpected = PHP_INT_MAX;

        do {
            $device_alerts_payload = [
                'CustomerCode' => $customerCode,
                'PageNumber'   => $pageNumber,
                'PageRows'     => DEFAULT_PAGE_SIZE,
                'SortColumn'   => 'CreationDate',
                'SortOrder'    => 1 // Descending (newest first)
            ];
            $alerts_response = call_api($device_alerts_api_url, $token, $device_alerts_payload);
            // CRITICAL: call_api ensures caching (or cache retrieval) is done before it returns.

            if (isset($alerts_response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['alerts_fetch_error'] = $alerts_response;
                error_log("    Alerts fetch error for customer $customerCode: " . json_encode($alerts_response));
                break; // Break pagination loop for this customer's alerts.
            }

            $currentAlerts = $alerts_response['Result'] ?? [];
            $totalAlertsExpected = $alerts_response['TotalRows'] ?? 0;
            
            $allAlerts = array_merge($allAlerts, $currentAlerts);
            $pageNumber++;
            error_log("      Fetched Alert Page $pageNumber-1 for customer $customerCode. Total alerts so far: " . count($allAlerts) . ". Expected: " . $totalAlertsExpected);

            // Add a delay after fetching each page of alerts.
            if (count($currentAlerts) > 0) { 
                sleep(REQUEST_DELAY_SECONDS);
            }

        } while (count($allAlerts) < $totalAlertsExpected && count($currentAlerts) === DEFAULT_PAGE_SIZE);

        $output['customer_data'][$customerCode]['devices'][$deviceKey]['alerts'] = [
            'total_alerts' => count($allAlerts),
            'data'         => $allAlerts
        ];
        error_log("    Finished fetching all alerts for customer $customerCode. Total: " . count($allAlerts));
    }
}

// Clear any buffered output (warnings, notices, etc.) before echoing the final JSON.
ob_clean();
// Output the final collected data as a pretty-printed JSON response.
echo json_encode($output, JSON_PRETTY_PRINT);

// End output buffering and send the content to the browser.
ob_end_flush();
?>
