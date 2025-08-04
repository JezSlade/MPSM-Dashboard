# OpenAPI\Client\ConsumableApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**consumableList()**](ConsumableApiApi.md#consumableList) | **POST** /Consumable/List | List all devices consumables |


## `consumableList()`

```php
consumableList($request): \OpenAPI\Client\Model\ListResultResponseConsumablesDeviceDto
```

List all devices consumables

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ConsumableApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetConsumablesRequest(); // \OpenAPI\Client\Model\GetConsumablesRequest

try {
    $result = $apiInstance->consumableList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConsumableApiApi->consumableList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetConsumablesRequest**](../Model/GetConsumablesRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseConsumablesDeviceDto**](../Model/ListResultResponseConsumablesDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
