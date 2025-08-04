# OpenAPI\Client\PanelMessageAlertApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**panelMessageAlertCreate()**](PanelMessageAlertApiApi.md#panelMessageAlertCreate) | **POST** /PanelMessageAlert/Create | Create the PanelMessage alert configuration |
| [**panelMessageAlertDelete()**](PanelMessageAlertApiApi.md#panelMessageAlertDelete) | **DELETE** /PanelMessageAlert/Delete | Deletes the specified panel message alert configuration |
| [**panelMessageAlertGetErrorCodes()**](PanelMessageAlertApiApi.md#panelMessageAlertGetErrorCodes) | **POST** /PanelMessageAlert/GetErrorCodes | Gets available panel message codes |
| [**panelMessageAlertList()**](PanelMessageAlertApiApi.md#panelMessageAlertList) | **POST** /PanelMessageAlert/List | Returns a list of panel message alert configurations. |
| [**panelMessageAlertUpdate()**](PanelMessageAlertApiApi.md#panelMessageAlertUpdate) | **PUT** /PanelMessageAlert/Update | Edit the PanelMessage alert configuration |


## `panelMessageAlertCreate()`

```php
panelMessageAlertCreate($request): \OpenAPI\Client\Model\SingleResultResponsePanelMessageAlertConfigurationDto
```

Create the PanelMessage alert configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\PanelMessageAlertApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestPanelMessageAlertConfigurationDto(); // \OpenAPI\Client\Model\CreateRequestPanelMessageAlertConfigurationDto

try {
    $result = $apiInstance->panelMessageAlertCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PanelMessageAlertApiApi->panelMessageAlertCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestPanelMessageAlertConfigurationDto**](../Model/CreateRequestPanelMessageAlertConfigurationDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponsePanelMessageAlertConfigurationDto**](../Model/SingleResultResponsePanelMessageAlertConfigurationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `panelMessageAlertDelete()`

```php
panelMessageAlertDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Deletes the specified panel message alert configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\PanelMessageAlertApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->panelMessageAlertDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PanelMessageAlertApiApi->panelMessageAlertDelete: ', $e->getMessage(), PHP_EOL;
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

## `panelMessageAlertGetErrorCodes()`

```php
panelMessageAlertGetErrorCodes($request): \OpenAPI\Client\Model\PagedResultResponsePanelMessageAlertCodeDto
```

Gets available panel message codes

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\PanelMessageAlertApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\FilteredPagedRequest(); // \OpenAPI\Client\Model\FilteredPagedRequest

try {
    $result = $apiInstance->panelMessageAlertGetErrorCodes($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PanelMessageAlertApiApi->panelMessageAlertGetErrorCodes: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\FilteredPagedRequest**](../Model/FilteredPagedRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponsePanelMessageAlertCodeDto**](../Model/PagedResultResponsePanelMessageAlertCodeDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `panelMessageAlertList()`

```php
panelMessageAlertList($request): \OpenAPI\Client\Model\PagedResultResponsePanelMessageAlertListDto
```

Returns a list of panel message alert configurations.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\PanelMessageAlertApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetPanelMessageAlertConfigurationsRequest(); // \OpenAPI\Client\Model\GetPanelMessageAlertConfigurationsRequest

try {
    $result = $apiInstance->panelMessageAlertList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PanelMessageAlertApiApi->panelMessageAlertList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetPanelMessageAlertConfigurationsRequest**](../Model/GetPanelMessageAlertConfigurationsRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponsePanelMessageAlertListDto**](../Model/PagedResultResponsePanelMessageAlertListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `panelMessageAlertUpdate()`

```php
panelMessageAlertUpdate($request): \OpenAPI\Client\Model\SingleResultResponsePanelMessageAlertConfigurationDto
```

Edit the PanelMessage alert configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\PanelMessageAlertApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestPanelMessageAlertConfigurationDto(); // \OpenAPI\Client\Model\UpdateRequestPanelMessageAlertConfigurationDto

try {
    $result = $apiInstance->panelMessageAlertUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PanelMessageAlertApiApi->panelMessageAlertUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestPanelMessageAlertConfigurationDto**](../Model/UpdateRequestPanelMessageAlertConfigurationDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponsePanelMessageAlertConfigurationDto**](../Model/SingleResultResponsePanelMessageAlertConfigurationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
