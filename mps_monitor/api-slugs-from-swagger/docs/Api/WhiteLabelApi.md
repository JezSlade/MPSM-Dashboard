# OpenAPI\Client\WhiteLabelApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**whiteLabelGet()**](WhiteLabelApi.md#whiteLabelGet) | **GET** /WhiteLabel/Get | Get the white label. |
| [**whiteLabelGetWhiteLabelCustomizationByUrl()**](WhiteLabelApi.md#whiteLabelGetWhiteLabelCustomizationByUrl) | **GET** /WhiteLabel/GetWhiteLabelCustomizationByUrl | Get the whitelabel customizations by the caller URL |
| [**whiteLabelGetWhitelabelPlaceholders()**](WhiteLabelApi.md#whiteLabelGetWhitelabelPlaceholders) | **GET** /WhiteLabel/GetWhitelabelPlaceholders | Get whitelabel placeholders |
| [**whiteLabelUpdate()**](WhiteLabelApi.md#whiteLabelUpdate) | **PUT** /WhiteLabel/Update | TResponse              Update the white label |
| [**whiteLabelUpdateDcaLicenses()**](WhiteLabelApi.md#whiteLabelUpdateDcaLicenses) | **PUT** /WhiteLabel/UpdateDcaLicenses | Update a DCA licence text |
| [**whiteLabelUpdateTemplates()**](WhiteLabelApi.md#whiteLabelUpdateTemplates) | **PUT** /WhiteLabel/UpdateTemplates | Updates and email template |


## `whiteLabelGet()`

```php
whiteLabelGet($code): \OpenAPI\Client\Model\SingleResultResponseWhiteLabelDto
```

Get the white label.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\WhiteLabelApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->whiteLabelGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WhiteLabelApi->whiteLabelGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseWhiteLabelDto**](../Model/SingleResultResponseWhiteLabelDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `whiteLabelGetWhiteLabelCustomizationByUrl()`

```php
whiteLabelGetWhiteLabelCustomizationByUrl(): \OpenAPI\Client\Model\SingleResultResponseWhiteLabelCustomizationDto
```

Get the whitelabel customizations by the caller URL

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\WhiteLabelApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->whiteLabelGetWhiteLabelCustomizationByUrl();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WhiteLabelApi->whiteLabelGetWhiteLabelCustomizationByUrl: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseWhiteLabelCustomizationDto**](../Model/SingleResultResponseWhiteLabelCustomizationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `whiteLabelGetWhitelabelPlaceholders()`

```php
whiteLabelGetWhitelabelPlaceholders($portal_email_template_type): \OpenAPI\Client\Model\ListResultResponseWhiteLabelPlaceholderDto
```

Get whitelabel placeholders

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\WhiteLabelApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$portal_email_template_type = 'portal_email_template_type_example'; // string

try {
    $result = $apiInstance->whiteLabelGetWhitelabelPlaceholders($portal_email_template_type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WhiteLabelApi->whiteLabelGetWhitelabelPlaceholders: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **portal_email_template_type** | **string**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseWhiteLabelPlaceholderDto**](../Model/ListResultResponseWhiteLabelPlaceholderDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `whiteLabelUpdate()`

```php
whiteLabelUpdate($request): \OpenAPI\Client\Model\SingleResultResponseWhiteLabelDto
```

TResponse              Update the white label

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\WhiteLabelApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestWhiteLabelDto(); // \OpenAPI\Client\Model\UpdateRequestWhiteLabelDto

try {
    $result = $apiInstance->whiteLabelUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WhiteLabelApi->whiteLabelUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestWhiteLabelDto**](../Model/UpdateRequestWhiteLabelDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseWhiteLabelDto**](../Model/SingleResultResponseWhiteLabelDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `whiteLabelUpdateDcaLicenses()`

```php
whiteLabelUpdateDcaLicenses($request): \OpenAPI\Client\Model\SingleResultResponseWhiteLabelDto
```

Update a DCA licence text

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\WhiteLabelApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestWhiteLabelDto(); // \OpenAPI\Client\Model\UpdateRequestWhiteLabelDto

try {
    $result = $apiInstance->whiteLabelUpdateDcaLicenses($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WhiteLabelApi->whiteLabelUpdateDcaLicenses: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestWhiteLabelDto**](../Model/UpdateRequestWhiteLabelDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseWhiteLabelDto**](../Model/SingleResultResponseWhiteLabelDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `whiteLabelUpdateTemplates()`

```php
whiteLabelUpdateTemplates($request): \OpenAPI\Client\Model\SingleResultResponseWhiteLabelDto
```

Updates and email template

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\WhiteLabelApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestWhiteLabelDto(); // \OpenAPI\Client\Model\UpdateRequestWhiteLabelDto

try {
    $result = $apiInstance->whiteLabelUpdateTemplates($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WhiteLabelApi->whiteLabelUpdateTemplates: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestWhiteLabelDto**](../Model/UpdateRequestWhiteLabelDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseWhiteLabelDto**](../Model/SingleResultResponseWhiteLabelDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
