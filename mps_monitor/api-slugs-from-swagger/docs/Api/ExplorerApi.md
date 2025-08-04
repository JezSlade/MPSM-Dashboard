# OpenAPI\Client\ExplorerApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**explorerAbortRequestSendLogs()**](ExplorerApi.md#explorerAbortRequestSendLogs) | **PUT** /Explorer/AbortRequestSendLogs | Abort request send logs |
| [**explorerAbortUpdateAgent()**](ExplorerApi.md#explorerAbortUpdateAgent) | **PUT** /Explorer/AbortUpdateAgent | Abort Update Agent |
| [**explorerAbortUpdateDca4Client()**](ExplorerApi.md#explorerAbortUpdateDca4Client) | **PUT** /Explorer/AbortUpdateDca4Client | Abort Update Service |
| [**explorerAbortUpdateDca4Monitor()**](ExplorerApi.md#explorerAbortUpdateDca4Monitor) | **PUT** /Explorer/AbortUpdateDca4Monitor | Abort Update Agent |
| [**explorerAbortUpdateService()**](ExplorerApi.md#explorerAbortUpdateService) | **PUT** /Explorer/AbortUpdateService | Abort Update Service |
| [**explorerAgentVersions()**](ExplorerApi.md#explorerAgentVersions) | **GET** /Explorer/AgentVersions | Get Agent Versions |
| [**explorerAutomaticUpdate()**](ExplorerApi.md#explorerAutomaticUpdate) | **PUT** /Explorer/AutomaticUpdate | Explorer Enable\\Disable Automatic Update |
| [**explorerClearOidRegistry()**](ExplorerApi.md#explorerClearOidRegistry) | **POST** /Explorer/ClearOidRegistry |  |
| [**explorerClusterAddSlaveToCluster()**](ExplorerApi.md#explorerClusterAddSlaveToCluster) | **POST** /Explorer/Cluster/AddSlaveToCluster | Add a Slave to a Cluster |
| [**explorerClusterAutoClusters()**](ExplorerApi.md#explorerClusterAutoClusters) | **GET** /Explorer/Cluster/AutoClusters | This operation suggests explorer clusters from all dealer customer |
| [**explorerClusterCreate()**](ExplorerApi.md#explorerClusterCreate) | **POST** /Explorer/Cluster/Create | Create Explorer Cluster |
| [**explorerClusterDelete()**](ExplorerApi.md#explorerClusterDelete) | **POST** /Explorer/Cluster/Delete | This operation deletes an explorer cluster |
| [**explorerClusterGet()**](ExplorerApi.md#explorerClusterGet) | **GET** /Explorer/Cluster/Get | This operation gets an explorer cluster |
| [**explorerClusterList()**](ExplorerApi.md#explorerClusterList) | **GET** /Explorer/Cluster/List | This operation gets explorer clusters from all dealer customer |
| [**explorerConfigurationCopy()**](ExplorerApi.md#explorerConfigurationCopy) | **PUT** /Explorer/Configuration/Copy | Copy Explorer Configuration |
| [**explorerConfigurationCreate()**](ExplorerApi.md#explorerConfigurationCreate) | **POST** /Explorer/Configuration/Create | Update Explorer Configuration |
| [**explorerConfigurationCreateFromTicket()**](ExplorerApi.md#explorerConfigurationCreateFromTicket) | **POST** /Explorer/Configuration/CreateFromTicket | Create Explore Configuration from ticket |
| [**explorerConfigurationDelete()**](ExplorerApi.md#explorerConfigurationDelete) | **DELETE** /Explorer/Configuration/Delete | Delete Explorer Configuration |
| [**explorerConfigurationGet()**](ExplorerApi.md#explorerConfigurationGet) | **GET** /Explorer/Configuration/Get | Returns eXplorer configuration with subnets and schedules |
| [**explorerConfigurationGetTestTableVersions()**](ExplorerApi.md#explorerConfigurationGetTestTableVersions) | **GET** /Explorer/Configuration/GetTestTableVersions |  |
| [**explorerConfigurationList()**](ExplorerApi.md#explorerConfigurationList) | **GET** /Explorer/Configuration/List | Returns eXplorer Configurations |
| [**explorerConfigurationScanImmediate()**](ExplorerApi.md#explorerConfigurationScanImmediate) | **PUT** /Explorer/Configuration/ScanImmediate | Update Explorer Configuration |
| [**explorerConfigurationUpdate()**](ExplorerApi.md#explorerConfigurationUpdate) | **PUT** /Explorer/Configuration/Update | Update Explorer Configuration |
| [**explorerDataLogs()**](ExplorerApi.md#explorerDataLogs) | **GET** /Explorer/DataLogs |  |
| [**explorerDataPings()**](ExplorerApi.md#explorerDataPings) | **GET** /Explorer/DataPings |  |
| [**explorerDca4ClientVersions()**](ExplorerApi.md#explorerDca4ClientVersions) | **GET** /Explorer/Dca4ClientVersions | Get Agent Versions |
| [**explorerDca4MonitorVersions()**](ExplorerApi.md#explorerDca4MonitorVersions) | **GET** /Explorer/Dca4MonitorVersions | Get Service Versions |
| [**explorerDeleteExplorerData()**](ExplorerApi.md#explorerDeleteExplorerData) | **POST** /Explorer/DeleteExplorerData | This operation delete explorer data entry |
| [**explorerDeleteExplorerDataExplorerData()**](ExplorerApi.md#explorerDeleteExplorerDataExplorerData) | **POST** /Explorer/DeleteExplorerDataExplorerData | This operation delete explorer data entry |
| [**explorerDownloadLogs()**](ExplorerApi.md#explorerDownloadLogs) | **GET** /Explorer/DownloadLogs | This operation gets explorer data and clusters |
| [**explorerExplorerDataCommandList()**](ExplorerApi.md#explorerExplorerDataCommandList) | **GET** /Explorer/ExplorerDataCommand/List | This operation gets explorer command list |
| [**explorerExplorerDataInfoList()**](ExplorerApi.md#explorerExplorerDataInfoList) | **GET** /Explorer/ExplorerDataInfo/List | This operation gets explorer environment infos |
| [**explorerGetClusterCounters()**](ExplorerApi.md#explorerGetClusterCounters) | **GET** /Explorer/GetClusterCounters | Returns a customer&#39;s cluster counters (number of clusters, masters and slaves) |
| [**explorerGetConnectorEndpoints()**](ExplorerApi.md#explorerGetConnectorEndpoints) | **GET** /Explorer/GetConnectorEndpoints | This operations gets the required web endpoints for a specific connector or group of connectors |
| [**explorerGetConnectors()**](ExplorerApi.md#explorerGetConnectors) | **GET** /Explorer/GetConnectors | This operation gets explorer data and clusters |
| [**explorerGetDca4Otp()**](ExplorerApi.md#explorerGetDca4Otp) | **GET** /Explorer/GetDca4Otp |  |
| [**explorerGetDcaCurrentVersion()**](ExplorerApi.md#explorerGetDcaCurrentVersion) | **GET** /Explorer/GetDcaCurrentVersion | Get the release table versions of a specific DCA |
| [**explorerGetDcaReleaseNotes()**](ExplorerApi.md#explorerGetDcaReleaseNotes) | **GET** /Explorer/GetDcaReleaseNotes | Get the release table versions of a specific DCA |
| [**explorerGetEndpointsLink()**](ExplorerApi.md#explorerGetEndpointsLink) | **GET** /Explorer/GetEndpointsLink | Get Endpoints Link |
| [**explorerGetExplorerDatas()**](ExplorerApi.md#explorerGetExplorerDatas) | **GET** /Explorer/GetExplorerDatas | This operation gets explorer data from all dealer customer |
| [**explorerGetExplorerSetupLink()**](ExplorerApi.md#explorerGetExplorerSetupLink) | **GET** /Explorer/GetExplorerSetupLink | Get Explorer Setup Link |
| [**explorerGetJamcSetupLink()**](ExplorerApi.md#explorerGetJamcSetupLink) | **GET** /Explorer/GetJamcSetupLink | Get Jamc Setup Link |
| [**explorerHostnameCreate()**](ExplorerApi.md#explorerHostnameCreate) | **POST** /Explorer/Hostname/Create | Create eXplorer hostname |
| [**explorerHostnameDelete()**](ExplorerApi.md#explorerHostnameDelete) | **DELETE** /Explorer/Hostname/Delete | Delete eXplorer hostname |
| [**explorerHostnameUpdate()**](ExplorerApi.md#explorerHostnameUpdate) | **PUT** /Explorer/Hostname/Update | Update eXplorer subnet |
| [**explorerImmediateScanDca4()**](ExplorerApi.md#explorerImmediateScanDca4) | **POST** /Explorer/ImmediateScanDca4 |  |
| [**explorerIntervalsUpdate()**](ExplorerApi.md#explorerIntervalsUpdate) | **PUT** /Explorer/Intervals/Update | Updates the explorer interval. |
| [**explorerLicenseGenerate()**](ExplorerApi.md#explorerLicenseGenerate) | **POST** /Explorer/License/Generate | Generate an Explorer License |
| [**explorerLicenseList()**](ExplorerApi.md#explorerLicenseList) | **GET** /Explorer/License/List |  |
| [**explorerRequestSendLogs()**](ExplorerApi.md#explorerRequestSendLogs) | **GET** /Explorer/RequestSendLogs | This operation gets explorer data and clusters |
| [**explorerScheduleCreate()**](ExplorerApi.md#explorerScheduleCreate) | **POST** /Explorer/Schedule/Create | Create schedule on explorerconfiguration |
| [**explorerScheduleDelete()**](ExplorerApi.md#explorerScheduleDelete) | **DELETE** /Explorer/Schedule/Delete | Delete schedule on explorerconfiguration |
| [**explorerScheduleUpdate()**](ExplorerApi.md#explorerScheduleUpdate) | **POST** /Explorer/Schedule/Update | Update schedule on explorerconfiguration |
| [**explorerSendGetOrWalk()**](ExplorerApi.md#explorerSendGetOrWalk) | **POST** /Explorer/SendGetOrWalk |  |
| [**explorerSendGetOrWalkByPrinter()**](ExplorerApi.md#explorerSendGetOrWalkByPrinter) | **POST** /Explorer/SendGetOrWalkByPrinter |  |
| [**explorerSendHpLfpXml()**](ExplorerApi.md#explorerSendHpLfpXml) | **POST** /Explorer/SendHpLfpXml |  |
| [**explorerSendHpLfpXmlByPrinter()**](ExplorerApi.md#explorerSendHpLfpXmlByPrinter) | **POST** /Explorer/SendHpLfpXmlByPrinter |  |
| [**explorerSendPing()**](ExplorerApi.md#explorerSendPing) | **POST** /Explorer/SendPing |  |
| [**explorerServiceVersions()**](ExplorerApi.md#explorerServiceVersions) | **GET** /Explorer/ServiceVersions | Get Service Versions |
| [**explorerSetDcaLogLevel()**](ExplorerApi.md#explorerSetDcaLogLevel) | **PUT** /Explorer/SetDcaLogLevel | This operation set the DCA log level |
| [**explorerSetPollingService()**](ExplorerApi.md#explorerSetPollingService) | **PUT** /Explorer/SetPollingService | Abort Update Service |
| [**explorerStagingActivate()**](ExplorerApi.md#explorerStagingActivate) | **POST** /Explorer/Staging/Activate | Activate a staging connector |
| [**explorerStagingDelete()**](ExplorerApi.md#explorerStagingDelete) | **DELETE** /Explorer/Staging/Delete | Activate a staging connector |
| [**explorerStagingList()**](ExplorerApi.md#explorerStagingList) | **GET** /Explorer/Staging/List | Get the staging connector list for a customer |
| [**explorerSubnetCreate()**](ExplorerApi.md#explorerSubnetCreate) | **POST** /Explorer/Subnet/Create | Create eXplorer subnet |
| [**explorerSubnetDelete()**](ExplorerApi.md#explorerSubnetDelete) | **DELETE** /Explorer/Subnet/Delete | Delete eXplorer subnet |
| [**explorerSubnetUpdate()**](ExplorerApi.md#explorerSubnetUpdate) | **PUT** /Explorer/Subnet/Update | Update eXplorer subnet |
| [**explorerUpdateAgent()**](ExplorerApi.md#explorerUpdateAgent) | **PUT** /Explorer/UpdateAgent | Explorer Update Agent |
| [**explorerUpdateDca4Client()**](ExplorerApi.md#explorerUpdateDca4Client) | **PUT** /Explorer/UpdateDca4Client | Explorer Update Agent |
| [**explorerUpdateDca4Monitor()**](ExplorerApi.md#explorerUpdateDca4Monitor) | **PUT** /Explorer/UpdateDca4Monitor | Explorer Update Service |
| [**explorerUpdateService()**](ExplorerApi.md#explorerUpdateService) | **PUT** /Explorer/UpdateService | Explorer Update Service |
| [**explorerV3ReleaseNotes()**](ExplorerApi.md#explorerV3ReleaseNotes) | **GET** /Explorer/V3/ReleaseNotes | Get the eXplorer V3 Release Notes |
| [**explorerWorkingDaysUpdate()**](ExplorerApi.md#explorerWorkingDaysUpdate) | **POST** /Explorer/WorkingDays/Update | Update configuration working days |


## `explorerAbortRequestSendLogs()`

```php
explorerAbortRequestSendLogs($request): \OpenAPI\Client\Model\BaseResponse
```

Abort request send logs

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->explorerAbortRequestSendLogs($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerAbortRequestSendLogs: ', $e->getMessage(), PHP_EOL;
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

## `explorerAbortUpdateAgent()`

```php
explorerAbortUpdateAgent($request): \OpenAPI\Client\Model\BaseResponse
```

Abort Update Agent

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->explorerAbortUpdateAgent($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerAbortUpdateAgent: ', $e->getMessage(), PHP_EOL;
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

## `explorerAbortUpdateDca4Client()`

```php
explorerAbortUpdateDca4Client($request): \OpenAPI\Client\Model\BaseResponse
```

Abort Update Service

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->explorerAbortUpdateDca4Client($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerAbortUpdateDca4Client: ', $e->getMessage(), PHP_EOL;
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

## `explorerAbortUpdateDca4Monitor()`

```php
explorerAbortUpdateDca4Monitor($request): \OpenAPI\Client\Model\BaseResponse
```

Abort Update Agent

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->explorerAbortUpdateDca4Monitor($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerAbortUpdateDca4Monitor: ', $e->getMessage(), PHP_EOL;
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

## `explorerAbortUpdateService()`

```php
explorerAbortUpdateService($request): \OpenAPI\Client\Model\BaseResponse
```

Abort Update Service

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->explorerAbortUpdateService($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerAbortUpdateService: ', $e->getMessage(), PHP_EOL;
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

## `explorerAgentVersions()`

```php
explorerAgentVersions($id): \OpenAPI\Client\Model\ListResultResponseIdDescDto
```

Get Agent Versions

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerAgentVersions($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerAgentVersions: ', $e->getMessage(), PHP_EOL;
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

## `explorerAutomaticUpdate()`

```php
explorerAutomaticUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Explorer Enable\\Disable Automatic Update

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\AutomaticUpdateRequest(); // \OpenAPI\Client\Model\AutomaticUpdateRequest

try {
    $result = $apiInstance->explorerAutomaticUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerAutomaticUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\AutomaticUpdateRequest**](../Model/AutomaticUpdateRequest.md)|  | |

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

## `explorerClearOidRegistry()`

```php
explorerClearOidRegistry($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->explorerClearOidRegistry($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerClearOidRegistry: ', $e->getMessage(), PHP_EOL;
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

## `explorerClusterAddSlaveToCluster()`

```php
explorerClusterAddSlaveToCluster($request): \OpenAPI\Client\Model\BaseResponse
```

Add a Slave to a Cluster

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestExplorerClusterDto(); // \OpenAPI\Client\Model\CreateRequestExplorerClusterDto

try {
    $result = $apiInstance->explorerClusterAddSlaveToCluster($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerClusterAddSlaveToCluster: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestExplorerClusterDto**](../Model/CreateRequestExplorerClusterDto.md)|  | |

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

## `explorerClusterAutoClusters()`

```php
explorerClusterAutoClusters($page_number, $page_rows, $sort_column, $sort_order, $list_type, $filter_dealer_codes, $filter_customer_codes, $search_key, $s_ds_only, $is_clustered, $is_v4, $is_embedded, $communication_status, $filter_customer_text, $filter_dealer_id, $filter_customer_id, $has_configuration): \OpenAPI\Client\Model\PagedResultResponseExplorerClusterDto
```

This operation suggests explorer clusters from all dealer customer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$list_type = 'list_type_example'; // string | Gets or sets the type of the list.
$filter_dealer_codes = array('filter_dealer_codes_example'); // string[] | Gets or sets the filter dealer codes.
$filter_customer_codes = array('filter_customer_codes_example'); // string[] | Gets or sets the filter customer codes.
$search_key = 'search_key_example'; // string | Gets or sets the search key.
$s_ds_only = True; // bool | Gets or sets the filter jam.
$is_clustered = True; // bool | True to return only clustered eXplorer datas,              False to return only unclustered eXplorer datas
$is_v4 = True; // bool | True to return only V4 eXplorer datas,              False to return only non V4 eXplorer datas
$is_embedded = True; // bool | Gets or sets the is embedded.
$communication_status = 'communication_status_example'; // string | Gets or sets the communication status.
$filter_customer_text = 'filter_customer_text_example'; // string | Gets or sets the filter customer text.
$filter_dealer_id = 'filter_dealer_id_example'; // string | Gets or sets the filter dealer id.
$filter_customer_id = 'filter_customer_id_example'; // string | Gets or sets the filter customer id.
$has_configuration = True; // bool | Gets or sets the filter customer id.

try {
    $result = $apiInstance->explorerClusterAutoClusters($page_number, $page_rows, $sort_column, $sort_order, $list_type, $filter_dealer_codes, $filter_customer_codes, $search_key, $s_ds_only, $is_clustered, $is_v4, $is_embedded, $communication_status, $filter_customer_text, $filter_dealer_id, $filter_customer_id, $has_configuration);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerClusterAutoClusters: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **list_type** | **string**| Gets or sets the type of the list. | [optional] |
| **filter_dealer_codes** | [**string[]**](../Model/string.md)| Gets or sets the filter dealer codes. | [optional] |
| **filter_customer_codes** | [**string[]**](../Model/string.md)| Gets or sets the filter customer codes. | [optional] |
| **search_key** | **string**| Gets or sets the search key. | [optional] |
| **s_ds_only** | **bool**| Gets or sets the filter jam. | [optional] |
| **is_clustered** | **bool**| True to return only clustered eXplorer datas,              False to return only unclustered eXplorer datas | [optional] |
| **is_v4** | **bool**| True to return only V4 eXplorer datas,              False to return only non V4 eXplorer datas | [optional] |
| **is_embedded** | **bool**| Gets or sets the is embedded. | [optional] |
| **communication_status** | **string**| Gets or sets the communication status. | [optional] |
| **filter_customer_text** | **string**| Gets or sets the filter customer text. | [optional] |
| **filter_dealer_id** | **string**| Gets or sets the filter dealer id. | [optional] |
| **filter_customer_id** | **string**| Gets or sets the filter customer id. | [optional] |
| **has_configuration** | **bool**| Gets or sets the filter customer id. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseExplorerClusterDto**](../Model/PagedResultResponseExplorerClusterDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerClusterCreate()`

```php
explorerClusterCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Create Explorer Cluster

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestExplorerClusterDto(); // \OpenAPI\Client\Model\CreateRequestExplorerClusterDto

try {
    $result = $apiInstance->explorerClusterCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerClusterCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestExplorerClusterDto**](../Model/CreateRequestExplorerClusterDto.md)|  | |

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

## `explorerClusterDelete()`

```php
explorerClusterDelete($request): \OpenAPI\Client\Model\BaseResponse
```

This operation deletes an explorer cluster

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DeleteRequest(); // \OpenAPI\Client\Model\DeleteRequest

try {
    $result = $apiInstance->explorerClusterDelete($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerClusterDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DeleteRequest**](../Model/DeleteRequest.md)|  | |

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

## `explorerClusterGet()`

```php
explorerClusterGet($id): \OpenAPI\Client\Model\SingleResultResponseExplorerClusterDto
```

This operation gets an explorer cluster

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerClusterGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerClusterGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseExplorerClusterDto**](../Model/SingleResultResponseExplorerClusterDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerClusterList()`

```php
explorerClusterList($page_number, $page_rows, $sort_column, $sort_order, $filter_dealer_codes, $filter_customer_codes, $filter_text): \OpenAPI\Client\Model\PagedResultResponseExplorerClusterDto
```

This operation gets explorer clusters from all dealer customer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$filter_dealer_codes = array('filter_dealer_codes_example'); // string[] | Gets or sets the filter dealer codes.
$filter_customer_codes = array('filter_customer_codes_example'); // string[] | Gets or sets the filter customer codes.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->explorerClusterList($page_number, $page_rows, $sort_column, $sort_order, $filter_dealer_codes, $filter_customer_codes, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerClusterList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **filter_dealer_codes** | [**string[]**](../Model/string.md)| Gets or sets the filter dealer codes. | [optional] |
| **filter_customer_codes** | [**string[]**](../Model/string.md)| Gets or sets the filter customer codes. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseExplorerClusterDto**](../Model/PagedResultResponseExplorerClusterDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerConfigurationCopy()`

```php
explorerConfigurationCopy($request): \OpenAPI\Client\Model\BaseResponse
```

Copy Explorer Configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CopyExplorerConfigurationRequest(); // \OpenAPI\Client\Model\CopyExplorerConfigurationRequest

try {
    $result = $apiInstance->explorerConfigurationCopy($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerConfigurationCopy: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CopyExplorerConfigurationRequest**](../Model/CopyExplorerConfigurationRequest.md)|  | |

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

## `explorerConfigurationCreate()`

```php
explorerConfigurationCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Update Explorer Configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestExplorerConfigurationDto(); // \OpenAPI\Client\Model\CreateRequestExplorerConfigurationDto

try {
    $result = $apiInstance->explorerConfigurationCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerConfigurationCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestExplorerConfigurationDto**](../Model/CreateRequestExplorerConfigurationDto.md)|  | |

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

## `explorerConfigurationCreateFromTicket()`

```php
explorerConfigurationCreateFromTicket($request): \OpenAPI\Client\Model\BaseResponse
```

Create Explore Configuration from ticket

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestTicketExplorerConfigurationDto(); // \OpenAPI\Client\Model\CreateRequestTicketExplorerConfigurationDto

try {
    $result = $apiInstance->explorerConfigurationCreateFromTicket($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerConfigurationCreateFromTicket: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestTicketExplorerConfigurationDto**](../Model/CreateRequestTicketExplorerConfigurationDto.md)|  | |

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

## `explorerConfigurationDelete()`

```php
explorerConfigurationDelete($id, $customer_id): \OpenAPI\Client\Model\BaseResponse
```

Delete Explorer Configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$customer_id = 'customer_id_example'; // string | CustomerCode

try {
    $result = $apiInstance->explorerConfigurationDelete($id, $customer_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerConfigurationDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **customer_id** | **string**| CustomerCode | [optional] |

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

## `explorerConfigurationGet()`

```php
explorerConfigurationGet($customer_code, $configuration_id): \OpenAPI\Client\Model\SingleResultResponseExplorerConfigurationDto
```

Returns eXplorer configuration with subnets and schedules

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | CustomerCode
$configuration_id = 'configuration_id_example'; // string | ConfigurationCode

try {
    $result = $apiInstance->explorerConfigurationGet($customer_code, $configuration_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerConfigurationGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| CustomerCode | |
| **configuration_id** | **string**| ConfigurationCode | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseExplorerConfigurationDto**](../Model/SingleResultResponseExplorerConfigurationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerConfigurationGetTestTableVersions()`

```php
explorerConfigurationGetTestTableVersions($id): \OpenAPI\Client\Model\ListResultResponseCodeDesc
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerConfigurationGetTestTableVersions($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerConfigurationGetTestTableVersions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseCodeDesc**](../Model/ListResultResponseCodeDesc.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerConfigurationList()`

```php
explorerConfigurationList($customer_code, $page_number, $page_rows, $sort_column, $sort_order, $explorer_data_identifier): \OpenAPI\Client\Model\PagedResultResponseExplorerConfigurationBaseDto
```

Returns eXplorer Configurations

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Gets or sets the customer code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$explorer_data_identifier = 'explorer_data_identifier_example'; // string | Gets or sets the explorer data identifier.

try {
    $result = $apiInstance->explorerConfigurationList($customer_code, $page_number, $page_rows, $sort_column, $sort_order, $explorer_data_identifier);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerConfigurationList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| Gets or sets the customer code. | |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **explorer_data_identifier** | **string**| Gets or sets the explorer data identifier. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseExplorerConfigurationBaseDto**](../Model/PagedResultResponseExplorerConfigurationBaseDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerConfigurationScanImmediate()`

```php
explorerConfigurationScanImmediate($request): \OpenAPI\Client\Model\BaseResponse
```

Update Explorer Configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateImmediateScheduleRequest(); // \OpenAPI\Client\Model\CreateImmediateScheduleRequest

try {
    $result = $apiInstance->explorerConfigurationScanImmediate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerConfigurationScanImmediate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateImmediateScheduleRequest**](../Model/CreateImmediateScheduleRequest.md)|  | |

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

## `explorerConfigurationUpdate()`

```php
explorerConfigurationUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Update Explorer Configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestExplorerConfigurationDto(); // \OpenAPI\Client\Model\UpdateRequestExplorerConfigurationDto

try {
    $result = $apiInstance->explorerConfigurationUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerConfigurationUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestExplorerConfigurationDto**](../Model/UpdateRequestExplorerConfigurationDto.md)|  | |

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

## `explorerDataLogs()`

```php
explorerDataLogs($id): \OpenAPI\Client\Model\ListResultResponseDataLogDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerDataLogs($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerDataLogs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseDataLogDto**](../Model/ListResultResponseDataLogDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerDataPings()`

```php
explorerDataPings($id): \OpenAPI\Client\Model\ListResultResponseDataPingDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerDataPings($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerDataPings: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseDataPingDto**](../Model/ListResultResponseDataPingDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerDca4ClientVersions()`

```php
explorerDca4ClientVersions($id): \OpenAPI\Client\Model\ListResultResponseIdDescDto
```

Get Agent Versions

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerDca4ClientVersions($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerDca4ClientVersions: ', $e->getMessage(), PHP_EOL;
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

## `explorerDca4MonitorVersions()`

```php
explorerDca4MonitorVersions($id): \OpenAPI\Client\Model\ListResultResponseIdDescDto
```

Get Service Versions

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerDca4MonitorVersions($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerDca4MonitorVersions: ', $e->getMessage(), PHP_EOL;
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

## `explorerDeleteExplorerData()`

```php
explorerDeleteExplorerData($request): \OpenAPI\Client\Model\BaseResponse
```

This operation delete explorer data entry

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DeleteRequestExplorerDataDto(); // \OpenAPI\Client\Model\DeleteRequestExplorerDataDto

try {
    $result = $apiInstance->explorerDeleteExplorerData($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerDeleteExplorerData: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DeleteRequestExplorerDataDto**](../Model/DeleteRequestExplorerDataDto.md)|  | |

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

## `explorerDeleteExplorerDataExplorerData()`

```php
explorerDeleteExplorerDataExplorerData($request): \OpenAPI\Client\Model\BaseResponse
```

This operation delete explorer data entry

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DeleteRequestExplorerDataDto(); // \OpenAPI\Client\Model\DeleteRequestExplorerDataDto

try {
    $result = $apiInstance->explorerDeleteExplorerDataExplorerData($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerDeleteExplorerDataExplorerData: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DeleteRequestExplorerDataDto**](../Model/DeleteRequestExplorerDataDto.md)|  | |

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

## `explorerDownloadLogs()`

```php
explorerDownloadLogs($id): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

This operation gets explorer data and clusters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerDownloadLogs($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerDownloadLogs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseFileInfoDto**](../Model/SingleResultResponseFileInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerExplorerDataCommandList()`

```php
explorerExplorerDataCommandList($customer_code, $id, $page_number, $page_rows, $sort_column, $sort_order, $filter_text): \OpenAPI\Client\Model\PagedResultResponseExplorerDataCommandDto
```

This operation gets explorer command list

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Gets or sets the CustomerCode.
$id = 'id_example'; // string | Gets or sets the identifier.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->explorerExplorerDataCommandList($customer_code, $id, $page_number, $page_rows, $sort_column, $sort_order, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerExplorerDataCommandList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| Gets or sets the CustomerCode. | |
| **id** | **string**| Gets or sets the identifier. | |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseExplorerDataCommandDto**](../Model/PagedResultResponseExplorerDataCommandDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerExplorerDataInfoList()`

```php
explorerExplorerDataInfoList($customer_code, $id): \OpenAPI\Client\Model\PagedResultResponseExplorerDataInfoDto
```

This operation gets explorer environment infos

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Gets or sets the CustomerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerExplorerDataInfoList($customer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerExplorerDataInfoList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| Gets or sets the CustomerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseExplorerDataInfoDto**](../Model/PagedResultResponseExplorerDataInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerGetClusterCounters()`

```php
explorerGetClusterCounters($code): \OpenAPI\Client\Model\SingleResultResponseClusteringCountersDto
```

Returns a customer's cluster counters (number of clusters, masters and slaves)

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->explorerGetClusterCounters($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerGetClusterCounters: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseClusteringCountersDto**](../Model/SingleResultResponseClusteringCountersDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerGetConnectorEndpoints()`

```php
explorerGetConnectorEndpoints($code): \OpenAPI\Client\Model\ListResultResponseConnectorEndpointDto
```

This operations gets the required web endpoints for a specific connector or group of connectors

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->explorerGetConnectorEndpoints($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerGetConnectorEndpoints: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseConnectorEndpointDto**](../Model/ListResultResponseConnectorEndpointDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerGetConnectors()`

```php
explorerGetConnectors($page_number, $page_rows, $sort_column, $sort_order, $list_type, $filter_dealer_codes, $filter_customer_codes, $search_key, $s_ds_only, $is_clustered, $is_v4, $is_embedded, $communication_status, $filter_customer_text, $filter_dealer_id, $filter_customer_id, $has_configuration): \OpenAPI\Client\Model\PagedResultResponseExplorerDataDto
```

This operation gets explorer data and clusters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$list_type = 'list_type_example'; // string | Gets or sets the type of the list.
$filter_dealer_codes = array('filter_dealer_codes_example'); // string[] | Gets or sets the filter dealer codes.
$filter_customer_codes = array('filter_customer_codes_example'); // string[] | Gets or sets the filter customer codes.
$search_key = 'search_key_example'; // string | Gets or sets the search key.
$s_ds_only = True; // bool | Gets or sets the filter jam.
$is_clustered = True; // bool | True to return only clustered eXplorer datas,              False to return only unclustered eXplorer datas
$is_v4 = True; // bool | True to return only V4 eXplorer datas,              False to return only non V4 eXplorer datas
$is_embedded = True; // bool | Gets or sets the is embedded.
$communication_status = 'communication_status_example'; // string | Gets or sets the communication status.
$filter_customer_text = 'filter_customer_text_example'; // string | Gets or sets the filter customer text.
$filter_dealer_id = 'filter_dealer_id_example'; // string | Gets or sets the filter dealer id.
$filter_customer_id = 'filter_customer_id_example'; // string | Gets or sets the filter customer id.
$has_configuration = True; // bool | Gets or sets the filter customer id.

try {
    $result = $apiInstance->explorerGetConnectors($page_number, $page_rows, $sort_column, $sort_order, $list_type, $filter_dealer_codes, $filter_customer_codes, $search_key, $s_ds_only, $is_clustered, $is_v4, $is_embedded, $communication_status, $filter_customer_text, $filter_dealer_id, $filter_customer_id, $has_configuration);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerGetConnectors: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **list_type** | **string**| Gets or sets the type of the list. | [optional] |
| **filter_dealer_codes** | [**string[]**](../Model/string.md)| Gets or sets the filter dealer codes. | [optional] |
| **filter_customer_codes** | [**string[]**](../Model/string.md)| Gets or sets the filter customer codes. | [optional] |
| **search_key** | **string**| Gets or sets the search key. | [optional] |
| **s_ds_only** | **bool**| Gets or sets the filter jam. | [optional] |
| **is_clustered** | **bool**| True to return only clustered eXplorer datas,              False to return only unclustered eXplorer datas | [optional] |
| **is_v4** | **bool**| True to return only V4 eXplorer datas,              False to return only non V4 eXplorer datas | [optional] |
| **is_embedded** | **bool**| Gets or sets the is embedded. | [optional] |
| **communication_status** | **string**| Gets or sets the communication status. | [optional] |
| **filter_customer_text** | **string**| Gets or sets the filter customer text. | [optional] |
| **filter_dealer_id** | **string**| Gets or sets the filter dealer id. | [optional] |
| **filter_customer_id** | **string**| Gets or sets the filter customer id. | [optional] |
| **has_configuration** | **bool**| Gets or sets the filter customer id. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseExplorerDataDto**](../Model/PagedResultResponseExplorerDataDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerGetDca4Otp()`

```php
explorerGetDca4Otp($id): \OpenAPI\Client\Model\SingleResultResponseDca4OtpDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerGetDca4Otp($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerGetDca4Otp: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDca4OtpDto**](../Model/SingleResultResponseDca4OtpDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerGetDcaCurrentVersion()`

```php
explorerGetDcaCurrentVersion($code): \OpenAPI\Client\Model\BaseResponse
```

Get the release table versions of a specific DCA

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->explorerGetDcaCurrentVersion($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerGetDcaCurrentVersion: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

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

## `explorerGetDcaReleaseNotes()`

```php
explorerGetDcaReleaseNotes($code): \OpenAPI\Client\Model\ListResultResponseDcaReleaseNoteDto
```

Get the release table versions of a specific DCA

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->explorerGetDcaReleaseNotes($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerGetDcaReleaseNotes: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseDcaReleaseNoteDto**](../Model/ListResultResponseDcaReleaseNoteDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerGetEndpointsLink()`

```php
explorerGetEndpointsLink($platform, $dealer_code, $language): \OpenAPI\Client\Model\BaseResponse
```

Get Endpoints Link

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$platform = 'platform_example'; // string | Gets or sets the platform.
$dealer_code = 'dealer_code_example'; // string | Gets or sets the dealer code.
$language = 'language_example'; // string | Gets or sets the language.

try {
    $result = $apiInstance->explorerGetEndpointsLink($platform, $dealer_code, $language);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerGetEndpointsLink: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **platform** | **string**| Gets or sets the platform. | |
| **dealer_code** | **string**| Gets or sets the dealer code. | [optional] |
| **language** | **string**| Gets or sets the language. | [optional] |

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

## `explorerGetExplorerDatas()`

```php
explorerGetExplorerDatas($page_number, $page_rows, $sort_column, $sort_order, $list_type, $filter_dealer_codes, $filter_customer_codes, $search_key, $s_ds_only, $is_clustered, $is_v4, $is_embedded, $communication_status, $filter_customer_text, $filter_dealer_id, $filter_customer_id, $has_configuration): \OpenAPI\Client\Model\PagedResultResponseExplorerDataDto
```

This operation gets explorer data from all dealer customer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$list_type = 'list_type_example'; // string | Gets or sets the type of the list.
$filter_dealer_codes = array('filter_dealer_codes_example'); // string[] | Gets or sets the filter dealer codes.
$filter_customer_codes = array('filter_customer_codes_example'); // string[] | Gets or sets the filter customer codes.
$search_key = 'search_key_example'; // string | Gets or sets the search key.
$s_ds_only = True; // bool | Gets or sets the filter jam.
$is_clustered = True; // bool | True to return only clustered eXplorer datas,              False to return only unclustered eXplorer datas
$is_v4 = True; // bool | True to return only V4 eXplorer datas,              False to return only non V4 eXplorer datas
$is_embedded = True; // bool | Gets or sets the is embedded.
$communication_status = 'communication_status_example'; // string | Gets or sets the communication status.
$filter_customer_text = 'filter_customer_text_example'; // string | Gets or sets the filter customer text.
$filter_dealer_id = 'filter_dealer_id_example'; // string | Gets or sets the filter dealer id.
$filter_customer_id = 'filter_customer_id_example'; // string | Gets or sets the filter customer id.
$has_configuration = True; // bool | Gets or sets the filter customer id.

try {
    $result = $apiInstance->explorerGetExplorerDatas($page_number, $page_rows, $sort_column, $sort_order, $list_type, $filter_dealer_codes, $filter_customer_codes, $search_key, $s_ds_only, $is_clustered, $is_v4, $is_embedded, $communication_status, $filter_customer_text, $filter_dealer_id, $filter_customer_id, $has_configuration);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerGetExplorerDatas: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **list_type** | **string**| Gets or sets the type of the list. | [optional] |
| **filter_dealer_codes** | [**string[]**](../Model/string.md)| Gets or sets the filter dealer codes. | [optional] |
| **filter_customer_codes** | [**string[]**](../Model/string.md)| Gets or sets the filter customer codes. | [optional] |
| **search_key** | **string**| Gets or sets the search key. | [optional] |
| **s_ds_only** | **bool**| Gets or sets the filter jam. | [optional] |
| **is_clustered** | **bool**| True to return only clustered eXplorer datas,              False to return only unclustered eXplorer datas | [optional] |
| **is_v4** | **bool**| True to return only V4 eXplorer datas,              False to return only non V4 eXplorer datas | [optional] |
| **is_embedded** | **bool**| Gets or sets the is embedded. | [optional] |
| **communication_status** | **string**| Gets or sets the communication status. | [optional] |
| **filter_customer_text** | **string**| Gets or sets the filter customer text. | [optional] |
| **filter_dealer_id** | **string**| Gets or sets the filter dealer id. | [optional] |
| **filter_customer_id** | **string**| Gets or sets the filter customer id. | [optional] |
| **has_configuration** | **bool**| Gets or sets the filter customer id. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseExplorerDataDto**](../Model/PagedResultResponseExplorerDataDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerGetExplorerSetupLink()`

```php
explorerGetExplorerSetupLink($customer_code, $code, $language, $is_dca_v4, $platform): \OpenAPI\Client\Model\BaseResponse
```

Get Explorer Setup Link

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Gets or sets the customer code.
$code = 'code_example'; // string | Gets or sets the code.
$language = 'language_example'; // string | Gets or sets the language.
$is_dca_v4 = True; // bool | Gets or sets a value indicating whether this instance is dca v4.
$platform = 'platform_example'; // string | Gets or sets the platform.

try {
    $result = $apiInstance->explorerGetExplorerSetupLink($customer_code, $code, $language, $is_dca_v4, $platform);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerGetExplorerSetupLink: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| Gets or sets the customer code. | |
| **code** | **string**| Gets or sets the code. | |
| **language** | **string**| Gets or sets the language. | [optional] |
| **is_dca_v4** | **bool**| Gets or sets a value indicating whether this instance is dca v4. | [optional] |
| **platform** | **string**| Gets or sets the platform. | [optional] |

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

## `explorerGetJamcSetupLink()`

```php
explorerGetJamcSetupLink($customer_code, $code, $language, $is_dca_v4, $platform): \OpenAPI\Client\Model\BaseResponse
```

Get Jamc Setup Link

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Gets or sets the customer code.
$code = 'code_example'; // string | Gets or sets the code.
$language = 'language_example'; // string | Gets or sets the language.
$is_dca_v4 = True; // bool | Gets or sets a value indicating whether this instance is dca v4.
$platform = 'platform_example'; // string | Gets or sets the platform.

try {
    $result = $apiInstance->explorerGetJamcSetupLink($customer_code, $code, $language, $is_dca_v4, $platform);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerGetJamcSetupLink: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| Gets or sets the customer code. | |
| **code** | **string**| Gets or sets the code. | |
| **language** | **string**| Gets or sets the language. | [optional] |
| **is_dca_v4** | **bool**| Gets or sets a value indicating whether this instance is dca v4. | [optional] |
| **platform** | **string**| Gets or sets the platform. | [optional] |

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

## `explorerHostnameCreate()`

```php
explorerHostnameCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Create eXplorer hostname

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateExplorerHostnameRequest(); // \OpenAPI\Client\Model\CreateExplorerHostnameRequest

try {
    $result = $apiInstance->explorerHostnameCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerHostnameCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateExplorerHostnameRequest**](../Model/CreateExplorerHostnameRequest.md)|  | |

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

## `explorerHostnameDelete()`

```php
explorerHostnameDelete($id, $customer_id, $explorer_configuration_id): \OpenAPI\Client\Model\BaseResponse
```

Delete eXplorer hostname

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$customer_id = 'customer_id_example'; // string | CustomerCode
$explorer_configuration_id = 'explorer_configuration_id_example'; // string | ConfigurationCode

try {
    $result = $apiInstance->explorerHostnameDelete($id, $customer_id, $explorer_configuration_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerHostnameDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **customer_id** | **string**| CustomerCode | [optional] |
| **explorer_configuration_id** | **string**| ConfigurationCode | [optional] |

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

## `explorerHostnameUpdate()`

```php
explorerHostnameUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Update eXplorer subnet

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateExplorerHostnameRequest(); // \OpenAPI\Client\Model\UpdateExplorerHostnameRequest

try {
    $result = $apiInstance->explorerHostnameUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerHostnameUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateExplorerHostnameRequest**](../Model/UpdateExplorerHostnameRequest.md)|  | |

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

## `explorerImmediateScanDca4()`

```php
explorerImmediateScanDca4($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\ImmediateScanDca4Request(); // \OpenAPI\Client\Model\ImmediateScanDca4Request

try {
    $result = $apiInstance->explorerImmediateScanDca4($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerImmediateScanDca4: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\ImmediateScanDca4Request**](../Model/ImmediateScanDca4Request.md)|  | |

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

## `explorerIntervalsUpdate()`

```php
explorerIntervalsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Updates the explorer interval.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateExplorerIntervalRequest(); // \OpenAPI\Client\Model\UpdateExplorerIntervalRequest | The request.

try {
    $result = $apiInstance->explorerIntervalsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerIntervalsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateExplorerIntervalRequest**](../Model/UpdateExplorerIntervalRequest.md)| The request. | |

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

## `explorerLicenseGenerate()`

```php
explorerLicenseGenerate($request): \OpenAPI\Client\Model\BaseResponse
```

Generate an Explorer License

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GenerateExplorerDataLicense(); // \OpenAPI\Client\Model\GenerateExplorerDataLicense

try {
    $result = $apiInstance->explorerLicenseGenerate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerLicenseGenerate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GenerateExplorerDataLicense**](../Model/GenerateExplorerDataLicense.md)|  | |

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

## `explorerLicenseList()`

```php
explorerLicenseList($customer_code, $page_number, $page_rows, $sort_column, $sort_order): \OpenAPI\Client\Model\PagedResultResponseExplorerDataLicenseDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Defines the customer code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.

try {
    $result = $apiInstance->explorerLicenseList($customer_code, $page_number, $page_rows, $sort_column, $sort_order);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerLicenseList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| Defines the customer code. | |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseExplorerDataLicenseDto**](../Model/PagedResultResponseExplorerDataLicenseDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerRequestSendLogs()`

```php
explorerRequestSendLogs($id, $send_log_full, $send_check_urls): \OpenAPI\Client\Model\SingleResultResponseBaseResponse
```

This operation gets explorer data and clusters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$send_log_full = True; // bool
$send_check_urls = True; // bool

try {
    $result = $apiInstance->explorerRequestSendLogs($id, $send_log_full, $send_check_urls);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerRequestSendLogs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **send_log_full** | **bool**|  | [optional] |
| **send_check_urls** | **bool**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseBaseResponse**](../Model/SingleResultResponseBaseResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerScheduleCreate()`

```php
explorerScheduleCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Create schedule on explorerconfiguration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateExplorerScheduleRequest(); // \OpenAPI\Client\Model\CreateExplorerScheduleRequest

try {
    $result = $apiInstance->explorerScheduleCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerScheduleCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateExplorerScheduleRequest**](../Model/CreateExplorerScheduleRequest.md)|  | |

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

## `explorerScheduleDelete()`

```php
explorerScheduleDelete($id, $customer_id, $explorer_configuration_id): \OpenAPI\Client\Model\BaseResponse
```

Delete schedule on explorerconfiguration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$customer_id = 'customer_id_example'; // string | CustomerCode
$explorer_configuration_id = 'explorer_configuration_id_example'; // string | ConfigurationCode

try {
    $result = $apiInstance->explorerScheduleDelete($id, $customer_id, $explorer_configuration_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerScheduleDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **customer_id** | **string**| CustomerCode | [optional] |
| **explorer_configuration_id** | **string**| ConfigurationCode | [optional] |

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

## `explorerScheduleUpdate()`

```php
explorerScheduleUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Update schedule on explorerconfiguration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateExplorerScheduleRequest(); // \OpenAPI\Client\Model\UpdateExplorerScheduleRequest

try {
    $result = $apiInstance->explorerScheduleUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerScheduleUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateExplorerScheduleRequest**](../Model/UpdateExplorerScheduleRequest.md)|  | |

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

## `explorerSendGetOrWalk()`

```php
explorerSendGetOrWalk($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SendGetOrWalkRequest(); // \OpenAPI\Client\Model\SendGetOrWalkRequest

try {
    $result = $apiInstance->explorerSendGetOrWalk($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerSendGetOrWalk: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SendGetOrWalkRequest**](../Model/SendGetOrWalkRequest.md)|  | |

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

## `explorerSendGetOrWalkByPrinter()`

```php
explorerSendGetOrWalkByPrinter($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SendGetOrWalkByPrinterRequest(); // \OpenAPI\Client\Model\SendGetOrWalkByPrinterRequest

try {
    $result = $apiInstance->explorerSendGetOrWalkByPrinter($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerSendGetOrWalkByPrinter: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SendGetOrWalkByPrinterRequest**](../Model/SendGetOrWalkByPrinterRequest.md)|  | |

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

## `explorerSendHpLfpXml()`

```php
explorerSendHpLfpXml($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SendHpLfpXmlRequest(); // \OpenAPI\Client\Model\SendHpLfpXmlRequest

try {
    $result = $apiInstance->explorerSendHpLfpXml($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerSendHpLfpXml: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SendHpLfpXmlRequest**](../Model/SendHpLfpXmlRequest.md)|  | |

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

## `explorerSendHpLfpXmlByPrinter()`

```php
explorerSendHpLfpXmlByPrinter($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SendHpLfpXmlByPrinterRequest(); // \OpenAPI\Client\Model\SendHpLfpXmlByPrinterRequest

try {
    $result = $apiInstance->explorerSendHpLfpXmlByPrinter($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerSendHpLfpXmlByPrinter: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SendHpLfpXmlByPrinterRequest**](../Model/SendHpLfpXmlByPrinterRequest.md)|  | |

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

## `explorerSendPing()`

```php
explorerSendPing($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SendPingRequest(); // \OpenAPI\Client\Model\SendPingRequest

try {
    $result = $apiInstance->explorerSendPing($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerSendPing: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SendPingRequest**](../Model/SendPingRequest.md)|  | |

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

## `explorerServiceVersions()`

```php
explorerServiceVersions($id): \OpenAPI\Client\Model\ListResultResponseIdDescDto
```

Get Service Versions

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerServiceVersions($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerServiceVersions: ', $e->getMessage(), PHP_EOL;
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

## `explorerSetDcaLogLevel()`

```php
explorerSetDcaLogLevel($request): \OpenAPI\Client\Model\SingleResultResponseBaseResponse
```

This operation set the DCA log level

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetDcaLogLevelRequest(); // \OpenAPI\Client\Model\SetDcaLogLevelRequest

try {
    $result = $apiInstance->explorerSetDcaLogLevel($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerSetDcaLogLevel: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetDcaLogLevelRequest**](../Model/SetDcaLogLevelRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseBaseResponse**](../Model/SingleResultResponseBaseResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerSetPollingService()`

```php
explorerSetPollingService($request): \OpenAPI\Client\Model\BaseResponse
```

Abort Update Service

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetPollingServiceRequest(); // \OpenAPI\Client\Model\SetPollingServiceRequest

try {
    $result = $apiInstance->explorerSetPollingService($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerSetPollingService: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetPollingServiceRequest**](../Model/SetPollingServiceRequest.md)|  | |

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

## `explorerStagingActivate()`

```php
explorerStagingActivate($request): \OpenAPI\Client\Model\BaseResponse
```

Activate a staging connector

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdAndCustomerCodeRequest(); // \OpenAPI\Client\Model\GetByIdAndCustomerCodeRequest

try {
    $result = $apiInstance->explorerStagingActivate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerStagingActivate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdAndCustomerCodeRequest**](../Model/GetByIdAndCustomerCodeRequest.md)|  | |

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

## `explorerStagingDelete()`

```php
explorerStagingDelete($customer_code, $id): \OpenAPI\Client\Model\BaseResponse
```

Activate a staging connector

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Gets or sets the CustomerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->explorerStagingDelete($customer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerStagingDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| Gets or sets the CustomerCode. | |
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

## `explorerStagingList()`

```php
explorerStagingList($code): \OpenAPI\Client\Model\ListResultResponseExplorerDataStagingDto
```

Get the staging connector list for a customer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->explorerStagingList($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerStagingList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseExplorerDataStagingDto**](../Model/ListResultResponseExplorerDataStagingDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `explorerSubnetCreate()`

```php
explorerSubnetCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Create eXplorer subnet

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateExplorerSubnetRequest(); // \OpenAPI\Client\Model\CreateExplorerSubnetRequest

try {
    $result = $apiInstance->explorerSubnetCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerSubnetCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateExplorerSubnetRequest**](../Model/CreateExplorerSubnetRequest.md)|  | |

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

## `explorerSubnetDelete()`

```php
explorerSubnetDelete($id, $customer_id, $explorer_configuration_id): \OpenAPI\Client\Model\BaseResponse
```

Delete eXplorer subnet

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$customer_id = 'customer_id_example'; // string | CustomerCode
$explorer_configuration_id = 'explorer_configuration_id_example'; // string | ConfigurationCode

try {
    $result = $apiInstance->explorerSubnetDelete($id, $customer_id, $explorer_configuration_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerSubnetDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **customer_id** | **string**| CustomerCode | [optional] |
| **explorer_configuration_id** | **string**| ConfigurationCode | [optional] |

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

## `explorerSubnetUpdate()`

```php
explorerSubnetUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Update eXplorer subnet

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateExplorerSubnetRequest(); // \OpenAPI\Client\Model\UpdateExplorerSubnetRequest

try {
    $result = $apiInstance->explorerSubnetUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerSubnetUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateExplorerSubnetRequest**](../Model/UpdateExplorerSubnetRequest.md)|  | |

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

## `explorerUpdateAgent()`

```php
explorerUpdateAgent($request): \OpenAPI\Client\Model\BaseResponse
```

Explorer Update Agent

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateAgentRequest(); // \OpenAPI\Client\Model\UpdateAgentRequest

try {
    $result = $apiInstance->explorerUpdateAgent($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerUpdateAgent: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateAgentRequest**](../Model/UpdateAgentRequest.md)|  | |

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

## `explorerUpdateDca4Client()`

```php
explorerUpdateDca4Client($request): \OpenAPI\Client\Model\BaseResponse
```

Explorer Update Agent

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateDca4Request(); // \OpenAPI\Client\Model\UpdateDca4Request

try {
    $result = $apiInstance->explorerUpdateDca4Client($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerUpdateDca4Client: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateDca4Request**](../Model/UpdateDca4Request.md)|  | |

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

## `explorerUpdateDca4Monitor()`

```php
explorerUpdateDca4Monitor($request): \OpenAPI\Client\Model\BaseResponse
```

Explorer Update Service

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateDca4Request(); // \OpenAPI\Client\Model\UpdateDca4Request

try {
    $result = $apiInstance->explorerUpdateDca4Monitor($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerUpdateDca4Monitor: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateDca4Request**](../Model/UpdateDca4Request.md)|  | |

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

## `explorerUpdateService()`

```php
explorerUpdateService($request): \OpenAPI\Client\Model\BaseResponse
```

Explorer Update Service

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateServiceRequest(); // \OpenAPI\Client\Model\UpdateServiceRequest

try {
    $result = $apiInstance->explorerUpdateService($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerUpdateService: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateServiceRequest**](../Model/UpdateServiceRequest.md)|  | |

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

## `explorerV3ReleaseNotes()`

```php
explorerV3ReleaseNotes(): string
```

Get the eXplorer V3 Release Notes

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->explorerV3ReleaseNotes();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerV3ReleaseNotes: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

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

## `explorerWorkingDaysUpdate()`

```php
explorerWorkingDaysUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Update configuration working days

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ExplorerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateWorkingDaysRequest(); // \OpenAPI\Client\Model\UpdateWorkingDaysRequest

try {
    $result = $apiInstance->explorerWorkingDaysUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ExplorerApi->explorerWorkingDaysUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateWorkingDaysRequest**](../Model/UpdateWorkingDaysRequest.md)|  | |

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
