# OpenAPI\Client\ProductApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**productCustomerList()**](ProductApi.md#productCustomerList) | **GET** /Product/Customer/List | Get the products of the Customer |
| [**productDealerList()**](ProductApi.md#productDealerList) | **GET** /Product/Dealer/List | Get the products of the Dealer |
| [**productDealerListBrands()**](ProductApi.md#productDealerListBrands) | **GET** /Product/Dealer/ListBrands | Get the brands of the Dealer |
| [**productDealerListModels()**](ProductApi.md#productDealerListModels) | **GET** /Product/Dealer/ListModels | Ge the models of the Dealer |
| [**productGetBrands()**](ProductApi.md#productGetBrands) | **GET** /Product/GetBrands | Gets the brands related to all dealers |
| [**productGetModels()**](ProductApi.md#productGetModels) | **GET** /Product/GetModels | Gets the models related to all dealers |
| [**productGetProduct()**](ProductApi.md#productGetProduct) | **POST** /Product/GetProduct | Gets the product by id |
| [**productGetProducts()**](ProductApi.md#productGetProducts) | **POST** /Product/GetProducts | Gets the products related to all dealers |
| [**productGetSnmpDiscoveryBrands()**](ProductApi.md#productGetSnmpDiscoveryBrands) | **GET** /Product/GetSnmpDiscoveryBrands | Gets the Snmp discovery brands |


## `productCustomerList()`

```php
productCustomerList($page_number, $page_rows, $sort_column, $sort_order, $customer_code, $dealer_code, $filter_text): \OpenAPI\Client\Model\PagedResultResponseProductDto
```

Get the products of the Customer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$customer_code = 'customer_code_example'; // string | Gets or sets the customer code.
$dealer_code = 'dealer_code_example'; // string | Gets or sets the dealer code.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->productCustomerList($page_number, $page_rows, $sort_column, $sort_order, $customer_code, $dealer_code, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductApi->productCustomerList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **customer_code** | **string**| Gets or sets the customer code. | [optional] |
| **dealer_code** | **string**| Gets or sets the dealer code. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseProductDto**](../Model/PagedResultResponseProductDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `productDealerList()`

```php
productDealerList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $brand, $filter_text): \OpenAPI\Client\Model\PagedResultResponseProductDto
```

Get the products of the Dealer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | The dealer code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$brand = 'brand_example'; // string | The brand
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->productDealerList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $brand, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductApi->productDealerList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| The dealer code. | |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **brand** | **string**| The brand | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseProductDto**](../Model/PagedResultResponseProductDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `productDealerListBrands()`

```php
productDealerListBrands($page_number, $page_rows, $sort_column, $sort_order, $dealer_code, $filter_text): \OpenAPI\Client\Model\PagedResultResponseString
```

Get the brands of the Dealer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProductApi(
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
    $result = $apiInstance->productDealerListBrands($page_number, $page_rows, $sort_column, $sort_order, $dealer_code, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductApi->productDealerListBrands: ', $e->getMessage(), PHP_EOL;
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

[**\OpenAPI\Client\Model\PagedResultResponseString**](../Model/PagedResultResponseString.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `productDealerListModels()`

```php
productDealerListModels($page_number, $page_rows, $sort_column, $sort_order, $brand, $dealer_code, $filter_text): \OpenAPI\Client\Model\PagedResultResponseString
```

Ge the models of the Dealer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$brand = 'brand_example'; // string
$dealer_code = 'dealer_code_example'; // string | Gets or sets the dealer code.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->productDealerListModels($page_number, $page_rows, $sort_column, $sort_order, $brand, $dealer_code, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductApi->productDealerListModels: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **brand** | **string**|  | [optional] |
| **dealer_code** | **string**| Gets or sets the dealer code. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseString**](../Model/PagedResultResponseString.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `productGetBrands()`

```php
productGetBrands($page_number, $page_rows, $sort_column, $sort_order, $filter_text): \OpenAPI\Client\Model\PagedResultResponseString
```

Gets the brands related to all dealers

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->productGetBrands($page_number, $page_rows, $sort_column, $sort_order, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductApi->productGetBrands: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseString**](../Model/PagedResultResponseString.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `productGetModels()`

```php
productGetModels($page_number, $page_rows, $sort_column, $sort_order, $brand, $filter_text): \OpenAPI\Client\Model\PagedResultResponseString
```

Gets the models related to all dealers

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$brand = 'brand_example'; // string | Gets or sets the brand.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->productGetModels($page_number, $page_rows, $sort_column, $sort_order, $brand, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductApi->productGetModels: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **brand** | **string**| Gets or sets the brand. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseString**](../Model/PagedResultResponseString.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `productGetProduct()`

```php
productGetProduct($request): \OpenAPI\Client\Model\SingleResultResponseProductDto
```

Gets the product by id

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->productGetProduct($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductApi->productGetProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseProductDto**](../Model/SingleResultResponseProductDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `productGetProducts()`

```php
productGetProducts($request): \OpenAPI\Client\Model\PagedResultResponseProductDto
```

Gets the products related to all dealers

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetProductsRequest(); // \OpenAPI\Client\Model\GetProductsRequest | The request.

try {
    $result = $apiInstance->productGetProducts($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductApi->productGetProducts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetProductsRequest**](../Model/GetProductsRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseProductDto**](../Model/PagedResultResponseProductDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `productGetSnmpDiscoveryBrands()`

```php
productGetSnmpDiscoveryBrands($page_number, $page_rows, $sort_column, $sort_order, $filter_text): \OpenAPI\Client\Model\PagedResultResponseString
```

Gets the Snmp discovery brands

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->productGetSnmpDiscoveryBrands($page_number, $page_rows, $sort_column, $sort_order, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductApi->productGetSnmpDiscoveryBrands: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseString**](../Model/PagedResultResponseString.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
