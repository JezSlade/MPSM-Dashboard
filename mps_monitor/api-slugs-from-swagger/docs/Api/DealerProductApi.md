# OpenAPI\Client\DealerProductApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**dealerProductCreate()**](DealerProductApi.md#dealerProductCreate) | **POST** /DealerProduct/Create | create the dealerProductReplacement |
| [**dealerProductDelete()**](DealerProductApi.md#dealerProductDelete) | **DELETE** /DealerProduct/Delete | Delete DealerProductReplacement |
| [**dealerProductEdit()**](DealerProductApi.md#dealerProductEdit) | **PUT** /DealerProduct/Edit | Edit the dealerProductReplacement |
| [**dealerProductGet()**](DealerProductApi.md#dealerProductGet) | **GET** /DealerProduct/Get | Gets the specified request. |
| [**dealerProductList()**](DealerProductApi.md#dealerProductList) | **GET** /DealerProduct/List | Gets the dealer list |


## `dealerProductCreate()`

```php
dealerProductCreate($request): \OpenAPI\Client\Model\SingleResultResponseDealerProductReplacementDto
```

create the dealerProductReplacement

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateDealerProductReplacementRequest(); // \OpenAPI\Client\Model\CreateDealerProductReplacementRequest | The request.

try {
    $result = $apiInstance->dealerProductCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerProductApi->dealerProductCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateDealerProductReplacementRequest**](../Model/CreateDealerProductReplacementRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerProductReplacementDto**](../Model/SingleResultResponseDealerProductReplacementDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerProductDelete()`

```php
dealerProductDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Delete DealerProductReplacement

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerProductDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerProductApi->dealerProductDelete: ', $e->getMessage(), PHP_EOL;
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

## `dealerProductEdit()`

```php
dealerProductEdit($request): \OpenAPI\Client\Model\SingleResultResponseDealerProductReplacementDto
```

Edit the dealerProductReplacement

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\EditDealerProductReplacementRequest(); // \OpenAPI\Client\Model\EditDealerProductReplacementRequest | The request.

try {
    $result = $apiInstance->dealerProductEdit($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerProductApi->dealerProductEdit: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\EditDealerProductReplacementRequest**](../Model/EditDealerProductReplacementRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerProductReplacementDto**](../Model/SingleResultResponseDealerProductReplacementDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerProductGet()`

```php
dealerProductGet($dealer_code, $id): \OpenAPI\Client\Model\SingleResultResponseDealerProductReplacementDto
```

Gets the specified request.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerProductGet($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerProductApi->dealerProductGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerProductReplacementDto**](../Model/SingleResultResponseDealerProductReplacementDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerProductList()`

```php
dealerProductList($page_number, $page_rows, $sort_column, $sort_order, $dealer_code, $filter_text): \OpenAPI\Client\Model\PagedResultResponseDealerProductReplacementDto
```

Gets the dealer list

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$dealer_code = 'dealer_code_example'; // string | Gets or sets the dealer code.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->dealerProductList($page_number, $page_rows, $sort_column, $sort_order, $dealer_code, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerProductApi->dealerProductList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **dealer_code** | **string**| Gets or sets the dealer code. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDealerProductReplacementDto**](../Model/PagedResultResponseDealerProductReplacementDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
