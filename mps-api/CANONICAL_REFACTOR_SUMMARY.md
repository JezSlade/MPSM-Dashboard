# API Engine Refactor Summary - Canonical Swagger Integration

## Overview
The MPS API Engine has been successfully refactored to work with the FULL canonical swagger.json file containing all 544 API operations.

## Changes Made

### 1. SwaggerActionRegistry.php
**File**: [mps-api/SwaggerActionRegistry.php](mps-api/SwaggerActionRegistry.php:29)

**Change**: Updated the swagger file search path priority to prefer the canonical swagger:

```php
$candidates = [
    dirname(__DIR__) . '/.canonical/Swagger.json',  // ← NEW: Canonical swagger (PRIORITY)
    dirname(__DIR__) . '/Swagger.json',
    __DIR__ . '/swagger.json',
    dirname(__DIR__, 1) . '/../Swagger.json',
    dirname(__DIR__, 2) . '/documentation/Endpoints/Swagger.json',
];
```

### 2. Engine.php
**File**: [mps-api/engine.php](mps-api/engine.php)

**No Changes Required**: The engine already supports dynamic action dispatch through `dispatchAction()` method which works with any swagger specification.

## Canonical Swagger Statistics

- **Total Operations**: 544
- **API Version**: Swagger 2.0
- **Source File**: `.canonical/Swagger.json` (1.2MB)

### Major API Groups

The canonical swagger includes comprehensive coverage of all MPS Monitor API endpoints:

- **Account** - User account management, authentication, profiles
- **AlertLimit** - Alert configuration at dealer, customer, and device levels
- **AlertLimit2Api** - Enhanced alert limit management
- **Customer** - Customer management operations
- **Dealer** - Dealer management operations
- **Device** - Device monitoring and management
- **InstalledProduct** - Product installation tracking
- **Meter** - Meter reading operations
- **Supply** - Supply and consumables management
- **Product** - Product catalog management
- **Report** - Reporting and analytics
- **And many more...**

## How to Use

### 1. Dispatch Actions by operationId

The engine now supports all 544 operations from the canonical swagger. Use the `dispatchAction()` method:

```php
require_once 'mps-api/engine.php';

$engine = MPSMonitorEngine::getInstance();

// Example: Get account profile
$result = $engine->dispatchAction('Account/GetProfile');

// Example: Get dealers with filters
$result = $engine->dispatchAction('Dealer/List', [
    'pageNumber' => 1,
    'pageSize' => 50
]);

// Example: Get device details
$result = $engine->dispatchAction('Device/Get', [
    'id' => 'device-id-here'
]);

// Example: Create customer
$result = $engine->dispatchAction('Customer/Create', [
    'request' => [
        'name' => 'Customer Name',
        'code' => 'CUST001',
        // ... other fields
    ]
]);
```

### 2. Browse Available Endpoints

```php
$endpoints = $engine->getAvailableEndpoints();

echo "Total operations: " . $endpoints['count'] . "\n";

foreach ($endpoints['groups'] as $group => $operations) {
    echo "\n[{$group}]: " . count($operations) . " operations\n";
    foreach ($operations as $op) {
        echo "  {$op['method']} {$op['path']} - {$op['summary']}\n";
    }
}
```

### 3. Parameter Handling

The engine automatically handles:
- **Path parameters** - Inserted into the URL path
- **Query parameters** - Added to the URL query string
- **Body parameters** - Sent as JSON in the request body
- **Header parameters** - Added to request headers
- **Form parameters** - Sent as form data

Example with mixed parameters:

```php
// Operation: /Device/Get/{id}
$result = $engine->dispatchAction('Device/Get', [
    'id' => 'ABC123',              // Path parameter
    'includeMeters' => true,       // Query parameter (if defined in swagger)
]);
```

## Verification

To verify the engine is working with the canonical swagger, you can:

1. Check which swagger file is loaded:
```php
$registry = SwaggerActionRegistry::getInstance();
echo $registry->getSpecPath();
// Should output: .../MPSM-Dashboard/.canonical/Swagger.json
```

2. Count loaded operations:
```php
$endpoints = $engine->getAvailableEndpoints();
echo $endpoints['count']; // Should be 544
```

## Testing

A test script has been created at `mps-api/test_canonical_swagger.php` to verify the integration. Run it with:

```bash
php mps-api/test_canonical_swagger.php
```

This will:
- Load the engine with canonical swagger
- Display all endpoint groups
- Show sample operations
- Verify key actions are accessible

## Backward Compatibility

The refactor maintains full backward compatibility:
- Existing `makeRequest()` method still works
- All convenience methods (getMonitors, createMonitor, etc.) still work
- Configuration and authentication unchanged
- Error handling unchanged

## Configuration

Ensure your `config.php` has the correct settings:

```php
return [
    'MPS_BASE_URL' => 'https://api.mpsmonitor.com',
    'MPS_API_KEY' => 'your-api-key',
    // OR for OAuth:
    'AUTH_MODE' => 'oauth_password',
    'TOKEN_URL' => 'https://api.mpsmonitor.com/token',
    'CLIENT_ID' => 'your-client-id',
    'CLIENT_SECRET' => 'your-client-secret',
    'USERNAME' => 'your-username',
    'PASSWORD' => 'your-password',
    'SCOPE' => 'api',

    'MPS_TIMEOUT' => 30,
    'MPS_CONNECT_TIMEOUT' => 10,
    'MPS_DEBUG' => false,
    'MPS_MAX_RETRIES' => 3,
];
```

## Next Steps

1. **Explore Available Operations**: Use `getAvailableEndpoints()` to see all 544 operations
2. **Update Your Code**: Replace direct `makeRequest()` calls with `dispatchAction()` for automatic parameter handling
3. **Test Your Use Cases**: Verify your specific API calls work with the canonical swagger
4. **Enable Debug Mode**: Set `MPS_DEBUG => true` in config.php to see detailed request/response logs

## Support

- Swagger specification: `.canonical/Swagger.json`
- API Documentation: `.canonical/MPS_Monitor_API_Endpoints.html`
- SDK Examples: `.canonical/SDK_Examples_Verified_Working.md`

## Summary

✅ Engine refactored to use canonical swagger
✅ All 544 API operations now available
✅ Automatic parameter parsing and routing
✅ Full backward compatibility maintained
✅ No breaking changes to existing code

The API engine is now ready to work with the complete MPS Monitor API specification!
