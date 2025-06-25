<?php
// Fetch & return the "data" array from your customers API
function api_get_customers(): array {
    // Build URL to your existing endpoint
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $url    = "{$scheme}://{$host}/api/get_customers.php";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // (optional) set timeouts
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("api_get_customers CURL error: " . curl_error($ch));
        curl_close($ch);
        return [];
    }
    curl_close($ch);

    $json = json_decode($resp, true);
    if (! is_array($json) || ! isset($json['data'])) {
        error_log("api_get_customers invalid JSON or missing data key");
        return [];
    }

    return $json['data'];
}
