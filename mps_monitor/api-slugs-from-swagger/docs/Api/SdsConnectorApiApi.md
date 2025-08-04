# OpenAPI\Client\SdsConnectorApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**sdsConnectorAssociate()**](SdsConnectorApiApi.md#sdsConnectorAssociate) | **POST** /SdsConnector/Associate |  |
| [**sdsConnectorDeleteLog()**](SdsConnectorApiApi.md#sdsConnectorDeleteLog) | **DELETE** /SdsConnector/DeleteLog | Delete the log from SDS Cloud |
| [**sdsConnectorDownload()**](SdsConnectorApiApi.md#sdsConnectorDownload) | **POST** /SdsConnector/Download | Download the JAMC connector |
| [**sdsConnectorDownloadLog()**](SdsConnectorApiApi.md#sdsConnectorDownloadLog) | **GET** /SdsConnector/DownloadLog | Download the log from SDS Cloud. |
| [**sdsConnectorGetConnector()**](SdsConnectorApiApi.md#sdsConnectorGetConnector) | **GET** /SdsConnector/GetConnector | Get a connector. |
| [**sdsConnectorGetConnectors()**](SdsConnectorApiApi.md#sdsConnectorGetConnectors) | **GET** /SdsConnector/GetConnectors | Gets the connectors. |
| [**sdsConnectorGetJamcConnectors()**](SdsConnectorApiApi.md#sdsConnectorGetJamcConnectors) | **GET** /SdsConnector/GetJamcConnectors | Gets the jamc connectors. |
| [**sdsConnectorGetLogs()**](SdsConnectorApiApi.md#sdsConnectorGetLogs) | **GET** /SdsConnector/GetLogs | Invoke the log request to the JAMC |
| [**sdsConnectorGetWppConnectors()**](SdsConnectorApiApi.md#sdsConnectorGetWppConnectors) | **GET** /SdsConnector/GetWppConnectors | Gets the wpp connectors. |
| [**sdsConnectorInstall()**](SdsConnectorApiApi.md#sdsConnectorInstall) | **POST** /SdsConnector/Install | Install JAMC connector |
| [**sdsConnectorRegister()**](SdsConnectorApiApi.md#sdsConnectorRegister) | **POST** /SdsConnector/Register | Register the JAMC connector |
| [**sdsConnectorRetrieveLog()**](SdsConnectorApiApi.md#sdsConnectorRetrieveLog) | **POST** /SdsConnector/RetrieveLog | Retrieves the log list from SDS Cloud |
| [**sdsConnectorUnregister()**](SdsConnectorApiApi.md#sdsConnectorUnregister) | **POST** /SdsConnector/Unregister | Unregister the JAMC connector |


## `sdsConnectorAssociate()`

```php
sdsConnectorAssociate($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\AssociateSdsConnectorRequest(); // \OpenAPI\Client\Model\AssociateSdsConnectorRequest

try {
    $result = $apiInstance->sdsConnectorAssociate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorAssociate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\AssociateSdsConnectorRequest**](../Model/AssociateSdsConnectorRequest.md)|  | |

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

## `sdsConnectorDeleteLog()`

```php
sdsConnectorDeleteLog($id, $dealer_id, $customer_id, $connector_id): \OpenAPI\Client\Model\BaseResponse
```

Delete the log from SDS Cloud

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$dealer_id = 'dealer_id_example'; // string
$customer_id = 'customer_id_example'; // string
$connector_id = 56; // int

try {
    $result = $apiInstance->sdsConnectorDeleteLog($id, $dealer_id, $customer_id, $connector_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorDeleteLog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **dealer_id** | **string**|  | [optional] |
| **customer_id** | **string**|  | [optional] |
| **connector_id** | **int**|  | [optional] |

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

## `sdsConnectorDownload()`

```php
sdsConnectorDownload($request): \OpenAPI\Client\Model\BaseResponse
```

Download the JAMC connector

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->sdsConnectorDownload($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorDownload: ', $e->getMessage(), PHP_EOL;
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

## `sdsConnectorDownloadLog()`

```php
sdsConnectorDownloadLog($id, $dealer_id, $customer_id, $connector_id): object
```

Download the log from SDS Cloud.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$dealer_id = 'dealer_id_example'; // string
$customer_id = 'customer_id_example'; // string
$connector_id = 56; // int

try {
    $result = $apiInstance->sdsConnectorDownloadLog($id, $dealer_id, $customer_id, $connector_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorDownloadLog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **dealer_id** | **string**|  | [optional] |
| **customer_id** | **string**|  | [optional] |
| **connector_id** | **int**|  | [optional] |

### Return type

**object**

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsConnectorGetConnector()`

```php
sdsConnectorGetConnector($id): \OpenAPI\Client\Model\SingleResultResponseExplorerDataSdsDto
```

Get a connector.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsConnectorGetConnector($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorGetConnector: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseExplorerDataSdsDto**](../Model/SingleResultResponseExplorerDataSdsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsConnectorGetConnectors()`

```php
sdsConnectorGetConnectors($page_number, $page_rows, $sort_column, $sort_order, $dealer_id, $customer_id, $customerfilter, $include_not_registered, $severity_filter, $filter_text): \OpenAPI\Client\Model\PagedResultResponseExplorerDataSdsDto
```

Gets the connectors.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$dealer_id = 'dealer_id_example'; // string
$customer_id = 'customer_id_example'; // string
$customerfilter = 'customerfilter_example'; // string
$include_not_registered = True; // bool
$severity_filter = 'severity_filter_example'; // string
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->sdsConnectorGetConnectors($page_number, $page_rows, $sort_column, $sort_order, $dealer_id, $customer_id, $customerfilter, $include_not_registered, $severity_filter, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorGetConnectors: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **dealer_id** | **string**|  | [optional] |
| **customer_id** | **string**|  | [optional] |
| **customerfilter** | **string**|  | [optional] |
| **include_not_registered** | **bool**|  | [optional] |
| **severity_filter** | **string**|  | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseExplorerDataSdsDto**](../Model/PagedResultResponseExplorerDataSdsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsConnectorGetJamcConnectors()`

```php
sdsConnectorGetJamcConnectors($dealer_id, $customer_id, $filter_text): \OpenAPI\Client\Model\ListResultResponseJamcConnectorDto
```

Gets the jamc connectors.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_id = 'dealer_id_example'; // string
$customer_id = 'customer_id_example'; // string
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->sdsConnectorGetJamcConnectors($dealer_id, $customer_id, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorGetJamcConnectors: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_id** | **string**|  | [optional] |
| **customer_id** | **string**|  | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseJamcConnectorDto**](../Model/ListResultResponseJamcConnectorDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsConnectorGetLogs()`

```php
sdsConnectorGetLogs($dealer_id, $customer_id, $connector_id): \OpenAPI\Client\Model\ListResultResponseExplorerDataSdsLogDto
```

Invoke the log request to the JAMC

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_id = 'dealer_id_example'; // string
$customer_id = 'customer_id_example'; // string
$connector_id = 56; // int

try {
    $result = $apiInstance->sdsConnectorGetLogs($dealer_id, $customer_id, $connector_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorGetLogs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_id** | **string**|  | [optional] |
| **customer_id** | **string**|  | [optional] |
| **connector_id** | **int**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseExplorerDataSdsLogDto**](../Model/ListResultResponseExplorerDataSdsLogDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsConnectorGetWppConnectors()`

```php
sdsConnectorGetWppConnectors($dealer_id, $customer_id, $filter_text): \OpenAPI\Client\Model\ListResultResponseWppConnectorDto
```

Gets the wpp connectors.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_id = 'dealer_id_example'; // string
$customer_id = 'customer_id_example'; // string
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->sdsConnectorGetWppConnectors($dealer_id, $customer_id, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorGetWppConnectors: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_id** | **string**|  | [optional] |
| **customer_id** | **string**|  | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseWppConnectorDto**](../Model/ListResultResponseWppConnectorDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsConnectorInstall()`

```php
sdsConnectorInstall($request): \OpenAPI\Client\Model\BaseResponse
```

Install JAMC connector

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->sdsConnectorInstall($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorInstall: ', $e->getMessage(), PHP_EOL;
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

## `sdsConnectorRegister()`

```php
sdsConnectorRegister($request): \OpenAPI\Client\Model\BaseResponse
```

Register the JAMC connector

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->sdsConnectorRegister($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorRegister: ', $e->getMessage(), PHP_EOL;
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

## `sdsConnectorRetrieveLog()`

```php
sdsConnectorRetrieveLog($request): \OpenAPI\Client\Model\BaseResponse
```

Retrieves the log list from SDS Cloud

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\RetrieveLogRequest(); // \OpenAPI\Client\Model\RetrieveLogRequest | The request.

try {
    $result = $apiInstance->sdsConnectorRetrieveLog($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorRetrieveLog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\RetrieveLogRequest**](../Model/RetrieveLogRequest.md)| The request. | |

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

## `sdsConnectorUnregister()`

```php
sdsConnectorUnregister($request): \OpenAPI\Client\Model\BaseResponse
```

Unregister the JAMC connector

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsConnectorApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->sdsConnectorUnregister($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsConnectorApiApi->sdsConnectorUnregister: ', $e->getMessage(), PHP_EOL;
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
