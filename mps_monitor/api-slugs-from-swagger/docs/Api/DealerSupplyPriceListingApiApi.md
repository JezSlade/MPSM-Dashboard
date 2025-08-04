# OpenAPI\Client\DealerSupplyPriceListingApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**dealerSupplyPriceListingCreate()**](DealerSupplyPriceListingApiApi.md#dealerSupplyPriceListingCreate) | **POST** /DealerSupplyPriceListing/Create | Create the tradingPartnerSupplyListing |
| [**dealerSupplyPriceListingCustomerCreate()**](DealerSupplyPriceListingApiApi.md#dealerSupplyPriceListingCustomerCreate) | **POST** /DealerSupplyPriceListing/Customer/Create | Create the tradingPartnerSupplyListing for customer |
| [**dealerSupplyPriceListingDelete()**](DealerSupplyPriceListingApiApi.md#dealerSupplyPriceListingDelete) | **DELETE** /DealerSupplyPriceListing/Delete | Deletes the specified Trading Partner. |
| [**dealerSupplyPriceListingGet()**](DealerSupplyPriceListingApiApi.md#dealerSupplyPriceListingGet) | **GET** /DealerSupplyPriceListing/Get | Get tradingPartnerSupplyListing |
| [**dealerSupplyPriceListingList()**](DealerSupplyPriceListingApiApi.md#dealerSupplyPriceListingList) | **GET** /DealerSupplyPriceListing/List | Get tradingPartnerSuppliesListing |
| [**dealerSupplyPriceListingUpdate()**](DealerSupplyPriceListingApiApi.md#dealerSupplyPriceListingUpdate) | **PUT** /DealerSupplyPriceListing/Update | Create the tradingPartnerSupplyListing |
| [**dealerSupplyPriceListingUpdateByCustomer()**](DealerSupplyPriceListingApiApi.md#dealerSupplyPriceListingUpdateByCustomer) | **PUT** /DealerSupplyPriceListing/UpdateByCustomer | Create the tradingPartnerSupplyListing |


## `dealerSupplyPriceListingCreate()`

```php
dealerSupplyPriceListingCreate($request): \OpenAPI\Client\Model\SingleResultResponseDealerSupplyPriceListingDto
```

Create the tradingPartnerSupplyListing

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyPriceListingApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DealerSupplyPriceListingToCreateRequest(); // \OpenAPI\Client\Model\DealerSupplyPriceListingToCreateRequest | The request.

try {
    $result = $apiInstance->dealerSupplyPriceListingCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyPriceListingApiApi->dealerSupplyPriceListingCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DealerSupplyPriceListingToCreateRequest**](../Model/DealerSupplyPriceListingToCreateRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupplyPriceListingDto**](../Model/SingleResultResponseDealerSupplyPriceListingDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplyPriceListingCustomerCreate()`

```php
dealerSupplyPriceListingCustomerCreate($request): \OpenAPI\Client\Model\SingleResultResponseDealerSupplyPriceListingDto
```

Create the tradingPartnerSupplyListing for customer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyPriceListingApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DealerSupplyPriceListingToCreateByCustomerRequest(); // \OpenAPI\Client\Model\DealerSupplyPriceListingToCreateByCustomerRequest | The request.

try {
    $result = $apiInstance->dealerSupplyPriceListingCustomerCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyPriceListingApiApi->dealerSupplyPriceListingCustomerCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DealerSupplyPriceListingToCreateByCustomerRequest**](../Model/DealerSupplyPriceListingToCreateByCustomerRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupplyPriceListingDto**](../Model/SingleResultResponseDealerSupplyPriceListingDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplyPriceListingDelete()`

```php
dealerSupplyPriceListingDelete($dealer_code, $id): \OpenAPI\Client\Model\BaseResponse
```

Deletes the specified Trading Partner.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyPriceListingApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerSupplyPriceListingDelete($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyPriceListingApiApi->dealerSupplyPriceListingDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
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

## `dealerSupplyPriceListingGet()`

```php
dealerSupplyPriceListingGet($dealer_code, $id): \OpenAPI\Client\Model\SingleResultResponseDealerSupplyPriceListingDto
```

Get tradingPartnerSupplyListing

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyPriceListingApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerSupplyPriceListingGet($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyPriceListingApiApi->dealerSupplyPriceListingGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupplyPriceListingDto**](../Model/SingleResultResponseDealerSupplyPriceListingDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplyPriceListingList()`

```php
dealerSupplyPriceListingList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $customer_code, $part_number, $supply_type, $color_type, $show_only_active, $filter_text): \OpenAPI\Client\Model\PagedResultResponseDealerSupplyPriceListingDto
```

Get tradingPartnerSuppliesListing

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyPriceListingApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$customer_code = 'customer_code_example'; // string
$part_number = 'part_number_example'; // string
$supply_type = 'supply_type_example'; // string
$color_type = 'color_type_example'; // string
$show_only_active = True; // bool
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->dealerSupplyPriceListingList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $customer_code, $part_number, $supply_type, $color_type, $show_only_active, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyPriceListingApiApi->dealerSupplyPriceListingList: ', $e->getMessage(), PHP_EOL;
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
| **customer_code** | **string**|  | [optional] |
| **part_number** | **string**|  | [optional] |
| **supply_type** | **string**|  | [optional] |
| **color_type** | **string**|  | [optional] |
| **show_only_active** | **bool**|  | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDealerSupplyPriceListingDto**](../Model/PagedResultResponseDealerSupplyPriceListingDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplyPriceListingUpdate()`

```php
dealerSupplyPriceListingUpdate($request): \OpenAPI\Client\Model\SingleResultResponseDealerSupplyPriceListingDto
```

Create the tradingPartnerSupplyListing

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyPriceListingApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DealerSupplyPriceListingToUpdateRequest(); // \OpenAPI\Client\Model\DealerSupplyPriceListingToUpdateRequest | The request.

try {
    $result = $apiInstance->dealerSupplyPriceListingUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyPriceListingApiApi->dealerSupplyPriceListingUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DealerSupplyPriceListingToUpdateRequest**](../Model/DealerSupplyPriceListingToUpdateRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupplyPriceListingDto**](../Model/SingleResultResponseDealerSupplyPriceListingDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplyPriceListingUpdateByCustomer()`

```php
dealerSupplyPriceListingUpdateByCustomer($request): \OpenAPI\Client\Model\SingleResultResponseDealerSupplyPriceListingDto
```

Create the tradingPartnerSupplyListing

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyPriceListingApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DealerSupplyPriceListingToRequestByCustomerRequest(); // \OpenAPI\Client\Model\DealerSupplyPriceListingToRequestByCustomerRequest | The request.

try {
    $result = $apiInstance->dealerSupplyPriceListingUpdateByCustomer($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyPriceListingApiApi->dealerSupplyPriceListingUpdateByCustomer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DealerSupplyPriceListingToRequestByCustomerRequest**](../Model/DealerSupplyPriceListingToRequestByCustomerRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupplyPriceListingDto**](../Model/SingleResultResponseDealerSupplyPriceListingDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
