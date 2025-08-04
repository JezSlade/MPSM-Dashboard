<?php
// PATCHED: select_customers.php v2.0 – TokenManager Integration

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/../backend/core/TokenManager.php';
$token = TokenManager::getToken();

if (empty($token)) {
    echo '<div class="widget-body"><p><strong>Error:</strong> Unable to retrieve valid token.</p></div>';
    return;
}

$customers = [];
try {
    $apiUrl = 'https://api.mpsmonitor.com/customers'; // Replace with env-configured URL if available
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
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
