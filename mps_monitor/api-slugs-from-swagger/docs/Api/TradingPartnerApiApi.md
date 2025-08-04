# OpenAPI\Client\TradingPartnerApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**tradingPartnerGet()**](TradingPartnerApiApi.md#tradingPartnerGet) | **GET** /TradingPartner/Get | Get tradingPartner |
| [**tradingPartnerList()**](TradingPartnerApiApi.md#tradingPartnerList) | **GET** /TradingPartner/List | Get tradingPartner |
| [**tradingPartnerUpdate()**](TradingPartnerApiApi.md#tradingPartnerUpdate) | **PUT** /TradingPartner/Update | Create the tradingPartner |


## `tradingPartnerGet()`

```php
tradingPartnerGet($dealer_code, $id): \OpenAPI\Client\Model\SingleResultResponseTradingPartnerDto
```

Get tradingPartner

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TradingPartnerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->tradingPartnerGet($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TradingPartnerApiApi->tradingPartnerGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseTradingPartnerDto**](../Model/SingleResultResponseTradingPartnerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `tradingPartnerList()`

```php
tradingPartnerList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $filter_text): \OpenAPI\Client\Model\PagedResultResponseTradingPartnerDto
```

Get tradingPartner

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TradingPartnerApiApi(
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
    $result = $apiInstance->tradingPartnerList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TradingPartnerApiApi->tradingPartnerList: ', $e->getMessage(), PHP_EOL;
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

[**\OpenAPI\Client\Model\PagedResultResponseTradingPartnerDto**](../Model/PagedResultResponseTradingPartnerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `tradingPartnerUpdate()`

```php
tradingPartnerUpdate($request): \OpenAPI\Client\Model\SingleResultResponseTradingPartnerDto
```

Create the tradingPartner

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TradingPartnerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\TradingPartnerUpdateRequest(); // \OpenAPI\Client\Model\TradingPartnerUpdateRequest | The request.

try {
    $result = $apiInstance->tradingPartnerUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TradingPartnerApiApi->tradingPartnerUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\TradingPartnerUpdateRequest**](../Model/TradingPartnerUpdateRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseTradingPartnerDto**](../Model/SingleResultResponseTradingPartnerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
