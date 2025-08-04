# OpenAPI\Client\CustomerApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**customerAccessoriesGet()**](CustomerApi.md#customerAccessoriesGet) | **GET** /Customer/Accessories/Get | Gets the dealer alert settings |
| [**customerAdvancedOptionsGet()**](CustomerApi.md#customerAdvancedOptionsGet) | **GET** /Customer/AdvancedOptions/Get | Gets the customer advanced options |
| [**customerAdvancedOptionsUpdate()**](CustomerApi.md#customerAdvancedOptionsUpdate) | **PUT** /Customer/AdvancedOptions/Update | Set the customer advanced options |
| [**customerAlertSettingsGet()**](CustomerApi.md#customerAlertSettingsGet) | **GET** /Customer/AlertSettings/Get | Gets the dealer alert settings |
| [**customerAlertSettingsUpdate()**](CustomerApi.md#customerAlertSettingsUpdate) | **PUT** /Customer/AlertSettings/Update | set the dealer alert settings |
| [**customerCreateCustomer()**](CustomerApi.md#customerCreateCustomer) | **POST** /Customer/CreateCustomer | Creates the customer. |
| [**customerCustomerServicesStatusGet()**](CustomerApi.md#customerCustomerServicesStatusGet) | **GET** /Customer/CustomerServicesStatus/Get | Gets the customer services status. |
| [**customerDeleteCustomer()**](CustomerApi.md#customerDeleteCustomer) | **POST** /Customer/DeleteCustomer | Delete customer |
| [**customerEXplorerSettingsGet()**](CustomerApi.md#customerEXplorerSettingsGet) | **GET** /Customer/eXplorerSettings/Get | Gets the customer eXplorer settings |
| [**customerEXplorerSettingsUpdate()**](CustomerApi.md#customerEXplorerSettingsUpdate) | **PUT** /Customer/eXplorerSettings/Update | set the customer eXplorer settings |
| [**customerEpsonSettingsGet()**](CustomerApi.md#customerEpsonSettingsGet) | **GET** /Customer/EpsonSettings/Get | Gets the epson ERS and USB settings |
| [**customerEpsonSettingsUpdate()**](CustomerApi.md#customerEpsonSettingsUpdate) | **PUT** /Customer/EpsonSettings/Update | set the customer epsons settings |
| [**customerEpsonUSBCustomerIdGet()**](CustomerApi.md#customerEpsonUSBCustomerIdGet) | **GET** /Customer/EpsonUSBCustomerId/Get | Get a new Epson USB customer ID for the email subject |
| [**customerGetCustomer()**](CustomerApi.md#customerGetCustomer) | **POST** /Customer/GetCustomer | Gets the customer. |
| [**customerGetCustomerByCode()**](CustomerApi.md#customerGetCustomerByCode) | **POST** /Customer/GetCustomerByCode | Gets the customer. |
| [**customerGetCustomers()**](CustomerApi.md#customerGetCustomers) | **POST** /Customer/GetCustomers | Gets the customers. |
| [**customerGetEmailExplorerInstallationToCustomer()**](CustomerApi.md#customerGetEmailExplorerInstallationToCustomer) | **POST** /Customer/GetEmailExplorerInstallationToCustomer | Returns the mail parts (subjet and body) of the email to be sent to the customer for eXplorer installation. |
| [**customerSendEXplorerInvitation()**](CustomerApi.md#customerSendEXplorerInvitation) | **POST** /Customer/SendEXplorerInvitation | Sends the e xplorer invitation. |
| [**customerUpdateCustomer()**](CustomerApi.md#customerUpdateCustomer) | **POST** /Customer/UpdateCustomer | Update the customer. |


## `customerAccessoriesGet()`

```php
customerAccessoriesGet($code): \OpenAPI\Client\Model\SingleResultResponseCustomerAccessoriesDto
```

Gets the dealer alert settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->customerAccessoriesGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerAccessoriesGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerAccessoriesDto**](../Model/SingleResultResponseCustomerAccessoriesDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerAdvancedOptionsGet()`

```php
customerAdvancedOptionsGet($code): \OpenAPI\Client\Model\SingleResultResponseCustomerAdvancedOptionsDto
```

Gets the customer advanced options

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->customerAdvancedOptionsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerAdvancedOptionsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerAdvancedOptionsDto**](../Model/SingleResultResponseCustomerAdvancedOptionsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerAdvancedOptionsUpdate()`

```php
customerAdvancedOptionsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Set the customer advanced options

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateCustomerAdvancedOptionsRequest(); // \OpenAPI\Client\Model\UpdateCustomerAdvancedOptionsRequest

try {
    $result = $apiInstance->customerAdvancedOptionsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerAdvancedOptionsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateCustomerAdvancedOptionsRequest**](../Model/UpdateCustomerAdvancedOptionsRequest.md)|  | |

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

## `customerAlertSettingsGet()`

```php
customerAlertSettingsGet($code): \OpenAPI\Client\Model\SingleResultResponseCustomerAlertSettingsDto
```

Gets the dealer alert settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->customerAlertSettingsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerAlertSettingsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerAlertSettingsDto**](../Model/SingleResultResponseCustomerAlertSettingsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerAlertSettingsUpdate()`

```php
customerAlertSettingsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

set the dealer alert settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestCustomerAlertSettingsDto(); // \OpenAPI\Client\Model\UpdateRequestCustomerAlertSettingsDto | The request.

try {
    $result = $apiInstance->customerAlertSettingsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerAlertSettingsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestCustomerAlertSettingsDto**](../Model/UpdateRequestCustomerAlertSettingsDto.md)| The request. | |

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

## `customerCreateCustomer()`

```php
customerCreateCustomer($request): \OpenAPI\Client\Model\CreateCustomerResponse
```

Creates the customer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestCreateCustomerDto(); // \OpenAPI\Client\Model\CreateRequestCreateCustomerDto | The request.

try {
    $result = $apiInstance->customerCreateCustomer($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerCreateCustomer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestCreateCustomerDto**](../Model/CreateRequestCreateCustomerDto.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\CreateCustomerResponse**](../Model/CreateCustomerResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerCustomerServicesStatusGet()`

```php
customerCustomerServicesStatusGet($code): \OpenAPI\Client\Model\SingleResultResponseCustomerServicesStatusDto
```

Gets the customer services status.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->customerCustomerServicesStatusGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerCustomerServicesStatusGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerServicesStatusDto**](../Model/SingleResultResponseCustomerServicesStatusDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerDeleteCustomer()`

```php
customerDeleteCustomer($request): \OpenAPI\Client\Model\BaseResponse
```

Delete customer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DeleteRequest(); // \OpenAPI\Client\Model\DeleteRequest

try {
    $result = $apiInstance->customerDeleteCustomer($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerDeleteCustomer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DeleteRequest**](../Model/DeleteRequest.md)|  | |

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

## `customerEXplorerSettingsGet()`

```php
customerEXplorerSettingsGet($code): \OpenAPI\Client\Model\SingleResultResponseCustomerExplorerSettingsDto
```

Gets the customer eXplorer settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->customerEXplorerSettingsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerEXplorerSettingsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerExplorerSettingsDto**](../Model/SingleResultResponseCustomerExplorerSettingsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerEXplorerSettingsUpdate()`

```php
customerEXplorerSettingsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

set the customer eXplorer settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestCustomerExplorerSettingsDto(); // \OpenAPI\Client\Model\UpdateRequestCustomerExplorerSettingsDto | The request.

try {
    $result = $apiInstance->customerEXplorerSettingsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerEXplorerSettingsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestCustomerExplorerSettingsDto**](../Model/UpdateRequestCustomerExplorerSettingsDto.md)| The request. | |

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

## `customerEpsonSettingsGet()`

```php
customerEpsonSettingsGet($code): \OpenAPI\Client\Model\SingleResultResponseCustomerEpsonSettingsDto
```

Gets the epson ERS and USB settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->customerEpsonSettingsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerEpsonSettingsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerEpsonSettingsDto**](../Model/SingleResultResponseCustomerEpsonSettingsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerEpsonSettingsUpdate()`

```php
customerEpsonSettingsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

set the customer epsons settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestCustomerEpsonSettingsDto(); // \OpenAPI\Client\Model\UpdateRequestCustomerEpsonSettingsDto | The request.

try {
    $result = $apiInstance->customerEpsonSettingsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerEpsonSettingsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestCustomerEpsonSettingsDto**](../Model/UpdateRequestCustomerEpsonSettingsDto.md)| The request. | |

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

## `customerEpsonUSBCustomerIdGet()`

```php
customerEpsonUSBCustomerIdGet($code): \OpenAPI\Client\Model\SingleResultResponseString
```

Get a new Epson USB customer ID for the email subject

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->customerEpsonUSBCustomerIdGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerEpsonUSBCustomerIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseString**](../Model/SingleResultResponseString.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerGetCustomer()`

```php
customerGetCustomer($request): \OpenAPI\Client\Model\SingleResultResponseCustomerDto
```

Gets the customer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->customerGetCustomer($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerGetCustomer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerDto**](../Model/SingleResultResponseCustomerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerGetCustomerByCode()`

```php
customerGetCustomerByCode($request): \OpenAPI\Client\Model\SingleResultResponseCustomerDto
```

Gets the customer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByCodeRequest(); // \OpenAPI\Client\Model\GetByCodeRequest | The request.

try {
    $result = $apiInstance->customerGetCustomerByCode($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerGetCustomerByCode: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByCodeRequest**](../Model/GetByCodeRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerDto**](../Model/SingleResultResponseCustomerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerGetCustomers()`

```php
customerGetCustomers($request): \OpenAPI\Client\Model\PagedResultResponseCustomerListDto
```

Gets the customers.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetCustomersRequest(); // \OpenAPI\Client\Model\GetCustomersRequest | The request.

try {
    $result = $apiInstance->customerGetCustomers($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerGetCustomers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetCustomersRequest**](../Model/GetCustomersRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseCustomerListDto**](../Model/PagedResultResponseCustomerListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerGetEmailExplorerInstallationToCustomer()`

```php
customerGetEmailExplorerInstallationToCustomer($request): \OpenAPI\Client\Model\ListResultResponseCodeDesc
```

Returns the mail parts (subjet and body) of the email to be sent to the customer for eXplorer installation.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestCreateCustomerDto(); // \OpenAPI\Client\Model\CreateRequestCreateCustomerDto | The request.

try {
    $result = $apiInstance->customerGetEmailExplorerInstallationToCustomer($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerGetEmailExplorerInstallationToCustomer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestCreateCustomerDto**](../Model/CreateRequestCreateCustomerDto.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseCodeDesc**](../Model/ListResultResponseCodeDesc.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerSendEXplorerInvitation()`

```php
customerSendEXplorerInvitation($request): \OpenAPI\Client\Model\BaseResponse
```

Sends the e xplorer invitation.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SendEXplorerInvitationRequest(); // \OpenAPI\Client\Model\SendEXplorerInvitationRequest | The request.

try {
    $result = $apiInstance->customerSendEXplorerInvitation($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerSendEXplorerInvitation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SendEXplorerInvitationRequest**](../Model/SendEXplorerInvitationRequest.md)| The request. | |

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

## `customerUpdateCustomer()`

```php
customerUpdateCustomer($request): \OpenAPI\Client\Model\SingleResultResponseCustomerDto
```

Update the customer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestUpdateCustomerDto(); // \OpenAPI\Client\Model\UpdateRequestUpdateCustomerDto | The request.

try {
    $result = $apiInstance->customerUpdateCustomer($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerApi->customerUpdateCustomer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestUpdateCustomerDto**](../Model/UpdateRequestUpdateCustomerDto.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerDto**](../Model/SingleResultResponseCustomerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
