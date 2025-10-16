# MPS API Engine - Usage Examples with Canonical Swagger

This document provides practical examples of using the refactored API engine with the full canonical swagger specification (544 operations).

## Table of Contents

- [Basic Setup](#basic-setup)
- [Account Operations](#account-operations)
- [Dealer Operations](#dealer-operations)
- [Customer Operations](#customer-operations)
- [Device Operations](#device-operations)
- [Alert Management](#alert-management)
- [Product & Supply Operations](#product--supply-operations)
- [Reporting](#reporting)
- [Explorer API](#explorer-api)
- [Advanced Usage](#advanced-usage)

## Basic Setup

```php
<?php
require_once 'mps-api/engine.php';

// Initialize the engine (loads canonical swagger automatically)
$engine = MPSMonitorEngine::getInstance();

// Check available endpoints
$endpoints = $engine->getAvailableEndpoints();
echo "Available operations: " . $endpoints['count'] . "\n";
```

## Account Operations

### Get Current User Profile

```php
// Get profile of authenticated user
$result = $engine->dispatchAction('Account/GetProfile');

if ($result['success']) {
    $profile = $result['data'];
    echo "User: " . $profile['userName'] . "\n";
    echo "Email: " . $profile['email'] . "\n";
}
```

### Update User Profile

```php
$result = $engine->dispatchAction('Account/UpdateProfile', [
    'request' => [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john.doe@example.com',
        'phoneNumber' => '+1234567890'
    ]
]);
```

### Get Account Details

```php
$result = $engine->dispatchAction('Account/GetAccount', [
    'request' => [
        'dealerCode' => 'DEALER001',
        'id' => 'account-id-here'
    ]
]);
```

### List Accounts with Pagination

```php
$result = $engine->dispatchAction('Account/GetAccounts', [
    'request' => [
        'dealerCode' => 'DEALER001',
        'pageNumber' => 1,
        'pageSize' => 50,
        'sortField' => 'userName',
        'sortDirection' => 'Asc'
    ]
]);
```

## Dealer Operations

### Get Dealer Information

```php
$result = $engine->dispatchAction('Dealer/Get', [
    'code' => 'DEALER001'
]);

if ($result['success']) {
    $dealer = $result['data'];
    echo "Dealer: " . $dealer['name'] . "\n";
    echo "Code: " . $dealer['code'] . "\n";
}
```

### Get Dealers (Search/List)

```php
$result = $engine->dispatchAction('Dealer/GetDealers', [
    'request' => [
        'pageNumber' => 1,
        'pageSize' => 100,
        'filterText' => 'Office', // Optional search
        'sortField' => 'name',
        'sortDirection' => 'Asc'
    ]
]);
```

### Create Dealer

```php
$result = $engine->dispatchAction('Dealer/Create', [
    'request' => [
        'code' => 'NEWDEALER',
        'name' => 'New Dealer Name',
        'email' => 'contact@dealer.com',
        'address' => '123 Main St',
        'city' => 'New York',
        'state' => 'NY',
        'zipCode' => '10001',
        'country' => 'USA'
    ]
]);
```

### Update Dealer

```php
$result = $engine->dispatchAction('Dealer/Update', [
    'request' => [
        'code' => 'DEALER001',
        'name' => 'Updated Dealer Name',
        'email' => 'newemail@dealer.com'
    ]
]);
```

## Customer Operations

### Get Customer

```php
$result = $engine->dispatchAction('Customer/Get', [
    'code' => 'CUST001'
]);
```

### List Customers

```php
$result = $engine->dispatchAction('Customer/GetCustomers', [
    'request' => [
        'dealerCode' => 'DEALER001',
        'pageNumber' => 1,
        'pageSize' => 50,
        'filterText' => 'Company', // Optional search
        'sortField' => 'name',
        'sortDirection' => 'Asc'
    ]
]);
```

### Create Customer

```php
$result = $engine->dispatchAction('Customer/Create', [
    'request' => [
        'dealerCode' => 'DEALER001',
        'code' => 'NEWCUST',
        'name' => 'New Customer Inc',
        'email' => 'contact@customer.com',
        'address' => '456 Business Ave',
        'city' => 'Los Angeles',
        'state' => 'CA',
        'zipCode' => '90001'
    ]
]);
```

### Update Customer

```php
$result = $engine->dispatchAction('Customer/Update', [
    'request' => [
        'code' => 'CUST001',
        'name' => 'Updated Customer Name',
        'email' => 'newemail@customer.com'
    ]
]);
```

## Device Operations

### List Devices

```php
$result = $engine->dispatchAction('Device/List', [
    'request' => [
        'customerCode' => 'CUST001',
        'pageNumber' => 1,
        'pageSize' => 50,
        'filterText' => 'HP', // Optional search by model/serial
        'includeOffline' => true
    ]
]);
```

### Get Device Details

```php
$result = $engine->dispatchAction('Device/Get', [
    'id' => 'device-id-here'
]);

if ($result['success']) {
    $device = $result['data'];
    echo "Model: " . $device['model'] . "\n";
    echo "Serial: " . $device['serialNumber'] . "\n";
    echo "Status: " . $device['status'] . "\n";
}
```

### Get Device Meters

```php
$result = $engine->dispatchAction('Device/GetMeters', [
    'id' => 'device-id-here',
    'includeHistory' => true
]);
```

### Get Device Supplies

```php
$result = $engine->dispatchAction('Device/GetSupplies', [
    'id' => 'device-id-here'
]);
```

### Update Device

```php
$result = $engine->dispatchAction('Device/Update', [
    'request' => [
        'id' => 'device-id-here',
        'nickname' => 'Office Printer',
        'location' => 'Floor 2, Room 201'
    ]
]);
```

## Alert Management

### Get Alert Limits for Dealer

```php
$result = $engine->dispatchAction('AlertLimit2/Dealer/GetDefault', [
    'code' => 'DEALER001'
]);
```

### Update Dealer Alert Limits

```php
$result = $engine->dispatchAction('AlertLimit2/Dealer/UpdateDefault', [
    'request' => [
        'dealerCode' => 'DEALER001',
        'items' => [
            [
                'supplyType' => 'Toner',
                'colorType' => 'Black',
                'alertPercentage' => 15,
                'orderPercentage' => 10
            ],
            [
                'supplyType' => 'Toner',
                'colorType' => 'Cyan',
                'alertPercentage' => 15,
                'orderPercentage' => 10
            ]
        ]
    ]
]);
```

### Get Device Alert Limits

```php
$result = $engine->dispatchAction('AlertLimit2/Device/GetDefault', [
    'id' => 'device-id-here'
]);
```

### Get All Alert Limits

```php
$result = $engine->dispatchAction('AlertLimit2/GetAllLimits', [
    'dealerId' => 'dealer-id',
    'customerId' => 'customer-id',
    'alertLimitSource' => 'Customer' // Default, Dealer, Customer, Device, etc.
]);
```

## Product & Supply Operations

### List Products

```php
$result = $engine->dispatchAction('Product/GetProducts', [
    'request' => [
        'pageNumber' => 1,
        'pageSize' => 100,
        'filterText' => 'LaserJet',
        'manufacturer' => 'HP'
    ]
]);
```

### Get Product Details

```php
$result = $engine->dispatchAction('Product/Get', [
    'id' => 'product-id-here'
]);
```

### List Installed Products

```php
$result = $engine->dispatchAction('InstalledProduct/GetInstalledProducts', [
    'request' => [
        'customerCode' => 'CUST001',
        'pageNumber' => 1,
        'pageSize' => 50
    ]
]);
```

### Get Supply Orders

```php
$result = $engine->dispatchAction('Supply/GetOrders', [
    'request' => [
        'dealerCode' => 'DEALER001',
        'startDate' => '2024-01-01',
        'endDate' => '2024-12-31',
        'pageNumber' => 1,
        'pageSize' => 50
    ]
]);
```

## Reporting

### Get Counter Reports

```php
$result = $engine->dispatchAction('Counter/GetReport', [
    'request' => [
        'customerCode' => 'CUST001',
        'startDate' => '2024-01-01',
        'endDate' => '2024-01-31',
        'reportType' => 'Monthly'
    ]
]);
```

### Get Device Statistics

```php
$result = $engine->dispatchAction('Report/GetDeviceStatistics', [
    'request' => [
        'deviceId' => 'device-id-here',
        'startDate' => '2024-01-01',
        'endDate' => '2024-01-31'
    ]
]);
```

## Explorer API

The Explorer API provides powerful query capabilities across the entire dataset.

### Explore Devices

```php
$result = $engine->dispatchAction('Explorer/Device', [
    'request' => [
        'pageNumber' => 1,
        'pageSize' => 50,
        'filters' => [
            [
                'field' => 'manufacturer',
                'operator' => 'equals',
                'value' => 'HP'
            ],
            [
                'field' => 'status',
                'operator' => 'equals',
                'value' => 'Online'
            ]
        ],
        'sortField' => 'lastSeen',
        'sortDirection' => 'Desc'
    ]
]);
```

### Explore Customers

```php
$result = $engine->dispatchAction('Explorer/Customer', [
    'request' => [
        'dealerCode' => 'DEALER001',
        'pageNumber' => 1,
        'pageSize' => 100,
        'filters' => [
            [
                'field' => 'totalDevices',
                'operator' => 'greaterThan',
                'value' => 10
            ]
        ]
    ]
]);
```

## Advanced Usage

### Check Operation Details

```php
// Get all available endpoints
$endpoints = $engine->getAvailableEndpoints();

// Find specific operation
foreach ($endpoints['groups'] as $group => $operations) {
    foreach ($operations as $op) {
        if ($op['action'] === 'Device/List') {
            echo "Method: " . $op['method'] . "\n";
            echo "Path: " . $op['path'] . "\n";
            echo "Summary: " . $op['summary'] . "\n";
        }
    }
}
```

### Error Handling

```php
$result = $engine->dispatchAction('Device/Get', [
    'id' => 'invalid-id'
]);

if (!$result['success']) {
    echo "Error: " . $result['error'] . "\n";
    echo "Error Code: " . $result['error_code'] . "\n";

    if (isset($result['http_code'])) {
        echo "HTTP Status: " . $result['http_code'] . "\n";
    }

    // In debug mode, you'll get more details
    if (isset($result['error_detail'])) {
        echo "Details: " . $result['error_detail'] . "\n";
    }
}
```

### Batch Operations

```php
// Process multiple devices
$deviceIds = ['dev1', 'dev2', 'dev3'];
$results = [];

foreach ($deviceIds as $deviceId) {
    $result = $engine->dispatchAction('Device/Get', [
        'id' => $deviceId
    ]);
    $results[$deviceId] = $result;
}
```

### Using Raw makeRequest (Legacy)

The old `makeRequest()` method still works for backward compatibility:

```php
// Old way (still supported)
$result = $engine->makeRequest('devices', 'GET', [], ['status' => 'online']);

// New way (recommended - uses swagger spec)
$result = $engine->dispatchAction('Device/List', [
    'request' => ['status' => 'online']
]);
```

## Tips

1. **Use dispatchAction()** - It automatically handles parameter routing (path, query, body, headers)
2. **Enable Debug Mode** - Set `MPS_DEBUG => true` in config.php to see detailed logs
3. **Check Swagger Spec** - Review `.canonical/Swagger.json` for exact parameter names
4. **Handle Pagination** - Most list operations support `pageNumber` and `pageSize`
5. **Use Filters** - Many operations support `filterText` for searching
6. **Check Response Structure** - Responses follow MPS API patterns (BaseResponse, SingleResultResponse, ListResultResponse, PagedResultResponse)

## Getting Help

- **List all operations**: `$engine->getAvailableEndpoints()`
- **Check swagger path**: `SwaggerActionRegistry::getInstance()->getSpecPath()`
- **View API docs**: Open `.canonical/MPS_Monitor_API_Endpoints.html` in browser
- **Review examples**: See `.canonical/SDK_Examples_Verified_Working.md`

## Complete Example

```php
<?php
require_once 'mps-api/engine.php';

try {
    // Initialize
    $engine = MPSMonitorEngine::getInstance();

    // Get dealers
    $dealersResult = $engine->dispatchAction('Dealer/GetDealers', [
        'request' => [
            'pageNumber' => 1,
            'pageSize' => 10
        ]
    ]);

    if ($dealersResult['success']) {
        $dealers = $dealersResult['data']['items'];

        foreach ($dealers as $dealer) {
            echo "Dealer: {$dealer['name']} ({$dealer['code']})\n";

            // Get customers for this dealer
            $customersResult = $engine->dispatchAction('Customer/GetCustomers', [
                'request' => [
                    'dealerCode' => $dealer['code'],
                    'pageNumber' => 1,
                    'pageSize' => 5
                ]
            ]);

            if ($customersResult['success']) {
                $customers = $customersResult['data']['items'];
                echo "  Customers: " . count($customers) . "\n";

                foreach ($customers as $customer) {
                    echo "    - {$customer['name']}\n";
                }
            }
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

This will work with all 544 operations in the canonical swagger!
