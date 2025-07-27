<?php
// mps_monitor/includes/api_functions.php
// STRICT PATCHED: Only adjusted HTTP method for Customer/GetCustomers to ensure Swagger compliance

declare(strict_types=1);

require_once __DIR__ . '/../config/mps_config.php';
require_once __DIR__ . '/../helpers/CacheHelper.php';
require_once __DIR__ . '/../src/MPSMonitorClient.php';

// Central API call wrapper
function call_mps_api(string $path, string $method = 'GET', array $data = []) {
    $client = new MPSMonitorClient();
    return $client->callApi($path, $method, $data);
}

// ✅ PATCHED TO USE GET AS REQUIRED BY SWAGGER
function get_all_customers(array $filters = []) {
    custom_log('Calling GetCustomers via api_functions (GET method, Swagger compliant).', 'INFO');
    return call_mps_api('Customer/GetCustomers', 'GET', $filters);
}

// Unchanged legacy functions
function create_customer(array $model) {
    custom_log('Calling CreateCustomer via api_functions.', 'INFO');
    return call_mps_api('Customer/CreateCustomer', 'POST', $model);
}

function update_customer(array $model) {
    custom_log('Calling UpdateCustomer via api_functions.', 'INFO');
    return call_mps_api('Customer/UpdateCustomer', 'POST', $model);
}

function get_devices(array $filters = []) {
    custom_log('Calling GetDevices via api_functions.', 'INFO');
    return call_mps_api('Device/GetDevices', 'POST', $filters);
}

function get_device_counters(array $filters = []) {
    custom_log('Calling GetDeviceCounters via api_functions.', 'INFO');
    return call_mps_api('Device/GetDeviceCounters', 'POST', $filters);
}

function get_alerts(array $filters = []) {
    custom_log('Calling GetAlerts via api_functions.', 'INFO');
    return call_mps_api('Alert/GetAlerts', 'POST', $filters);
}
?>
