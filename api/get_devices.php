<?php declare(strict_types=1);
// api/get_devices.php
// -------------------------------------------------------------------
// Self-contained endpoint to list devices for the selected customer.
// Parses .env, handles auth, CORS, and logging, and calls the
// MPSM API’s Device/List endpoint.
// -------------------------------------------------------------------

header('Content-Type: application/json; charset=UTF-8');

try {
    // 1) Load environment variables
    require_once __DIR__ . '/../includes/env_parser.php';
    parse_env_file(__DIR__ . '/../.env');
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => 'Configuration error',
        'message' => 'Could not load .env: ' . $e->getMessage(),
    ]);
    exit;
}

try {
    // 2) Optional: CORS & request logging
    require_once __DIR__ . '/../includes/cors.php';
    send_cors_headers();

    require_once __DIR__ . '/../includes/logger.php';
    log_request();

    // 3) Auth & API client
    require_once __DIR__ . '/../includes/auth.php';       // get_token()
    require_once __DIR__ . '/../includes/api_client.php'; // api_request()

    // 4) Read pagination & sort params
    $pageNumber = isset($_GET['PageNumber']) ? (int)$_GET['PageNumber'] : 1;
    $pageRows   = isset($_GET['PageRows'])   ? (int)$_GET['PageRows']   : 15;
    $sortCol    = $_GET['SortColumn']  ?? 'ExternalIdentifier';
    $sortOrder  = in_array($_GET['SortOrder'] ?? 'Asc', ['Asc','Desc'], true)
                  ? $_GET['SortOrder']
                  : 'Asc';

    // 5) Read selected customer from cookie
    $customerCode = $_COOKIE['customer'] ?? null;
    if (!$customerCode) {
        http_response_code(400);
        echo json_encode([
            'error'   => 'Missing customer',
            'message' => 'No customer selected (cookie "customer" not set)',
        ]);
        exit;
    }

    // 6) Build request body
    $body = [
        'DealerCode'   => DEALER_CODE,
        'CustomerCode' => $customerCode,
        'PageNumber'   => $pageNumber,
        'PageRows'     => $pageRows,
        'SortColumn'   => $sortCol,
        'SortOrder'    => $sortOrder,
    ];

    // 7) Call upstream Device/List endpoint
    $resp = api_request('Device/List', $body);
    http_response_code($resp['status']);
    echo json_encode($resp['data']);
    exit;
} catch (RuntimeException $e) {
    // 8) Upstream or client error
    http_response_code(502);
    echo json_encode([
        'error'   => 'Upstream API error',
        'message' => $e->getMessage(),
    ]);
    exit;
}
