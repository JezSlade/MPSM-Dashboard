# OpenAPI\Client\CostCenterApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**costCenterCreate()**](CostCenterApi.md#costCenterCreate) | **POST** /CostCenter/Create | Creates the office. |
| [**costCenterDelete()**](CostCenterApi.md#costCenterDelete) | **DELETE** /CostCenter/Delete | Delete  office. |
| [**costCenterGet()**](CostCenterApi.md#costCenterGet) | **POST** /CostCenter/Get | Gets the office. |
| [**costCenterList()**](CostCenterApi.md#costCenterList) | **POST** /CostCenter/List | Gets the offices. |
| [**costCenterUpdate()**](CostCenterApi.md#costCenterUpdate) | **POST** /CostCenter/Update | Updates the office. |


## `costCenterCreate()`

```php
costCenterCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Creates the office.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CostCenterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestCostCenterDto(); // \OpenAPI\Client\Model\CreateRequestCostCenterDto | The request.

try {
    $result = $apiInstance->costCenterCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CostCenterApi->costCenterCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestCostCenterDto**](../Model/CreateRequestCostCenterDto.md)| The request. | |

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

## `costCenterDelete()`

```php
costCenterDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Delete  office.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CostCenterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->costCenterDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CostCenterApi->costCenterDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\BaseResponse**](../Model/BaseResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `costCenterGet()`

```php
costCenterGet($request): \OpenAPI\Client\Model\SingleResultResponseCostCenterDto
```

Gets the office.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CostCenterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->costCenterGet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CostCenterApi->costCenterGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCostCenterDto**](../Model/SingleResultResponseCostCenterDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `costCenterList()`

```php
costCenterList($request): \OpenAPI\Client\Model\PagedResultResponseCostCenterDto
```

Gets the offices.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CostCenterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetCostCentersRequest(); // \OpenAPI\Client\Model\GetCostCentersRequest | The request.

try {
    $result = $apiInstance->costCenterList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CostCenterApi->costCenterList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetCostCentersRequest**](../Model/GetCostCentersRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseCostCenterDto**](../Model/PagedResultResponseCostCenterDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `costCenterUpdate()`

```php
costCenterUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Updates the office.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CostCenterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestCostCenterDto(); // \OpenAPI\Client\Model\UpdateRequestCostCenterDto | The request.

try {
    $result = $apiInstance->costCenterUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CostCenterApi->costCenterUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestCostCenterDto**](../Model/UpdateRequestCostCenterDto.md)| The request. | |

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
