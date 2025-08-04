<?php
// PATCHED: select_customers.php v3.0 – Bootstrap Integrated

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../bootstrap.php';

$customers = [];
try {
    $customers = ApiCaller::request('GET', '/customers');

    if (!is_array($customers)) {
        throw new Exception('Unexpected API response structure');
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
