<?php
/**
 * views/developer.php
 *
 * Default “Developer” view.
 * Self-contained PHP file to render whatever
 * cards or data you choose for this POC.
 */

DebugPanel::log('Loaded Developer view');
?>

<h1>Developer View</h1>
<p>This is your starting point. Replace or extend with your first “card” logic.</p>

<?php
// Example: fetch and dump Customer/GetCustomers
try {
    $client   = new ApiClient();
    $response = $client->post('Customer/GetCustomers', [
        'DealerCode' => DEALER_CODE,
        'PageNumber' => 1,
        'PageRows'   => DEVICE_PAGE_SIZE,
        'SortColumn' => 'Id',
        'SortOrder'  => 0,
    ]);

    echo '<pre>' . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') . '</pre>';
} catch (Exception $e) {
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    DebugPanel::log('Customer/GetCustomers exception', $e->getTrace());
}
?>
