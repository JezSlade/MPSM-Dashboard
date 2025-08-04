# OpenAPI\Client\CounterApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**counterCatalogExport()**](CounterApi.md#counterCatalogExport) | **POST** /Counter/Catalog/Export |  |
| [**counterCatalogList()**](CounterApi.md#counterCatalogList) | **POST** /Counter/Catalog/List |  |
| [**counterCatalogSuggestions()**](CounterApi.md#counterCatalogSuggestions) | **GET** /Counter/Catalog/Suggestions |  |
| [**counterCatalogUpdate()**](CounterApi.md#counterCatalogUpdate) | **PUT** /Counter/Catalog/Update |  |
| [**counterDeviceDelete()**](CounterApi.md#counterDeviceDelete) | **DELETE** /Counter/Device/Delete | delete device counters |
| [**counterDeviceExport()**](CounterApi.md#counterDeviceExport) | **GET** /Counter/Device/Export | Export detailed counters |
| [**counterDeviceList()**](CounterApi.md#counterDeviceList) | **GET** /Counter/Device/List | Returns detailed counters |
| [**counterDeviceUpdate()**](CounterApi.md#counterDeviceUpdate) | **POST** /Counter/Device/Update | Insert Update device counters |
| [**counterDeviceUpdateCounterDetailTag()**](CounterApi.md#counterDeviceUpdateCounterDetailTag) | **POST** /Counter/Device/UpdateCounterDetailTag | Insert Update device counters |
| [**counterList()**](CounterApi.md#counterList) | **POST** /Counter/List | Returns counters |
| [**counterListBlended()**](CounterApi.md#counterListBlended) | **POST** /Counter/ListBlended | Returns blended counters |
| [**counterListDetailed()**](CounterApi.md#counterListDetailed) | **POST** /Counter/ListDetailed | Returns detailed counters |
| [**counterListMaintenanceKitCounters()**](CounterApi.md#counterListMaintenanceKitCounters) | **GET** /Counter/ListMaintenanceKitCounters | Returns maintenance kit counters |
| [**counterUploadOfflineDeviceCounters()**](CounterApi.md#counterUploadOfflineDeviceCounters) | **POST** /Counter/UploadOfflineDeviceCounters | Upload offline device counters |


## `counterCatalogExport()`

```php
counterCatalogExport($request): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = array('key' => new \stdClass); // object

try {
    $result = $apiInstance->counterCatalogExport($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterCatalogExport: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | **object**|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseFileInfoDto**](../Model/SingleResultResponseFileInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `counterCatalogList()`

```php
counterCatalogList($request): \OpenAPI\Client\Model\PagedResultResponseCounterCatalogItemDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetCountersCatalogRequest(); // \OpenAPI\Client\Model\GetCountersCatalogRequest

try {
    $result = $apiInstance->counterCatalogList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterCatalogList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetCountersCatalogRequest**](../Model/GetCountersCatalogRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseCounterCatalogItemDto**](../Model/PagedResultResponseCounterCatalogItemDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `counterCatalogSuggestions()`

```php
counterCatalogSuggestions(): \OpenAPI\Client\Model\SingleResultResponseCounterCatalogSuggestionsDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->counterCatalogSuggestions();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterCatalogSuggestions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCounterCatalogSuggestionsDto**](../Model/SingleResultResponseCounterCatalogSuggestionsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `counterCatalogUpdate()`

```php
counterCatalogUpdate($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SaveCountersCatalogRequest(); // \OpenAPI\Client\Model\SaveCountersCatalogRequest

try {
    $result = $apiInstance->counterCatalogUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterCatalogUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SaveCountersCatalogRequest**](../Model/SaveCountersCatalogRequest.md)|  | |

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

## `counterDeviceDelete()`

```php
counterDeviceDelete($counter_id, $id): \OpenAPI\Client\Model\SingleResultResponseBaseResponse
```

delete device counters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$counter_id = 'counter_id_example'; // string | Gets or sets the identifier.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->counterDeviceDelete($counter_id, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterDeviceDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **counter_id** | **string**| Gets or sets the identifier. | |
| **id** | **string**| Gets or sets the identifier. | |

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

## `counterDeviceExport()`

```php
counterDeviceExport($from_date, $to_date, $id, $export_to_csv): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

Export detailed counters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$from_date = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime | Gets or sets the from date.
$to_date = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime | Gets or sets the to date.
$id = 'id_example'; // string | Gets or sets the identifier.
$export_to_csv = True; // bool | Gets or sets a value indicating whether [export to CSV] or [export to XSLSX].

try {
    $result = $apiInstance->counterDeviceExport($from_date, $to_date, $id, $export_to_csv);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterDeviceExport: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **from_date** | **\DateTime**| Gets or sets the from date. | |
| **to_date** | **\DateTime**| Gets or sets the to date. | |
| **id** | **string**| Gets or sets the identifier. | |
| **export_to_csv** | **bool**| Gets or sets a value indicating whether [export to CSV] or [export to XSLSX]. | [optional] |

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

## `counterDeviceList()`

```php
counterDeviceList($from_date, $to_date, $id): \OpenAPI\Client\Model\ListResultResponseCounterDto
```

Returns detailed counters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$from_date = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime | Gets or sets the from date.
$to_date = new \DateTime('2013-10-20T19:20:30+01:00'); // \DateTime | Gets or sets the to date.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->counterDeviceList($from_date, $to_date, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterDeviceList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **from_date** | **\DateTime**| Gets or sets the from date. | |
| **to_date** | **\DateTime**| Gets or sets the to date. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseCounterDto**](../Model/ListResultResponseCounterDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `counterDeviceUpdate()`

```php
counterDeviceUpdate($request): \OpenAPI\Client\Model\SingleResultResponseCounterDto
```

Insert Update device counters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateDeviceCounterRequest(); // \OpenAPI\Client\Model\UpdateDeviceCounterRequest

try {
    $result = $apiInstance->counterDeviceUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterDeviceUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateDeviceCounterRequest**](../Model/UpdateDeviceCounterRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCounterDto**](../Model/SingleResultResponseCounterDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `counterDeviceUpdateCounterDetailTag()`

```php
counterDeviceUpdateCounterDetailTag($request): \OpenAPI\Client\Model\BaseResponse
```

Insert Update device counters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateCounterDetailedTagRequest(); // \OpenAPI\Client\Model\UpdateCounterDetailedTagRequest

try {
    $result = $apiInstance->counterDeviceUpdateCounterDetailTag($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterDeviceUpdateCounterDetailTag: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateCounterDetailedTagRequest**](../Model/UpdateCounterDetailedTagRequest.md)|  | |

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

## `counterList()`

```php
counterList($request): \OpenAPI\Client\Model\ListResultResponseCountersDeviceDto
```

Returns counters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetCountersRequest(); // \OpenAPI\Client\Model\GetCountersRequest

try {
    $result = $apiInstance->counterList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetCountersRequest**](../Model/GetCountersRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseCountersDeviceDto**](../Model/ListResultResponseCountersDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `counterListBlended()`

```php
counterListBlended($request): \OpenAPI\Client\Model\ListResultResponseCountersBlendDeviceDto
```

Returns blended counters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetCountersRequest(); // \OpenAPI\Client\Model\GetCountersRequest

try {
    $result = $apiInstance->counterListBlended($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterListBlended: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetCountersRequest**](../Model/GetCountersRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseCountersBlendDeviceDto**](../Model/ListResultResponseCountersBlendDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `counterListDetailed()`

```php
counterListDetailed($request): \OpenAPI\Client\Model\ListResultResponseCountersDetailedDeviceDto
```

Returns detailed counters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetCountersDetailedRequest(); // \OpenAPI\Client\Model\GetCountersDetailedRequest

try {
    $result = $apiInstance->counterListDetailed($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterListDetailed: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetCountersDetailedRequest**](../Model/GetCountersDetailedRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseCountersDetailedDeviceDto**](../Model/ListResultResponseCountersDetailedDeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `counterListMaintenanceKitCounters()`

```php
counterListMaintenanceKitCounters($id): \OpenAPI\Client\Model\ListResultResponseMaintenanceKitCounterDto
```

Returns maintenance kit counters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->counterListMaintenanceKitCounters($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterListMaintenanceKitCounters: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseMaintenanceKitCounterDto**](../Model/ListResultResponseMaintenanceKitCounterDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `counterUploadOfflineDeviceCounters()`

```php
counterUploadOfflineDeviceCounters($request): \OpenAPI\Client\Model\BaseResponse
```

Upload offline device counters

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CounterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UploadOfflineDeviceCountersRequest(); // \OpenAPI\Client\Model\UploadOfflineDeviceCountersRequest

try {
    $result = $apiInstance->counterUploadOfflineDeviceCounters($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CounterApi->counterUploadOfflineDeviceCounters: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UploadOfflineDeviceCountersRequest**](../Model/UploadOfflineDeviceCountersRequest.md)|  | |

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
