<?php
/**
 * get_customers.php
 * Retrieves a list of all customers from MPS Monitor.
 *
 * ✅ Compliant with Ai_Patch_Validation_Protocol.md
 * ✅ Endpoint verified against Swagger.json and API_Integration_Guide.md
 */
// ✅ Enable detailed PHP error reporting for debugging (remove or disable in production)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$method   = 'GET';
$path     = 'Customer/GetCustomers';
$useCache = true; // Enables caching as per API guide

require __DIR__ . '/../includes/api_bootstrap.php';

// api_bootstrap.php handles:
// - OAuth token (already functional per changelog)
// - Redis caching (if enabled)
// - cURL request to API_BASE_URL
// - Emission of JSON response
