<?php
// Fetch & return the array of customers via the Customer/GetCustomers endpoint
function api_get_customers(): array {
    // Build URL to your existing endpoint
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $url    = "{$scheme}://{$host}/api/get_customers.php";

    // Prepare the POST body exactly as the API expects:
    // IssuerDealerCode = your dealer code, pulled from env
    $body = [
        'IssuerDealerCode' => getenv('DEALER_CODE'),
        'PageNumber'       => 1,
        'PageRows'         => 100,               // adjust as needed
        'SortColumn'       => 'Name',
        'SortOrder'        => 'Asc'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST,         true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,   json_encode($body));
    curl_setopt($ch, CURLOPT_HTTPHEADER,   ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT,       10);

    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("api_get_customers CURL error: " . curl_error($ch));
        curl_close($ch);
        return [];
    }
    curl_close($ch);

    $json = json_decode($resp, true);
    if (! is_array($json) || ! isset($json['Result'])) {
        error_log("api_get_customers unexpected response: " . substr($resp, 0, 200));
        return [];
    }

    // The PagedResultResponse has the list under "Result" 
    return $json['Result'];
}
?>
