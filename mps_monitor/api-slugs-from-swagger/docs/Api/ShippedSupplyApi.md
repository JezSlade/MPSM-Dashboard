# OpenAPI\Client\ShippedSupplyApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**shippedSupplyCreateInAdvance()**](ShippedSupplyApi.md#shippedSupplyCreateInAdvance) | **POST** /ShippedSupply/CreateInAdvance | Creates a shipped supply entry for a device (and alert). |
| [**shippedSupplyCreateOnAlert()**](ShippedSupplyApi.md#shippedSupplyCreateOnAlert) | **POST** /ShippedSupply/CreateOnAlert | Creates a shipped supply entry for a device (and alert). |
| [**shippedSupplyCreateStock()**](ShippedSupplyApi.md#shippedSupplyCreateStock) | **POST** /ShippedSupply/CreateStock | Creates a shipped supply entry for a device (and alert). |
| [**shippedSupplyGet()**](ShippedSupplyApi.md#shippedSupplyGet) | **POST** /ShippedSupply/Get | Gets the specified request. |
| [**shippedSupplyList()**](ShippedSupplyApi.md#shippedSupplyList) | **POST** /ShippedSupply/List | Lists the specified request. |
| [**shippedSupplyLog()**](ShippedSupplyApi.md#shippedSupplyLog) | **POST** /ShippedSupply/Log | Gets the log. |
| [**shippedSupplyMassiveUpdate()**](ShippedSupplyApi.md#shippedSupplyMassiveUpdate) | **POST** /ShippedSupply/MassiveUpdate | Update massive supply alerts |
| [**shippedSupplyUpdate()**](ShippedSupplyApi.md#shippedSupplyUpdate) | **POST** /ShippedSupply/Update | Updates the specified request. |


## `shippedSupplyCreateInAdvance()`

```php
shippedSupplyCreateInAdvance($request): \OpenAPI\Client\Model\BaseResponse
```

Creates a shipped supply entry for a device (and alert).

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ShippedSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateShippingSupplyRequest(); // \OpenAPI\Client\Model\CreateShippingSupplyRequest | The request.

try {
    $result = $apiInstance->shippedSupplyCreateInAdvance($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ShippedSupplyApi->shippedSupplyCreateInAdvance: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateShippingSupplyRequest**](../Model/CreateShippingSupplyRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\BaseResponse**](../Model/BaseResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `shippedSupplyCreateOnAlert()`

```php
shippedSupplyCreateOnAlert($request): \OpenAPI\Client\Model\BaseResponse
```

Creates a shipped supply entry for a device (and alert).

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ShippedSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateShippingSupplyOnAlertRequest(); // \OpenAPI\Client\Model\CreateShippingSupplyOnAlertRequest | The request.

try {
    $result = $apiInstance->shippedSupplyCreateOnAlert($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ShippedSupplyApi->shippedSupplyCreateOnAlert: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateShippingSupplyOnAlertRequest**](../Model/CreateShippingSupplyOnAlertRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\BaseResponse**](../Model/BaseResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `shippedSupplyCreateStock()`

```php
shippedSupplyCreateStock($request): \OpenAPI\Client\Model\BaseResponse
```

Creates a shipped supply entry for a device (and alert).

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ShippedSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateShippingSupplyRequest(); // \OpenAPI\Client\Model\CreateShippingSupplyRequest | The request.

try {
    $result = $apiInstance->shippedSupplyCreateStock($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ShippedSupplyApi->shippedSupplyCreateStock: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateShippingSupplyRequest**](../Model/CreateShippingSupplyRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\BaseResponse**](../Model/BaseResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `shippedSupplyGet()`

```php
shippedSupplyGet($request): \OpenAPI\Client\Model\SingleResultResponseShippedSupplyDto
```

Gets the specified request.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ShippedSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetShippedSupplyRequest(); // \OpenAPI\Client\Model\GetShippedSupplyRequest | The request.

try {
    $result = $apiInstance->shippedSupplyGet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ShippedSupplyApi->shippedSupplyGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetShippedSupplyRequest**](../Model/GetShippedSupplyRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseShippedSupplyDto**](../Model/SingleResultResponseShippedSupplyDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `shippedSupplyList()`

```php
shippedSupplyList($request): \OpenAPI\Client\Model\PagedResultResponseShippedSupplyDto
```

Lists the specified request.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ShippedSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetShippedSuppliesRequest(); // \OpenAPI\Client\Model\GetShippedSuppliesRequest | The request.

try {
    $result = $apiInstance->shippedSupplyList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ShippedSupplyApi->shippedSupplyList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetShippedSuppliesRequest**](../Model/GetShippedSuppliesRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseShippedSupplyDto**](../Model/PagedResultResponseShippedSupplyDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `shippedSupplyLog()`

```php
shippedSupplyLog($request): \OpenAPI\Client\Model\ListResultResponseSagaDto
```

Gets the log.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ShippedSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetShippedSupplyRequest(); // \OpenAPI\Client\Model\GetShippedSupplyRequest | The request.

try {
    $result = $apiInstance->shippedSupplyLog($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ShippedSupplyApi->shippedSupplyLog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetShippedSupplyRequest**](../Model/GetShippedSupplyRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseSagaDto**](../Model/ListResultResponseSagaDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `shippedSupplyMassiveUpdate()`

```php
shippedSupplyMassiveUpdate($request): \OpenAPI\Client\Model\SingleResultResponseMassiveShippingSuppliesResponse
```

Update massive supply alerts

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ShippedSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\MassiveShippingSuppliesRequest(); // \OpenAPI\Client\Model\MassiveShippingSuppliesRequest

try {
    $result = $apiInstance->shippedSupplyMassiveUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ShippedSupplyApi->shippedSupplyMassiveUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\MassiveShippingSuppliesRequest**](../Model/MassiveShippingSuppliesRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseMassiveShippingSuppliesResponse**](../Model/SingleResultResponseMassiveShippingSuppliesResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `shippedSupplyUpdate()`

```php
shippedSupplyUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Updates the specified request.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ShippedSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestUpdateShippedSupplyDto(); // \OpenAPI\Client\Model\UpdateRequestUpdateShippedSupplyDto | The request.

try {
    $result = $apiInstance->shippedSupplyUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ShippedSupplyApi->shippedSupplyUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestUpdateShippedSupplyDto**](../Model/UpdateRequestUpdateShippedSupplyDto.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\BaseResponse**](../Model/BaseResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
