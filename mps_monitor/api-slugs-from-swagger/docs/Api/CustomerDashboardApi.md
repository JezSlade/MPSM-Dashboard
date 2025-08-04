# OpenAPI\Client\CustomerDashboardApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**customerDashboard()**](CustomerDashboardApi.md#customerDashboard) | **GET** /CustomerDashboard | Gets the customer&#39;s dashboard. |
| [**customerDashboardConnectors()**](CustomerDashboardApi.md#customerDashboardConnectors) | **POST** /CustomerDashboard/Connectors |  |
| [**customerDashboardDevices()**](CustomerDashboardApi.md#customerDashboardDevices) | **POST** /CustomerDashboard/Devices |  |
| [**customerDashboardGet()**](CustomerDashboardApi.md#customerDashboardGet) | **POST** /CustomerDashboard/Get | Gets the customer&#39;s dashboard. |
| [**customerDashboardPages()**](CustomerDashboardApi.md#customerDashboardPages) | **GET** /CustomerDashboard/Pages |  |


## `customerDashboard()`

```php
customerDashboard($code): \OpenAPI\Client\Model\SingleResultResponseCustomerDashboardDto
```

Gets the customer's dashboard.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerDashboardApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->customerDashboard($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerDashboardApi->customerDashboard: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerDashboardDto**](../Model/SingleResultResponseCustomerDashboardDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerDashboardConnectors()`

```php
customerDashboardConnectors($request): \OpenAPI\Client\Model\SingleResultResponseCustomerDashboardConnectorsDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerDashboardApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CustomerDashboardRequest(); // \OpenAPI\Client\Model\CustomerDashboardRequest

try {
    $result = $apiInstance->customerDashboardConnectors($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerDashboardApi->customerDashboardConnectors: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CustomerDashboardRequest**](../Model/CustomerDashboardRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerDashboardConnectorsDto**](../Model/SingleResultResponseCustomerDashboardConnectorsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerDashboardDevices()`

```php
customerDashboardDevices($request): \OpenAPI\Client\Model\SingleResultResponseCustomerDashboardDevicesDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerDashboardApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CustomerDashboardRequest(); // \OpenAPI\Client\Model\CustomerDashboardRequest

try {
    $result = $apiInstance->customerDashboardDevices($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerDashboardApi->customerDashboardDevices: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CustomerDashboardRequest**](../Model/CustomerDashboardRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerDashboardDevicesDto**](../Model/SingleResultResponseCustomerDashboardDevicesDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerDashboardGet()`

```php
customerDashboardGet($request): \OpenAPI\Client\Model\SingleResultResponseMpsDashboardCustomerDto
```

Gets the customer's dashboard.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerDashboardApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByCodeRequest(); // \OpenAPI\Client\Model\GetByCodeRequest | The request.

try {
    $result = $apiInstance->customerDashboardGet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerDashboardApi->customerDashboardGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByCodeRequest**](../Model/GetByCodeRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseMpsDashboardCustomerDto**](../Model/SingleResultResponseMpsDashboardCustomerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerDashboardPages()`

```php
customerDashboardPages($code): \OpenAPI\Client\Model\SingleResultResponseCustomerDashboardPagesDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerDashboardApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->customerDashboardPages($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerDashboardApi->customerDashboardPages: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerDashboardPagesDto**](../Model/SingleResultResponseCustomerDashboardPagesDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
