# OpenAPI\Client\SdsScanApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**sdsScanScanConnectors()**](SdsScanApiApi.md#sdsScanScanConnectors) | **POST** /SdsScan/ScanConnectors | Retrieve saved SDS CLOUD customer data |
| [**sdsScanScanCustomer()**](SdsScanApiApi.md#sdsScanScanCustomer) | **POST** /SdsScan/ScanCustomer | Retrieve saved SDS CLOUD customer data |
| [**sdsScanScanDevice()**](SdsScanApiApi.md#sdsScanScanDevice) | **GET** /SdsScan/ScanDevice | Retrieve saved SDS CLOUD device data |
| [**sdsScanScanDevices()**](SdsScanApiApi.md#sdsScanScanDevices) | **POST** /SdsScan/ScanDevices | Run an SDS Scan on a specific customer |
| [**sdsScanScanImmediate()**](SdsScanApiApi.md#sdsScanScanImmediate) | **GET** /SdsScan/ScanImmediate | Retrieve all updated SDS device data. The operation will take about 20 minutes |


## `sdsScanScanConnectors()`

```php
sdsScanScanConnectors($request): \OpenAPI\Client\Model\BaseResponse
```

Retrieve saved SDS CLOUD customer data

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsScanApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SdsScanRequest(); // \OpenAPI\Client\Model\SdsScanRequest | The request.

try {
    $result = $apiInstance->sdsScanScanConnectors($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsScanApiApi->sdsScanScanConnectors: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SdsScanRequest**](../Model/SdsScanRequest.md)| The request. | |

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

## `sdsScanScanCustomer()`

```php
sdsScanScanCustomer($request): \OpenAPI\Client\Model\BaseResponse
```

Retrieve saved SDS CLOUD customer data

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsScanApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SdsScanRequest(); // \OpenAPI\Client\Model\SdsScanRequest | The request.

try {
    $result = $apiInstance->sdsScanScanCustomer($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsScanApiApi->sdsScanScanCustomer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SdsScanRequest**](../Model/SdsScanRequest.md)| The request. | |

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

## `sdsScanScanDevice()`

```php
sdsScanScanDevice($id): \OpenAPI\Client\Model\BaseResponse
```

Retrieve saved SDS CLOUD device data

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsScanApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsScanScanDevice($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsScanApiApi->sdsScanScanDevice: ', $e->getMessage(), PHP_EOL;
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

## `sdsScanScanDevices()`

```php
sdsScanScanDevices($request): \OpenAPI\Client\Model\BaseResponse
```

Run an SDS Scan on a specific customer

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsScanApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SdsScanRequest(); // \OpenAPI\Client\Model\SdsScanRequest

try {
    $result = $apiInstance->sdsScanScanDevices($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsScanApiApi->sdsScanScanDevices: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SdsScanRequest**](../Model/SdsScanRequest.md)|  | |

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

## `sdsScanScanImmediate()`

```php
sdsScanScanImmediate($id): \OpenAPI\Client\Model\BaseResponse
```

Retrieve all updated SDS device data. The operation will take about 20 minutes

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsScanApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsScanScanImmediate($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsScanApiApi->sdsScanScanImmediate: ', $e->getMessage(), PHP_EOL;
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
