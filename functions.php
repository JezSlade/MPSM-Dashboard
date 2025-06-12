<?php
declare(strict_types=1);
/**
 * functions.php - shared helpers: logging, partials, API calls.
 */

// 1) Ensure necessary constants exist
defined('DEBUG_MODE')    or define('DEBUG_MODE', false);
defined('API_BASE_URL')  or define('API_BASE_URL', '');
defined('SITE_BASE_URL') or define('SITE_BASE_URL', '/');

/**
 * debug_log - logs messages if DEBUG_MODE is true.
 */
function debug_log(string $msg, string $level = 'INFO'): void {
    if (! DEBUG_MODE) {
        return;
    }
    $entry = sprintf("[%s] %s: %s\n", date('c'), strtoupper($level), $msg);
    // append to daily log
    file_put_contents(__DIR__ . '/logs/debug-' . date('Y-m-d') . '.log', $entry, FILE_APPEND);
}

/**
 * include_partial - loads a PHP partial with scoped variables.
 */
function include_partial(string $path, array $vars = []): bool {
    $full = __DIR__ . '/' . $path;
    if (! file_exists($full)) {
        debug_log("Partial not found: {$full}", 'ERROR');
        return false;
    }
    extract($vars, EXTR_SKIP);
    include $full;
    return true;
}

/**
 * sanitize_html - safe escape for output.
 */
function sanitize_html(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * fetch_customers - example API call using cURL & API_BASE_URL.
 */
function fetch_customers(): array {
    $url = API_BASE_URL . 'Customer/GetCustomers';
    $payload = json_encode([
        'DealerCode' => DEALER_CODE,
        'PageNumber' => 1,
        'PageRows'   => 2147483647,
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
        CURLOPT_POSTFIELDS     => $payload
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        debug_log('cURL error: ' . curl_error($ch), 'ERROR');
        curl_close($ch);
        return [];
    }
    curl_close($ch);
    $data = json_decode($resp, true);
    if (! $data || ! isset($data['Result'])) {
        debug_log('Invalid customer response', 'ERROR');
        return [];
    }
    return $data['Result'];
}

// ... any other helpers ...

