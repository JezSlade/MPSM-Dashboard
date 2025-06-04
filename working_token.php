<?php
/**
 * File: working_token.php
 * -----------------------
 */

// Replace these constants with your actual Azure AD values.
define('AZURE_TENANT',      'YOUR_TENANT_ID_OR_NAME');
define('AZURE_CLIENT_ID',   'YOUR_CLIENT_ID_HERE');
define('AZURE_CLIENT_SECRET','YOUR_CLIENT_SECRET_HERE');
define('AZURE_RESOURCE',    'https://api.abassetmanagement.com/');
define('TOKEN_CACHE_FILE',  __DIR__ . '/.token_cache.json');
define('AZURE_OAUTH2_URL',  'https://login.microsoftonline.com/' . AZURE_TENANT . '/oauth2/token');

function getAccessToken() {
    if (file_exists(TOKEN_CACHE_FILE)) {
        $cacheData = json_decode(file_get_contents(TOKEN_CACHE_FILE), true);
        if (json_last_error() === JSON_ERROR_NONE
            && isset($cacheData['access_token'], $cacheData['expires_on'])
            && time() < intval($cacheData['expires_on']) - 60
        ) {
            return $cacheData['access_token'];
        }
    }
    $postFields = http_build_query([
        'grant_type'    => 'client_credentials',
        'client_id'     => AZURE_CLIENT_ID,
        'client_secret' => AZURE_CLIENT_SECRET,
        'resource'      => AZURE_RESOURCE
    ]);
    $ch = curl_init(AZURE_OAUTH2_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);
    if ($curlErr) {
        throw new Exception("cURL error obtaining token: {$curlErr}");
    }
    if ($httpCode !== 200) {
        throw new Exception("Azure AD returned HTTP {$httpCode}: $response");
    }
    $tokenData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Failed to parse JSON from Azure AD: $response");
    }
    if (empty($tokenData['access_token']) || empty($tokenData['expires_on'])) {
        throw new Exception("Malformed token response: $response");
    }
    file_put_contents(
        TOKEN_CACHE_FILE,
        json_encode([
            'access_token' => $tokenData['access_token'],
            'expires_on'   => intval($tokenData['expires_on'])
        ]),
        LOCK_EX
    );
    return $tokenData['access_token'];
}
?>