<?php
/**
 * functions.php
 *
 * MPSM Dashboard helper library:
 *  - Output buffering to prevent stray output
 *  - Debug logging (debug_log), mirrored to footer via $GLOBALS['debug_messages']
 *  - Template partial inclusion (include_partial)
 *  - Data sanitization (sanitize_html, sanitize_url, sanitize_int)
 *  - OAuth2 password-grant token management (loadEnv, loadCachedToken, cacheToken, requestNewToken, getAccessToken)
 *  - JSON response helper (respond_json)
 *  - Customer list fetcher (fetch_customers) – now defaults to DEALER_CODE from .env
 *
 * PHP 8.2+ required.
 */

// -----------------------------------------------------------------------------
//  Output Buffering
// -----------------------------------------------------------------------------
if (function_exists('ob_start')) {
    ob_start();
}

// -----------------------------------------------------------------------------
//  Global Debug Log Storage
// -----------------------------------------------------------------------------
$debug_log_entries = [];
if (! isset($GLOBALS['debug_messages'])) {
    $GLOBALS['debug_messages'] = [];
}

// -----------------------------------------------------------------------------
//  Debug Logging
// -----------------------------------------------------------------------------
function debug_log(string $message, string $level = 'INFO'): void
{
    global $debug_log_entries;

    $level = strtoupper($level);
    $logLevels = defined('DEBUG_LOG_LEVELS') ? DEBUG_LOG_LEVELS : [];

    $shouldLog =
        in_array($level, ['ERROR','CRITICAL','SECURITY'], true)
        || (
            defined('DEBUG_MODE')
            && DEBUG_MODE === true
            && isset($logLevels[$level])
            && $logLevels[$level] === true
        );
    if (! $shouldLog) {
        return;
    }

    $entry = [
        'time'    => date('Y-m-d H:i:s'),
        'level'   => $level,
        'message' => $message,
    ];
    $debug_log_entries[] = $entry;

    // Mirror to footer array
    $formatted = "[{$entry['time']}] [{$entry['level']}] {$entry['message']}";
    $GLOBALS['debug_messages'][] = $formatted;

    // Optional file logging
    if (defined('DEBUG_LOG_TO_FILE') && DEBUG_LOG_TO_FILE && defined('DEBUG_LOG_FILE')) {
        $filePath = DEBUG_LOG_FILE;
        $dir      = dirname($filePath);

        if (! is_dir($dir)) {
            if (! mkdir($dir, 0755, true)) {
                error_log("Failed to create log directory: {$dir}");
                goto skip_file;
            }
        }

        if (defined('MAX_DEBUG_LOG_SIZE_MB') && MAX_DEBUG_LOG_SIZE_MB > 0
            && file_exists($filePath)
            && filesize($filePath)/(1024*1024) > MAX_DEBUG_LOG_SIZE_MB
        ) {
            file_put_contents($filePath, "--- Log truncated ---\n", LOCK_EX);
        }

        file_put_contents($filePath, $formatted . "\n", FILE_APPEND | LOCK_EX);
    }
    skip_file:

    if (in_array($level, ['ERROR','CRITICAL','SECURITY'], true)) {
        error_log("[MPSM_APP_LOG][{$level}] {$message}");
    }
}

// -----------------------------------------------------------------------------
//  Template Partial Inclusion
// -----------------------------------------------------------------------------
function include_partial(string $relativePath, array $data = []): bool
{
    $fullPath = APP_BASE_PATH . $relativePath;
    if (! file_exists($fullPath)) {
        debug_log("Partial not found: {$fullPath}", 'WARNING');
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "<div class='warning-banner'>WARNING: Partial '{$relativePath}' missing.</div>";
        }
        return false;
    }
    extract($data, EXTR_SKIP);
    include $fullPath;
    debug_log("Included partial: {$relativePath}", 'DEBUG');
    return true;
}

// -----------------------------------------------------------------------------
//  Data Sanitization
// -----------------------------------------------------------------------------
function sanitize_html(string $input): string
{
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function sanitize_url(string $input): string
{
    $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $input);
    $slug = preg_replace('/[-_]+/', '-', $slug);
    return strtolower(trim($slug, '-_'));
}

function sanitize_int($input): int
{
    $val = filter_var($input, FILTER_VALIDATE_INT);
    return ($val !== false) ? (int)$val : 0;
}

