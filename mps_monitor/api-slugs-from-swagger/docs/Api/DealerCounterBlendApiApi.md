# OpenAPI\Client\DealerCounterBlendApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**dealerCounterBlendClearCounter()**](DealerCounterBlendApiApi.md#dealerCounterBlendClearCounter) | **DELETE** /Dealer/CounterBlend/ClearCounter | Clear the counter readings for a specific blend counter |
| [**dealerCounterBlendCreate()**](DealerCounterBlendApiApi.md#dealerCounterBlendCreate) | **POST** /Dealer/CounterBlend/Create | Create the counter blend Field and related  descriptions |
| [**dealerCounterBlendDelete()**](DealerCounterBlendApiApi.md#dealerCounterBlendDelete) | **DELETE** /Dealer/CounterBlend/Delete | Delete the Dealer counter blend field |
| [**dealerCounterBlendGet()**](DealerCounterBlendApiApi.md#dealerCounterBlendGet) | **GET** /Dealer/CounterBlend/Get | Return a counter blend |
| [**dealerCounterBlendList()**](DealerCounterBlendApiApi.md#dealerCounterBlendList) | **GET** /Dealer/CounterBlend/List | Returns list of dealer counters detailed tags |
| [**dealerCounterBlendSearch()**](DealerCounterBlendApiApi.md#dealerCounterBlendSearch) | **GET** /Dealer/CounterBlend/Search | Search form available counters detailed TAG |
| [**dealerCounterBlendUpdate()**](DealerCounterBlendApiApi.md#dealerCounterBlendUpdate) | **PUT** /Dealer/CounterBlend/Update | Update the counter blend Field and related  descriptions |


## `dealerCounterBlendClearCounter()`

```php
dealerCounterBlendClearCounter($id, $brand): \OpenAPI\Client\Model\BaseResponse
```

Clear the counter readings for a specific blend counter

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$brand = 'brand_example'; // string

try {
    $result = $apiInstance->dealerCounterBlendClearCounter($id, $brand);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendApiApi->dealerCounterBlendClearCounter: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **brand** | **string**|  | [optional] |

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

## `dealerCounterBlendCreate()`

```php
dealerCounterBlendCreate($request): \OpenAPI\Client\Model\SingleResultResponseDealerCounterBlendListDto
```

Create the counter blend Field and related  descriptions

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestDealerCounterBlendDto(); // \OpenAPI\Client\Model\CreateRequestDealerCounterBlendDto

try {
    $result = $apiInstance->dealerCounterBlendCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendApiApi->dealerCounterBlendCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestDealerCounterBlendDto**](../Model/CreateRequestDealerCounterBlendDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerCounterBlendListDto**](../Model/SingleResultResponseDealerCounterBlendListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerCounterBlendDelete()`

```php
dealerCounterBlendDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Delete the Dealer counter blend field

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerCounterBlendDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendApiApi->dealerCounterBlendDelete: ', $e->getMessage(), PHP_EOL;
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

## `dealerCounterBlendGet()`

```php
dealerCounterBlendGet($dealer_code, $id): \OpenAPI\Client\Model\SingleResultResponseDealerCounterBlendDto
```

Return a counter blend

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerCounterBlendGet($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendApiApi->dealerCounterBlendGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerCounterBlendDto**](../Model/SingleResultResponseDealerCounterBlendDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerCounterBlendList()`

```php
dealerCounterBlendList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $brand, $source, $filter_text): \OpenAPI\Client\Model\PagedResultResponseDealerCounterBlendListDto
```

Returns list of dealer counters detailed tags

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$brand = 'brand_example'; // string | Gets or sets the brand.
$source = 'source_example'; // string | Gets or sets the source.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->dealerCounterBlendList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $brand, $source, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendApiApi->dealerCounterBlendList: ', $e->getMessage(), PHP_EOL;
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
| **brand** | **string**| Gets or sets the brand. | [optional] |
| **source** | **string**| Gets or sets the source. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDealerCounterBlendListDto**](../Model/PagedResultResponseDealerCounterBlendListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerCounterBlendSearch()`

```php
dealerCounterBlendSearch($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $brand, $source, $filter_text): \OpenAPI\Client\Model\PagedResultResponseCounterDetailedAssociableListDto
```

Search form available counters detailed TAG

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$brand = 'brand_example'; // string
$source = 'source_example'; // string
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->dealerCounterBlendSearch($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $brand, $source, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendApiApi->dealerCounterBlendSearch: ', $e->getMessage(), PHP_EOL;
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
| **brand** | **string**|  | [optional] |
| **source** | **string**|  | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseCounterDetailedAssociableListDto**](../Model/PagedResultResponseCounterDetailedAssociableListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerCounterBlendUpdate()`

```php
dealerCounterBlendUpdate($request): \OpenAPI\Client\Model\SingleResultResponseDealerCounterBlendListDto
```

Update the counter blend Field and related  descriptions

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerCounterBlendApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerCounterBlendDto(); // \OpenAPI\Client\Model\UpdateRequestDealerCounterBlendDto

try {
    $result = $apiInstance->dealerCounterBlendUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerCounterBlendApiApi->dealerCounterBlendUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerCounterBlendDto**](../Model/UpdateRequestDealerCounterBlendDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerCounterBlendListDto**](../Model/SingleResultResponseDealerCounterBlendListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
