# OpenAPI\Client\SupplyAlertApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**supplyAlertGet()**](SupplyAlertApi.md#supplyAlertGet) | **POST** /SupplyAlert/Get | Gets a specific alert by its id |
| [**supplyAlertGetAvailableMaintenanceKitColors()**](SupplyAlertApi.md#supplyAlertGetAvailableMaintenanceKitColors) | **GET** /SupplyAlert/GetAvailableMaintenanceKitColors | Gets available Maintenance kit colors |
| [**supplyAlertGetAvailableMaintenanceKitTypes()**](SupplyAlertApi.md#supplyAlertGetAvailableMaintenanceKitTypes) | **GET** /SupplyAlert/GetAvailableMaintenanceKitTypes | Gets available Maintenance kit types |
| [**supplyAlertGetAvailableSuppliesForADevice()**](SupplyAlertApi.md#supplyAlertGetAvailableSuppliesForADevice) | **GET** /SupplyAlert/GetAvailableSuppliesForADevice | Gets available supplies for a device |
| [**supplyAlertList()**](SupplyAlertApi.md#supplyAlertList) | **POST** /SupplyAlert/List | Returns a list of opened alerts (not installed yet). |
| [**supplyAlertListByOffice()**](SupplyAlertApi.md#supplyAlertListByOffice) | **POST** /SupplyAlert/ListByOffice | Returns a list of opened alerts (not installed yet) by customer. |
| [**supplyAlertMassiveUpdate()**](SupplyAlertApi.md#supplyAlertMassiveUpdate) | **POST** /SupplyAlert/MassiveUpdate | Update massive supply alerts |
| [**supplyAlertPostponeAlert()**](SupplyAlertApi.md#supplyAlertPostponeAlert) | **PUT** /SupplyAlert/PostponeAlert | Postpone an alert until percentage |
| [**supplyAlertUpdate()**](SupplyAlertApi.md#supplyAlertUpdate) | **POST** /SupplyAlert/Update | Updates a supply alert |


## `supplyAlertGet()`

```php
supplyAlertGet($request): \OpenAPI\Client\Model\SingleResultResponseSupplyAlertDto
```

Gets a specific alert by its id

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SupplyAlertApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->supplyAlertGet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SupplyAlertApi->supplyAlertGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSupplyAlertDto**](../Model/SingleResultResponseSupplyAlertDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `supplyAlertGetAvailableMaintenanceKitColors()`

```php
supplyAlertGetAvailableMaintenanceKitColors(): \OpenAPI\Client\Model\ListResultResponseKeyValueDto
```

Gets available Maintenance kit colors

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SupplyAlertApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->supplyAlertGetAvailableMaintenanceKitColors();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SupplyAlertApi->supplyAlertGetAvailableMaintenanceKitColors: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\OpenAPI\Client\Model\ListResultResponseKeyValueDto**](../Model/ListResultResponseKeyValueDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `supplyAlertGetAvailableMaintenanceKitTypes()`

```php
supplyAlertGetAvailableMaintenanceKitTypes(): \OpenAPI\Client\Model\ListResultResponseKeyValueDto
```

Gets available Maintenance kit types

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SupplyAlertApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->supplyAlertGetAvailableMaintenanceKitTypes();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SupplyAlertApi->supplyAlertGetAvailableMaintenanceKitTypes: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\OpenAPI\Client\Model\ListResultResponseKeyValueDto**](../Model/ListResultResponseKeyValueDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `supplyAlertGetAvailableSuppliesForADevice()`

```php
supplyAlertGetAvailableSuppliesForADevice($device_id, $supply_type, $color_type, $maintenance_kit_type_id, $maintenance_kit_color_id, $warning, $language, $show_only_current_supplies_in_use): \OpenAPI\Client\Model\SingleResultResponseAvailableSuppliesDto
```

Gets available supplies for a device

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SupplyAlertApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$device_id = 'device_id_example'; // string | Gets or sets the device Id
$supply_type = 'supply_type_example'; // string | Gets or sets the SupplyType
$color_type = 'color_type_example'; // string | Gets or sets the ColorType
$maintenance_kit_type_id = 56; // int | Gets or sets the MaintenanceKitType
$maintenance_kit_color_id = 56; // int | Gets or sets the MaintenanceKitColor
$warning = 'warning_example'; // string | Gets or sets the SupplyAlert warning
$language = 'language_example'; // string | Set the language to retrieve supplies localizated
$show_only_current_supplies_in_use = True; // bool | Show only current supplies in use

try {
    $result = $apiInstance->supplyAlertGetAvailableSuppliesForADevice($device_id, $supply_type, $color_type, $maintenance_kit_type_id, $maintenance_kit_color_id, $warning, $language, $show_only_current_supplies_in_use);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SupplyAlertApi->supplyAlertGetAvailableSuppliesForADevice: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **device_id** | **string**| Gets or sets the device Id | |
| **supply_type** | **string**| Gets or sets the SupplyType | |
| **color_type** | **string**| Gets or sets the ColorType | |
| **maintenance_kit_type_id** | **int**| Gets or sets the MaintenanceKitType | |
| **maintenance_kit_color_id** | **int**| Gets or sets the MaintenanceKitColor | |
| **warning** | **string**| Gets or sets the SupplyAlert warning | |
| **language** | **string**| Set the language to retrieve supplies localizated | |
| **show_only_current_supplies_in_use** | **bool**| Show only current supplies in use | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAvailableSuppliesDto**](../Model/SingleResultResponseAvailableSuppliesDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `supplyAlertList()`

```php
supplyAlertList($request): \OpenAPI\Client\Model\PagedResultResponseSupplyAlertListDto
```

Returns a list of opened alerts (not installed yet).

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SupplyAlertApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetSupplyAlertRequest(); // \OpenAPI\Client\Model\GetSupplyAlertRequest | The request.

try {
    $result = $apiInstance->supplyAlertList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SupplyAlertApi->supplyAlertList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetSupplyAlertRequest**](../Model/GetSupplyAlertRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSupplyAlertListDto**](../Model/PagedResultResponseSupplyAlertListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `supplyAlertListByOffice()`

```php
supplyAlertListByOffice($request): \OpenAPI\Client\Model\ListResultResponseAlertOfficeDto
```

Returns a list of opened alerts (not installed yet) by customer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SupplyAlertApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetAlertsByOfficeRequest(); // \OpenAPI\Client\Model\GetAlertsByOfficeRequest

try {
    $result = $apiInstance->supplyAlertListByOffice($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SupplyAlertApi->supplyAlertListByOffice: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetAlertsByOfficeRequest**](../Model/GetAlertsByOfficeRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseAlertOfficeDto**](../Model/ListResultResponseAlertOfficeDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `supplyAlertMassiveUpdate()`

```php
supplyAlertMassiveUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Update massive supply alerts

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SupplyAlertApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestMassiveUpdateAlertDto(); // \OpenAPI\Client\Model\UpdateRequestMassiveUpdateAlertDto

try {
    $result = $apiInstance->supplyAlertMassiveUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SupplyAlertApi->supplyAlertMassiveUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestMassiveUpdateAlertDto**](../Model/UpdateRequestMassiveUpdateAlertDto.md)|  | |

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

## `supplyAlertPostponeAlert()`

```php
supplyAlertPostponeAlert($request): \OpenAPI\Client\Model\BaseResponse
```

Postpone an alert until percentage

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SupplyAlertApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerablePostponeAlertDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerablePostponeAlertDto

try {
    $result = $apiInstance->supplyAlertPostponeAlert($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SupplyAlertApi->supplyAlertPostponeAlert: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerablePostponeAlertDto**](../Model/UpdateRequestIEnumerablePostponeAlertDto.md)|  | |

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

## `supplyAlertUpdate()`

```php
supplyAlertUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Updates a supply alert

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SupplyAlertApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestUpdateAlertDto(); // \OpenAPI\Client\Model\UpdateRequestUpdateAlertDto

try {
    $result = $apiInstance->supplyAlertUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SupplyAlertApi->supplyAlertUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestUpdateAlertDto**](../Model/UpdateRequestUpdateAlertDto.md)|  | |

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
