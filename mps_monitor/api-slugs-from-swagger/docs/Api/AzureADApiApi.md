# OpenAPI\Client\AzureADApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**azureadCreateAzureDomain()**](AzureADApiApi.md#azureadCreateAzureDomain) | **POST** /azuread/CreateAzureDomain | Create the azure domain configuration. |
| [**azureadGetChallengeUrlRedirect()**](AzureADApiApi.md#azureadGetChallengeUrlRedirect) | **GET** /azuread/GetChallengeUrlRedirect | Returns the login url based on the domain specified. |
| [**azureadGetCustomerAzureSettings()**](AzureADApiApi.md#azureadGetCustomerAzureSettings) | **GET** /azuread/GetCustomerAzureSettings | Get Azure Ad customer configuration |
| [**azureadGetDealerAzureSettings()**](AzureADApiApi.md#azureadGetDealerAzureSettings) | **GET** /azuread/GetDealerAzureSettings | Get Azure Ad configuration |
| [**azureadUpdateAzureDomain()**](AzureADApiApi.md#azureadUpdateAzureDomain) | **PUT** /azuread/UpdateAzureDomain | Update the azure domain configuration. |


## `azureadCreateAzureDomain()`

```php
azureadCreateAzureDomain($request): \OpenAPI\Client\Model\SingleResultResponseAzureADDto
```

Create the azure domain configuration.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AzureADApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestAzureADDto(); // \OpenAPI\Client\Model\CreateRequestAzureADDto

try {
    $result = $apiInstance->azureadCreateAzureDomain($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AzureADApiApi->azureadCreateAzureDomain: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestAzureADDto**](../Model/CreateRequestAzureADDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAzureADDto**](../Model/SingleResultResponseAzureADDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `azureadGetChallengeUrlRedirect()`

```php
azureadGetChallengeUrlRedirect($name, $return_url): string
```

Returns the login url based on the domain specified.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AzureADApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$name = 'name_example'; // string | Gets or sets the name.
$return_url = 'return_url_example'; // string

try {
    $result = $apiInstance->azureadGetChallengeUrlRedirect($name, $return_url);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AzureADApiApi->azureadGetChallengeUrlRedirect: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **name** | **string**| Gets or sets the name. | |
| **return_url** | **string**|  | [optional] |

### Return type

**string**

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `azureadGetCustomerAzureSettings()`

```php
azureadGetCustomerAzureSettings($code): \OpenAPI\Client\Model\SingleResultResponseAzureADDto
```

Get Azure Ad customer configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AzureADApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->azureadGetCustomerAzureSettings($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AzureADApiApi->azureadGetCustomerAzureSettings: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAzureADDto**](../Model/SingleResultResponseAzureADDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `azureadGetDealerAzureSettings()`

```php
azureadGetDealerAzureSettings($code): \OpenAPI\Client\Model\SingleResultResponseAzureADDto
```

Get Azure Ad configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AzureADApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->azureadGetDealerAzureSettings($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AzureADApiApi->azureadGetDealerAzureSettings: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAzureADDto**](../Model/SingleResultResponseAzureADDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `azureadUpdateAzureDomain()`

```php
azureadUpdateAzureDomain($request): \OpenAPI\Client\Model\SingleResultResponseAzureADDto
```

Update the azure domain configuration.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AzureADApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestAzureADDto(); // \OpenAPI\Client\Model\UpdateRequestAzureADDto

try {
    $result = $apiInstance->azureadUpdateAzureDomain($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AzureADApiApi->azureadUpdateAzureDomain: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestAzureADDto**](../Model/UpdateRequestAzureADDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAzureADDto**](../Model/SingleResultResponseAzureADDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
