# OpenAPI\Client\SagaApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**sagaGet()**](SagaApi.md#sagaGet) | **GET** /Saga/Get | Gets the saga details. |
| [**sagaGetSagaOperationLogFile()**](SagaApi.md#sagaGetSagaOperationLogFile) | **POST** /Saga/GetSagaOperationLogFile | Gets the Saga Operation Log file |
| [**sagaGetSagaOperationLogList()**](SagaApi.md#sagaGetSagaOperationLogList) | **POST** /Saga/GetSagaOperationLogList | Gets the saga operation log list. |
| [**sagaGetSagaOperationLogMessage()**](SagaApi.md#sagaGetSagaOperationLogMessage) | **GET** /Saga/GetSagaOperationLogMessage | Downloads the message from saga operation log. |
| [**sagaGetSagaOperationsList()**](SagaApi.md#sagaGetSagaOperationsList) | **POST** /Saga/GetSagaOperationsList | Gets the saga operations list. |
| [**sagaList()**](SagaApi.md#sagaList) | **POST** /Saga/List | Gets the saga list. |


## `sagaGet()`

```php
sagaGet($id): \OpenAPI\Client\Model\SingleResultResponseSagaDto
```

Gets the saga details.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SagaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sagaGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SagaApi->sagaGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSagaDto**](../Model/SingleResultResponseSagaDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sagaGetSagaOperationLogFile()`

```php
sagaGetSagaOperationLogFile($request): \OpenAPI\Client\Model\BaseHttpResponseMessage
```

Gets the Saga Operation Log file

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SagaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | request

try {
    $result = $apiInstance->sagaGetSagaOperationLogFile($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SagaApi->sagaGetSagaOperationLogFile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| request | |

### Return type

[**\OpenAPI\Client\Model\BaseHttpResponseMessage**](../Model/BaseHttpResponseMessage.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sagaGetSagaOperationLogList()`

```php
sagaGetSagaOperationLogList($request): \OpenAPI\Client\Model\PagedResultResponseSagaOperationLogDto
```

Gets the saga operation log list.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SagaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetSagaOperationLogListRequest(); // \OpenAPI\Client\Model\GetSagaOperationLogListRequest | The request.

try {
    $result = $apiInstance->sagaGetSagaOperationLogList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SagaApi->sagaGetSagaOperationLogList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetSagaOperationLogListRequest**](../Model/GetSagaOperationLogListRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSagaOperationLogDto**](../Model/PagedResultResponseSagaOperationLogDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sagaGetSagaOperationLogMessage()`

```php
sagaGetSagaOperationLogMessage($id): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

Downloads the message from saga operation log.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SagaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sagaGetSagaOperationLogMessage($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SagaApi->sagaGetSagaOperationLogMessage: ', $e->getMessage(), PHP_EOL;
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

## `sagaGetSagaOperationsList()`

```php
sagaGetSagaOperationsList($request): \OpenAPI\Client\Model\PagedResultResponseSagaOperationListDto
```

Gets the saga operations list.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SagaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetSagaOperationsRequest(); // \OpenAPI\Client\Model\GetSagaOperationsRequest | The request.

try {
    $result = $apiInstance->sagaGetSagaOperationsList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SagaApi->sagaGetSagaOperationsList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetSagaOperationsRequest**](../Model/GetSagaOperationsRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSagaOperationListDto**](../Model/PagedResultResponseSagaOperationListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sagaList()`

```php
sagaList($request): \OpenAPI\Client\Model\PagedResultResponseSagaListDto
```

Gets the saga list.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SagaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetSagasRequest(); // \OpenAPI\Client\Model\GetSagasRequest | The request.

try {
    $result = $apiInstance->sagaList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SagaApi->sagaList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetSagasRequest**](../Model/GetSagasRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSagaListDto**](../Model/PagedResultResponseSagaListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
