# OpenAPI\Client\OrderApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**ordersGetOrderAdditionalInfo()**](OrderApi.md#ordersGetOrderAdditionalInfo) | **GET** /Orders/GetOrderAdditionalInfo | Gets the dealers list |
| [**ordersGetOrderLineStatuses()**](OrderApi.md#ordersGetOrderLineStatuses) | **GET** /Orders/GetOrderLineStatuses | Gets the dealers list |
| [**ordersGetOrderLines()**](OrderApi.md#ordersGetOrderLines) | **GET** /Orders/GetOrderLines | Gets the dealers list |
| [**ordersGetOrderProofOfDelivery()**](OrderApi.md#ordersGetOrderProofOfDelivery) | **GET** /Orders/GetOrderProofOfDelivery | Gets the dealers list |
| [**ordersGetOrders()**](OrderApi.md#ordersGetOrders) | **GET** /Orders/GetOrders | Gets the dealers list |


## `ordersGetOrderAdditionalInfo()`

```php
ordersGetOrderAdditionalInfo($request_order_id): \OpenAPI\Client\Model\ListResultResponseOrderAdditionalInfoDto
```

Gets the dealers list

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OrderApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request_order_id = 'request_order_id_example'; // string | Gets or sets the Customer Id.

try {
    $result = $apiInstance->ordersGetOrderAdditionalInfo($request_order_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrderApi->ordersGetOrderAdditionalInfo: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request_order_id** | **string**| Gets or sets the Customer Id. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseOrderAdditionalInfoDto**](../Model/ListResultResponseOrderAdditionalInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `ordersGetOrderLineStatuses()`

```php
ordersGetOrderLineStatuses($request_order_id): \OpenAPI\Client\Model\ListResultResponseOrderLineStatusDto
```

Gets the dealers list

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OrderApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request_order_id = 'request_order_id_example'; // string | Gets or sets the Customer Id.

try {
    $result = $apiInstance->ordersGetOrderLineStatuses($request_order_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrderApi->ordersGetOrderLineStatuses: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request_order_id** | **string**| Gets or sets the Customer Id. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseOrderLineStatusDto**](../Model/ListResultResponseOrderLineStatusDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `ordersGetOrderLines()`

```php
ordersGetOrderLines($request_page_number, $request_page_rows, $request_sort_column, $request_sort_order, $request_dealer_id, $request_order_id, $request_from_date, $request_to_date, $request_status, $request_provide_all_rows): \OpenAPI\Client\Model\PagedResultResponseOrderLineListDto
```

Gets the dealers list

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OrderApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request_page_number = 56; // int | Gets or sets the page number.
$request_page_rows = 56; // int | Gets or sets the page rows.
$request_sort_column = 'request_sort_column_example'; // string | Gets or sets the sort column.
$request_sort_order = 'request_sort_order_example'; // string | Gets or sets the sort order.
$request_dealer_id = 'request_dealer_id_example'; // string | Gets or sets the Dealer Id.
$request_order_id = 'request_order_id_example'; // string | Gets or sets the Customer Id.
$request_from_date = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime | Gets or sets the From Date.
$request_to_date = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime | Gets or sets the To Date.
$request_status = 56; // int | Gets or sets the Purchase Order Id.
$request_provide_all_rows = 56; // int | Gets or sets the Purchase Order Id.

try {
    $result = $apiInstance->ordersGetOrderLines($request_page_number, $request_page_rows, $request_sort_column, $request_sort_order, $request_dealer_id, $request_order_id, $request_from_date, $request_to_date, $request_status, $request_provide_all_rows);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrderApi->ordersGetOrderLines: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request_page_number** | **int**| Gets or sets the page number. | |
| **request_page_rows** | **int**| Gets or sets the page rows. | |
| **request_sort_column** | **string**| Gets or sets the sort column. | |
| **request_sort_order** | **string**| Gets or sets the sort order. | |
| **request_dealer_id** | **string**| Gets or sets the Dealer Id. | [optional] |
| **request_order_id** | **string**| Gets or sets the Customer Id. | [optional] |
| **request_from_date** | **\DateTime**| Gets or sets the From Date. | [optional] |
| **request_to_date** | **\DateTime**| Gets or sets the To Date. | [optional] |
| **request_status** | **int**| Gets or sets the Purchase Order Id. | [optional] |
| **request_provide_all_rows** | **int**| Gets or sets the Purchase Order Id. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseOrderLineListDto**](../Model/PagedResultResponseOrderLineListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `ordersGetOrderProofOfDelivery()`

```php
ordersGetOrderProofOfDelivery($request_order_id): \OpenAPI\Client\Model\ListResultResponseOrderProofOfDeliveryDto
```

Gets the dealers list

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OrderApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request_order_id = 'request_order_id_example'; // string | Gets or sets the Customer Id.

try {
    $result = $apiInstance->ordersGetOrderProofOfDelivery($request_order_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrderApi->ordersGetOrderProofOfDelivery: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request_order_id** | **string**| Gets or sets the Customer Id. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseOrderProofOfDeliveryDto**](../Model/ListResultResponseOrderProofOfDeliveryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `ordersGetOrders()`

```php
ordersGetOrders($request_page_number, $request_page_rows, $request_sort_column, $request_sort_order, $request_dealer_id, $request_customer_id, $request_purchase_order_id, $request_serial_number, $request_from_date, $request_to_date, $request_status): \OpenAPI\Client\Model\PagedResultResponseOrderListDto
```

Gets the dealers list

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OrderApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request_page_number = 56; // int | Gets or sets the page number.
$request_page_rows = 56; // int | Gets or sets the page rows.
$request_sort_column = 'request_sort_column_example'; // string | Gets or sets the sort column.
$request_sort_order = 'request_sort_order_example'; // string | Gets or sets the sort order.
$request_dealer_id = 'request_dealer_id_example'; // string | Gets or sets the Dealer Id.
$request_customer_id = 'request_customer_id_example'; // string | Gets or sets the Customer Id.
$request_purchase_order_id = 56; // int | Gets or sets the Purchase Order Id.
$request_serial_number = 'request_serial_number_example'; // string | Gets or sets the Purchase Order Id.
$request_from_date = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime | Gets or sets the From Date.
$request_to_date = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime | Gets or sets the To Date.
$request_status = 56; // int | Gets or sets the Purchase Order Id.

try {
    $result = $apiInstance->ordersGetOrders($request_page_number, $request_page_rows, $request_sort_column, $request_sort_order, $request_dealer_id, $request_customer_id, $request_purchase_order_id, $request_serial_number, $request_from_date, $request_to_date, $request_status);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrderApi->ordersGetOrders: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request_page_number** | **int**| Gets or sets the page number. | |
| **request_page_rows** | **int**| Gets or sets the page rows. | |
| **request_sort_column** | **string**| Gets or sets the sort column. | |
| **request_sort_order** | **string**| Gets or sets the sort order. | |
| **request_dealer_id** | **string**| Gets or sets the Dealer Id. | [optional] |
| **request_customer_id** | **string**| Gets or sets the Customer Id. | [optional] |
| **request_purchase_order_id** | **int**| Gets or sets the Purchase Order Id. | [optional] |
| **request_serial_number** | **string**| Gets or sets the Purchase Order Id. | [optional] |
| **request_from_date** | **\DateTime**| Gets or sets the From Date. | [optional] |
| **request_to_date** | **\DateTime**| Gets or sets the To Date. | [optional] |
| **request_status** | **int**| Gets or sets the Purchase Order Id. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseOrderListDto**](../Model/PagedResultResponseOrderListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
