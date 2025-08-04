# OpenAPI\Client\ApiClientApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**apiClientAccountCreate()**](ApiClientApiApi.md#apiClientAccountCreate) | **POST** /ApiClient/Account/Create | GEt Api Client Detail |
| [**apiClientAccountDelete()**](ApiClientApiApi.md#apiClientAccountDelete) | **DELETE** /ApiClient/Account/Delete | GEt Api Client Detail |
| [**apiClientAccountGet()**](ApiClientApiApi.md#apiClientAccountGet) | **GET** /ApiClient/Account/Get | GEt Api Client Detail |
| [**apiClientAccountList()**](ApiClientApiApi.md#apiClientAccountList) | **GET** /ApiClient/Account/List | Get Api user list |
| [**apiClientAccountUpdate()**](ApiClientApiApi.md#apiClientAccountUpdate) | **PUT** /ApiClient/Account/Update | GEt Api Client Detail |
| [**apiClientCreate()**](ApiClientApiApi.md#apiClientCreate) | **POST** /ApiClient/Create | GEt Api Client Detail |
| [**apiClientDelete()**](ApiClientApiApi.md#apiClientDelete) | **DELETE** /ApiClient/Delete | Delete Api Client |
| [**apiClientGet()**](ApiClientApiApi.md#apiClientGet) | **GET** /ApiClient/Get | GEt Api Client Detail |
| [**apiClientList()**](ApiClientApiApi.md#apiClientList) | **GET** /ApiClient/List | Get Api Clients for Dealer. |
| [**apiClientUpdate()**](ApiClientApiApi.md#apiClientUpdate) | **PUT** /ApiClient/Update | GEt Api Client Detail |


## `apiClientAccountCreate()`

```php
apiClientAccountCreate($request): \OpenAPI\Client\Model\SingleResultResponseAccountApiDto
```

GEt Api Client Detail

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ApiClientApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestApiClientAccountCreateRequest(); // \OpenAPI\Client\Model\CreateRequestApiClientAccountCreateRequest

try {
    $result = $apiInstance->apiClientAccountCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiClientApiApi->apiClientAccountCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestApiClientAccountCreateRequest**](../Model/CreateRequestApiClientAccountCreateRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAccountApiDto**](../Model/SingleResultResponseAccountApiDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiClientAccountDelete()`

```php
apiClientAccountDelete($id): \OpenAPI\Client\Model\BaseResponse
```

GEt Api Client Detail

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ApiClientApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->apiClientAccountDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiClientApiApi->apiClientAccountDelete: ', $e->getMessage(), PHP_EOL;
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

## `apiClientAccountGet()`

```php
apiClientAccountGet($id): \OpenAPI\Client\Model\SingleResultResponseAccountApiDto
```

GEt Api Client Detail

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ApiClientApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->apiClientAccountGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiClientApiApi->apiClientAccountGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAccountApiDto**](../Model/SingleResultResponseAccountApiDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiClientAccountList()`

```php
apiClientAccountList($id): \OpenAPI\Client\Model\ListResultResponseAccountApiDto
```

Get Api user list

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ApiClientApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->apiClientAccountList($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiClientApiApi->apiClientAccountList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseAccountApiDto**](../Model/ListResultResponseAccountApiDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiClientAccountUpdate()`

```php
apiClientAccountUpdate($request): \OpenAPI\Client\Model\SingleResultResponseAccountApiDto
```

GEt Api Client Detail

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ApiClientApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestApiClientAccountUpdateRequest(); // \OpenAPI\Client\Model\UpdateRequestApiClientAccountUpdateRequest

try {
    $result = $apiInstance->apiClientAccountUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiClientApiApi->apiClientAccountUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestApiClientAccountUpdateRequest**](../Model/UpdateRequestApiClientAccountUpdateRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAccountApiDto**](../Model/SingleResultResponseAccountApiDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiClientCreate()`

```php
apiClientCreate($request): \OpenAPI\Client\Model\SingleResultResponseApiClientDto
```

GEt Api Client Detail

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ApiClientApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestApiClientCreateRequest(); // \OpenAPI\Client\Model\CreateRequestApiClientCreateRequest

try {
    $result = $apiInstance->apiClientCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiClientApiApi->apiClientCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestApiClientCreateRequest**](../Model/CreateRequestApiClientCreateRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseApiClientDto**](../Model/SingleResultResponseApiClientDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiClientDelete()`

```php
apiClientDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Delete Api Client

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ApiClientApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->apiClientDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiClientApiApi->apiClientDelete: ', $e->getMessage(), PHP_EOL;
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

## `apiClientGet()`

```php
apiClientGet($id): \OpenAPI\Client\Model\SingleResultResponseApiClientDto
```

GEt Api Client Detail

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ApiClientApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->apiClientGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiClientApiApi->apiClientGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseApiClientDto**](../Model/SingleResultResponseApiClientDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiClientList()`

```php
apiClientList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $filter_text): \OpenAPI\Client\Model\PagedResultResponseApiClientDto
```

Get Api Clients for Dealer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ApiClientApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->apiClientList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiClientApiApi->apiClientList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the code. | |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseApiClientDto**](../Model/PagedResultResponseApiClientDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `apiClientUpdate()`

```php
apiClientUpdate($request): \OpenAPI\Client\Model\SingleResultResponseApiClientDto
```

GEt Api Client Detail

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ApiClientApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestApiClientUpdateRequest(); // \OpenAPI\Client\Model\UpdateRequestApiClientUpdateRequest

try {
    $result = $apiInstance->apiClientUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiClientApiApi->apiClientUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestApiClientUpdateRequest**](../Model/UpdateRequestApiClientUpdateRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseApiClientDto**](../Model/SingleResultResponseApiClientDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
