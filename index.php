<?php
// === Load .env manually ===
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}
loadEnv(__DIR__ . '/.env');

// === ENV config ===
$clientId = $_ENV['CLIENT_ID'] ?? '';
$clientSecret = $_ENV['CLIENT_SECRET'] ?? '';
$username = $_ENV['USERNAME'] ?? '';
$password = $_ENV['PASSWORD'] ?? '';
$scope = $_ENV['SCOPE'] ?? '';
$tokenUrl = $_ENV['TOKEN_URL'] ?? '';
$baseUrl = rtrim($_ENV['BASE_URL'] ?? '', '/');
$dealerCode = $_ENV['DEALER_CODE'] ?? '';  // <-- NEW
$debug = ($_ENV['DEBUG'] ?? 'false') === 'true';

// === Get Access Token ===
function getAccessToken($url, $clientId, $clientSecret, $username, $password, $scope, $debug = false) {
    $data = http_build_query([
        'grant_type' => 'password',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'username' => $username,
        'password' => $password,
        'scope' => $scope
    ]);

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' =>
                "Content-Type: application/x-www-form-urlencoded\r\n" .
                "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data,
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);
    $json = json_decode($result, true);

    if ($debug) {
        echo "<pre><strong>Token Response:</strong>\n" . htmlspecialchars(print_r($json, true)) . "</pre>";
    }

    return $json['access_token'] ?? null;
}

// === Canonical POST to /Customer/GetCustomers ===
function callGetCustomers($baseUrl, $token, $dealerCode, $debug = false) {
    $payload = [
        "PageNumber" => 1,
        "PageRows" => 100,
        "SortColumn" => "CompanyName",
        "DealerCode" => $dealerCode
    ];

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' =>
                "Authorization: Bearer $token\r\n" .
                "Accept: application/json\r\n" .
                "Content-Type: application/json\r\n",
            'content' => json_encode($payload),
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($opts);
    $result = file_get_contents($baseUrl . '/Customer/GetCustomers', false, $context);
    $json = json_decode($result, true);

    if ($debug) {
        echo "<pre><strong>API Response:</strong>\n" . htmlspecialchars(print_r($json, true)) . "</pre>";
    }

    return $json['Result'] ?? [];
}

// === Run ===
if (!$dealerCode) die("❌ Missing required DEALER_CODE in .env");

$token = getAccessToken($tokenUrl, $clientId, $clientSecret, $username, $password, $scope, $debug);
if (!$token) die("<strong style='color:red;'>❌ Failed to get access token.</strong>");

$customers = callGetCustomers($baseUrl, $token, $dealerCode, $debug);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MPSM MVP - Customers</title>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: monospace;
            padding: 2rem;
        }
        h1 {
            color: #00ffcc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1f1f1f;
        }
        th, td {
            padding: 10px;
            border: 1px solid #333;
        }
        th {
            background: #333;
        }
        tr:hover {
            background: #2a2a2a;
        }
    </style>
</head>
<body>
    <h1>MPSM Customers</h1>
    <?php if (empty($customers)): ?>
        <p>No customers returned.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>CustomerId</th>
                <th>CompanyName</th>
                <th>Country</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($customers as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['CustomerId'] ?? '-') ?></td>
                <td><?= htmlspecialchars($c['CompanyName'] ?? '-') ?></td>
                <td><?= htmlspecialchars($c['Country'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</body>
</html>
