# OpenAPI\Client\AlertLimitApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**alertLimitCustomerDelete()**](AlertLimitApiApi.md#alertLimitCustomerDelete) | **DELETE** /AlertLimit/Customer/Delete | Delete Alert limits |
| [**alertLimitCustomerGet()**](AlertLimitApiApi.md#alertLimitCustomerGet) | **GET** /AlertLimit/Customer/Get | Get customer Alert Limit settings |
| [**alertLimitCustomerProductDelete()**](AlertLimitApiApi.md#alertLimitCustomerProductDelete) | **POST** /AlertLimit/Customer/Product/Delete |  |
| [**alertLimitCustomerProductList()**](AlertLimitApiApi.md#alertLimitCustomerProductList) | **GET** /AlertLimit/Customer/Product/List | Get dealers Alert Limit settings |
| [**alertLimitCustomerProductUpdate()**](AlertLimitApiApi.md#alertLimitCustomerProductUpdate) | **PUT** /AlertLimit/Customer/Product/Update | Set Alert limits for a specified Customer and Product |
| [**alertLimitCustomerUpdate()**](AlertLimitApiApi.md#alertLimitCustomerUpdate) | **PUT** /AlertLimit/Customer/Update | Set Alert limits for a specified Customer |
| [**alertLimitDealerGet()**](AlertLimitApiApi.md#alertLimitDealerGet) | **GET** /AlertLimit/Dealer/Get | Get dealers Alert Limit settings |
| [**alertLimitDealerUpdate()**](AlertLimitApiApi.md#alertLimitDealerUpdate) | **PUT** /AlertLimit/Dealer/Update | Set Alert limits for a specified dealer |
| [**alertLimitDeviceDelete()**](AlertLimitApiApi.md#alertLimitDeviceDelete) | **DELETE** /AlertLimit/Device/Delete |  |
| [**alertLimitDeviceGet()**](AlertLimitApiApi.md#alertLimitDeviceGet) | **GET** /AlertLimit/Device/Get | Get device Alert Limit settings |
| [**alertLimitDeviceUpdate()**](AlertLimitApiApi.md#alertLimitDeviceUpdate) | **PUT** /AlertLimit/Device/Update | Set Alert limits for a specified Customer and Product |


## `alertLimitCustomerDelete()`

```php
alertLimitCustomerDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Delete Alert limits

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->alertLimitCustomerDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitCustomerDelete: ', $e->getMessage(), PHP_EOL;
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

## `alertLimitCustomerGet()`

```php
alertLimitCustomerGet($code): \OpenAPI\Client\Model\SingleResultResponseAlertLimitCustomerProductDto
```

Get customer Alert Limit settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->alertLimitCustomerGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitCustomerGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAlertLimitCustomerProductDto**](../Model/SingleResultResponseAlertLimitCustomerProductDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimitCustomerProductDelete()`

```php
alertLimitCustomerProductDelete($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DeleteRequestKeyValue(); // \OpenAPI\Client\Model\DeleteRequestKeyValue

try {
    $result = $apiInstance->alertLimitCustomerProductDelete($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitCustomerProductDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DeleteRequestKeyValue**](../Model/DeleteRequestKeyValue.md)|  | |

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

## `alertLimitCustomerProductList()`

```php
alertLimitCustomerProductList($code): \OpenAPI\Client\Model\ListResultResponseAlertLimitCustomerProductDto
```

Get dealers Alert Limit settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->alertLimitCustomerProductList($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitCustomerProductList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseAlertLimitCustomerProductDto**](../Model/ListResultResponseAlertLimitCustomerProductDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimitCustomerProductUpdate()`

```php
alertLimitCustomerProductUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Set Alert limits for a specified Customer and Product

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitCustomerProductDto(); // \OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitCustomerProductDto

try {
    $result = $apiInstance->alertLimitCustomerProductUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitCustomerProductUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitCustomerProductDto**](../Model/UpdateRequestUpdateAlertLimitCustomerProductDto.md)|  | |

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

## `alertLimitCustomerUpdate()`

```php
alertLimitCustomerUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Set Alert limits for a specified Customer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitCustomerProductDto(); // \OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitCustomerProductDto

try {
    $result = $apiInstance->alertLimitCustomerUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitCustomerUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitCustomerProductDto**](../Model/UpdateRequestUpdateAlertLimitCustomerProductDto.md)|  | |

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

## `alertLimitDealerGet()`

```php
alertLimitDealerGet($code): \OpenAPI\Client\Model\SingleResultResponseUpdateAlertLimitDealerDto
```

Get dealers Alert Limit settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->alertLimitDealerGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitDealerGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseUpdateAlertLimitDealerDto**](../Model/SingleResultResponseUpdateAlertLimitDealerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimitDealerUpdate()`

```php
alertLimitDealerUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Set Alert limits for a specified dealer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitDealerDto(); // \OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitDealerDto

try {
    $result = $apiInstance->alertLimitDealerUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitDealerUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitDealerDto**](../Model/UpdateRequestUpdateAlertLimitDealerDto.md)|  | |

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

## `alertLimitDeviceDelete()`

```php
alertLimitDeviceDelete($id): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->alertLimitDeviceDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitDeviceDelete: ', $e->getMessage(), PHP_EOL;
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

## `alertLimitDeviceGet()`

```php
alertLimitDeviceGet($id): \OpenAPI\Client\Model\SingleResultResponseAlertLimitBaseDto
```

Get device Alert Limit settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->alertLimitDeviceGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitDeviceGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAlertLimitBaseDto**](../Model/SingleResultResponseAlertLimitBaseDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimitDeviceUpdate()`

```php
alertLimitDeviceUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Set Alert limits for a specified Customer and Product

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimitApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitDeviceDto(); // \OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitDeviceDto

try {
    $result = $apiInstance->alertLimitDeviceUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimitApiApi->alertLimitDeviceUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestUpdateAlertLimitDeviceDto**](../Model/UpdateRequestUpdateAlertLimitDeviceDto.md)|  | |

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
