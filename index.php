<?php
declare(strict_types=1);

/**
 * Simple landing page for monitoring the local MPS Monitor API Engine.
 * Shows health check information and provides a basic /query test harness.
 */

/**
 * Build the engine base URL. Allows override via MPS_ENGINE_BASE_URL env/config.
 */
function detect_engine_base_url(): string
{
    $override = getenv('MPS_ENGINE_BASE_URL');
    if ($override) {
        return rtrim($override, '/');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/') ?: '/';

    if ($scriptDir === '/' || $scriptDir === '\\') {
        $scriptDir = '';
    }

    return rtrim($scheme . '://' . $host . $scriptDir . '/mps-api', '/');
}

/**
 * Make an HTTP call and capture status, response body, and timing info.
 *
 * @param string      $url
 * @param string      $method
 * @param string|null $body
 * @param string[]    $headers
 * @return array{ok:bool,status:?int,body:?string,error:?string,duration:float,transport:string,headers:string[]}
 */
function make_http_request(string $url, string $method = 'GET', ?string $body = null, array $headers = []): array
{
    $method = strtoupper($method);
    $headers[] = 'Accept: application/json';
    $start = microtime(true);

    $result = [
        'ok' => false,
        'status' => null,
        'body' => null,
        'error' => null,
        'duration' => 0.0,
        'transport' => 'curl',
        'headers' => [],
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            $result['error'] = curl_error($ch) ?: 'Unknown cURL error';
        } else {
            $result['body'] = $responseBody;
        }

        $result['status'] = curl_getinfo($ch, CURLINFO_RESPONSE_CODE) ?: null;
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        if ($contentType) {
            $result['headers'][] = 'content-type: ' . $contentType;
        }

        curl_close($ch);
    } else {
        $result['transport'] = 'stream';
        $contextHeaders = $headers ? implode("\r\n", $headers) . "\r\n" : '';
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'timeout' => 15,
                'ignore_errors' => true,
                'header' => $contextHeaders,
                'content' => $body ?? '',
            ],
        ]);

        $responseBody = @file_get_contents($url, false, $context);
        if ($responseBody === false) {
            $lastError = error_get_last();
            $result['error'] = $lastError['message'] ?? 'HTTP request failed';
        } else {
            $result['body'] = $responseBody;
        }

        if (isset($http_response_header) && is_array($http_response_header)) {
            $result['headers'] = $http_response_header;
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $http_response_header[0], $matches)) {
                $result['status'] = (int)$matches[1];
            }
        }
    }

    $result['duration'] = microtime(true) - $start;
    if ($result['status'] !== null) {
        $result['ok'] = $result['status'] >= 200 && $result['status'] < 300;
    }

    return $result;
}

/**
 * Pretty-print JSON when possible, fallback to raw text.
 */
