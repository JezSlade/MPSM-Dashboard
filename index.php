<?php
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/data/endpoints.php';
require_once __DIR__ . '/views/table.php';
require_once __DIR__ . '/api/call.php';

    // Fetch customer list from API
    $custResp = callApi('Customer/GetCustomers', [
        'DealerCode' => env('DEALER_CODE'),
        'Code'       => null,
        'HasHpSds'   => null,
        'FilterText' => null,
        'PageNumber' => 1,
        'PageRows'   => 2147483647,
        'SortColumn' => 'Id',
        'SortOrder'  => 0
    ]);
    $customers = $custResp['Result'] ?? [];
    $selectedCustomer = $_GET['customer'] ?? null;

$endpoints = getAllEndpoints();
$selected = $_GET['endpoint'] ?? null;
$results  = null;

if ($selected && isset($endpoints[$selected])) {
    $info = $endpoints[$selected];
    $payload = [];

    // Prepare placeholder payload based on expected parameter types (simplified)
    if (isset($info['parameters']) && is_array($info['parameters'])) {
        foreach ($info['parameters'] as $param) {
            $name = $param['name'];
            $type = $param['type'] ?? 'string';
            if ($type === 'integer' || $type === 'int') {
                $payload[$name] = 1;
            } else {
                $payload[$name] = env('DEALER_CODE');
            }
        }
    }

    $response = callApi($selected, $payload, strtolower($info['method'] ?? 'post'));
    $results = $response['body']['value'] ?? $response['body'];
}

ob_start();
?>
<form method="get">
    <label for="endpoint">Select Endpoint:</label>
    <select id="endpoint" name="endpoint" onchange="this.form.submit()">
        <option value="">-- Choose --</option>
        <?php foreach ($endpoints as $key => $e): ?>
            <option value="<?= $key ?>" <?= $key === $selected ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['operationId'] ?? $e['path']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php
if ($results) {
    echo renderTable(is_array($results) && isset($results[0]) ? $results : array($results));
}
$content = ob_get_clean();
include __DIR__ . '/views/layout.php';
