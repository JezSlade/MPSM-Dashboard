<?php
// api/lib/token_helper.php — Token utility for internal API calls

function get_fresh_token(array $env): string {
    $payload = http_build_query([
        'grant_type' => 'password',
        'client_id' => $env['CLIENT_ID'],
        'client_secret' => $env['CLIENT_SECRET'],
        'username' => $env['USERNAME'],
        'password' => $env['PASSWORD'],
        'scope' => $env['SCOPE']
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $env['TOKEN_URL']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        throw new Exception("Token request failed with status $httpCode");
    }

    $data = json_decode($response, true);
    if (empty($data['access_token'])) {
        throw new Exception("Token missing from response: $response");
    }

    return $data['access_token'];
}
