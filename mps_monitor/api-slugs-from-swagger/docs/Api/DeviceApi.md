# OpenAPI\Client\DeviceApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**deviceAssignOfficeToDevicesByDeviceId()**](DeviceApi.md#deviceAssignOfficeToDevicesByDeviceId) | **PUT** /Device/AssignOfficeToDevicesByDeviceId | Assign an office to some devices by  DeviceId |
| [**deviceAssignOfficeToDevicesBySerialNumber()**](DeviceApi.md#deviceAssignOfficeToDevicesBySerialNumber) | **PUT** /Device/AssignOfficeToDevicesBySerialNumber | Assign an office to some devices by  SerialNumber |
| [**deviceDelete()**](DeviceApi.md#deviceDelete) | **DELETE** /Device/Delete | This operation deletes a device |
| [**deviceDeletedList()**](DeviceApi.md#deviceDeletedList) | **GET** /Device/Deleted/List | This operation gets lists of devices paged and filtered |
| [**deviceDeletedListByDealer()**](DeviceApi.md#deviceDeletedListByDealer) | **GET** /Device/Deleted/ListByDealer | This operation gets lists of devices paged and filtered by dealer |
| [**deviceDeletedRestore()**](DeviceApi.md#deviceDeletedRestore) | **PUT** /Device/Deleted/Restore | This operation restore a device |
| [**deviceExplorerDataAffinitiesList()**](DeviceApi.md#deviceExplorerDataAffinitiesList) | **GET** /Device/ExplorerDataAffinities/List | Returns a list of DeviceExplorerDataAffinities filtered by idDevice |
| [**deviceGet()**](DeviceApi.md#deviceGet) | **POST** /Device/Get | Returns a device by request parameters |
| [**deviceGetDetailedInformations()**](DeviceApi.md#deviceGetDetailedInformations) | **POST** /Device/GetDetailedInformations | Returns  device detailed Informations by request parameters |
| [**deviceGetDeviceAdditionalInfos()**](DeviceApi.md#deviceGetDeviceAdditionalInfos) | **GET** /Device/GetDeviceAdditionalInfos |  |
| [**deviceGetDeviceGapInfos()**](DeviceApi.md#deviceGetDeviceGapInfos) | **GET** /Device/GetDeviceGapInfos |  |
| [**deviceGetLfpCounters()**](DeviceApi.md#deviceGetLfpCounters) | **GET** /Device/GetLfpCounters |  |
| [**deviceGetSuppliesDetails()**](DeviceApi.md#deviceGetSuppliesDetails) | **GET** /Device/GetSuppliesDetails | Returns a device by request parameters |
| [**deviceGetSuppliesDetailsInfo()**](DeviceApi.md#deviceGetSuppliesDetailsInfo) | **GET** /Device/GetSuppliesDetailsInfo | Gets current forecast and history consumable details for a specific device, consumable and consumable color type |
| [**deviceGetSuppliesDetailsSummary()**](DeviceApi.md#deviceGetSuppliesDetailsSummary) | **GET** /Device/GetSuppliesDetailsSummary | Get toners and photoconductors forecast details for a specific device |
| [**deviceGetZebraSuppliesDetailsSummary()**](DeviceApi.md#deviceGetZebraSuppliesDetailsSummary) | **GET** /Device/GetZebraSuppliesDetailsSummary |  |
| [**deviceList()**](DeviceApi.md#deviceList) | **POST** /Device/List | This operation gets lists of devices paged and filtered |
| [**deviceListAttributesDataHistory()**](DeviceApi.md#deviceListAttributesDataHistory) | **POST** /Device/ListAttributesDataHistory |  |
| [**deviceListErrorsMessagesDataHistory()**](DeviceApi.md#deviceListErrorsMessagesDataHistory) | **POST** /Device/ListErrorsMessagesDataHistory |  |
| [**deviceListLevelsDataHistory()**](DeviceApi.md#deviceListLevelsDataHistory) | **POST** /Device/ListLevelsDataHistory |  |
| [**deviceListMaintenanceKitMessagesDataHistory()**](DeviceApi.md#deviceListMaintenanceKitMessagesDataHistory) | **POST** /Device/ListMaintenanceKitMessagesDataHistory |  |
| [**deviceListMeterReads()**](DeviceApi.md#deviceListMeterReads) | **POST** /Device/ListMeterReads | This operation gets lists of meter reads daily from eXplorer |
| [**deviceListMetersDataHistory()**](DeviceApi.md#deviceListMetersDataHistory) | **POST** /Device/ListMetersDataHistory |  |
| [**deviceListSuppliesDataHistory()**](DeviceApi.md#deviceListSuppliesDataHistory) | **POST** /Device/ListSuppliesDataHistory |  |
| [**deviceMaintenanceAlertsList()**](DeviceApi.md#deviceMaintenanceAlertsList) | **GET** /Device/MaintenanceAlerts/List | Returns a list of maintenanceAlert device. |
| [**deviceOfflineCreate()**](DeviceApi.md#deviceOfflineCreate) | **POST** /Device/Offline/Create | Returns an offline device by request parameters |
| [**deviceOfflineGet()**](DeviceApi.md#deviceOfflineGet) | **POST** /Device/Offline/Get | Returns an offline device by request parameters |
| [**deviceOfflineList()**](DeviceApi.md#deviceOfflineList) | **POST** /Device/Offline/List | This operation gets lists of offline devices paged and filtered |
| [**deviceResetWorkflowMode()**](DeviceApi.md#deviceResetWorkflowMode) | **POST** /Device/ResetWorkflowMode | This operation reset the workflow mode of a device |
| [**deviceSharpFSSCreate()**](DeviceApi.md#deviceSharpFSSCreate) | **POST** /Device/SharpFSS/Create | Returns a new SharpFSS Offline device by request parameters |
| [**deviceUpdate()**](DeviceApi.md#deviceUpdate) | **POST** /Device/Update | Update Device |
| [**deviceUpdateDeviceSerialNumber()**](DeviceApi.md#deviceUpdateDeviceSerialNumber) | **POST** /Device/UpdateDeviceSerialNumber | Update Device Serial Number |
| [**deviceUpdateDevicesBySerialNumbers()**](DeviceApi.md#deviceUpdateDevicesBySerialNumbers) | **PUT** /Device/UpdateDevicesBySerialNumbers | Update Devices By Serial Numbers |
| [**deviceUpdateSupplySetAssociation()**](DeviceApi.md#deviceUpdateSupplySetAssociation) | **POST** /Device/UpdateSupplySetAssociation | Associate the device with a project linked to a DealerSupplySet |


## `deviceAssignOfficeToDevicesByDeviceId()`

```php
deviceAssignOfficeToDevicesByDeviceId($request): \OpenAPI\Client\Model\SingleResultResponseAssignOfficeToDevicesRejectedDto
```

Assign an office to some devices by  DeviceId

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\AssignOfficeToDevicesByDeviceIdRequest(); // \OpenAPI\Client\Model\AssignOfficeToDevicesByDeviceIdRequest | The request.

try {
    $result = $apiInstance->deviceAssignOfficeToDevicesByDeviceId($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceAssignOfficeToDevicesByDeviceId: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\AssignOfficeToDevicesByDeviceIdRequest**](../Model/AssignOfficeToDevicesByDeviceIdRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAssignOfficeToDevicesRejectedDto**](../Model/SingleResultResponseAssignOfficeToDevicesRejectedDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceAssignOfficeToDevicesBySerialNumber()`

```php
deviceAssignOfficeToDevicesBySerialNumber($request): \OpenAPI\Client\Model\SingleResultResponseAssignOfficeToDevicesRejectedDto
```

Assign an office to some devices by  SerialNumber

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\AssignOfficeToDevicesBySerialNumberRequest(); // \OpenAPI\Client\Model\AssignOfficeToDevicesBySerialNumberRequest | The request.

try {
    $result = $apiInstance->deviceAssignOfficeToDevicesBySerialNumber($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceAssignOfficeToDevicesBySerialNumber: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\AssignOfficeToDevicesBySerialNumberRequest**](../Model/AssignOfficeToDevicesBySerialNumberRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAssignOfficeToDevicesRejectedDto**](../Model/SingleResultResponseAssignOfficeToDevicesRejectedDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceDelete()`

```php
deviceDelete($id): \OpenAPI\Client\Model\BaseResponse
```

This operation deletes a device

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->deviceDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceDelete: ', $e->getMessage(), PHP_EOL;
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

## `deviceDeletedList()`

```php
deviceDeletedList($page_number, $page_rows, $sort_column, $sort_order, $customer_code, $dealer_code, $filter_text): \OpenAPI\Client\Model\PagedResultResponseInstalledProductDeletedDto
```

This operation gets lists of devices paged and filtered

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$customer_code = 'customer_code_example'; // string | Gets or sets the customer code.
$dealer_code = 'dealer_code_example'; // string | Gets or sets the dealer code.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->deviceDeletedList($page_number, $page_rows, $sort_column, $sort_order, $customer_code, $dealer_code, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceDeletedList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **customer_code** | **string**| Gets or sets the customer code. | [optional] |
| **dealer_code** | **string**| Gets or sets the dealer code. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseInstalledProductDeletedDto**](../Model/PagedResultResponseInstalledProductDeletedDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceDeletedListByDealer()`

```php
deviceDeletedListByDealer($page_number, $page_rows, $sort_column, $sort_order, $dealer_code, $filter_text): \OpenAPI\Client\Model\PagedResultResponseInstalledProductDeletedDto
```

This operation gets lists of devices paged and filtered by dealer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$dealer_code = 'dealer_code_example'; // string | Gets or sets the dealer code.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->deviceDeletedListByDealer($page_number, $page_rows, $sort_column, $sort_order, $dealer_code, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceDeletedListByDealer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **dealer_code** | **string**| Gets or sets the dealer code. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseInstalledProductDeletedDto**](../Model/PagedResultResponseInstalledProductDeletedDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceDeletedRestore()`

```php
deviceDeletedRestore($request): \OpenAPI\Client\Model\BaseResponse
```

This operation restore a device

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->deviceDeletedRestore($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceDeletedRestore: ', $e->getMessage(), PHP_EOL;
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

## `deviceExplorerDataAffinitiesList()`

```php
deviceExplorerDataAffinitiesList($id): \OpenAPI\Client\Model\ListResultResponseDeviceExplorerDataAffinityDto
```

Returns a list of DeviceExplorerDataAffinities filtered by idDevice

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->deviceExplorerDataAffinitiesList($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceExplorerDataAffinitiesList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseDeviceExplorerDataAffinityDto**](../Model/ListResultResponseDeviceExplorerDataAffinityDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceGet()`

```php
deviceGet($request): \OpenAPI\Client\Model\SingleResultResponseDeviceDto
```

Returns a device by request parameters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->deviceGet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceDto**](../Model/SingleResultResponseDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceGetDetailedInformations()`

```php
deviceGetDetailedInformations($request): \OpenAPI\Client\Model\SingleResultResponseDeviceDetailsDto
```

Returns  device detailed Informations by request parameters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDeviceDetailsRequest(); // \OpenAPI\Client\Model\GetDeviceDetailsRequest

try {
    $result = $apiInstance->deviceGetDetailedInformations($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceGetDetailedInformations: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDeviceDetailsRequest**](../Model/GetDeviceDetailsRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceDetailsDto**](../Model/SingleResultResponseDeviceDetailsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceGetDeviceAdditionalInfos()`

```php
deviceGetDeviceAdditionalInfos($id): \OpenAPI\Client\Model\ListResultResponseDeviceAdditionalInfoDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->deviceGetDeviceAdditionalInfos($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceGetDeviceAdditionalInfos: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseDeviceAdditionalInfoDto**](../Model/ListResultResponseDeviceAdditionalInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceGetDeviceGapInfos()`

```php
deviceGetDeviceGapInfos($id): \OpenAPI\Client\Model\ListResultResponseDeviceGapInfoDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->deviceGetDeviceGapInfos($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceGetDeviceGapInfos: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseDeviceGapInfoDto**](../Model/ListResultResponseDeviceGapInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceGetLfpCounters()`

```php
deviceGetLfpCounters($id): \OpenAPI\Client\Model\ListResultResponseDeviceLfpCounterDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->deviceGetLfpCounters($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceGetLfpCounters: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseDeviceLfpCounterDto**](../Model/ListResultResponseDeviceLfpCounterDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceGetSuppliesDetails()`

```php
deviceGetSuppliesDetails($id): \OpenAPI\Client\Model\SingleResultResponseDeviceSuppliesDetailsDto
```

Returns a device by request parameters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->deviceGetSuppliesDetails($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceGetSuppliesDetails: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceSuppliesDetailsDto**](../Model/SingleResultResponseDeviceSuppliesDetailsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceGetSuppliesDetailsInfo()`

```php
deviceGetSuppliesDetailsInfo($id, $supply_type, $color_type, $id_maintenance_kit_type, $id_maintenance_kit_color, $id_maintenance_kit_description): \OpenAPI\Client\Model\SingleResultResponseDeviceSuppliesDetailsInfoDto
```

Gets current forecast and history consumable details for a specific device, consumable and consumable color type

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$supply_type = 'supply_type_example'; // string
$color_type = 'color_type_example'; // string
$id_maintenance_kit_type = 56; // int
$id_maintenance_kit_color = 56; // int
$id_maintenance_kit_description = 56; // int

try {
    $result = $apiInstance->deviceGetSuppliesDetailsInfo($id, $supply_type, $color_type, $id_maintenance_kit_type, $id_maintenance_kit_color, $id_maintenance_kit_description);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceGetSuppliesDetailsInfo: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **supply_type** | **string**|  | [optional] |
| **color_type** | **string**|  | [optional] |
| **id_maintenance_kit_type** | **int**|  | [optional] |
| **id_maintenance_kit_color** | **int**|  | [optional] |
| **id_maintenance_kit_description** | **int**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceSuppliesDetailsInfoDto**](../Model/SingleResultResponseDeviceSuppliesDetailsInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceGetSuppliesDetailsSummary()`

```php
deviceGetSuppliesDetailsSummary($id): \OpenAPI\Client\Model\SingleResultResponseDeviceSuppliesSummaryDto
```

Get toners and photoconductors forecast details for a specific device

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->deviceGetSuppliesDetailsSummary($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceGetSuppliesDetailsSummary: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceSuppliesSummaryDto**](../Model/SingleResultResponseDeviceSuppliesSummaryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceGetZebraSuppliesDetailsSummary()`

```php
deviceGetZebraSuppliesDetailsSummary($id): \OpenAPI\Client\Model\SingleResultResponseZebraDeviceSuppliesSummaryDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->deviceGetZebraSuppliesDetailsSummary($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceGetZebraSuppliesDetailsSummary: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseZebraDeviceSuppliesSummaryDto**](../Model/SingleResultResponseZebraDeviceSuppliesSummaryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceList()`

```php
deviceList($request): \OpenAPI\Client\Model\PagedResultResponseDeviceListDto
```

This operation gets lists of devices paged and filtered

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDevicesRequest(); // \OpenAPI\Client\Model\GetDevicesRequest

try {
    $result = $apiInstance->deviceList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDevicesRequest**](../Model/GetDevicesRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDeviceListDto**](../Model/PagedResultResponseDeviceListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceListAttributesDataHistory()`

```php
deviceListAttributesDataHistory($request): \OpenAPI\Client\Model\PagedResultResponseAttributesDataHistoryDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDeviceDataHistoryRequest(); // \OpenAPI\Client\Model\GetDeviceDataHistoryRequest

try {
    $result = $apiInstance->deviceListAttributesDataHistory($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceListAttributesDataHistory: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDeviceDataHistoryRequest**](../Model/GetDeviceDataHistoryRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseAttributesDataHistoryDto**](../Model/PagedResultResponseAttributesDataHistoryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceListErrorsMessagesDataHistory()`

```php
deviceListErrorsMessagesDataHistory($request): \OpenAPI\Client\Model\PagedResultResponseErrorsMessagesDataHistoryDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDeviceDataHistoryRequest(); // \OpenAPI\Client\Model\GetDeviceDataHistoryRequest

try {
    $result = $apiInstance->deviceListErrorsMessagesDataHistory($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceListErrorsMessagesDataHistory: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDeviceDataHistoryRequest**](../Model/GetDeviceDataHistoryRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseErrorsMessagesDataHistoryDto**](../Model/PagedResultResponseErrorsMessagesDataHistoryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceListLevelsDataHistory()`

```php
deviceListLevelsDataHistory($request): \OpenAPI\Client\Model\ListResultResponseLevelsDataHistoryDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetLevelsDataHistoryRequest(); // \OpenAPI\Client\Model\GetLevelsDataHistoryRequest

try {
    $result = $apiInstance->deviceListLevelsDataHistory($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceListLevelsDataHistory: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetLevelsDataHistoryRequest**](../Model/GetLevelsDataHistoryRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseLevelsDataHistoryDto**](../Model/ListResultResponseLevelsDataHistoryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceListMaintenanceKitMessagesDataHistory()`

```php
deviceListMaintenanceKitMessagesDataHistory($request): \OpenAPI\Client\Model\PagedResultResponseMaintenanceKitMessagesDataHistoryDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDeviceDataHistoryRequest(); // \OpenAPI\Client\Model\GetDeviceDataHistoryRequest

try {
    $result = $apiInstance->deviceListMaintenanceKitMessagesDataHistory($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceListMaintenanceKitMessagesDataHistory: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDeviceDataHistoryRequest**](../Model/GetDeviceDataHistoryRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseMaintenanceKitMessagesDataHistoryDto**](../Model/PagedResultResponseMaintenanceKitMessagesDataHistoryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceListMeterReads()`

```php
deviceListMeterReads($request): \OpenAPI\Client\Model\PagedResultResponseDeviceListMeterReadsDto
```

This operation gets lists of meter reads daily from eXplorer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDevicesMeterReadsRequest(); // \OpenAPI\Client\Model\GetDevicesMeterReadsRequest

try {
    $result = $apiInstance->deviceListMeterReads($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceListMeterReads: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDevicesMeterReadsRequest**](../Model/GetDevicesMeterReadsRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDeviceListMeterReadsDto**](../Model/PagedResultResponseDeviceListMeterReadsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceListMetersDataHistory()`

```php
deviceListMetersDataHistory($request): \OpenAPI\Client\Model\PagedResultResponseMetersDataHistoryDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDeviceDataHistoryRequest(); // \OpenAPI\Client\Model\GetDeviceDataHistoryRequest

try {
    $result = $apiInstance->deviceListMetersDataHistory($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceListMetersDataHistory: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDeviceDataHistoryRequest**](../Model/GetDeviceDataHistoryRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseMetersDataHistoryDto**](../Model/PagedResultResponseMetersDataHistoryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceListSuppliesDataHistory()`

```php
deviceListSuppliesDataHistory($request): \OpenAPI\Client\Model\PagedResultResponseSuppliesDataHistoryDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDeviceDataHistoryRequest(); // \OpenAPI\Client\Model\GetDeviceDataHistoryRequest

try {
    $result = $apiInstance->deviceListSuppliesDataHistory($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceListSuppliesDataHistory: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDeviceDataHistoryRequest**](../Model/GetDeviceDataHistoryRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSuppliesDataHistoryDto**](../Model/PagedResultResponseSuppliesDataHistoryDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceMaintenanceAlertsList()`

```php
deviceMaintenanceAlertsList($page_number, $page_rows, $sort_column, $sort_order, $id_installed_product, $filter_by_opened, $filter_by_closed, $filter_by_panel_message_alert_configuration, $filter_text): \OpenAPI\Client\Model\PagedResultResponseMaintenanceAlertDto
```

Returns a list of maintenanceAlert device.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$id_installed_product = 'id_installed_product_example'; // string | Gets or sets the installedproduct
$filter_by_opened = True; // bool | Gets or sets the FilterByOpened
$filter_by_closed = True; // bool | Gets or sets the FilterByClosed
$filter_by_panel_message_alert_configuration = True; // bool | Gets or sets the FilterByPanelMessageAlertConfiguration
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->deviceMaintenanceAlertsList($page_number, $page_rows, $sort_column, $sort_order, $id_installed_product, $filter_by_opened, $filter_by_closed, $filter_by_panel_message_alert_configuration, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceMaintenanceAlertsList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **id_installed_product** | **string**| Gets or sets the installedproduct | [optional] |
| **filter_by_opened** | **bool**| Gets or sets the FilterByOpened | [optional] |
| **filter_by_closed** | **bool**| Gets or sets the FilterByClosed | [optional] |
| **filter_by_panel_message_alert_configuration** | **bool**| Gets or sets the FilterByPanelMessageAlertConfiguration | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseMaintenanceAlertDto**](../Model/PagedResultResponseMaintenanceAlertDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceOfflineCreate()`

```php
deviceOfflineCreate($request): \OpenAPI\Client\Model\SingleResultResponseDeviceDto
```

Returns an offline device by request parameters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\OfflineDeviceRequest(); // \OpenAPI\Client\Model\OfflineDeviceRequest

try {
    $result = $apiInstance->deviceOfflineCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceOfflineCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\OfflineDeviceRequest**](../Model/OfflineDeviceRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceDto**](../Model/SingleResultResponseDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceOfflineGet()`

```php
deviceOfflineGet($request): \OpenAPI\Client\Model\SingleResultResponseDeviceDto
```

Returns an offline device by request parameters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->deviceOfflineGet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceOfflineGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceDto**](../Model/SingleResultResponseDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceOfflineList()`

```php
deviceOfflineList($request): \OpenAPI\Client\Model\PagedResultResponseDeviceListDto
```

This operation gets lists of offline devices paged and filtered

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetDevicesRequest(); // \OpenAPI\Client\Model\GetDevicesRequest

try {
    $result = $apiInstance->deviceOfflineList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceOfflineList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetDevicesRequest**](../Model/GetDevicesRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDeviceListDto**](../Model/PagedResultResponseDeviceListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceResetWorkflowMode()`

```php
deviceResetWorkflowMode($request): \OpenAPI\Client\Model\BaseResponse
```

This operation reset the workflow mode of a device

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->deviceResetWorkflowMode($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceResetWorkflowMode: ', $e->getMessage(), PHP_EOL;
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

## `deviceSharpFSSCreate()`

```php
deviceSharpFSSCreate($request): \OpenAPI\Client\Model\SingleResultResponseDeviceDto
```

Returns a new SharpFSS Offline device by request parameters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SharpFSSDeviceRequest(); // \OpenAPI\Client\Model\SharpFSSDeviceRequest

try {
    $result = $apiInstance->deviceSharpFSSCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceSharpFSSCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SharpFSSDeviceRequest**](../Model/SharpFSSDeviceRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceDto**](../Model/SingleResultResponseDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceUpdate()`

```php
deviceUpdate($request): \OpenAPI\Client\Model\SingleResultResponseDeviceDto
```

Update Device

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateDeviceRequest(); // \OpenAPI\Client\Model\UpdateDeviceRequest

try {
    $result = $apiInstance->deviceUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateDeviceRequest**](../Model/UpdateDeviceRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceDto**](../Model/SingleResultResponseDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceUpdateDeviceSerialNumber()`

```php
deviceUpdateDeviceSerialNumber($request): \OpenAPI\Client\Model\SingleResultResponseDeviceDto
```

Update Device Serial Number

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateDeviceSerialNumberRequest(); // \OpenAPI\Client\Model\UpdateDeviceSerialNumberRequest

try {
    $result = $apiInstance->deviceUpdateDeviceSerialNumber($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceUpdateDeviceSerialNumber: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateDeviceSerialNumberRequest**](../Model/UpdateDeviceSerialNumberRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDeviceDto**](../Model/SingleResultResponseDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceUpdateDevicesBySerialNumbers()`

```php
deviceUpdateDevicesBySerialNumbers($request): \OpenAPI\Client\Model\SingleResultResponseUpdateDevicesBySerialNumbersRejectedDto
```

Update Devices By Serial Numbers

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateDevicesBySerialNumbersRequest(); // \OpenAPI\Client\Model\UpdateDevicesBySerialNumbersRequest | The request.

try {
    $result = $apiInstance->deviceUpdateDevicesBySerialNumbers($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceUpdateDevicesBySerialNumbers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateDevicesBySerialNumbersRequest**](../Model/UpdateDevicesBySerialNumbersRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseUpdateDevicesBySerialNumbersRejectedDto**](../Model/SingleResultResponseUpdateDevicesBySerialNumbersRejectedDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deviceUpdateSupplySetAssociation()`

```php
deviceUpdateSupplySetAssociation($request): \OpenAPI\Client\Model\BaseResponse
```

Associate the device with a project linked to a DealerSupplySet

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DeviceApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateDeviceSupplySetAssociationRequest(); // \OpenAPI\Client\Model\UpdateDeviceSupplySetAssociationRequest

try {
    $result = $apiInstance->deviceUpdateSupplySetAssociation($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DeviceApi->deviceUpdateSupplySetAssociation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateDeviceSupplySetAssociationRequest**](../Model/UpdateDeviceSupplySetAssociationRequest.md)|  | |

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
