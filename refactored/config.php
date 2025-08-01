<?php
// config.php

// Define the path to the dashboard settings file
if (!defined('DASHBOARD_SETTINGS_FILE')) {
    define('DASHBOARD_SETTINGS_FILE', __DIR__ . '/dashboard_settings.json');
}

// Define the application root directory for security
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}

// Define Dashboard Version Constants
if (!defined('DASHBOARD_VERSION_MAJOR')) {
    define('DASHBOARD_VERSION_MAJOR', 1);
}
if (!defined('DASHBOARD_VERSION_MINOR')) {
    define('DASHBOARD_VERSION_MINOR', 0);
}
if (!defined('DASHBOARD_VERSION_PATCH')) {
    define('DASHBOARD_VERSION_PATCH', 0);
}
if (!defined('DASHBOARD_VERSION_BUILD')) {
    define('DASHBOARD_VERSION_BUILD', 1);
}

// ✅ UPDATED FOR .ENV COMPLIANCE (values overridden by parse_env_file at runtime)
if (!defined('API_BASE_URL')) {
    define('API_BASE_URL', getenv('API_BASE_URL') ?: 'https://api.abassetmanagement.com/api3/');
}
if (!defined('MPS_API_BASE')) {
    define('MPS_API_BASE', API_BASE_URL);
}
if (!defined('MPS_TOKEN_URL')) {
    define('MPS_TOKEN_URL', getenv('TOKEN_URL') ?: API_BASE_URL . '/token');
}
if (!defined('USERNAME')) {
    define('USERNAME', getenv('USERNAME') ?: 'dashboard');
}
if (!defined('PASSWORD')) {
    define('PASSWORD', getenv('PASSWORD') ?: 'd@$hpa$$2024');
}
if (!defined('CLIENT_ID')) {
    define('CLIENT_ID', getenv('CLIENT_ID') ?: '9AT9j4UoU2BgLEqmiYCz');
}
if (!defined('CLIENT_SECRET')) {
    define('CLIENT_SECRET', getenv('CLIENT_SECRET') ?: '9gTbAKBCZe1ftYQbLbq9');
}
if (!defined('SCOPE')) {
    define('SCOPE', getenv('SCOPE') ?: 'account');
}
if (!defined('DEALER_CODE')) {
    define('DEALER_CODE', getenv('DEALER_CODE') ?: 'NY06AGDWUQ');
}
if (!defined('DEALER_ID')) {
    define('DEALER_ID', getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2');
}

// Include helper functions for widget discovery
require_once APP_ROOT . '/helpers.php';

$available_widgets = discover_widgets();
?>
