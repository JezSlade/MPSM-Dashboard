<?php
declare(strict_types=1);
/**
 * functions.php – shared helpers: logging, partials, sanitization, API calls.
 */

require_once __DIR__ . '/config.php';  // ensures all constants & session are set

/**
 * debug_log – append debug messages only if DEBUG_MODE is true.
 */
function debug_log(string $message, string $level = 'INFO'): void {
    if (! DEBUG_MODE) {
        return;
    }
    $entry = sprintf("[%s] %s: %s\n", date('c'), strtoupper($level), $message);
    $logDir = __DIR__ . '/logs';
    if (! is_dir($logDir) && ! mkdir($logDir, 0755, true)) {
        error_log("Cannot create log directory: {$logDir}");
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
        debug_log("Partial path traversal or missing: {$relPath}", 'ERROR');
        return false;
    }
    extract($vars, EXTR_SKIP);
    include $full;
    return true;
}

/**
 * sanitize_html – escape user/output strings for HTML contexts.
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
        'SortOrder'  => 0
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . ($_SESSION['access_token'] ?? '')
        ],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        debug_log('cURL error: ' . curl_error($ch), 'ERROR');
        curl_close($ch);
        return [];
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        debug_log("fetch_customers: HTTP {$httpCode}", 'ERROR');
        return [];
    }

    $data = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        debug_log('JSON parse error: ' . json_last_error_msg(), 'ERROR');
        return [];
    }

    return $data['Result'] ?? [];
}
