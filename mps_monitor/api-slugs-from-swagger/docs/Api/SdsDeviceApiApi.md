# OpenAPI\Client\SdsDeviceApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**sdsDeviceAbortDeviceReboot()**](SdsDeviceApiApi.md#sdsDeviceAbortDeviceReboot) | **POST** /SdsDevice/AbortDeviceReboot | Abort a scheduled device reboot. |
| [**sdsDeviceAbortDeviceUpdateFirmware()**](SdsDeviceApiApi.md#sdsDeviceAbortDeviceUpdateFirmware) | **POST** /SdsDevice/AbortDeviceUpdateFirmware | Abort a scheduled firmware upgrade. |
| [**sdsDeviceAssessAndRemediate()**](SdsDeviceApiApi.md#sdsDeviceAssessAndRemediate) | **POST** /SdsDevice/AssessAndRemediate | Run an Assess and Remediate operation |
| [**sdsDeviceCheckDeviceOperation()**](SdsDeviceApiApi.md#sdsDeviceCheckDeviceOperation) | **PUT** /SdsDevice/CheckDeviceOperation | CheckDeviceOperation |
| [**sdsDeviceCopyPolicy()**](SdsDeviceApiApi.md#sdsDeviceCopyPolicy) | **PUT** /SdsDevice/CopyPolicy | Copy device config items to a new policy entity |
| [**sdsDeviceDeleteDeviceCredential()**](SdsDeviceApiApi.md#sdsDeviceDeleteDeviceCredential) | **DELETE** /SdsDevice/DeleteDeviceCredential | Deletes the device credential. |
| [**sdsDeviceGetAssessTemplate()**](SdsDeviceApiApi.md#sdsDeviceGetAssessTemplate) | **GET** /SdsDevice/GetAssessTemplate |  |
| [**sdsDeviceGetConfigItems()**](SdsDeviceApiApi.md#sdsDeviceGetConfigItems) | **GET** /SdsDevice/GetConfigItems | Gets the configuration items. |
| [**sdsDeviceGetCounters()**](SdsDeviceApiApi.md#sdsDeviceGetCounters) | **GET** /SdsDevice/GetCounters | Gets the counters. |
| [**sdsDeviceGetDeviceOperation()**](SdsDeviceApiApi.md#sdsDeviceGetDeviceOperation) | **GET** /SdsDevice/GetDeviceOperation | Gets the device operation. |
| [**sdsDeviceGetDeviceRemoteEws()**](SdsDeviceApiApi.md#sdsDeviceGetDeviceRemoteEws) | **GET** /SdsDevice/GetDeviceRemoteEws | Gets the device remote ews. |
| [**sdsDeviceGetDevicesOperations()**](SdsDeviceApiApi.md#sdsDeviceGetDevicesOperations) | **GET** /SdsDevice/GetDevicesOperations | Gets the devices operations. |
| [**sdsDeviceGetDevicesWithFirmwareOutOfDate()**](SdsDeviceApiApi.md#sdsDeviceGetDevicesWithFirmwareOutOfDate) | **POST** /SdsDevice/GetDevicesWithFirmwareOutOfDate | Gets the devices with firmware out of date. |
| [**sdsDeviceGetOnDeviceServices()**](SdsDeviceApiApi.md#sdsDeviceGetOnDeviceServices) | **GET** /SdsDevice/GetOnDeviceServices | Gets the on device services. |
| [**sdsDeviceGetSupplyDetails()**](SdsDeviceApiApi.md#sdsDeviceGetSupplyDetails) | **GET** /SdsDevice/GetSupplyDetails | Gets the supply details. |
| [**sdsDeviceGetZendeskTicketInfo()**](SdsDeviceApiApi.md#sdsDeviceGetZendeskTicketInfo) | **GET** /SdsDevice/GetZendeskTicketInfo |  |
| [**sdsDeviceHideEarlyReplacement()**](SdsDeviceApiApi.md#sdsDeviceHideEarlyReplacement) | **PUT** /SdsDevice/HideEarlyReplacement | Hides the supply detail. |
| [**sdsDeviceListSdsAssessAndRemediate()**](SdsDeviceApiApi.md#sdsDeviceListSdsAssessAndRemediate) | **POST** /SdsDevice/ListSdsAssessAndRemediate |  |
| [**sdsDeviceListSdsCredentials()**](SdsDeviceApiApi.md#sdsDeviceListSdsCredentials) | **POST** /SdsDevice/ListSdsCredentials |  |
| [**sdsDeviceListSdsMessageSuppression()**](SdsDeviceApiApi.md#sdsDeviceListSdsMessageSuppression) | **POST** /SdsDevice/ListSdsMessageSuppression | This operation gets lists of devices with Message suppression paged and filtered |
| [**sdsDeviceListSdsRapa()**](SdsDeviceApiApi.md#sdsDeviceListSdsRapa) | **POST** /SdsDevice/ListSdsRapa |  |
| [**sdsDeviceListSdsReboot()**](SdsDeviceApiApi.md#sdsDeviceListSdsReboot) | **POST** /SdsDevice/ListSdsReboot |  |
| [**sdsDevicePerformPrintQualityDiagnostics()**](SdsDeviceApiApi.md#sdsDevicePerformPrintQualityDiagnostics) | **POST** /SdsDevice/PerformPrintQualityDiagnostics | Perform a Print Quality Diagnostics operation on this device |
| [**sdsDeviceRemoveFromCloudDCA()**](SdsDeviceApiApi.md#sdsDeviceRemoveFromCloudDCA) | **DELETE** /SdsDevice/RemoveFromCloudDCA | Remove a device from HP SDS |
| [**sdsDeviceRemoveFromSDS()**](SdsDeviceApiApi.md#sdsDeviceRemoveFromSDS) | **DELETE** /SdsDevice/RemoveFromSDS | Remove a device from HP SDS |
| [**sdsDeviceRetrieveDeviceData()**](SdsDeviceApiApi.md#sdsDeviceRetrieveDeviceData) | **POST** /SdsDevice/RetrieveDeviceData | Retrieves the device data. |
| [**sdsDeviceSendPanelMessage()**](SdsDeviceApiApi.md#sdsDeviceSendPanelMessage) | **POST** /SdsDevice/SendPanelMessage | Send a message to the printer panel |
| [**sdsDeviceSetDeviceConfigData()**](SdsDeviceApiApi.md#sdsDeviceSetDeviceConfigData) | **POST** /SdsDevice/SetDeviceConfigData | Sets the device configuration data. |
| [**sdsDeviceSetDeviceCredential()**](SdsDeviceApiApi.md#sdsDeviceSetDeviceCredential) | **POST** /SdsDevice/SetDeviceCredential | Sets the device credential. |
| [**sdsDeviceSetDeviceUpdateFirmware()**](SdsDeviceApiApi.md#sdsDeviceSetDeviceUpdateFirmware) | **POST** /SdsDevice/SetDeviceUpdateFirmware | Sets the device update firmware. |
| [**sdsDeviceSetDevicesReboot()**](SdsDeviceApiApi.md#sdsDeviceSetDevicesReboot) | **POST** /SdsDevice/SetDevicesReboot | Set the devices specified to reboot at a specific date. |
| [**sdsDeviceSetOnDeviceServices()**](SdsDeviceApiApi.md#sdsDeviceSetOnDeviceServices) | **POST** /SdsDevice/SetOnDeviceServices | Sets the device user interface attributes. |
| [**sdsDeviceUpdateConfigItems()**](SdsDeviceApiApi.md#sdsDeviceUpdateConfigItems) | **POST** /SdsDevice/UpdateConfigItems | Retrieves the device data. |
| [**sdsDeviceUpdateDevicesFirmware()**](SdsDeviceApiApi.md#sdsDeviceUpdateDevicesFirmware) | **PUT** /SdsDevice/UpdateDevicesFirmware | Updates the devices firmware. |


## `sdsDeviceAbortDeviceReboot()`

```php
sdsDeviceAbortDeviceReboot($request): \OpenAPI\Client\Model\BaseResponse
```

Abort a scheduled device reboot.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->sdsDeviceAbortDeviceReboot($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceAbortDeviceReboot: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

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

## `sdsDeviceAbortDeviceUpdateFirmware()`

```php
sdsDeviceAbortDeviceUpdateFirmware($request): \OpenAPI\Client\Model\BaseResponse
```

Abort a scheduled firmware upgrade.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->sdsDeviceAbortDeviceUpdateFirmware($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceAbortDeviceUpdateFirmware: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

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

## `sdsDeviceAssessAndRemediate()`

```php
sdsDeviceAssessAndRemediate($request): \OpenAPI\Client\Model\BaseResponse
```

Run an Assess and Remediate operation

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\AssessAndRemediateRequest(); // \OpenAPI\Client\Model\AssessAndRemediateRequest

try {
    $result = $apiInstance->sdsDeviceAssessAndRemediate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceAssessAndRemediate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\AssessAndRemediateRequest**](../Model/AssessAndRemediateRequest.md)|  | |

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

## `sdsDeviceCheckDeviceOperation()`

```php
sdsDeviceCheckDeviceOperation($request): \OpenAPI\Client\Model\BaseResponse
```

CheckDeviceOperation

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CheckDeviceOperationRequest(); // \OpenAPI\Client\Model\CheckDeviceOperationRequest

try {
    $result = $apiInstance->sdsDeviceCheckDeviceOperation($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceCheckDeviceOperation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CheckDeviceOperationRequest**](../Model/CheckDeviceOperationRequest.md)|  | |

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

## `sdsDeviceCopyPolicy()`

```php
sdsDeviceCopyPolicy($request): \OpenAPI\Client\Model\BaseResponse
```

Copy device config items to a new policy entity

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CopyPolicyRequest(); // \OpenAPI\Client\Model\CopyPolicyRequest

try {
    $result = $apiInstance->sdsDeviceCopyPolicy($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceCopyPolicy: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CopyPolicyRequest**](../Model/CopyPolicyRequest.md)|  | |

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

## `sdsDeviceDeleteDeviceCredential()`

```php
sdsDeviceDeleteDeviceCredential($id, $credential_type): \OpenAPI\Client\Model\BaseResponse
```

Deletes the device credential.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$credential_type = 'credential_type_example'; // string

try {
    $result = $apiInstance->sdsDeviceDeleteDeviceCredential($id, $credential_type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceDeleteDeviceCredential: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **credential_type** | **string**|  | [optional] |

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

## `sdsDeviceGetAssessTemplate()`

```php
sdsDeviceGetAssessTemplate($id): \OpenAPI\Client\Model\ListResultResponseIdDescDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsDeviceGetAssessTemplate($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceGetAssessTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseIdDescDto**](../Model/ListResultResponseIdDescDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceGetConfigItems()`

```php
sdsDeviceGetConfigItems($id): \OpenAPI\Client\Model\SingleResultResponseSdsConfigItemsDto
```

Gets the configuration items.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsDeviceGetConfigItems($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceGetConfigItems: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSdsConfigItemsDto**](../Model/SingleResultResponseSdsConfigItemsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceGetCounters()`

```php
sdsDeviceGetCounters($id): \OpenAPI\Client\Model\ListResultResponseSdsNestedConfigItemDto
```

Gets the counters.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsDeviceGetCounters($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceGetCounters: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseSdsNestedConfigItemDto**](../Model/ListResultResponseSdsNestedConfigItemDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceGetDeviceOperation()`

```php
sdsDeviceGetDeviceOperation($id, $device_id, $operation_type): \OpenAPI\Client\Model\SingleResultResponseSdsDeviceOperationDto
```

Gets the device operation.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$device_id = 'device_id_example'; // string
$operation_type = 'operation_type_example'; // string

try {
    $result = $apiInstance->sdsDeviceGetDeviceOperation($id, $device_id, $operation_type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceGetDeviceOperation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **device_id** | **string**|  | [optional] |
| **operation_type** | **string**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSdsDeviceOperationDto**](../Model/SingleResultResponseSdsDeviceOperationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceGetDeviceRemoteEws()`

```php
sdsDeviceGetDeviceRemoteEws($id): \OpenAPI\Client\Model\BaseResponse
```

Gets the device remote ews.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsDeviceGetDeviceRemoteEws($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceGetDeviceRemoteEws: ', $e->getMessage(), PHP_EOL;
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

## `sdsDeviceGetDevicesOperations()`

```php
sdsDeviceGetDevicesOperations($page_number, $page_rows, $sort_column, $sort_order, $device_id, $filter_text): \OpenAPI\Client\Model\PagedResultResponseSdsDeviceOperationDto
```

Gets the devices operations.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$device_id = 'device_id_example'; // string
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->sdsDeviceGetDevicesOperations($page_number, $page_rows, $sort_column, $sort_order, $device_id, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceGetDevicesOperations: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **device_id** | **string**|  | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSdsDeviceOperationDto**](../Model/PagedResultResponseSdsDeviceOperationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceGetDevicesWithFirmwareOutOfDate()`

```php
sdsDeviceGetDevicesWithFirmwareOutOfDate($request): \OpenAPI\Client\Model\PagedResultResponseSdsDeviceFirmwareOutOfDateDto
```

Gets the devices with firmware out of date.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDevicesWithFirmwareOutOfDateRequest(); // \OpenAPI\Client\Model\GetDevicesWithFirmwareOutOfDateRequest | The request.

try {
    $result = $apiInstance->sdsDeviceGetDevicesWithFirmwareOutOfDate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceGetDevicesWithFirmwareOutOfDate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDevicesWithFirmwareOutOfDateRequest**](../Model/GetDevicesWithFirmwareOutOfDateRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSdsDeviceFirmwareOutOfDateDto**](../Model/PagedResultResponseSdsDeviceFirmwareOutOfDateDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceGetOnDeviceServices()`

```php
sdsDeviceGetOnDeviceServices($id): \OpenAPI\Client\Model\SingleResultResponseSdsOnDeviceServicesDto
```

Gets the on device services.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsDeviceGetOnDeviceServices($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceGetOnDeviceServices: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSdsOnDeviceServicesDto**](../Model/SingleResultResponseSdsOnDeviceServicesDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceGetSupplyDetails()`

```php
sdsDeviceGetSupplyDetails($id, $supply_type): \OpenAPI\Client\Model\SingleResultResponseSupplyDetailsDto
```

Gets the supply details.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$supply_type = 'supply_type_example'; // string | Gets or sets the type of the supply.

try {
    $result = $apiInstance->sdsDeviceGetSupplyDetails($id, $supply_type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceGetSupplyDetails: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **supply_type** | **string**| Gets or sets the type of the supply. | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSupplyDetailsDto**](../Model/SingleResultResponseSupplyDetailsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceGetZendeskTicketInfo()`

```php
sdsDeviceGetZendeskTicketInfo($id): \OpenAPI\Client\Model\SingleResultResponseZendeskTicketInfoDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsDeviceGetZendeskTicketInfo($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceGetZendeskTicketInfo: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseZendeskTicketInfoDto**](../Model/SingleResultResponseZendeskTicketInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceHideEarlyReplacement()`

```php
sdsDeviceHideEarlyReplacement($request): \OpenAPI\Client\Model\BaseResponse
```

Hides the supply detail.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\HideSupplyDetailRequest(); // \OpenAPI\Client\Model\HideSupplyDetailRequest | The request.

try {
    $result = $apiInstance->sdsDeviceHideEarlyReplacement($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceHideEarlyReplacement: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\HideSupplyDetailRequest**](../Model/HideSupplyDetailRequest.md)| The request. | |

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

## `sdsDeviceListSdsAssessAndRemediate()`

```php
sdsDeviceListSdsAssessAndRemediate($request): \OpenAPI\Client\Model\PagedResultResponseSdsAssessAndRemediateDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDevicesSdsAssessAndRemediateRequest(); // \OpenAPI\Client\Model\GetDevicesSdsAssessAndRemediateRequest

try {
    $result = $apiInstance->sdsDeviceListSdsAssessAndRemediate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceListSdsAssessAndRemediate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDevicesSdsAssessAndRemediateRequest**](../Model/GetDevicesSdsAssessAndRemediateRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSdsAssessAndRemediateDto**](../Model/PagedResultResponseSdsAssessAndRemediateDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceListSdsCredentials()`

```php
sdsDeviceListSdsCredentials($request): \OpenAPI\Client\Model\PagedResultResponseSdsCredentialsDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDevicesSdsCredentialsRequest(); // \OpenAPI\Client\Model\GetDevicesSdsCredentialsRequest

try {
    $result = $apiInstance->sdsDeviceListSdsCredentials($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceListSdsCredentials: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDevicesSdsCredentialsRequest**](../Model/GetDevicesSdsCredentialsRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSdsCredentialsDto**](../Model/PagedResultResponseSdsCredentialsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceListSdsMessageSuppression()`

```php
sdsDeviceListSdsMessageSuppression($request): \OpenAPI\Client\Model\PagedResultResponseSdsMessageSuppressionDto
```

This operation gets lists of devices with Message suppression paged and filtered

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDevicesSdsMessageSuppressionRequest(); // \OpenAPI\Client\Model\GetDevicesSdsMessageSuppressionRequest

try {
    $result = $apiInstance->sdsDeviceListSdsMessageSuppression($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceListSdsMessageSuppression: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDevicesSdsMessageSuppressionRequest**](../Model/GetDevicesSdsMessageSuppressionRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSdsMessageSuppressionDto**](../Model/PagedResultResponseSdsMessageSuppressionDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceListSdsRapa()`

```php
sdsDeviceListSdsRapa($request): \OpenAPI\Client\Model\PagedResultResponseSdsRapaDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDevicesSdsRapaRequest(); // \OpenAPI\Client\Model\GetDevicesSdsRapaRequest

try {
    $result = $apiInstance->sdsDeviceListSdsRapa($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceListSdsRapa: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDevicesSdsRapaRequest**](../Model/GetDevicesSdsRapaRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSdsRapaDto**](../Model/PagedResultResponseSdsRapaDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDeviceListSdsReboot()`

```php
sdsDeviceListSdsReboot($request): \OpenAPI\Client\Model\PagedResultResponseSdsRebootDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDevicesRebootRequest(); // \OpenAPI\Client\Model\GetDevicesRebootRequest

try {
    $result = $apiInstance->sdsDeviceListSdsReboot($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceListSdsReboot: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDevicesRebootRequest**](../Model/GetDevicesRebootRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSdsRebootDto**](../Model/PagedResultResponseSdsRebootDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsDevicePerformPrintQualityDiagnostics()`

```php
sdsDevicePerformPrintQualityDiagnostics($request): \OpenAPI\Client\Model\BaseResponse
```

Perform a Print Quality Diagnostics operation on this device

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->sdsDevicePerformPrintQualityDiagnostics($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDevicePerformPrintQualityDiagnostics: ', $e->getMessage(), PHP_EOL;
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

## `sdsDeviceRemoveFromCloudDCA()`

```php
sdsDeviceRemoveFromCloudDCA($id): \OpenAPI\Client\Model\BaseResponse
```

Remove a device from HP SDS

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsDeviceRemoveFromCloudDCA($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceRemoveFromCloudDCA: ', $e->getMessage(), PHP_EOL;
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

## `sdsDeviceRemoveFromSDS()`

```php
sdsDeviceRemoveFromSDS($id): \OpenAPI\Client\Model\BaseResponse
```

Remove a device from HP SDS

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsDeviceRemoveFromSDS($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceRemoveFromSDS: ', $e->getMessage(), PHP_EOL;
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

## `sdsDeviceRetrieveDeviceData()`

```php
sdsDeviceRetrieveDeviceData($request): \OpenAPI\Client\Model\BaseResponse
```

Retrieves the device data.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\RetrieveDeviceDataRequest(); // \OpenAPI\Client\Model\RetrieveDeviceDataRequest | The request.

try {
    $result = $apiInstance->sdsDeviceRetrieveDeviceData($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceRetrieveDeviceData: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\RetrieveDeviceDataRequest**](../Model/RetrieveDeviceDataRequest.md)| The request. | |

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

## `sdsDeviceSendPanelMessage()`

```php
sdsDeviceSendPanelMessage($request): \OpenAPI\Client\Model\BaseResponse
```

Send a message to the printer panel

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SdsSendPanelMessageRequest(); // \OpenAPI\Client\Model\SdsSendPanelMessageRequest

try {
    $result = $apiInstance->sdsDeviceSendPanelMessage($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceSendPanelMessage: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SdsSendPanelMessageRequest**](../Model/SdsSendPanelMessageRequest.md)|  | |

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

## `sdsDeviceSetDeviceConfigData()`

```php
sdsDeviceSetDeviceConfigData($request): \OpenAPI\Client\Model\BaseResponse
```

Sets the device configuration data.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetDeviceConfigDataRequest(); // \OpenAPI\Client\Model\SetDeviceConfigDataRequest | The request.

try {
    $result = $apiInstance->sdsDeviceSetDeviceConfigData($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceSetDeviceConfigData: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetDeviceConfigDataRequest**](../Model/SetDeviceConfigDataRequest.md)| The request. | |

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

## `sdsDeviceSetDeviceCredential()`

```php
sdsDeviceSetDeviceCredential($request): \OpenAPI\Client\Model\BaseResponse
```

Sets the device credential.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetJamCredentialRequest(); // \OpenAPI\Client\Model\SetJamCredentialRequest | The request.

try {
    $result = $apiInstance->sdsDeviceSetDeviceCredential($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceSetDeviceCredential: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetJamCredentialRequest**](../Model/SetJamCredentialRequest.md)| The request. | |

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

## `sdsDeviceSetDeviceUpdateFirmware()`

```php
sdsDeviceSetDeviceUpdateFirmware($request): \OpenAPI\Client\Model\BaseResponse
```

Sets the device update firmware.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetDeviceUpdateFirmwareRequest(); // \OpenAPI\Client\Model\SetDeviceUpdateFirmwareRequest | The request.

try {
    $result = $apiInstance->sdsDeviceSetDeviceUpdateFirmware($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceSetDeviceUpdateFirmware: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetDeviceUpdateFirmwareRequest**](../Model/SetDeviceUpdateFirmwareRequest.md)| The request. | |

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

## `sdsDeviceSetDevicesReboot()`

```php
sdsDeviceSetDevicesReboot($request): \OpenAPI\Client\Model\BaseResponse
```

Set the devices specified to reboot at a specific date.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetDevicesRebootRequest(); // \OpenAPI\Client\Model\SetDevicesRebootRequest

try {
    $result = $apiInstance->sdsDeviceSetDevicesReboot($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceSetDevicesReboot: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetDevicesRebootRequest**](../Model/SetDevicesRebootRequest.md)|  | |

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

## `sdsDeviceSetOnDeviceServices()`

```php
sdsDeviceSetOnDeviceServices($request): \OpenAPI\Client\Model\BaseResponse
```

Sets the device user interface attributes.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetSdsOnDeviceServicesRequest(); // \OpenAPI\Client\Model\SetSdsOnDeviceServicesRequest | The request.

try {
    $result = $apiInstance->sdsDeviceSetOnDeviceServices($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceSetOnDeviceServices: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetSdsOnDeviceServicesRequest**](../Model/SetSdsOnDeviceServicesRequest.md)| The request. | |

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

## `sdsDeviceUpdateConfigItems()`

```php
sdsDeviceUpdateConfigItems($request): \OpenAPI\Client\Model\BaseResponse
```

Retrieves the device data.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->sdsDeviceUpdateConfigItems($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceUpdateConfigItems: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

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

## `sdsDeviceUpdateDevicesFirmware()`

```php
sdsDeviceUpdateDevicesFirmware($request): \OpenAPI\Client\Model\SdsSetDevicesUpdateFirmwareResponse
```

Updates the devices firmware.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsDeviceApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestSdsSetDevicesUpdateFirmwareRequest(); // \OpenAPI\Client\Model\UpdateRequestSdsSetDevicesUpdateFirmwareRequest | The request.

try {
    $result = $apiInstance->sdsDeviceUpdateDevicesFirmware($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsDeviceApiApi->sdsDeviceUpdateDevicesFirmware: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestSdsSetDevicesUpdateFirmwareRequest**](../Model/UpdateRequestSdsSetDevicesUpdateFirmwareRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SdsSetDevicesUpdateFirmwareResponse**](../Model/SdsSetDevicesUpdateFirmwareResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
