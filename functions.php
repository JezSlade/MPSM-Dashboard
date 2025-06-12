<?php
declare(strict_types=1);
/**
 * functions.php – shared helpers: logging, partials, sanitization,
 *                 data fetches, JSON responses, and OAuth2 token handling.
 *
 * Patches applied:
 *  1. Strict types declaration.
 *  2. Error reporting inherited from config.php.
 *  3. Safe bootstrap via require_once.
 *  4. Core helpers: debug_log(), include_partial(), sanitize_html(), sanitize_url().
 *  5. Data fetch: fetch_customers() with cURL error handling.
 *  6. JSON output: respond_json() sets proper headers and exits.
 *  7. Token management:
 *     - loadCachedToken(): returns valid session token if unexpired.
 *     - requestNewToken(): fetches fresh token via OAuth2 client credentials.
 *     - getAccessToken(): orchestrates cache refresh and exception bubbling.
 */

require_once __DIR__ . '/config.php';  // ensures DEBUG_MODE, constants, session, error_reporting

/**
 * debug_log – append debug messages only if DEBUG_MODE is true.
 */
function debug_log(string $message, string $level = 'INFO'): void {
    if (!DEBUG_MODE) {
        return;
    }
    $entry  = sprintf("[%s] %s: %s\n", date('c'), strtoupper($level), $message);
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir) && !mkdir($logDir, 0755, true)) {
        error_log("Cannot create log dir: {$logDir}");
        return;
    }
    file_put_contents("{$logDir}/debug-" . date('Y-m-d') . ".log", $entry, FILE_APPEND);
}

/**
 * include_partial – safely include a PHP partial with scoped variables.
 */
function include_partial(string $relPath, array $vars = []): bool {
    $base = realpath(__DIR__);
    $full = realpath(__DIR__ . '/' . ltrim($relPath, '/'));
    if ($full === false || strpos($full, $base) !== 0) {
        debug_log("Invalid partial path: {$relPath}", 'ERROR');
        return false;
    }
    extract($vars, EXTR_SKIP);
    include $full;
    return true;
}

/**
 * sanitize_html – escape strings for safe HTML output.
 */
function sanitize_html(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * sanitize_url – escape strings safe for URL segments/queries.
 */
function sanitize_url(string $s): string {
    $clean = filter_var($s, FILTER_SANITIZE_URL);
    return $clean === false ? '' : $clean;
}

/**
 * fetch_customers – retrieves customer list via API, with basic error checks.
 */
function fetch_customers(): array {
    $url = API_BASE_URL . 'Customer/GetCustomers';
    $payload = json_encode([
        'DealerCode' => DEALER_CODE,
        'PageNumber' => 1,
        'PageRows'   => DEVICE_PAGE_SIZE,
        'SortColumn' => 'Id',
        'SortOrder'  => 0,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . ($_SESSION['access_token'] ?? ''),
        ],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        debug_log('cURL error (fetch_customers): ' . curl_error($ch), 'ERROR');
        curl_close($ch);
        return [];
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        debug_log("fetch_customers HTTP {$httpCode}", 'ERROR');
        return [];
    }

    $data = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        debug_log('JSON parse error: ' . json_last_error_msg(), 'ERROR');
        return [];
    }

    return $data['Result'] ?? [];
}

/**
 * respond_json – send a clean JSON response and terminate.
 */
function respond_json(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * loadCachedToken – return a valid token from session if not expired.
 */
function loadCachedToken(): ?string {
    if (!empty($_SESSION['access_token']) && !empty($_SESSION['token_expires'])) {
        if (time() < (int)$_SESSION['token_expires']) {
            debug_log('Using cached access token', 'DEBUG');
            return $_SESSION['access_token'];
        }
        debug_log('Cached token expired, refreshing', 'DEBUG');
    }
    return null;
}

/**
 * requestNewToken – perform OAuth2 password grant to get a fresh token.
 *
 * @throws RuntimeException on any error.
 */
function requestNewToken(): string {
    $url  = TOKEN_URL;
    $body = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'username'      => USERNAME,
        'password'      => PASSWORD,
        'scope'         => SCOPE,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("Token request cURL error: {$err}");
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new RuntimeException("Token endpoint HTTP {$httpCode}");
    }

    $data = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Token JSON parse error: ' . json_last_error_msg());
    }
    if (empty($data['access_token']) || empty($data['expires_in'])) {
        throw new RuntimeException('Invalid token response structure');
    }

    // Cache in session
    $_SESSION['access_token']  = $data['access_token'];
    $_SESSION['token_expires'] = time() + (int)$data['expires_in'] - 30; // refresh margin
    debug_log('Fetched new access token; expires in ' . $data['expires_in'] . 's', 'DEBUG');

    return $data['access_token'];
}

/**
 * getAccessToken – return a valid access token, using cache or fresh fetch.
 *
 * @throws Throwable on any failure during token acquire.
 */
function getAccessToken(): string {
    $token = loadCachedToken();
    if ($token !== null) {
        return $token;
    }
    return requestNewToken();
}
// functions.php (add towards the end, before the final “no closing tag” comment)

/**
 * render_card – include one of the cards under /cards by name
 *
 * @param string $partialName  The basename of the card file (without “.php”)
 * @param array  $vars         Variables to pass into that card
 */
function render_card(string $partialName, array $vars = []): void {
    // e.g. "printer_status_card" → include "cards/printer_status_card.php"
    include_partial("cards/{$partialName}.php", $vars);
}

// End of file – no closing PHP tag
