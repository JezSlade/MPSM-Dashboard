<?php
// PHP Debugging Lines - START
// Enable all error reporting for development purposes.
// This helps in identifying and debugging issues quickly.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../bootstrap.php';

$customers = ApiCaller::request('POST', '', [
    'Url' => 'Customer/GetCustomers',
    'Request' => [
        'DealerCode' => 'NY06AGDWUQ',
        'PageNumber' => 1,
        'PageRows' => 100,
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
