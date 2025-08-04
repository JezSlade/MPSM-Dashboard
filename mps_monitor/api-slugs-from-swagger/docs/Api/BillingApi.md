# OpenAPI\Client\BillingApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**billingGetCustomerInvoice()**](BillingApi.md#billingGetCustomerInvoice) | **POST** /Billing/GetCustomerInvoice | Gets the customer invoice Header and Rows |
| [**billingGetCustomerInvoicesList()**](BillingApi.md#billingGetCustomerInvoicesList) | **POST** /Billing/GetCustomerInvoicesList | Gets the customer invoices. |
| [**billingGetCustomersContracts()**](BillingApi.md#billingGetCustomersContracts) | **POST** /Billing/GetCustomersContracts | Gets the customers contracts. |
| [**billingGetCustomersInvoicesList()**](BillingApi.md#billingGetCustomersInvoicesList) | **POST** /Billing/GetCustomersInvoicesList | Gets the customers invoices. |
| [**billingGetDealerInvoice()**](BillingApi.md#billingGetDealerInvoice) | **POST** /Billing/GetDealerInvoice | Gets the invoice Header and Rows. |
| [**billingGetDealerInvoicesList()**](BillingApi.md#billingGetDealerInvoicesList) | **POST** /Billing/GetDealerInvoicesList | Gets the dealer to dealer invoices. |
| [**billingGetInvoiceCategories()**](BillingApi.md#billingGetInvoiceCategories) | **GET** /Billing/GetInvoiceCategories | Get Invoice Categories |
| [**billingUpdateCustomerInvoice()**](BillingApi.md#billingUpdateCustomerInvoice) | **PATCH** /Billing/UpdateCustomerInvoice | Update Customer Invoice |


## `billingGetCustomerInvoice()`

```php
billingGetCustomerInvoice($request): \OpenAPI\Client\Model\SingleResultResponseInvoiceHeaderDto
```

Gets the customer invoice Header and Rows

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\BillingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->billingGetCustomerInvoice($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BillingApi->billingGetCustomerInvoice: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseInvoiceHeaderDto**](../Model/SingleResultResponseInvoiceHeaderDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `billingGetCustomerInvoicesList()`

```php
billingGetCustomerInvoicesList($request): \OpenAPI\Client\Model\PagedResultResponseInvoiceListDto
```

Gets the customer invoices.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\BillingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetCustomerInvoicesRequest(); // \OpenAPI\Client\Model\GetCustomerInvoicesRequest | The request.

try {
    $result = $apiInstance->billingGetCustomerInvoicesList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BillingApi->billingGetCustomerInvoicesList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetCustomerInvoicesRequest**](../Model/GetCustomerInvoicesRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseInvoiceListDto**](../Model/PagedResultResponseInvoiceListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `billingGetCustomersContracts()`

```php
billingGetCustomersContracts($request): \OpenAPI\Client\Model\PagedResultResponseCustomerContractDto
```

Gets the customers contracts.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\BillingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetCustomersContractsRequest(); // \OpenAPI\Client\Model\GetCustomersContractsRequest | The request.

try {
    $result = $apiInstance->billingGetCustomersContracts($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BillingApi->billingGetCustomersContracts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetCustomersContractsRequest**](../Model/GetCustomersContractsRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseCustomerContractDto**](../Model/PagedResultResponseCustomerContractDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `billingGetCustomersInvoicesList()`

```php
billingGetCustomersInvoicesList($request): \OpenAPI\Client\Model\PagedResultResponseCustomerInvoiceListDto
```

Gets the customers invoices.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\BillingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetCustomersInvoicesRequest(); // \OpenAPI\Client\Model\GetCustomersInvoicesRequest | The request.

try {
    $result = $apiInstance->billingGetCustomersInvoicesList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BillingApi->billingGetCustomersInvoicesList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetCustomersInvoicesRequest**](../Model/GetCustomersInvoicesRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseCustomerInvoiceListDto**](../Model/PagedResultResponseCustomerInvoiceListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `billingGetDealerInvoice()`

```php
billingGetDealerInvoice($request): \OpenAPI\Client\Model\SingleResultResponseDealerInvoiceDto
```

Gets the invoice Header and Rows.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\BillingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->billingGetDealerInvoice($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BillingApi->billingGetDealerInvoice: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerInvoiceDto**](../Model/SingleResultResponseDealerInvoiceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `billingGetDealerInvoicesList()`

```php
billingGetDealerInvoicesList($request): \OpenAPI\Client\Model\PagedResultResponseDealerInvoicesListDto
```

Gets the dealer to dealer invoices.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\BillingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDealerInvoicesRequest(); // \OpenAPI\Client\Model\GetDealerInvoicesRequest | The request.

try {
    $result = $apiInstance->billingGetDealerInvoicesList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BillingApi->billingGetDealerInvoicesList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDealerInvoicesRequest**](../Model/GetDealerInvoicesRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDealerInvoicesListDto**](../Model/PagedResultResponseDealerInvoicesListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `billingGetInvoiceCategories()`

```php
billingGetInvoiceCategories(): \OpenAPI\Client\Model\ListResultResponseInvoiceCategoryDto
```

Get Invoice Categories

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\BillingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->billingGetInvoiceCategories();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BillingApi->billingGetInvoiceCategories: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\OpenAPI\Client\Model\ListResultResponseInvoiceCategoryDto**](../Model/ListResultResponseInvoiceCategoryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `billingUpdateCustomerInvoice()`

```php
billingUpdateCustomerInvoice($request): \OpenAPI\Client\Model\BaseResponse
```

Update Customer Invoice

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\BillingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateCustomerInvoiceRequest(); // \OpenAPI\Client\Model\UpdateCustomerInvoiceRequest

try {
    $result = $apiInstance->billingUpdateCustomerInvoice($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BillingApi->billingUpdateCustomerInvoice: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateCustomerInvoiceRequest**](../Model/UpdateCustomerInvoiceRequest.md)|  | |

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
