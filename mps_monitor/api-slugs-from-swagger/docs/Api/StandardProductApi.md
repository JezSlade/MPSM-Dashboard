# OpenAPI\Client\StandardProductApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**standardProductCreateOrUpdateOperation()**](StandardProductApi.md#standardProductCreateOrUpdateOperation) | **POST** /StandardProduct/CreateOrUpdateOperation |  |
| [**standardProductGetExcelReport()**](StandardProductApi.md#standardProductGetExcelReport) | **GET** /StandardProduct/GetExcelReport |  |
| [**standardProductGetOperation()**](StandardProductApi.md#standardProductGetOperation) | **GET** /StandardProduct/GetOperation |  |
| [**standardProductGetProductsToAssociate()**](StandardProductApi.md#standardProductGetProductsToAssociate) | **GET** /StandardProduct/GetProductsToAssociate |  |
| [**standardProductGetStandardProductsSummary()**](StandardProductApi.md#standardProductGetStandardProductsSummary) | **GET** /StandardProduct/GetStandardProductsSummary |  |
| [**standardProductListDevicesInOperation()**](StandardProductApi.md#standardProductListDevicesInOperation) | **GET** /StandardProduct/ListDevicesInOperation |  |
| [**standardProductListOperations()**](StandardProductApi.md#standardProductListOperations) | **GET** /StandardProduct/ListOperations |  |
| [**standardProductListStandardProducts()**](StandardProductApi.md#standardProductListStandardProducts) | **GET** /StandardProduct/ListStandardProducts |  |
| [**standardProductProcessOperation()**](StandardProductApi.md#standardProductProcessOperation) | **POST** /StandardProduct/ProcessOperation |  |
| [**standardProductRollbackOperation()**](StandardProductApi.md#standardProductRollbackOperation) | **POST** /StandardProduct/RollbackOperation |  |


## `standardProductCreateOrUpdateOperation()`

```php
standardProductCreateOrUpdateOperation($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\StandardProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateOrUpdateStandardProductOperation(); // \OpenAPI\Client\Model\CreateOrUpdateStandardProductOperation

try {
    $result = $apiInstance->standardProductCreateOrUpdateOperation($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StandardProductApi->standardProductCreateOrUpdateOperation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateOrUpdateStandardProductOperation**](../Model/CreateOrUpdateStandardProductOperation.md)|  | |

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

## `standardProductGetExcelReport()`

```php
standardProductGetExcelReport($dealer_code, $id): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\StandardProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->standardProductGetExcelReport($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StandardProductApi->standardProductGetExcelReport: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseFileInfoDto**](../Model/SingleResultResponseFileInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `standardProductGetOperation()`

```php
standardProductGetOperation($id, $customer_code, $dealer_code): \OpenAPI\Client\Model\SingleResultResponseStandardProductOperationDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\StandardProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$customer_code = 'customer_code_example'; // string | Gets or sets the customer code.
$dealer_code = 'dealer_code_example'; // string | Gets or sets the dealer code.

try {
    $result = $apiInstance->standardProductGetOperation($id, $customer_code, $dealer_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StandardProductApi->standardProductGetOperation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **customer_code** | **string**| Gets or sets the customer code. | [optional] |
| **dealer_code** | **string**| Gets or sets the dealer code. | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseStandardProductOperationDto**](../Model/SingleResultResponseStandardProductOperationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `standardProductGetProductsToAssociate()`

```php
standardProductGetProductsToAssociate($dealer_code): \OpenAPI\Client\Model\ListResultResponseProductToAssociateDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\StandardProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->standardProductGetProductsToAssociate($dealer_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StandardProductApi->standardProductGetProductsToAssociate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseProductToAssociateDto**](../Model/ListResultResponseProductToAssociateDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `standardProductGetStandardProductsSummary()`

```php
standardProductGetStandardProductsSummary($dealer_code): \OpenAPI\Client\Model\SingleResultResponseStandardProductsSummaryDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\StandardProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->standardProductGetStandardProductsSummary($dealer_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StandardProductApi->standardProductGetStandardProductsSummary: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseStandardProductsSummaryDto**](../Model/SingleResultResponseStandardProductsSummaryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `standardProductListDevicesInOperation()`

```php
standardProductListDevicesInOperation($page_number, $page_rows, $sort_column, $sort_order, $dealer_code, $operation_id, $filter_text): \OpenAPI\Client\Model\PagedResultResponseCustomerDeviceDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\StandardProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$dealer_code = 'dealer_code_example'; // string | Gets or sets the dealer code.
$operation_id = 'operation_id_example'; // string | Gets or sets the operation identifier.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->standardProductListDevicesInOperation($page_number, $page_rows, $sort_column, $sort_order, $dealer_code, $operation_id, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StandardProductApi->standardProductListDevicesInOperation: ', $e->getMessage(), PHP_EOL;
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
| **operation_id** | **string**| Gets or sets the operation identifier. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseCustomerDeviceDto**](../Model/PagedResultResponseCustomerDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `standardProductListOperations()`

```php
standardProductListOperations($page_number, $page_rows, $sort_column, $sort_order, $customer_code, $dealer_code, $filter_text): \OpenAPI\Client\Model\PagedResultResponseStandardProductOperationDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\StandardProductApi(
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
    $result = $apiInstance->standardProductListOperations($page_number, $page_rows, $sort_column, $sort_order, $customer_code, $dealer_code, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StandardProductApi->standardProductListOperations: ', $e->getMessage(), PHP_EOL;
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

[**\OpenAPI\Client\Model\PagedResultResponseStandardProductOperationDto**](../Model/PagedResultResponseStandardProductOperationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `standardProductListStandardProducts()`

```php
standardProductListStandardProducts($page_number, $page_rows, $sort_column, $sort_order, $filter_text): \OpenAPI\Client\Model\PagedResultResponseStandardProductDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\StandardProductApi(
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
    $result = $apiInstance->standardProductListStandardProducts($page_number, $page_rows, $sort_column, $sort_order, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StandardProductApi->standardProductListStandardProducts: ', $e->getMessage(), PHP_EOL;
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

[**\OpenAPI\Client\Model\PagedResultResponseStandardProductDto**](../Model/PagedResultResponseStandardProductDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `standardProductProcessOperation()`

```php
standardProductProcessOperation($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\StandardProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdAndDealerCodeRequest(); // \OpenAPI\Client\Model\GetByIdAndDealerCodeRequest

try {
    $result = $apiInstance->standardProductProcessOperation($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StandardProductApi->standardProductProcessOperation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdAndDealerCodeRequest**](../Model/GetByIdAndDealerCodeRequest.md)|  | |

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

## `standardProductRollbackOperation()`

```php
standardProductRollbackOperation($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\StandardProductApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\RollbackStandardProductOperationRequest(); // \OpenAPI\Client\Model\RollbackStandardProductOperationRequest

try {
    $result = $apiInstance->standardProductRollbackOperation($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StandardProductApi->standardProductRollbackOperation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\RollbackStandardProductOperationRequest**](../Model/RollbackStandardProductOperationRequest.md)|  | |

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
