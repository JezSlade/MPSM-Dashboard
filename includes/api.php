<?php
// Fetch & return the list of customers via Customer/GetCustomers
function api_get_customers(): array {
    // Build URL to the real endpoint
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    // Adjust path if your file lives at /api/Customer/GetCustomers.php
    $url    = "{$scheme}://{$host}/api/Customer/GetCustomers.php";

    // Body must match GetCustomersRequest exactly :contentReference[oaicite:0]{index=0}
    $body = [
        'DealerCode' => getenv('DEALER_CODE'),
        'PageNumber' => 1,
        'PageRows'   => 50,
        'SortColumn' => 'Code',
        'SortOrder'  => 'Asc'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST,        true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($body));
    curl_setopt($ch, CURLOPT_HTTPHEADER,  ['Content-Type: application/json']);
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
    // PagedResultResponse[CustomerListDto] puts array under "Result" :contentReference[oaicite:1]{index=1}
    if (!isset($json['Result']) || !is_array($json['Result'])) {
        error_log("api_get_customers unexpected response: " . substr($resp, 0, 200));
        return [];
    }

    return $json['Result'];
}
?>
