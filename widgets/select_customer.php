<?php
// PATCHED: select_customers.php v3.0 – Bootstrap Integrated

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../bootstrap.php';

$customers = ApiCaller::request('POST', '/Customer/GetCustomers', [
    'Url' => 'Customer/GetCustomers',
    'Request' => [
        'DealerCode' => 'NY06AGDWUQ',
        'Code' => null,
        'HasHpSds' => null,
        'FilterText' => null,
        'PageNumber' => 1,
        'PageRows' => 2147483647,
        'SortColumn' => 'Id',
        'SortOrder' => 0
    ],
    'Method' => 'POST'
]);

echo "<pre>";
print_r($customers);
echo "</pre>";
exit;

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
