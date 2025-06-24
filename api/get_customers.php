<?php declare(strict_types=1);
// api/get_customers.php
// -------------------------------------------------------------------
// Returns JSON list of customers for dropdowns and tables.
// Uses MPSM API auth, no OpenAI plugin token required.
// -------------------------------------------------------------------

header('Content-Type: application/json; charset=UTF-8');

try {
    // 1) Load environment (.env) parser
    require_once __DIR__ . '/../includes/env_parser.php';
    // This will define constants like DEALER_CODE, API_BASE_URL, etc.
    parse_env_file(__DIR__ . '/../.env');
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => 'Configuration error',
        'message' => 'Could not load .env: ' . $e->getMessage()
    ]);
    exit;
}

try {
    // 2) CORS and logging (optional but recommended)
    require_once __DIR__ . '/../includes/cors.php';
    send_cors_headers();

    require_once __DIR__ . '/../includes/logger.php';
    log_request();

    // 3) Auth and API client
    require_once __DIR__ . '/../includes/auth.php';       // defines get_token()
    require_once __DIR__ . '/../includes/api_client.php'; // defines api_request()

    // 4) Read query params
    $pageNumber = isset($_GET['q']) ? 1 : (int)($_GET['PageNumber'] ?? 1);
    $pageRows   = isset($_GET['q']) ? 1000 : (int)($_GET['PageRows'] ?? 15);
    $sortCol    = $_GET['SortColumn'] ?? 'Description';
    $sortOrder  = in_array($_GET['SortOrder'] ?? 'Asc', ['Asc','Desc'], true)
                  ? $_GET['SortOrder']
                  : 'Asc';

    // 5) Build request body
    $body = [
        'DealerCode'  => DEALER_CODE,
        'PageNumber'  => $pageNumber,
        'PageRows'    => $pageRows,
        'SortColumn'  => $sortCol,
        'SortOrder'   => $sortOrder,
    ];
    if (!empty($_GET['q'])) {
        $body['FilterDescription'] = trim((string)$_GET['q']);
    }

    // 6) Call upstream MPSM API
    $resp = api_request('Customer/GetCustomers', $body);
    // api_request returns ['status'=>HTTP_CODE, 'data'=>...]
    http_response_code($resp['status']);
    echo json_encode($resp['data']);
    exit;
} catch (RuntimeException $e) {
    // Upstream failure
    http_response_code(502);
    echo json_encode([
        'error'   => 'Upstream API error',
        'message' => $e->getMessage()
    ]);
    exit;
}