function pretty_json(?string $body): string
{
    if ($body === null || $body === '') {
        return '';
    }

    $decoded = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    return $body;
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$engineBaseUrl = detect_engine_base_url();
$healthResult = make_http_request($engineBaseUrl . '/health');
$healthData = null;

if ($healthResult['body']) {
    $decoded = json_decode($healthResult['body'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $healthData = $decoded;
    }
}

$testResponse = null;
$testError = null;
$submittedAction = trim($_POST['action'] ?? '');
$submittedParams = $_POST['params'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($submittedAction === '') {
        $testError = 'Action name is required.';
    } else {
        $paramsRaw = trim($submittedParams);
        if ($paramsRaw === '') {
            $params = [];
        } else {
            $params = json_decode($paramsRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $testError = 'Params must be valid JSON. Error: ' . json_last_error_msg();
            }
        }

        if ($testError === null) {
            $payload = [
                'action' => $submittedAction,
                'params' => $params ?? [],
            ];

            $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $testResponse = make_http_request(
                $engineBaseUrl . '/query',
                'POST',
                $jsonPayload,
                ['Content-Type: application/json']
            );
        }
    }
}

$defaultAction = $submittedAction !== '' ? $submittedAction : 'healthCheck';
$defaultParams = $submittedParams !== '' ? $submittedParams : "{\n    \n}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPS Monitor Dashboard - Engine Monitor</title>
    <style>
        :root {
            color-scheme: light dark;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f5f5f5;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
        }
        h1 {
            margin-bottom: 0.5rem;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }
        .card h2 {
            margin-top: 0;
            font-size: 1.25rem;
        }
        .status {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .status.ok {
            background: #e6f4ea;
            color: #1e7a35;
        }
        .status.fail {
            background: #fde7e9;
            color: #b71c1c;
        }
        dl {
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 0.5rem 1rem;
        }
        dt {
            font-weight: 600;
        }
        pre {
            background: #1e1e1e;
            color: #f5f2f0;
            border-radius: 8px;
            padding: 1rem;
            overflow-x: auto;
        }
        form label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-family: monospace;
            font-size: 0.95rem;
            box-sizing: border-box;
            margin-bottom: 1rem;
        }
        button {
            background: #004aad;
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background: #00357d;
        }
        .example-btn {
            background: #fff;
            color: #004aad;
            border: 2px solid #004aad;
            padding: 0.65rem 1rem;
            text-align: left;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .example-btn:hover {
            background: #004aad;
            color: #fff;
            transform: translateX(4px);
        }
        .error {
            color: #b71c1c;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .meta {
            font-size: 0.9rem;
            color: #555;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background: #111827;
                color: #e5e7eb;
            }
            .card {
                background: #1f2937;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            }
            input[type="text"],
            textarea {
                background: #111827;
                color: #e5e7eb;
                border-color: #374151;
            }
            pre {
                background: #0b1120;
            }
            .example-btn {
                background: #1f2937;
                color: #60a5fa;
                border-color: #60a5fa;
            }
            .example-btn:hover {
                background: #60a5fa;
                color: #111827;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const actionInput = document.getElementById('action');
            const paramsTextarea = document.getElementById('params');
            const exampleButtons = document.querySelectorAll('.example-btn');

            exampleButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const action = this.getAttribute('data-action');
                    const params = this.getAttribute('data-params');

                    actionInput.value = action;

                    // Pretty format the JSON
                    try {
                        const paramsObj = JSON.parse(params);
                        paramsTextarea.value = JSON.stringify(paramsObj, null, 4);
                    } catch (e) {
                        paramsTextarea.value = params;
                    }

                    // Scroll to form
                    actionInput.focus();
                    actionInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>MPS Monitor Dashboard</h1>
            <p style="font-size: 1.1rem; color: #666;">Engine Status & Testing Interface</p>
            <p class="meta">Engine URL: <code><?= h($engineBaseUrl) ?></code></p>
        </header>

        <section class="card">
            <h2>Health Check</h2>
            <?php
            $statusClass = $healthResult['ok'] ? 'ok' : 'fail';
            $statusLabel = $healthResult['ok'] ? 'Healthy' : 'Unavailable';
            ?>
            <span class="status <?= $statusClass ?>">
                <?= h($statusLabel) ?>
            </span>
            <dl>
                <dt>HTTP Status</dt>
                <dd><?= h($healthResult['status'] !== null ? (string)$healthResult['status'] : 'n/a') ?></dd>

                <dt>Duration</dt>
                <dd><?= number_format($healthResult['duration'], 3) ?>s (via <?= h($healthResult['transport']) ?>)</dd>

                <dt>Error</dt>
                <dd><?= h($healthResult['error']) ?: 'None' ?></dd>
            </dl>
            <?php if ($healthData !== null): ?>
                <h3>Engine Info</h3>
                <dl>
                    <?php if (isset($healthData['action_count'])): ?>
                        <dt>Available Operations</dt>
                        <dd><strong><?= h((string)$healthData['action_count']) ?></strong> operations from canonical swagger</dd>
                    <?php endif; ?>
                    <?php if (isset($healthData['version'])): ?>
                        <dt>Engine Version</dt>
                        <dd><?= h($healthData['version']) ?></dd>
                    <?php endif; ?>
                    <?php if (isset($healthData['service'])): ?>
                        <dt>Service</dt>
                        <dd><?= h($healthData['service']) ?></dd>
                    <?php endif; ?>
                </dl>
                <details>
                    <summary style="cursor: pointer; font-weight: 600; margin-top: 1rem;">View Full Response</summary>
                    <pre><?= h(pretty_json($healthResult['body'])) ?></pre>
                </details>
            <?php elseif ($healthResult['body']): ?>
                <h3>Raw Response</h3>
                <pre><?= h($healthResult['body']) ?></pre>
            <?php endif; ?>
        </section>

        <section class="card">
            <h2>Quick Links</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 0.5rem;">
                    <a href="<?= h($engineBaseUrl) ?>" target="_blank" style="color: #004aad; text-decoration: none;">
                        📡 Engine Root (JSON API)
                    </a>
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <a href="<?= h($engineBaseUrl) ?>/endpoints" target="_blank" style="color: #004aad; text-decoration: none;">
                        📋 All Available Endpoints (544 operations)
                    </a>
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <a href="<?= h($engineBaseUrl) ?>/swagger.json" target="_blank" style="color: #004aad; text-decoration: none;">
                        📄 Canonical Swagger Specification
                    </a>
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <a href="mps-api/QUICK_START.md" target="_blank" style="color: #004aad; text-decoration: none;">
                        🚀 Quick Start Guide
                    </a>
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <a href="mps-api/USAGE_EXAMPLES.md" target="_blank" style="color: #004aad; text-decoration: none;">
                        📚 Usage Examples
                    </a>
                </li>
            </ul>
        </section>

        <section class="card">
            <h2>/query Test Harness</h2>
            <p class="meta">Test any of the 544 available operations from the canonical swagger.</p>

            <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f0f7ff; border-radius: 8px; border-left: 4px solid #004aad;">
                <h3 style="margin-top: 0; font-size: 1rem;">📋 Example Queries</h3>
                <p style="font-size: 0.9rem; margin-bottom: 0.75rem;">Click any example to load it into the form:</p>
                <div style="display: grid; gap: 0.5rem;">
                    <button type="button" class="example-btn" data-action="healthCheck" data-params="{}">
                        🔍 Health Check - Test engine connectivity
                    </button>
                    <button type="button" class="example-btn" data-action="Account/GetProfile" data-params="{}">
                        👤 Get Account Profile - Current authenticated user
                    </button>
                    <button type="button" class="example-btn" data-action="Dealer/Get" data-params='{"code": "DEALER001"}'>
                        🏢 Get Dealer - Fetch dealer info by code
                    </button>
                    <button type="button" class="example-btn" data-action="Dealer/GetDealers" data-params='{"request": {"pageNumber": 1, "pageSize": 10, "sortField": "name", "sortDirection": "Asc"}}'>
                        🏢 List Dealers - Get first 10 dealers sorted by name
                    </button>
                    <button type="button" class="example-btn" data-action="Customer/Get" data-params='{"code": "CUST001"}'>
                        🏪 Get Customer - Fetch customer by code
                    </button>
                    <button type="button" class="example-btn" data-action="Customer/GetCustomers" data-params='{"request": {"dealerCode": "DEALER001", "pageNumber": 1, "pageSize": 10}}'>
                        🏪 List Customers - Get customers for a dealer
                    </button>
                    <button type="button" class="example-btn" data-action="Device/List" data-params='{"request": {"pageNumber": 1, "pageSize": 10}}'>
                        🖨️ List Devices - Get first 10 devices
                    </button>
                    <button type="button" class="example-btn" data-action="Device/Get" data-params='{"id": "device-id-here"}'>
                        🖨️ Get Device - Fetch specific device by ID
                    </button>
                    <button type="button" class="example-btn" data-action="Explorer/Device" data-params='{"request": {"pageNumber": 1, "pageSize": 5}}'>
                        🔎 Explorer Device - Advanced device search
                    </button>
                    <button type="button" class="example-btn" data-action="Explorer/Customer" data-params='{"request": {"pageNumber": 1, "pageSize": 5}}'>
                        🔎 Explorer Customer - Advanced customer search
                    </button>
                </div>
            </div>

            <?php if ($testError): ?>
                <p class="error"><?= h($testError) ?></p>
            <?php endif; ?>
            <form method="post" novalidate id="queryForm">
                <label for="action">Action <span style="font-weight: normal; color: #666;">(or click an example above)</span></label>
                <input type="text" id="action" name="action" value="<?= h($defaultAction) ?>" placeholder="e.g. Account/GetProfile" required>

                <label for="params">Params (JSON) <span style="font-weight: normal; color: #666;">(leave empty {} for no params)</span></label>
                <textarea id="params" name="params" rows="8" placeholder='{"key": "value"}'><?= h($defaultParams) ?></textarea>

                <button type="submit">Send Request</button>
            </form>
            <?php if ($testResponse !== null): ?>
                <h3>Result</h3>
                <dl>
                    <dt>HTTP Status</dt>
                    <dd><?= h($testResponse['status'] !== null ? (string)$testResponse['status'] : 'n/a') ?></dd>
                    <dt>Duration</dt>
                    <dd><?= number_format($testResponse['duration'], 3) ?>s (via <?= h($testResponse['transport']) ?>)</dd>
                    <dt>Error</dt>
                    <dd><?= h($testResponse['error']) ?: 'None' ?></dd>
                </dl>
                <?php if ($testResponse['body']): ?>
                    <pre><?= h(pretty_json($testResponse['body'])) ?></pre>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
