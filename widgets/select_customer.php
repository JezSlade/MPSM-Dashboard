<?php
// PATCHED: widgets/select_customers.php
// Version: v1.2 - Fixed Missing API Base URL Constant
// ------------------------------------------------------
// • Now ensures MPS_API_BASE_URL is defined by including mps_config.php.
// • Fully cohesive with get_token.php v2.2 & global_token_handler.php v3.3.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Ensure API config constants are available
$configPath = dirname(__DIR__) . '/mps_monitor/config/mps_config.php';
if (file_exists($configPath) && !defined('MPS_API_BASE_URL')) {
    require_once $configPath;
}

// ✅ Include the global token handler
require_once dirname(__DIR__) . '/mps_monitor/includes/global_token_handler.php';
$token = get_valid_mps_token();

if (empty($token['access_token'])) {
    echo '<div class="widget-body"><p><strong>Error:</strong> Unable to retrieve valid token.</p></div>';
    return;
}

// ✅ Fetch customer list from MPS Monitor API
$customers = [];
try {
    $apiUrl = rtrim(MPS_API_BASE_URL, '/') . '/customers';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token['access_token'],
            'Content-Type: application/json'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        throw new Exception('cURL Error: ' . $curlErr);
    }
    if ($httpCode !== 200) {
        throw new Exception('API returned HTTP ' . $httpCode . ': ' . $response);
    }

    $decoded = json_decode($response, true);
    if (isset($decoded['data']) && is_array($decoded['data'])) {
        $customers = $decoded['data'];
    } else {
        throw new Exception('Invalid API response structure: ' . $response);
    }
} catch (Throwable $e) {
    echo '<div class="widget-body"><p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p></div>';
    return;
}
?>

<div class="widget-body">
    <h3 class="widget-section-title">Select Customers</h3>
    <?php if (!empty($customers)): ?>
        <ul>
            <?php foreach ($customers as $customer): ?>
                <li><strong><?= htmlspecialchars($customer['name']) ?></strong> (ID: <?= htmlspecialchars($customer['id']) ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p><em>No customers found.</em></p>
    <?php endif; ?>
</div>
