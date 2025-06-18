<?php
// Start output buffering immediately to capture any unintended output (like warnings or notices).
// This is crucial to prevent "SyntaxError: JSON.parse: unexpected character" from non-JSON output.
ob_start();

// Set PHP's execution time limit to unlimited.
// IMPORTANT: This only affects PHP's internal timeout. Server-level timeouts (e.g., Apache, Nginx, load balancers)
// must be configured separately by your hosting provider if you still experience "Request Timeout" errors when accessing via HTTP.
set_time_limit(0);

// Include the Redis cache helper functions.
// This path assumes 'redis.php' is located in an 'includes' subdirectory relative to this script.
require_once __DIR__ . '/includes/redis.php'; 

// Configure PHP error reporting and logging.
// 'display_errors' should be '0' in a production environment to prevent sensitive information disclosure.
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
            $env[trim($key)] = trim($val); // Store the trimmed key-value pair.
        }
        return $env;
    }
}

/**
 * Retrieves an access token from the API's token endpoint.
 * This function incorporates caching using Redis to minimize redundant API calls.
 * It's adapted from the token logic found in the provided `get_customers.php` file.
 *
 * @param array $env Associative array containing environment variables (CLIENT_ID, CLIENT_SECRET, etc.).
 * @return string The access token string.
 */
if (!function_exists('get_token')) {
    function get_token($env) {
        $cacheKey = 'mpsm:api:token';
        // Attempt to retrieve the token from Redis cache first.
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

        // Prepare the POST fields for the OAuth 2.0 token request (grant_type=password).
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

        // Cache the obtained token in Redis. Token TTL is 3500 seconds (just under 1 hour).
        setCache($cacheKey, $json['access_token'], 3500);
        error_log("New token fetched and cached.");
        return $json['access_token'];
    }
}

/**
 * Generic helper function to make API calls to the MPS Monitor API.
 * This function integrates Redis caching and is designed to be robust against
 * cURL errors, invalid JSON, and non-200 HTTP codes.
 * It handles both POST (with JSON payload) and GET requests.
 *
 * @param string $url The API endpoint URL to call.
 * @param string $token The Bearer access token for authorization.
 * @param array $payload The associative array representing the request body for POST. For GET, it will be ignored.
 * @param string $method The HTTP method for the request ('POST' or 'GET', default 'POST').
 * @param int $cacheTtl Time-to-live for the cached response in seconds (default 3600s = 1 hour).
 * Set to 0 to disable caching for a specific call.
 * @return array The decoded JSON response from the API, or a structured error array.
 */
if (!function_exists('call_api')) {
    function call_api($url, $token, $payload = [], $method = 'POST', $cacheTtl = 3600) {
        // Generate a unique cache key based on URL, payload (if POST), and method.
        $cacheData = ($method === 'POST') ? json_encode($payload) : '';
        $cacheKey = 'mpsm:api:' . md5($url . $cacheData . $method);

        // Attempt to retrieve the response from Redis cache first if caching is enabled.
        if ($cacheTtl > 0 && ($cachedResponse = getCache($cacheKey))) {
            $decodedCache = json_decode($cachedResponse, true);
            // Validate that the cached data is a valid array.
            if (is_array($decodedCache)) {
                error_log("API response for $url (hash: " . md5($cacheData) . ") retrieved from cache.");
                return $decodedCache;
            } else {
                // Log and purge corrupted cache entries to ensure fresh data fetch next time.
                error_log("Corrupted cached data for key: $cacheKey. Purging and fetching new data.");
                purgeCache($cacheKey);
            }
        }
        error_log("API response for $url (hash: " . md5($cacheData) . ") not in cache, fetching new data.");

        // Initialize cURL session for the API call.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string.
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ // Set required HTTP headers.
            "Authorization: Bearer $token",
            "Accept: application/json" // Always accept JSON response.
        ]);

        // Configure for POST or GET requests.
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(curl_getinfo($ch, CURLINFO_HEADER_OUT), ["Content-Type: application/json"]));
        } else { // Assume GET if not POST
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); // Explicitly set GET method
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

        // If the API call was successful and caching is enabled, cache the response.
        // This is synchronous, so the cache operation completes before the function returns.
        if ($cacheTtl > 0) {
            setCache($cacheKey, json_encode($json), $cacheTtl);
            error_log("API response for $url (hash: " . md5($cacheData) . ") cached successfully.");
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

    $customers_response = call_api($customers_api_url, $token, $customers_payload, 'POST');
    // Caching is handled inside call_api before it returns.

    if (isset($customers_response['error'])) {
        $output['customers_fetch_status'] = $customers_response;
        error_log("Customer fetch error: " . json_encode($customers_response));
        ob_clean();
        echo json_encode($output, JSON_PRETTY_PRINT);
        ob_end_flush();
        exit;
    }

    $currentCustomers = $customers_response['Result'] ?? []; 
    $totalCustomersExpected = $customers_response['TotalRows'] ?? 0;

    $allCustomers = array_merge($allCustomers, $currentCustomers);
    $pageNumber++;

    error_log("Fetched Customer Page " . ($pageNumber - 1) . ". Total customers so far: " . count($allCustomers) . ". Expected: " . $totalCustomersExpected);

    if (count($currentCustomers) > 0 && (count($allCustomers) < $totalCustomersExpected || $pageNumber == 2)) { 
        sleep(REQUEST_DELAY_SECONDS); // Delay before potentially fetching the next page
    }

} while (count($allCustomers) < $totalCustomersExpected && count($currentCustomers) === DEFAULT_PAGE_SIZE);

