<?php
// PATCHED: select_customers.php v1.6 - Final Unified Version
// ------------------------------------------------------
// • Uses correct constant MPS_API_BASE from mps_config.php.
// • Config included only once, no redundant fallbacks.
// • Fully cohesive with get_token.php v2.3 and global_token_handler.php v3.4.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configPath = dirname(__DIR__) . '/mps_monitor/config/mps_config.php';
if (file_exists($configPath) && !defined('MPS_API_BASE')) {
    require_once $configPath;
}

require_once dirname(__DIR__) . '/mps_monitor/includes/global_token_handler.php';
$token = get_valid_mps_token();

if (empty($token['access_token'])) {
    echo '<div class="widget-body"><p><strong>Error:</strong> Unable to retrieve valid token.</p></div>';
    return;
}

$customers = [];
try {
    $apiUrl = rtrim(MPS_API_BASE, '/') . '/customers';
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
