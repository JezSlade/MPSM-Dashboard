<?php
/**
 * views/developer.php
 *
 * Default “Developer” view.
 * Self-contained: call whichever endpoints/cards you like here.
 */

DebugPanel::log('Developer view loaded');
?>

<h1>Developer View</h1>
<p>Replace this with your first card logic.</p>

<?php
// Example card: fetch Customer/GetCustomers
try {
    $client   = new ApiClient();
    $response = $client->post('Customer/GetCustomers', [
        'DealerCode' => DEALER_CODE,
        'PageNumber' => 1,
        'PageRows'   => DEVICE_PAGE_SIZE,
        'SortColumn' => 'Id',
        'SortOrder'  => 0,
    ]);
    echo '<pre>' . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT), ENT_QUOTES) . '</pre>';
} catch (Exception $e) {
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</p>';
    DebugPanel::log('Customer/GetCustomers failed', $e->getTrace());
}
