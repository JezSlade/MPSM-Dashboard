<?php
// includes/api_client.php
// -------------------------------------------------------------------
// Central HTTP client for MPS Monitor API without sending headers.
// Returns ['status'=>HTTP_CODE,'data'=>Decoded JSON].
// -------------------------------------------------------------------

declare(strict_types=1);

require_once __DIR__ . '/auth.php';  // for get_bearer_token()

/**
 * Sends a JSON POST to the downstream MPS Monitor API.
 * Retries once on 401 by clearing token cache.
 *
 * @param string $path
 * @param array  $body
 * @return array ['status'=>int,'data'=>array]
 * @throws RuntimeException
 */
function api_request(string $path, array $body): array
{
    $maxAttempts = 2;
    $lastException = null;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $token = get_bearer_token();

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

        $resp   = curl_exec($ch);
        $err    = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            $lastException = new RuntimeException("cURL error: {$err}");
            break;
        }

        if ($status === 401 && $attempt === 1) {
            // clear cache and retry
            @unlink(__DIR__ . '/../.token_cache.json');
            continue;
        }

        $data = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $lastException = new RuntimeException('Invalid JSON: ' . $resp);
            break;
        }

        return ['status' => $status, 'data' => $data];
    }

    throw $lastException ?? new RuntimeException('Unknown api_request error');
}