$output['customers_fetch_status'] = [
    'message'             => 'Successfully fetched customers',
    'total_customers_found' => count($allCustomers)
];
$output['customer_data'] = [];
error_log("Finished fetching all customers. Total: " . count($allCustomers));

// --- STEP 2: Iterate through Customers and Fetch Devices + Device Details ---
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

    // Fetch Devices for the current customer with Pagination. (Using /Device/List as it's for multiple devices)
    error_log("  Fetching Devices for customer: $customerCode");
    $devices_api_url = $apiBaseUrl . '/Device/List';
    $allDevices = [];
    $pageNumber = 1;
    $totalDevicesExpected = PHP_INT_MAX;

    do {
        $devices_payload = [
            'FilterDealerId'      => $dealerId,          // Uses DEALER_ID from .env.
            'FilterCustomerCodes' => [$customerCode],     // Uses CustomerCode from current customer.
            'ProductBrand'        => null,
            'ProductModel'        => null,
            'OfficeId'            => null,
            'Status'              => 1,                 // Filter for active devices.
            'FilterText'          => null,
            'PageNumber'          => $pageNumber,
            'PageRows'            => DEFAULT_PAGE_SIZE,
            'SortColumn'          => 'Id',
            'SortOrder'           => 0
        ];

        $devices_response = call_api($devices_api_url, $token, $devices_payload, 'POST');
        // Caching is handled inside call_api before it returns.

        if (isset($devices_response['error'])) {
            $output['customer_data'][$customerCode]['devices_fetch_error'] = $devices_response;
            error_log("  Device fetch error for customer $customerCode: " . json_encode($devices_response));
            break; // Break pagination loop for this customer's devices.
        }

        $currentDevices = $devices_response['Result'] ?? [];
        $totalDevicesExpected = $devices_response['TotalRows'] ?? 0;

        $allDevices = array_merge($allDevices, $currentDevices);
        $pageNumber++;
        error_log("  Fetched Device Page " . ($pageNumber - 1) . " for customer $customerCode. Total devices so far: " . count($allDevices) . ". Expected: " . $totalDevicesExpected);

        if (count($currentDevices) > 0 && (count($allDevices) < $totalDevicesExpected || $pageNumber == 2)) {
            sleep(REQUEST_DELAY_SECONDS); // Delay before potentially fetching the next page
        }

    } while (count($allDevices) < $totalDevicesExpected && count($currentDevices) === DEFAULT_PAGE_SIZE);

    $output['customer_data'][$customerCode]['total_devices_found'] = count($allDevices);
    error_log("  Finished fetching all devices for customer $customerCode. Total: " . count($allDevices));
    
    // --- STEP 3: Iterate through Devices and Fetch Detailed Device Data ---
    foreach ($allDevices as $device) {
        if (!is_array($device)) {
            error_log("    Skipping non-array device entry for customer $customerCode: " . json_encode($device));
            continue;
        }

        $serialNumber = $device['SerialNumber'] ?? null;
        $assetNumber = $device['AssetNumber'] ?? null;
        $deviceId = $device['Id'] ?? null; // Assume Device ID might be in the response too.

        if ($serialNumber === null && $assetNumber === null && $deviceId === null) {
            error_log("    Skipping device due to missing identifiers (SerialNumber, AssetNumber, Id) for customer $customerCode: " . json_encode($device));
            continue;
        }
        $deviceKey = $serialNumber ?? $assetNumber ?? $deviceId; // Use the most reliable identifier as key.

        $output['customer_data'][$customerCode]['devices'][$deviceKey] = [
            'description'   => $device['Description'] ?? 'N/A',
            'asset_number'  => $assetNumber,
            'serial_number' => $serialNumber,
            'device_id'     => $deviceId,
            'details'       => [] // Group all specific device details here
        ];
        error_log("    Processing Device: $deviceKey - " . ($device['Description'] ?? 'N/A') . " for customer $customerCode.");

        // Add a delay before fetching details for this specific device.
        sleep(REQUEST_DELAY_SECONDS);

        // Define a general payload for single device endpoints, preferring SerialNumber
        $single_device_payload = ['SerialNumber' => $serialNumber];
        if ($serialNumber === null) {
            $single_device_payload = ['DeviceId' => $deviceId]; // Fallback to DeviceId if SerialNumber isn't there
        }
        // Also add CustomerCode to payloads where relevant (most Device endpoints accept it)
        $single_device_payload['CustomerCode'] = $customerCode;

        // --- Fetch Device Information (/Device/Get) ---
        // This is a POST request.
        $url = $apiBaseUrl . '/Device/Get';
        $response = call_api($url, $token, ['SerialNumber' => $serialNumber ?? $assetNumber], 'POST');
        $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['info'] = isset($response['error']) ? $response : ($response['Result'] ?? []);
        error_log("      Fetched /Device/Get for device $deviceKey.");
        sleep(REQUEST_DELAY_SECONDS);

        // --- Fetch Device Dashboard (/Device/GetDeviceDashboard) ---
        // This is a GET request. Parameters are passed as query string if needed.
        // As per Swagger, takes CustomerCode and SerialNumber as query params.
        $queryParams = http_build_query([
            'CustomerCode' => $customerCode,
            'SerialNumber' => $serialNumber ?? $assetNumber
        ]);
        $url = $apiBaseUrl . '/Device/GetDeviceDashboard?' . $queryParams;
        $response = call_api($url, $token, [], 'GET'); // No payload for GET.
        $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['dashboard'] = isset($response['error']) ? $response : ($response['Result'] ?? []);
        error_log("      Fetched /Device/GetDeviceDashboard for device $deviceKey.");
        sleep(REQUEST_DELAY_SECONDS);

        // --- Fetch Device Alerts (/Device/GetDeviceAlerts) ---
        // This is a POST request, paginated.
        $alerts_url = $apiBaseUrl . '/Device/GetDeviceAlerts';
        $allDeviceAlerts = [];
        $alertPage = 1;
        $totalDeviceAlertsExpected = PHP_INT_MAX;
        do {
            $alert_payload = [
                'CustomerCode' => $customerCode,
                'SerialNumber' => $serialNumber,
                'PageNumber'   => $alertPage,
                'PageRows'     => DEFAULT_PAGE_SIZE,
                'SortColumn'   => 'CreationDate',
                'SortOrder'    => 1 // Descending
            ];
            $response = call_api($alerts_url, $token, $alert_payload, 'POST');
            if (isset($response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['alerts_error'] = $response;
                error_log("        Device Alerts fetch error for device $deviceKey: " . json_encode($response));
                break;
            }
            $currentAlerts = $response['Result'] ?? [];
            $totalDeviceAlertsExpected = $response['TotalRows'] ?? 0;
            $allDeviceAlerts = array_merge($allDeviceAlerts, $currentAlerts);
            $alertPage++;
            if (count($currentAlerts) > 0 && (count($allDeviceAlerts) < $totalDeviceAlertsExpected || $alertPage == 2)) {
                sleep(REQUEST_DELAY_SECONDS);
            }
        } while (count($allDeviceAlerts) < $totalDeviceAlertsExpected && count($currentAlerts) === DEFAULT_PAGE_SIZE);
        $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['alerts'] = [
            'total' => count($allDeviceAlerts), 'data' => $allDeviceAlerts
        ];
        error_log("      Fetched /Device/GetDeviceAlerts for device $deviceKey. Total: " . count($allDeviceAlerts));
        sleep(REQUEST_DELAY_SECONDS);

        // --- Fetch Available Supplies (/Device/GetAvailableSupplies) ---
        // This is a POST request.
        $url = $apiBaseUrl . '/Device/GetAvailableSupplies';
        $response = call_api($url, $token, $single_device_payload, 'POST');
        $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['available_supplies'] = isset($response['error']) ? $response : ($response['Result'] ?? []);
        error_log("      Fetched /Device/GetAvailableSupplies for device $deviceKey.");
        sleep(REQUEST_DELAY_SECONDS);

        // --- Fetch Supply Alerts (/Device/GetSupplyAlerts) ---
        // This is a POST request, paginated.
        $supply_alerts_url = $apiBaseUrl . '/Device/GetSupplyAlerts';
        $allSupplyAlerts = [];
        $supplyAlertPage = 1;
        $totalSupplyAlertsExpected = PHP_INT_MAX;
        do {
            $supply_alert_payload = [
                'CustomerCode' => $customerCode,
                'SerialNumber' => $serialNumber,
                'PageNumber'   => $supplyAlertPage,
                'PageRows'     => DEFAULT_PAGE_SIZE,
                'SortColumn'   => 'CreationDate',
                'SortOrder'    => 1 // Descending
            ];
            $response = call_api($supply_alerts_url, $token, $supply_alert_payload, 'POST');
            if (isset($response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['supply_alerts_error'] = $response;
                error_log("        Supply Alerts fetch error for device $deviceKey: " . json_encode($response));
                break;
            }
            $currentSupplyAlerts = $response['Result'] ?? [];
            $totalSupplyAlertsExpected = $response['TotalRows'] ?? 0;
            $allSupplyAlerts = array_merge($allSupplyAlerts, $currentSupplyAlerts);
            $supplyAlertPage++;
            if (count($currentSupplyAlerts) > 0 && (count($allSupplyAlerts) < $totalSupplyAlertsExpected || $supplyAlertPage == 2)) {
                sleep(REQUEST_DELAY_SECONDS);
            }
        } while (count($allSupplyAlerts) < $totalSupplyAlertsExpected && count($currentSupplyAlerts) === DEFAULT_PAGE_SIZE);
        $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['supply_alerts'] = [
            'total' => count($allSupplyAlerts), 'data' => $allSupplyAlerts
        ];
        error_log("      Fetched /Device/GetSupplyAlerts for device $deviceKey. Total: " . count($allSupplyAlerts));
        sleep(REQUEST_DELAY_SECONDS);

        // --- Fetch Maintenance Alerts (/Device/GetMaintenanceAlerts) ---
        // This is a POST request, paginated.
        $maintenance_alerts_url = $apiBaseUrl . '/Device/GetMaintenanceAlerts';
        $allMaintenanceAlerts = [];
        $maintenanceAlertPage = 1;
        $totalMaintenanceAlertsExpected = PHP_INT_MAX;
        do {
            $maintenance_alert_payload = [
                'CustomerCode' => $customerCode,
                'SerialNumber' => $serialNumber,
                'PageNumber'   => $maintenanceAlertPage,
                'PageRows'     => DEFAULT_PAGE_SIZE,
                'SortColumn'   => 'CreationDate',
                'SortOrder'    => 1 // Descending
            ];
            $response = call_api($maintenance_alerts_url, $token, $maintenance_alert_payload, 'POST');
            if (isset($response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['maintenance_alerts_error'] = $response;
                error_log("        Maintenance Alerts fetch error for device $deviceKey: " . json_encode($response));
                break;
            }
            $currentMaintenanceAlerts = $response['Result'] ?? [];
            $totalMaintenanceAlertsExpected = $response['TotalRows'] ?? 0;
            $allMaintenanceAlerts = array_merge($allMaintenanceAlerts, $currentMaintenanceAlerts);
            $maintenanceAlertPage++;
            if (count($currentMaintenanceAlerts) > 0 && (count($allMaintenanceAlerts) < $totalMaintenanceAlertsExpected || $maintenanceAlertPage == 2)) {
                sleep(REQUEST_DELAY_SECONDS);
            }
        } while (count($allMaintenanceAlerts) < $totalMaintenanceAlertsExpected && count($currentMaintenanceAlerts) === DEFAULT_PAGE_SIZE);
        $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['maintenance_alerts'] = [
            'total' => count($allMaintenanceAlerts), 'data' => $allMaintenanceAlerts
        ];
        error_log("      Fetched /Device/GetMaintenanceAlerts for device $deviceKey. Total: " . count($allMaintenanceAlerts));
        sleep(REQUEST_DELAY_SECONDS);

        // --- Fetch Device Data History (/Device/GetDeviceDataHistory) ---
        // This is a POST request, paginated. Needs StartDate/EndDate.
        $data_history_url = $apiBaseUrl . '/Device/GetDeviceDataHistory';
        $allDataHistory = [];
        $dataHistoryPage = 1;
        $totalDataHistoryExpected = PHP_INT_MAX;
        // Fetch data for the last 30 days as an example
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        do {
            $data_history_payload = [
                'CustomerCode' => $customerCode,
                'SerialNumber' => $serialNumber,
                'StartDate'    => $startDate,
                'EndDate'      => $endDate,
                'PageNumber'   => $dataHistoryPage,
                'PageRows'     => DEFAULT_PAGE_SIZE
            ];
            $response = call_api($data_history_url, $token, $data_history_payload, 'POST');
            if (isset($response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['data_history_error'] = $response;
                error_log("        Data History fetch error for device $deviceKey: " . json_encode($response));
                break;
            }
            $currentDataHistory = $response['Result'] ?? [];
            $totalDataHistoryExpected = $response['TotalRows'] ?? 0;
            $allDataHistory = array_merge($allDataHistory, $currentDataHistory);
            $dataHistoryPage++;
            if (count($currentDataHistory) > 0 && (count($allDataHistory) < $totalDataHistoryExpected || $dataHistoryPage == 2)) {
                sleep(REQUEST_DELAY_SECONDS);
            }
        } while (count($allDataHistory) < $totalDataHistoryExpected && count($currentDataHistory) === DEFAULT_PAGE_SIZE);
        $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['data_history'] = [
            'total' => count($allDataHistory), 'data' => $allDataHistory
        ];
        error_log("      Fetched /Device/GetDeviceDataHistory for device $deviceKey. Total: " . count($allDataHistory));
        sleep(REQUEST_DELAY_SECONDS);

        // --- Fetch Device Chart Data (/Device/GetDeviceChart) ---
        // This is a POST request, paginated. Needs date range.
        $chart_url = $apiBaseUrl . '/Device/GetDeviceChart';
        $allChartData = [];
        $chartPage = 1;
        $totalChartDataExpected = PHP_INT_MAX;
        do {
            $chart_payload = [
                'CustomerCode' => $customerCode,
                'SerialNumber' => $serialNumber,
                'StartDate'    => $startDate, // Using same date range as data history
                'EndDate'      => $endDate,
                'PageNumber'   => $chartPage,
                'PageRows'     => DEFAULT_PAGE_SIZE,
                'PrinterCounterIds' => null // Fetching all available counters for simplicity
            ];
            $response = call_api($chart_url, $token, $chart_payload, 'POST');
            if (isset($response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['chart_data_error'] = $response;
                error_log("        Chart Data fetch error for device $deviceKey: " . json_encode($response));
                break;
            }
            $currentChartData = $response['Result'] ?? [];
            $totalChartDataExpected = $response['TotalRows'] ?? 0;
            $allChartData = array_merge($allChartData, $currentChartData);
            $chartPage++;
            if (count($currentChartData) > 0 && (count($allChartData) < $totalChartDataExpected || $chartPage == 2)) {
                sleep(REQUEST_DELAY_SECONDS);
            }
        } while (count($allChartData) < $totalChartDataExpected && count($currentChartData) === DEFAULT_PAGE_SIZE);
        $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['chart_data'] = [
            'total' => count($allChartData), 'data' => $allChartData
        ];
        error_log("      Fetched /Device/GetDeviceChart for device $deviceKey. Total: " . count($allChartData));
        sleep(REQUEST_DELAY_SECONDS);

        // --- Fetch Errors Messages Data History (/Device/GetErrorsMessagesDataHistory) ---
        // This is a POST request, paginated. Needs date range.
        $errors_history_url = $apiBaseUrl . '/Device/GetErrorsMessagesDataHistory';
        $allErrorsHistory = [];
        $errorsHistoryPage = 1;
        $totalErrorsHistoryExpected = PHP_INT_MAX;
        do {
            $errors_history_payload = [
                'CustomerCode' => $customerCode,
                'SerialNumber' => $serialNumber,
                'StartDate'    => $startDate,
                'EndDate'      => $endDate,
                'PageNumber'   => $errorsHistoryPage,
                'PageRows'     => DEFAULT_PAGE_SIZE
            ];
            $response = call_api($errors_history_url, $token, $errors_history_payload, 'POST');
            if (isset($response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['errors_history_error'] = $response;
                error_log("        Errors History fetch error for device $deviceKey: " . json_encode($response));
                break;
            }
            $currentErrorsHistory = $response['Result'] ?? [];
            $totalErrorsHistoryExpected = $response['TotalRows'] ?? 0;
            $allErrorsHistory = array_merge($allErrorsHistory, $currentErrorsHistory);
            $errorsHistoryPage++;
            if (count($currentErrorsHistory) > 0 && (count($allErrorsHistory) < $totalErrorsHistoryExpected || $errorsHistoryPage == 2)) {
                sleep(REQUEST_DELAY_SECONDS);
            }
        } while (count($allErrorsHistory) < $totalErrorsHistoryExpected && count($currentErrorsHistory) === DEFAULT_PAGE_SIZE);
        $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['errors_history'] = [
            'total' => count($allErrorsHistory), 'data' => $allErrorsHistory
        ];
        error_log("      Fetched /Device/GetErrorsMessagesDataHistory for device $deviceKey. Total: " . count($allErrorsHistory));
        sleep(REQUEST_DELAY_SECONDS);

        // --- Fetch Attributes Data History (/Device/GetAttributesDataHistory) ---
        // This is a POST request, paginated. Needs date range.
        $attributes_history_url = $apiBaseUrl . '/Device/GetAttributesDataHistory';
        $allAttributesHistory = [];
        $attributesHistoryPage = 1;
        $totalAttributesHistoryExpected = PHP_INT_MAX;
        do {
            $attributes_history_payload = [
                'CustomerCode' => $customerCode,
                'SerialNumber' => $serialNumber,
                'StartDate'    => $startDate,
                'EndDate'      => $endDate,
                'PageNumber'   => $attributesHistoryPage,
                'PageRows'     => DEFAULT_PAGE_SIZE,
                'AttributeIds' => null // Fetching all available attributes for simplicity
            ];
            $response = call_api($attributes_history_url, $token, $attributes_history_payload, 'POST');
            if (isset($response['error'])) {
                $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['attributes_history_error'] = $response;
                error_log("        Attributes History fetch error for device $deviceKey: " . json_encode($response));
                break;
            }
            $currentAttributesHistory = $response['Result'] ?? [];
            $totalAttributesHistoryExpected = $response['TotalRows'] ?? 0;
            $allAttributesHistory = array_merge($allAttributesHistory, $currentAttributesHistory);
            $attributesHistoryPage++;
            if (count($currentAttributesHistory) > 0 && (count($allAttributesHistory) < $totalAttributesHistoryExpected || $attributesHistoryPage == 2)) {
                sleep(REQUEST_DELAY_SECONDS);
            }
        } while (count($allAttributesHistory) < $totalAttributesHistoryExpected && count($currentAttributesHistory) === DEFAULT_PAGE_SIZE);
        $output['customer_data'][$customerCode]['devices'][$deviceKey]['details']['attributes_history'] = [
            'total' => count($allAttributesHistory), 'data' => $allAttributesHistory
        ];
        error_log("      Fetched /Device/GetAttributesDataHistory for device $deviceKey. Total: " . count($allAttributesHistory));
        sleep(REQUEST_DELAY_SECONDS);
    }
}

// Clear any buffered output (warnings, notices, etc.) before echoing the final JSON.
ob_clean();
// Output the final collected data as a pretty-printed JSON response.
echo json_encode($output, JSON_PRETTY_PRINT);

// End output buffering and send the content to the browser.
ob_end_flush();
?>
