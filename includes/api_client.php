<?php
// includes/api_client.php

/**
 * Sends a JSON POST to the downstream MPS Monitor API.
 *
 * @param string $path   e.g. 'Customer/GetCustomers'
 * @param array  $body   PHP array to json_encode
 * @return array         Decoded JSON response
 * @throws RuntimeException on network or JSON error
 */
function api_request(string $path, array $body): array
{
    // ensure we have a valid token
    $token = get_bearer_token();

    // init curl
    $ch = curl_init(API_BASE_URL . $path);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_POSTFIELDS     => json_encode($body),
    ]);

    $resp = curl_exec($ch);
    if ($err = curl_error($ch)) {
        curl_close($ch);
        throw new RuntimeException("cURL error: {$err}");
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON response: ' . $resp);
    }

    http_response_code($status);
    return $data;
}