// -----------------------------------------------------------------------------
//  JSON Response Helper
// -----------------------------------------------------------------------------
function respond_json($data): void
{
    if (ob_get_length() !== false) {
        ob_clean();
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// -----------------------------------------------------------------------------
//  OAuth2 Token Management (Password Grant)
// -----------------------------------------------------------------------------
define('ENV_FILE', __DIR__ . '/.env');
define('TOKEN_CACHE_FILE', __DIR__ . '/logs/token_cache.json');

function loadEnv(): void
{
    if (! file_exists(ENV_FILE) || ! is_readable(ENV_FILE)) {
        throw new RuntimeException("Cannot load .env at " . ENV_FILE);
    }
    foreach (file(ENV_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $val] = explode('=', $line, 2) + [1=>''];
        $_ENV[trim($key)] = trim($val);
    }
    debug_log(".env loaded into \$_ENV", 'DEBUG');
}

function loadCachedToken(): ?array
{
    if (! file_exists(TOKEN_CACHE_FILE)) {
        debug_log("Token cache file not found", 'DEBUG');
        return null;
    }
    $raw = file_get_contents(TOKEN_CACHE_FILE);
    if ($raw === false) {
        debug_log("Failed to read token cache file", 'WARNING');
        return null;
    }
    $data = json_decode($raw, true);
    if (! is_array($data) || empty($data['access_token']) || empty($data['expires_at'])) {
        debug_log("Token cache corrupted or incomplete", 'WARNING');
        return null;
    }
    if (time() >= (int)$data['expires_at']) {
        debug_log("Cached token expired at {$data['expires_at']}", 'DEBUG');
        return null;
    }
    debug_log("Using cached token (expires at {$data['expires_at']})", 'DEBUG');
    debug_log("Cached access token: {$data['access_token']}", 'DEBUG');
    return $data;
}

function cacheToken(string $accessToken, int $expiresIn): void
{
    $payload = [
        'access_token'=> $accessToken,
        'expires_at'  => time() + $expiresIn - 30,
    ];
    $dir = dirname(TOKEN_CACHE_FILE);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (file_put_contents(TOKEN_CACHE_FILE, json_encode($payload, JSON_PRETTY_PRINT)) === false) {
        throw new RuntimeException("Failed to write token cache to " . TOKEN_CACHE_FILE);
    }
    debug_log("Cached new token (expires in {$expiresIn} seconds)", 'DEBUG');
}

function requestNewToken(): array
{
    loadEnv();
    foreach (['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','TOKEN_URL'] as $key) {
        if (empty($_ENV[$key])) {
            throw new RuntimeException("Missing \${$key} in .env");
        }
    }
    $form = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => $_ENV['CLIENT_ID'],
        'client_secret' => $_ENV['CLIENT_SECRET'],
        'username'      => $_ENV['USERNAME'],
        'password'      => $_ENV['PASSWORD'],
        'scope'         => $_ENV['SCOPE'] ?? '',
    ]);
    debug_log("Requesting new OAuth2 token from {$_ENV['TOKEN_URL']}", 'DEBUG');
    $ch = curl_init($_ENV['TOKEN_URL']);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $form,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_FAILONERROR    => false,
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("cURL error fetching token: {$err}");
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200) {
        throw new RuntimeException("Token endpoint returned HTTP {$httpCode}: {$response}");
    }
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("Invalid JSON from token endpoint: " . json_last_error_msg());
    }
    debug_log("Received new OAuth2 token response", 'DEBUG');
    debug_log("New access token: {$data['access_token']} (expires_in {$data['expires_in']}s)", 'DEBUG');
    return $data;
}

function getAccessToken(): string
{
    debug_log("getAccessToken() called", 'DEBUG');
    $cached = loadCachedToken();
    if ($cached !== null) {
        debug_log("getAccessToken returning cached token", 'DEBUG');
        return $cached['access_token'];
    }
    $tokenData = requestNewToken();
    if (empty($tokenData['access_token']) || empty($tokenData['expires_in'])) {
        throw new RuntimeException("Token response missing required fields");
    }
    cacheToken($tokenData['access_token'], (int)$tokenData['expires_in']);
    debug_log("getAccessToken returning new token", 'DEBUG');
    return $tokenData['access_token'];
}

// -----------------------------------------------------------------------------
//  Customer List Fetcher (updated)
// -----------------------------------------------------------------------------
/**
 * Fetch a list of customers.
 *
 * If you omit $dealerCode, it will automatically use DEALER_CODE from .env.
 *
 * @param string|null $dealerCode
 * @return array  List of customer arrays, each with 'Code' and 'Description'.
 */
function fetch_customers(string $dealerCode = null): array
{
    // Ensure .env values are loaded (so $_ENV['DEALER_CODE'] is available)
    loadEnv();

    if ($dealerCode === null) {
        if (empty($_ENV['DEALER_CODE'])) {
            throw new RuntimeException("Missing DEALER_CODE in .env");
        }
        $dealerCode = $_ENV['DEALER_CODE'];
        debug_log("fetch_customers defaulting to DEALER_CODE {$dealerCode} from .env", 'DEBUG');
    } else {
        debug_log("fetch_customers using explicit DealerCode {$dealerCode}", 'DEBUG');
    }

    $token   = getAccessToken();
    $url     = MPSM_API_BASE_URL . 'Customer/GetCustomers';
    $payload = [
        'DealerCode' => $dealerCode,
        'Code'       => null,
        'HasHpSds'   => null,
        'FilterText' => null,
        'PageNumber' => 1,
        'PageRows'   => 2147483647,
        'SortColumn' => 'Id',
        'SortOrder'  => 0
    ];

    debug_log("Sending Customer/GetCustomers request: " . json_encode($payload), 'DEBUG');

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ],
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        debug_log("cURL error in fetch_customers: {$err}", 'ERROR');
        return [];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        debug_log("Customer/GetCustomers returned HTTP {$httpCode}: {$response}", 'ERROR');
        return [];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        debug_log("JSON error parsing customers: " . json_last_error_msg(), 'ERROR');
        return [];
    }

    $resultCount = count($data['Result'] ?? []);
    debug_log("fetch_customers returned {$resultCount} entries", 'DEBUG');

    return $data['Result'] ?? [];
}
