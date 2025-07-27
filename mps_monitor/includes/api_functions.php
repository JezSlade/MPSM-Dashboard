<?php
// ✅ Enable detailed PHP error reporting for debugging (remove or disable in production)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// mps_monitor/includes/api_functions.php
// STRICT PATCHED: Added PHP error reporting for debugging 500 errors and ensured parse_env_file is available for api_bootstrap.php dependency

declare(strict_types=1);



require_once __DIR__ . '/../config/mps_config.php';
require_once __DIR__ . '/../helpers/CacheHelper.php';
require_once __DIR__ . '/../src/MPSMonitorClient.php';

// ✅ Ensure parse_env_file is available (api_bootstrap.php dependency)
if (!function_exists('parse_env_file')) {
    /**
     * Parses a .env file and returns its contents as an associative array.
     */
    function parse_env_file(string $filePath): array {
        $env = [];
        if (!file_exists($filePath)) {
            if (function_exists('custom_log')) {
                custom_log("Error: .env file not found at " . $filePath, 'ERROR');
            }
            return $env;
        }
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " \"'");
                $env[$key] = $value;
            }
        }
        if (function_exists('custom_log')) {
            custom_log('Parsed .env file.', 'DEBUG');
        }
        return $env;
    }
}

// Central API call wrapper
function call_mps_api(string $path, string $method = 'GET', array $data = []) {
    $client = new MPSMonitorClient();
    return $client->callApi($path, $method, $data);
}

// ✅ PATCHED TO USE GET AS REQUIRED BY SWAGGER
function get_all_customers(array $filters = []) {
    if (function_exists('custom_log')) {
        custom_log('Calling GetCustomers via api_functions (GET method, Swagger compliant).', 'INFO');
    }
    return call_mps_api('Customer/GetCustomers', 'GET', $filters);
}

function create_customer(array $model) {
    if (function_exists('custom_log')) {
        custom_log('Calling CreateCustomer via api_functions.', 'INFO');
    }
    return call_mps_api('Customer/CreateCustomer', 'POST', $model);
}

function update_customer(array $model) {
    if (function_exists('custom_log')) {
        custom_log('Calling UpdateCustomer via api_functions.', 'INFO');
    }
    return call_mps_api('Customer/UpdateCustomer', 'POST', $model);
}

function get_devices(array $filters = []) {
    if (function_exists('custom_log')) {
        custom_log('Calling GetDevices via api_functions.', 'INFO');
    }
    return call_mps_api('Device/GetDevices', 'POST', $filters);
}

function get_device_counters(array $filters = []) {
    if (function_exists('custom_log')) {
        custom_log('Calling GetDeviceCounters via api_functions.', 'INFO');
    }
    return call_mps_api('Device/GetDeviceCounters', 'POST', $filters);
}

function get_alerts(array $filters = []) {
    if (function_exists('custom_log')) {
        custom_log('Calling GetAlerts via api_functions.', 'INFO');
    }
    return call_mps_api('Alert/GetAlerts', 'POST', $filters);
}
?>
