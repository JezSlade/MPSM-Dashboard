# OpenAPI\Client\SdsEventApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**sdsEventGetDeviceEvent()**](SdsEventApiApi.md#sdsEventGetDeviceEvent) | **GET** /SdsEvent/GetDeviceEvent | Gets the device event. |
| [**sdsEventGetDeviceEvents()**](SdsEventApiApi.md#sdsEventGetDeviceEvents) | **GET** /SdsEvent/GetDeviceEvents |  |


## `sdsEventGetDeviceEvent()`

```php
sdsEventGetDeviceEvent($id): \OpenAPI\Client\Model\SingleResultResponseSdsDeviceEventDto
```

Gets the device event.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsEventApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsEventGetDeviceEvent($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsEventApiApi->sdsEventGetDeviceEvent: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSdsDeviceEventDto**](../Model/SingleResultResponseSdsDeviceEventDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsEventGetDeviceEvents()`

```php
sdsEventGetDeviceEvents($device_id, $date_from, $event_type): \OpenAPI\Client\Model\ListResultResponseSdsDeviceEventDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsEventApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$device_id = 'device_id_example'; // string
$date_from = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime
$event_type = 'event_type_example'; // string

try {
    $result = $apiInstance->sdsEventGetDeviceEvents($device_id, $date_from, $event_type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsEventApiApi->sdsEventGetDeviceEvents: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **device_id** | **string**|  | [optional] |
| **date_from** | **\DateTime**|  | [optional] |
| **event_type** | **string**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseSdsDeviceEventDto**](../Model/ListResultResponseSdsDeviceEventDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
