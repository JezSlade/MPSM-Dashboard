# OpenAPI\Client\TraceVolumeApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**traceVolumeCloseEvent()**](TraceVolumeApi.md#traceVolumeCloseEvent) | **POST** /TraceVolume/CloseEvent | Close a TraceVolume configuration event |
| [**traceVolumeCreate()**](TraceVolumeApi.md#traceVolumeCreate) | **POST** /TraceVolume/Create | Create the TraceVolume alert configuration |
| [**traceVolumeCreateManualEvent()**](TraceVolumeApi.md#traceVolumeCreateManualEvent) | **POST** /TraceVolume/CreateManualEvent | Create a manual TraceVolume configuration event |
| [**traceVolumeDelete()**](TraceVolumeApi.md#traceVolumeDelete) | **DELETE** /TraceVolume/Delete | Set a specific trace volume to deleted |
| [**traceVolumeGet()**](TraceVolumeApi.md#traceVolumeGet) | **GET** /TraceVolume/Get | Gets a specific trace volume by its id |
| [**traceVolumeList()**](TraceVolumeApi.md#traceVolumeList) | **GET** /TraceVolume/List | Returns a list of TraceVolume by device |
| [**traceVolumeResetEvents()**](TraceVolumeApi.md#traceVolumeResetEvents) | **PUT** /TraceVolume/ResetEvents | Reset the TraceVolume configuration events and set a new one |
| [**traceVolumeUpdate()**](TraceVolumeApi.md#traceVolumeUpdate) | **PUT** /TraceVolume/Update | Update the TraceVolume alert configuration |


## `traceVolumeCloseEvent()`

```php
traceVolumeCloseEvent($request): \OpenAPI\Client\Model\BaseResponse
```

Close a TraceVolume configuration event

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TraceVolumeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->traceVolumeCloseEvent($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TraceVolumeApi->traceVolumeCloseEvent: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)|  | |

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

## `traceVolumeCreate()`

```php
traceVolumeCreate($request): \OpenAPI\Client\Model\SingleResultResponseTraceVolumeConfigurationDto
```

Create the TraceVolume alert configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TraceVolumeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestTraceVolumeConfigurationDto(); // \OpenAPI\Client\Model\CreateRequestTraceVolumeConfigurationDto

try {
    $result = $apiInstance->traceVolumeCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TraceVolumeApi->traceVolumeCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestTraceVolumeConfigurationDto**](../Model/CreateRequestTraceVolumeConfigurationDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseTraceVolumeConfigurationDto**](../Model/SingleResultResponseTraceVolumeConfigurationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `traceVolumeCreateManualEvent()`

```php
traceVolumeCreateManualEvent($request): \OpenAPI\Client\Model\BaseResponse
```

Create a manual TraceVolume configuration event

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TraceVolumeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\TraceVolumeNewEventRequest(); // \OpenAPI\Client\Model\TraceVolumeNewEventRequest

try {
    $result = $apiInstance->traceVolumeCreateManualEvent($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TraceVolumeApi->traceVolumeCreateManualEvent: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\TraceVolumeNewEventRequest**](../Model/TraceVolumeNewEventRequest.md)|  | |

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

## `traceVolumeDelete()`

```php
traceVolumeDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Set a specific trace volume to deleted

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TraceVolumeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->traceVolumeDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TraceVolumeApi->traceVolumeDelete: ', $e->getMessage(), PHP_EOL;
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

## `traceVolumeGet()`

```php
traceVolumeGet($id): \OpenAPI\Client\Model\SingleResultResponseTraceVolumeConfigurationDto
```

Gets a specific trace volume by its id

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TraceVolumeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->traceVolumeGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TraceVolumeApi->traceVolumeGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseTraceVolumeConfigurationDto**](../Model/SingleResultResponseTraceVolumeConfigurationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `traceVolumeList()`

```php
traceVolumeList($id): \OpenAPI\Client\Model\PagedResultResponseTraceVolumeConfigurationListDto
```

Returns a list of TraceVolume by device

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TraceVolumeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->traceVolumeList($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TraceVolumeApi->traceVolumeList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseTraceVolumeConfigurationListDto**](../Model/PagedResultResponseTraceVolumeConfigurationListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `traceVolumeResetEvents()`

```php
traceVolumeResetEvents($request): \OpenAPI\Client\Model\BaseResponse
```

Reset the TraceVolume configuration events and set a new one

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TraceVolumeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\TraceVolumeNewEventRequest(); // \OpenAPI\Client\Model\TraceVolumeNewEventRequest

try {
    $result = $apiInstance->traceVolumeResetEvents($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TraceVolumeApi->traceVolumeResetEvents: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\TraceVolumeNewEventRequest**](../Model/TraceVolumeNewEventRequest.md)|  | |

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

## `traceVolumeUpdate()`

```php
traceVolumeUpdate($request): \OpenAPI\Client\Model\SingleResultResponseTraceVolumeConfigurationDto
```

Update the TraceVolume alert configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\TraceVolumeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestTraceVolumeConfigurationDto(); // \OpenAPI\Client\Model\UpdateRequestTraceVolumeConfigurationDto

try {
    $result = $apiInstance->traceVolumeUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TraceVolumeApi->traceVolumeUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestTraceVolumeConfigurationDto**](../Model/UpdateRequestTraceVolumeConfigurationDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseTraceVolumeConfigurationDto**](../Model/SingleResultResponseTraceVolumeConfigurationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
