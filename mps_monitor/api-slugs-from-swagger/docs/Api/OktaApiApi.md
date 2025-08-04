# OpenAPI\Client\OktaApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**oktaCreateOktaDomain()**](OktaApiApi.md#oktaCreateOktaDomain) | **POST** /okta/CreateOktaDomain | Create the Okta domain configuration. |
| [**oktaGetCustomerOktaSettings()**](OktaApiApi.md#oktaGetCustomerOktaSettings) | **GET** /okta/GetCustomerOktaSettings | Get the Okta settings for the customer |
| [**oktaGetDealerOktaSettings()**](OktaApiApi.md#oktaGetDealerOktaSettings) | **GET** /okta/GetDealerOktaSettings | Get the Okta settings for the dealer |
| [**oktaGetDomainRedirect()**](OktaApiApi.md#oktaGetDomainRedirect) | **GET** /okta/GetDomainRedirect | Returns the Okta login url based on the domain specified. |
| [**oktaUpdateOktaDomain()**](OktaApiApi.md#oktaUpdateOktaDomain) | **PUT** /okta/UpdateOktaDomain | Update the Okta domain configuration. |


## `oktaCreateOktaDomain()`

```php
oktaCreateOktaDomain($request): \OpenAPI\Client\Model\SingleResultResponseOktaDomainDto
```

Create the Okta domain configuration.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OktaApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestOktaDomainDto(); // \OpenAPI\Client\Model\CreateRequestOktaDomainDto

try {
    $result = $apiInstance->oktaCreateOktaDomain($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OktaApiApi->oktaCreateOktaDomain: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestOktaDomainDto**](../Model/CreateRequestOktaDomainDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseOktaDomainDto**](../Model/SingleResultResponseOktaDomainDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `oktaGetCustomerOktaSettings()`

```php
oktaGetCustomerOktaSettings($code): \OpenAPI\Client\Model\SingleResultResponseOktaDomainDto
```

Get the Okta settings for the customer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OktaApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->oktaGetCustomerOktaSettings($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OktaApiApi->oktaGetCustomerOktaSettings: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseOktaDomainDto**](../Model/SingleResultResponseOktaDomainDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `oktaGetDealerOktaSettings()`

```php
oktaGetDealerOktaSettings($code): \OpenAPI\Client\Model\SingleResultResponseOktaDomainDto
```

Get the Okta settings for the dealer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OktaApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->oktaGetDealerOktaSettings($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OktaApiApi->oktaGetDealerOktaSettings: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseOktaDomainDto**](../Model/SingleResultResponseOktaDomainDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `oktaGetDomainRedirect()`

```php
oktaGetDomainRedirect($okta_domain): string
```

Returns the Okta login url based on the domain specified.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OktaApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$okta_domain = 'okta_domain_example'; // string | Gets or sets the okta domain.

try {
    $result = $apiInstance->oktaGetDomainRedirect($okta_domain);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OktaApiApi->oktaGetDomainRedirect: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **okta_domain** | **string**| Gets or sets the okta domain. | |

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

## `oktaUpdateOktaDomain()`

```php
oktaUpdateOktaDomain($request): \OpenAPI\Client\Model\SingleResultResponseOktaDomainDto
```

Update the Okta domain configuration.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OktaApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestOktaDomainDto(); // \OpenAPI\Client\Model\UpdateRequestOktaDomainDto

try {
    $result = $apiInstance->oktaUpdateOktaDomain($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OktaApiApi->oktaUpdateOktaDomain: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestOktaDomainDto**](../Model/UpdateRequestOktaDomainDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseOktaDomainDto**](../Model/SingleResultResponseOktaDomainDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
