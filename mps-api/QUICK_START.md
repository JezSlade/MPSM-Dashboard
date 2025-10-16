# Quick Start - MPS API Engine with Canonical Swagger

## Installation Complete ✓

The API engine has been refactored to work with the full canonical swagger (544 operations).

## 5-Minute Quick Start

### 1. Initialize the Engine

```php
<?php
require_once 'mps-api/engine.php';

$engine = MPSMonitorEngine::getInstance();
```

### 2. Check Available Operations

```php
$endpoints = $engine->getAvailableEndpoints();
echo "Available: " . $endpoints['count'] . " operations\n";
```

### 3. Make Your First Call

```php
// Get user profile
$result = $engine->dispatchAction('Account/GetProfile');

if ($result['success']) {
    print_r($result['data']);
} else {
    echo "Error: " . $result['error'] . "\n";
}
```

## Common Operations

### Dealers
```php
// List dealers
$result = $engine->dispatchAction('Dealer/GetDealers', [
    'request' => ['pageNumber' => 1, 'pageSize' => 50]
]);

// Get specific dealer
$result = $engine->dispatchAction('Dealer/Get', [
    'code' => 'DEALER001'
]);
```

### Customers
```php
// List customers
$result = $engine->dispatchAction('Customer/GetCustomers', [
    'request' => [
        'dealerCode' => 'DEALER001',
        'pageNumber' => 1,
        'pageSize' => 50
    ]
]);
```

### Devices
```php
// List devices
$result = $engine->dispatchAction('Device/List', [
    'request' => [
        'customerCode' => 'CUST001',
        'pageNumber' => 1,
        'pageSize' => 50
    ]
]);

// Get device details
$result = $engine->dispatchAction('Device/Get', [
    'id' => 'device-id'
]);
```

## HTTP API Access

### Base URL
```
http://your-domain.com/mps-api/
```

### Endpoints
- `GET /` - Service info (shows 544 operations available)
- `GET /health` - Health check
- `GET /endpoints` - List all operations
- `POST /query` - Execute any operation

### Example Request
```bash
curl -X POST http://your-domain.com/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{
    "action": "Device/List",
    "params": {
      "request": {
        "customerCode": "CUST001",
        "pageNumber": 1,
        "pageSize": 50
      }
    }
  }'
```

## Finding Operations

### Method 1: List All Operations
```php
$endpoints = $engine->getAvailableEndpoints();

foreach ($endpoints['groups'] as $group => $operations) {
    echo "\n[{$group}]:\n";
    foreach ($operations as $op) {
        echo "  - {$op['action']} ({$op['method']} {$op['path']})\n";
    }
}
```

### Method 2: View in Browser
Visit: `http://your-domain.com/mps-api/endpoints`

### Method 3: Check Swagger
View the full canonical swagger: `http://your-domain.com/mps-api/swagger.json`

## Configuration

Ensure your [config.php](config.php) is set up:

```php
<?php
return [
    'MPS_BASE_URL' => 'https://api.mpsmonitor.com',
    'MPS_API_KEY' => 'your-api-key-here',

    // Optional
    'MPS_TIMEOUT' => 30,
    'MPS_DEBUG' => false,
    'MPS_MAX_RETRIES' => 3,
];
```

## Error Handling

```php
$result = $engine->dispatchAction('SomeAction', $params);

if (!$result['success']) {
    echo "Error: " . $result['error'] . "\n";
    echo "Code: " . $result['error_code'] . "\n";

    if (isset($result['http_code'])) {
        echo "HTTP: " . $result['http_code'] . "\n";
    }
}
```

## Enable Debug Mode

In [config.php](config.php):
```php
'MPS_DEBUG' => true,
```

Then check logs in: `mps-api/logs/`

## Available Operation Groups (49 total)

- **Account** (22 ops) - User accounts & authentication
- **AlertLimit** (34 ops) - Alert configuration
- **Customer** (19 ops) - Customer management
- **Dealer** (34 ops) - Dealer management
- **Device** (34 ops) - Device monitoring
- **Explorer** (73 ops) - Advanced queries
- **Product** - Product catalog
- **Supply** - Supply management
- **Report** - Reporting
- **Counter** - Meter readings
- And 40 more...

## Need More Examples?

See [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) for comprehensive examples of all major operations.

## Troubleshooting

### Operation Not Found
```php
// List all operations to find the correct name
$endpoints = $engine->getAvailableEndpoints();
print_r($endpoints);
```

### Authentication Errors
- Check `MPS_API_KEY` in config.php
- Verify API endpoint URL is correct
- Enable debug mode to see detailed error logs

### Connection Errors
- Verify `MPS_BASE_URL` is correct
- Check firewall/proxy settings
- Increase timeout in config if needed

## Documentation

- **Full Documentation**: [CANONICAL_REFACTOR_SUMMARY.md](CANONICAL_REFACTOR_SUMMARY.md)
- **Usage Examples**: [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md)
- **Complete Status**: [../REFACTOR_COMPLETE.md](../REFACTOR_COMPLETE.md)

## Summary

✅ 544 operations available
✅ 49 API groups
✅ Full backward compatibility
✅ Production ready

You're all set! Start using the API with any of the 544 available operations.
