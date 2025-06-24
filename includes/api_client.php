<?php
// includes/api_client.php
// -------------------------------------------------------------------
// Central HTTP client for MPS Monitor API with automatic token refresh.
// -------------------------------------------------------------------

/**
 * Sends a JSON POST to the downstream MPS Monitor API.
 * If the first request returns 401, it clears the cached token,
 * retries once, then fails if still unauthorized or any other error.
 *
 * @param string $path   e.g. 'Customer/GetCustomers'
 * @param array  $body   PHP array to json_encode
 * @return array         Decoded JSON response
 * @throws RuntimeException on network or JSON error
 */
function api_request(string $path, array $body): array
{
    // Attempt request up to 2 times (first with cached token, second after refresh)
    $attempt = 0;
    $maxAttempts = 2;
    $lastException = null;

    while ($attempt < $maxAttempts) {
        $attempt++;
        // 1) Ensure we have a valid bearer token
        $token = get_bearer_token();

        // 2) Initialize cURL
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

        // 3) Execute
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 4) Network-level error?
        if ($err) {
            $lastException = new RuntimeException("cURL error: {$err}");
            break;
        }

        // 5) Unauthorized (token expired)?
        if ($status === 401 && $attempt === 1) {
            // Clear the cached token so get_bearer_token() fetches a fresh one
            @unlink(__DIR__ . '/../.token_cache.json');
            // retry loop will run again
            continue;
        }

        // 6) Decode JSON
        $data = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $lastException = new RuntimeException('Invalid JSON response: ' . $resp);
            break;
        }

        // 7) Set HTTP status for response proxy
        http_response_code($status);

        // 8) If still unauthorized after retry, treat as error
        if ($status === 401) {
            $lastException = new RuntimeException('Unauthorized after token refresh');
            break;
        }

        // 9) Success or other status (e.g. 200, 400, 502, etc.)
        return $data;
    }

    // If we reach here, all attempts failed
    if ($lastException) {
        throw $lastException;
    }
    throw new RuntimeException('Unknown error in api_request');
}
