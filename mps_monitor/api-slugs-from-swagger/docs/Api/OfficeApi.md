# OpenAPI\Client\OfficeApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**officeCreate()**](OfficeApi.md#officeCreate) | **POST** /Office/Create | Creates the office. |
| [**officeDelete()**](OfficeApi.md#officeDelete) | **DELETE** /Office/Delete | Delete  office. |
| [**officeGet()**](OfficeApi.md#officeGet) | **POST** /Office/Get | Gets the office. |
| [**officeList()**](OfficeApi.md#officeList) | **POST** /Office/List | Gets the offices. |
| [**officeOfficeFloorCreate()**](OfficeApi.md#officeOfficeFloorCreate) | **POST** /Office/OfficeFloor/Create |  |
| [**officeOfficeFloorDelete()**](OfficeApi.md#officeOfficeFloorDelete) | **DELETE** /Office/OfficeFloor/Delete |  |
| [**officeOfficeFloorDeletePin()**](OfficeApi.md#officeOfficeFloorDeletePin) | **DELETE** /Office/OfficeFloor/DeletePin |  |
| [**officeOfficeFloorGetPin()**](OfficeApi.md#officeOfficeFloorGetPin) | **GET** /Office/OfficeFloor/GetPin |  |
| [**officeOfficeFloorList()**](OfficeApi.md#officeOfficeFloorList) | **GET** /Office/OfficeFloor/List |  |
| [**officeOfficeFloorSavePin()**](OfficeApi.md#officeOfficeFloorSavePin) | **POST** /Office/OfficeFloor/SavePin |  |
| [**officeOfficeFloorUpdate()**](OfficeApi.md#officeOfficeFloorUpdate) | **PUT** /Office/OfficeFloor/Update |  |
| [**officeSubnetCreate()**](OfficeApi.md#officeSubnetCreate) | **POST** /Office/Subnet/Create | Create office subnet |
| [**officeSubnetDelete()**](OfficeApi.md#officeSubnetDelete) | **DELETE** /Office/Subnet/Delete | Delete office subnet |
| [**officeUpdate()**](OfficeApi.md#officeUpdate) | **POST** /Office/Update | Updates the office. |
| [**officeUploadOffices()**](OfficeApi.md#officeUploadOffices) | **POST** /Office/UploadOffices | Upload Offices |


## `officeCreate()`

```php
officeCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Creates the office.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestOfficeDto(); // \OpenAPI\Client\Model\CreateRequestOfficeDto | The request.

try {
    $result = $apiInstance->officeCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestOfficeDto**](../Model/CreateRequestOfficeDto.md)| The request. | |

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

## `officeDelete()`

```php
officeDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Delete  office.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->officeDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeDelete: ', $e->getMessage(), PHP_EOL;
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

## `officeGet()`

```php
officeGet($request): \OpenAPI\Client\Model\SingleResultResponseOfficeDto
```

Gets the office.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->officeGet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseOfficeDto**](../Model/SingleResultResponseOfficeDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `officeList()`

```php
officeList($request): \OpenAPI\Client\Model\PagedResultResponseOfficeListDto
```

Gets the offices.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetOfficesRequest(); // \OpenAPI\Client\Model\GetOfficesRequest | The request.

try {
    $result = $apiInstance->officeList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetOfficesRequest**](../Model/GetOfficesRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseOfficeListDto**](../Model/PagedResultResponseOfficeListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `officeOfficeFloorCreate()`

```php
officeOfficeFloorCreate($request): \OpenAPI\Client\Model\SingleResultResponseOfficeFloorDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateOfficeFloorRequest(); // \OpenAPI\Client\Model\CreateOfficeFloorRequest

try {
    $result = $apiInstance->officeOfficeFloorCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeOfficeFloorCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateOfficeFloorRequest**](../Model/CreateOfficeFloorRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseOfficeFloorDto**](../Model/SingleResultResponseOfficeFloorDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `officeOfficeFloorDelete()`

```php
officeOfficeFloorDelete($office_id, $office_floor_id): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$office_id = 'office_id_example'; // string | Gets or sets the office identifier.
$office_floor_id = 'office_floor_id_example'; // string | Gets or sets the office floor identifier.

try {
    $result = $apiInstance->officeOfficeFloorDelete($office_id, $office_floor_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeOfficeFloorDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **office_id** | **string**| Gets or sets the office identifier. | [optional] |
| **office_floor_id** | **string**| Gets or sets the office floor identifier. | [optional] |

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

## `officeOfficeFloorDeletePin()`

```php
officeOfficeFloorDeletePin($office_id, $office_floor_id, $device_id): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$office_id = 'office_id_example'; // string | Gets or sets the office identifier.
$office_floor_id = 'office_floor_id_example'; // string | Gets or sets the office floor identifier.
$device_id = 'device_id_example'; // string | Gets or sets the device identifier.

try {
    $result = $apiInstance->officeOfficeFloorDeletePin($office_id, $office_floor_id, $device_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeOfficeFloorDeletePin: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **office_id** | **string**| Gets or sets the office identifier. | [optional] |
| **office_floor_id** | **string**| Gets or sets the office floor identifier. | [optional] |
| **device_id** | **string**| Gets or sets the device identifier. | [optional] |

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

## `officeOfficeFloorGetPin()`

```php
officeOfficeFloorGetPin($id): \OpenAPI\Client\Model\SingleResultResponseOfficeFloorDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->officeOfficeFloorGetPin($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeOfficeFloorGetPin: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseOfficeFloorDto**](../Model/SingleResultResponseOfficeFloorDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `officeOfficeFloorList()`

```php
officeOfficeFloorList($id): \OpenAPI\Client\Model\ListResultResponseOfficeFloorDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->officeOfficeFloorList($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeOfficeFloorList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseOfficeFloorDto**](../Model/ListResultResponseOfficeFloorDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `officeOfficeFloorSavePin()`

```php
officeOfficeFloorSavePin($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SaveOfficeFloorPinRequest(); // \OpenAPI\Client\Model\SaveOfficeFloorPinRequest

try {
    $result = $apiInstance->officeOfficeFloorSavePin($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeOfficeFloorSavePin: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SaveOfficeFloorPinRequest**](../Model/SaveOfficeFloorPinRequest.md)|  | |

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

## `officeOfficeFloorUpdate()`

```php
officeOfficeFloorUpdate($request): \OpenAPI\Client\Model\SingleResultResponseOfficeFloorDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateOfficeFloorRequest(); // \OpenAPI\Client\Model\UpdateOfficeFloorRequest

try {
    $result = $apiInstance->officeOfficeFloorUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeOfficeFloorUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateOfficeFloorRequest**](../Model/UpdateOfficeFloorRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseOfficeFloorDto**](../Model/SingleResultResponseOfficeFloorDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `officeSubnetCreate()`

```php
officeSubnetCreate($request): \OpenAPI\Client\Model\ListResultResponseOfficeSubnetDto
```

Create office subnet

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateOfficeSubnetRequest(); // \OpenAPI\Client\Model\CreateOfficeSubnetRequest

try {
    $result = $apiInstance->officeSubnetCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeSubnetCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateOfficeSubnetRequest**](../Model/CreateOfficeSubnetRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseOfficeSubnetDto**](../Model/ListResultResponseOfficeSubnetDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `officeSubnetDelete()`

```php
officeSubnetDelete($id, $office_id): \OpenAPI\Client\Model\BaseResponse
```

Delete office subnet

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$office_id = 'office_id_example'; // string | OfficeId

try {
    $result = $apiInstance->officeSubnetDelete($id, $office_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeSubnetDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **office_id** | **string**| OfficeId | [optional] |

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

## `officeUpdate()`

```php
officeUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Updates the office.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestOfficeDto(); // \OpenAPI\Client\Model\UpdateRequestOfficeDto | The request.

try {
    $result = $apiInstance->officeUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestOfficeDto**](../Model/UpdateRequestOfficeDto.md)| The request. | |

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

## `officeUploadOffices()`

```php
officeUploadOffices($request): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

Upload Offices

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\OfficeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UploadOfficesRequest(); // \OpenAPI\Client\Model\UploadOfficesRequest | The request.

try {
    $result = $apiInstance->officeUploadOffices($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OfficeApi->officeUploadOffices: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UploadOfficesRequest**](../Model/UploadOfficesRequest.md)| The request. | |

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
