# OpenAPI\Client\ZebraDeviceApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**zebraDeviceSetDevicesReboot()**](ZebraDeviceApiApi.md#zebraDeviceSetDevicesReboot) | **POST** /ZebraDevice/SetDevicesReboot | Reboot zebra devices |
| [**zebraDeviceSetDevicesUpdateFirmware()**](ZebraDeviceApiApi.md#zebraDeviceSetDevicesUpdateFirmware) | **POST** /ZebraDevice/SetDevicesUpdateFirmware | Update firmware for zebra devices |
| [**zebraDeviceSetZebraSettings()**](ZebraDeviceApiApi.md#zebraDeviceSetZebraSettings) | **POST** /ZebraDevice/SetZebraSettings | Sets the zebra settings. |


## `zebraDeviceSetDevicesReboot()`

```php
zebraDeviceSetDevicesReboot($request): \OpenAPI\Client\Model\BaseResponse
```

Reboot zebra devices

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ZebraDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetDevicesRebootRequest(); // \OpenAPI\Client\Model\SetDevicesRebootRequest | The request.

try {
    $result = $apiInstance->zebraDeviceSetDevicesReboot($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ZebraDeviceApiApi->zebraDeviceSetDevicesReboot: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetDevicesRebootRequest**](../Model/SetDevicesRebootRequest.md)| The request. | |

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

## `zebraDeviceSetDevicesUpdateFirmware()`

```php
zebraDeviceSetDevicesUpdateFirmware($request): \OpenAPI\Client\Model\BaseResponse
```

Update firmware for zebra devices

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ZebraDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetDevicesUpdateFirmwareRequest(); // \OpenAPI\Client\Model\SetDevicesUpdateFirmwareRequest | The request.

try {
    $result = $apiInstance->zebraDeviceSetDevicesUpdateFirmware($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ZebraDeviceApiApi->zebraDeviceSetDevicesUpdateFirmware: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetDevicesUpdateFirmwareRequest**](../Model/SetDevicesUpdateFirmwareRequest.md)| The request. | |

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

## `zebraDeviceSetZebraSettings()`

```php
zebraDeviceSetZebraSettings($request): \OpenAPI\Client\Model\BaseResponse
```

Sets the zebra settings.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ZebraDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetZebraSettingsRequest(); // \OpenAPI\Client\Model\SetZebraSettingsRequest | The request.

try {
    $result = $apiInstance->zebraDeviceSetZebraSettings($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ZebraDeviceApiApi->zebraDeviceSetZebraSettings: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetZebraSettingsRequest**](../Model/SetZebraSettingsRequest.md)| The request. | |

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
