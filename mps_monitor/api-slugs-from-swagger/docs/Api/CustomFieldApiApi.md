# OpenAPI\Client\CustomFieldApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**customFieldCreate()**](CustomFieldApiApi.md#customFieldCreate) | **POST** /CustomField/Create | Create a custom Field |
| [**customFieldDelete()**](CustomFieldApiApi.md#customFieldDelete) | **DELETE** /CustomField/Delete | Delete a custom Field |
| [**customFieldGet()**](CustomFieldApiApi.md#customFieldGet) | **GET** /CustomField/Get | Returns a Custom Fields by Id |
| [**customFieldList()**](CustomFieldApiApi.md#customFieldList) | **GET** /CustomField/List | Returns the list of Custom Fields configured by the dealer |
| [**customFieldUpdate()**](CustomFieldApiApi.md#customFieldUpdate) | **PUT** /CustomField/Update | Update a custom field |


## `customFieldCreate()`

```php
customFieldCreate($request): \OpenAPI\Client\Model\SingleResultResponseCustomFieldDto
```

Create a custom Field

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomFieldApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateCustomFieldRequest(); // \OpenAPI\Client\Model\CreateCustomFieldRequest

try {
    $result = $apiInstance->customFieldCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomFieldApiApi->customFieldCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateCustomFieldRequest**](../Model/CreateCustomFieldRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomFieldDto**](../Model/SingleResultResponseCustomFieldDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customFieldDelete()`

```php
customFieldDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Delete a custom Field

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomFieldApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->customFieldDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomFieldApiApi->customFieldDelete: ', $e->getMessage(), PHP_EOL;
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

## `customFieldGet()`

```php
customFieldGet($id): \OpenAPI\Client\Model\SingleResultResponseCustomFieldDto
```

Returns a Custom Fields by Id

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomFieldApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->customFieldGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomFieldApiApi->customFieldGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomFieldDto**](../Model/SingleResultResponseCustomFieldDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customFieldList()`

```php
customFieldList($code): \OpenAPI\Client\Model\ListResultResponseCustomFieldDto
```

Returns the list of Custom Fields configured by the dealer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomFieldApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->customFieldList($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomFieldApiApi->customFieldList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseCustomFieldDto**](../Model/ListResultResponseCustomFieldDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customFieldUpdate()`

```php
customFieldUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Update a custom field

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomFieldApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestCustomFieldDto(); // \OpenAPI\Client\Model\UpdateRequestCustomFieldDto

try {
    $result = $apiInstance->customFieldUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomFieldApiApi->customFieldUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestCustomFieldDto**](../Model/UpdateRequestCustomFieldDto.md)|  | |

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
