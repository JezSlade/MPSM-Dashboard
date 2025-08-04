# OpenAPI\Client\DealerCounterBlendToStandardApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**dealerCounterBlendToStandardCreate()**](DealerCounterBlendToStandardApiApi.md#dealerCounterBlendToStandardCreate) | **POST** /Dealer/CounterBlendToStandard/Create | Creates the specified request. |
| [**dealerCounterBlendToStandardDelete()**](DealerCounterBlendToStandardApiApi.md#dealerCounterBlendToStandardDelete) | **DELETE** /Dealer/CounterBlendToStandard/Delete | Deletes the specified request. |
| [**dealerCounterBlendToStandardGet()**](DealerCounterBlendToStandardApiApi.md#dealerCounterBlendToStandardGet) | **GET** /Dealer/CounterBlendToStandard/Get | Gets the specified request. |
| [**dealerCounterBlendToStandardGetByDevice()**](DealerCounterBlendToStandardApiApi.md#dealerCounterBlendToStandardGetByDevice) | **GET** /Dealer/CounterBlendToStandard/GetByDevice | Gets the by device. |
| [**dealerCounterBlendToStandardList()**](DealerCounterBlendToStandardApiApi.md#dealerCounterBlendToStandardList) | **GET** /Dealer/CounterBlendToStandard/List | Lists the specified request. |
| [**dealerCounterBlendToStandardUpdate()**](DealerCounterBlendToStandardApiApi.md#dealerCounterBlendToStandardUpdate) | **PUT** /Dealer/CounterBlendToStandard/Update | Updates the specified request. |


## `dealerCounterBlendToStandardCreate()`

```php
dealerCounterBlendToStandardCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Creates the specified request.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendToStandardApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateDealerCountersBlendedToStandardRequest(); // \OpenAPI\Client\Model\CreateDealerCountersBlendedToStandardRequest | The request.

try {
    $result = $apiInstance->dealerCounterBlendToStandardCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendToStandardApiApi->dealerCounterBlendToStandardCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateDealerCountersBlendedToStandardRequest**](../Model/CreateDealerCountersBlendedToStandardRequest.md)| The request. | |

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

## `dealerCounterBlendToStandardDelete()`

```php
dealerCounterBlendToStandardDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Deletes the specified request.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendToStandardApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerCounterBlendToStandardDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendToStandardApiApi->dealerCounterBlendToStandardDelete: ', $e->getMessage(), PHP_EOL;
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

## `dealerCounterBlendToStandardGet()`

```php
dealerCounterBlendToStandardGet($id): \OpenAPI\Client\Model\SingleResultResponseDealerCounterBlendToStandardListDto
```

Gets the specified request.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendToStandardApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerCounterBlendToStandardGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendToStandardApiApi->dealerCounterBlendToStandardGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerCounterBlendToStandardListDto**](../Model/SingleResultResponseDealerCounterBlendToStandardListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerCounterBlendToStandardGetByDevice()`

```php
dealerCounterBlendToStandardGetByDevice($id): \OpenAPI\Client\Model\SingleResultResponseDeviceCounterBlendToStandardDto
```

Gets the by device.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendToStandardApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerCounterBlendToStandardGetByDevice($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendToStandardApiApi->dealerCounterBlendToStandardGetByDevice: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceCounterBlendToStandardDto**](../Model/SingleResultResponseDeviceCounterBlendToStandardDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerCounterBlendToStandardList()`

```php
dealerCounterBlendToStandardList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $filter_text): \OpenAPI\Client\Model\PagedResultResponseDealerCounterBlendToStandardListDto
```

Lists the specified request.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendToStandardApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->dealerCounterBlendToStandardList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendToStandardApiApi->dealerCounterBlendToStandardList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the code. | |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDealerCounterBlendToStandardListDto**](../Model/PagedResultResponseDealerCounterBlendToStandardListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerCounterBlendToStandardUpdate()`

```php
dealerCounterBlendToStandardUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Updates the specified request.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendToStandardApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateDealerCountersBlendedToStandardRequest(); // \OpenAPI\Client\Model\UpdateDealerCountersBlendedToStandardRequest | The request.

try {
    $result = $apiInstance->dealerCounterBlendToStandardUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendToStandardApiApi->dealerCounterBlendToStandardUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateDealerCountersBlendedToStandardRequest**](../Model/UpdateDealerCountersBlendedToStandardRequest.md)| The request. | |

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
