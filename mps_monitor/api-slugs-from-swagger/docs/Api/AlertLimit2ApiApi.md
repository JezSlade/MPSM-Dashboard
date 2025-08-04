# OpenAPI\Client\AlertLimit2ApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**alertLimit2CustomerCreateDefault()**](AlertLimit2ApiApi.md#alertLimit2CustomerCreateDefault) | **POST** /AlertLimit2/Customer/CreateDefault |  |
| [**alertLimit2CustomerCreateProduct()**](AlertLimit2ApiApi.md#alertLimit2CustomerCreateProduct) | **POST** /AlertLimit2/Customer/CreateProduct |  |
| [**alertLimit2CustomerDeleteDefaultForConsumable()**](AlertLimit2ApiApi.md#alertLimit2CustomerDeleteDefaultForConsumable) | **DELETE** /AlertLimit2/Customer/DeleteDefaultForConsumable |  |
| [**alertLimit2CustomerDeleteProduct()**](AlertLimit2ApiApi.md#alertLimit2CustomerDeleteProduct) | **DELETE** /AlertLimit2/Customer/DeleteProduct |  |
| [**alertLimit2CustomerGetDefault()**](AlertLimit2ApiApi.md#alertLimit2CustomerGetDefault) | **GET** /AlertLimit2/Customer/GetDefault |  |
| [**alertLimit2CustomerGetProduct()**](AlertLimit2ApiApi.md#alertLimit2CustomerGetProduct) | **GET** /AlertLimit2/Customer/GetProduct |  |
| [**alertLimit2CustomerGetProductList()**](AlertLimit2ApiApi.md#alertLimit2CustomerGetProductList) | **GET** /AlertLimit2/Customer/GetProductList |  |
| [**alertLimit2CustomerUpdateDefault()**](AlertLimit2ApiApi.md#alertLimit2CustomerUpdateDefault) | **PUT** /AlertLimit2/Customer/UpdateDefault |  |
| [**alertLimit2CustomerUpdateProduct()**](AlertLimit2ApiApi.md#alertLimit2CustomerUpdateProduct) | **PUT** /AlertLimit2/Customer/UpdateProduct |  |
| [**alertLimit2DealerCreateDefault()**](AlertLimit2ApiApi.md#alertLimit2DealerCreateDefault) | **POST** /AlertLimit2/Dealer/CreateDefault |  |
| [**alertLimit2DealerCreateProduct()**](AlertLimit2ApiApi.md#alertLimit2DealerCreateProduct) | **POST** /AlertLimit2/Dealer/CreateProduct |  |
| [**alertLimit2DealerDeleteProduct()**](AlertLimit2ApiApi.md#alertLimit2DealerDeleteProduct) | **DELETE** /AlertLimit2/Dealer/DeleteProduct |  |
| [**alertLimit2DealerGetDefault()**](AlertLimit2ApiApi.md#alertLimit2DealerGetDefault) | **GET** /AlertLimit2/Dealer/GetDefault |  |
| [**alertLimit2DealerGetProduct()**](AlertLimit2ApiApi.md#alertLimit2DealerGetProduct) | **GET** /AlertLimit2/Dealer/GetProduct |  |
| [**alertLimit2DealerGetProductList()**](AlertLimit2ApiApi.md#alertLimit2DealerGetProductList) | **GET** /AlertLimit2/Dealer/GetProductList |  |
| [**alertLimit2DealerUpdateDefault()**](AlertLimit2ApiApi.md#alertLimit2DealerUpdateDefault) | **PUT** /AlertLimit2/Dealer/UpdateDefault |  |
| [**alertLimit2DealerUpdateProduct()**](AlertLimit2ApiApi.md#alertLimit2DealerUpdateProduct) | **PUT** /AlertLimit2/Dealer/UpdateProduct |  |
| [**alertLimit2DeviceCreateDefault()**](AlertLimit2ApiApi.md#alertLimit2DeviceCreateDefault) | **POST** /AlertLimit2/Device/CreateDefault |  |
| [**alertLimit2DeviceDeleteDefaultForConsumable()**](AlertLimit2ApiApi.md#alertLimit2DeviceDeleteDefaultForConsumable) | **DELETE** /AlertLimit2/Device/DeleteDefaultForConsumable |  |
| [**alertLimit2DeviceGetDefault()**](AlertLimit2ApiApi.md#alertLimit2DeviceGetDefault) | **GET** /AlertLimit2/Device/GetDefault |  |
| [**alertLimit2DeviceUpdateDefault()**](AlertLimit2ApiApi.md#alertLimit2DeviceUpdateDefault) | **PUT** /AlertLimit2/Device/UpdateDefault |  |
| [**alertLimit2DisableAlertLimits()**](AlertLimit2ApiApi.md#alertLimit2DisableAlertLimits) | **POST** /AlertLimit2/DisableAlertLimits |  |
| [**alertLimit2GetAllLimits()**](AlertLimit2ApiApi.md#alertLimit2GetAllLimits) | **GET** /AlertLimit2/GetAllLimits |  |


## `alertLimit2CustomerCreateDefault()`

```php
alertLimit2CustomerCreateDefault($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerDto

try {
    $result = $apiInstance->alertLimit2CustomerCreateDefault($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2CustomerCreateDefault: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerDto**](../Model/UpdateRequestIEnumerableAlertLimit2CustomerDto.md)|  | |

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

## `alertLimit2CustomerCreateProduct()`

```php
alertLimit2CustomerCreateProduct($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerProductDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerProductDto

try {
    $result = $apiInstance->alertLimit2CustomerCreateProduct($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2CustomerCreateProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerProductDto**](../Model/UpdateRequestIEnumerableAlertLimit2CustomerProductDto.md)|  | |

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

## `alertLimit2CustomerDeleteDefaultForConsumable()`

```php
alertLimit2CustomerDeleteDefaultForConsumable($customer_id, $supply_type, $color_type, $maintenance_kit_type_id, $maintenance_kit_color_id): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_id = 'customer_id_example'; // string
$supply_type = 'supply_type_example'; // string
$color_type = 'color_type_example'; // string
$maintenance_kit_type_id = 56; // int
$maintenance_kit_color_id = 56; // int

try {
    $result = $apiInstance->alertLimit2CustomerDeleteDefaultForConsumable($customer_id, $supply_type, $color_type, $maintenance_kit_type_id, $maintenance_kit_color_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2CustomerDeleteDefaultForConsumable: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_id** | **string**|  | [optional] |
| **supply_type** | **string**|  | [optional] |
| **color_type** | **string**|  | [optional] |
| **maintenance_kit_type_id** | **int**|  | [optional] |
| **maintenance_kit_color_id** | **int**|  | [optional] |

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

## `alertLimit2CustomerDeleteProduct()`

```php
alertLimit2CustomerDeleteProduct($customer_code, $id): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Gets or sets the CustomerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->alertLimit2CustomerDeleteProduct($customer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2CustomerDeleteProduct: ', $e->getMessage(), PHP_EOL;
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

## `alertLimit2CustomerGetDefault()`

```php
alertLimit2CustomerGetDefault($code): \OpenAPI\Client\Model\ListResultResponseAlertLimit2CustomerDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->alertLimit2CustomerGetDefault($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2CustomerGetDefault: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseAlertLimit2CustomerDto**](../Model/ListResultResponseAlertLimit2CustomerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimit2CustomerGetProduct()`

```php
alertLimit2CustomerGetProduct($customer_code, $id, $filter_text): \OpenAPI\Client\Model\ListResultResponseAlertLimit2CustomerProductDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Gets or sets the CustomerCode.
$id = 'id_example'; // string | Gets or sets the identifier.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->alertLimit2CustomerGetProduct($customer_code, $id, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2CustomerGetProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| Gets or sets the CustomerCode. | |
| **id** | **string**| Gets or sets the identifier. | |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseAlertLimit2CustomerProductDto**](../Model/ListResultResponseAlertLimit2CustomerProductDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimit2CustomerGetProductList()`

```php
alertLimit2CustomerGetProductList($code, $filter_text): \OpenAPI\Client\Model\ListResultResponseProductBaseDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the dealer code.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->alertLimit2CustomerGetProductList($code, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2CustomerGetProductList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the dealer code. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseProductBaseDto**](../Model/ListResultResponseProductBaseDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimit2CustomerUpdateDefault()`

```php
alertLimit2CustomerUpdateDefault($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerDto

try {
    $result = $apiInstance->alertLimit2CustomerUpdateDefault($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2CustomerUpdateDefault: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerDto**](../Model/UpdateRequestIEnumerableAlertLimit2CustomerDto.md)|  | |

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

## `alertLimit2CustomerUpdateProduct()`

```php
alertLimit2CustomerUpdateProduct($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerProductDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerProductDto

try {
    $result = $apiInstance->alertLimit2CustomerUpdateProduct($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2CustomerUpdateProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2CustomerProductDto**](../Model/UpdateRequestIEnumerableAlertLimit2CustomerProductDto.md)|  | |

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

## `alertLimit2DealerCreateDefault()`

```php
alertLimit2DealerCreateDefault($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerDto

try {
    $result = $apiInstance->alertLimit2DealerCreateDefault($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DealerCreateDefault: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerDto**](../Model/UpdateRequestIEnumerableAlertLimit2DealerDto.md)|  | |

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

## `alertLimit2DealerCreateProduct()`

```php
alertLimit2DealerCreateProduct($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerProductDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerProductDto

try {
    $result = $apiInstance->alertLimit2DealerCreateProduct($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DealerCreateProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerProductDto**](../Model/UpdateRequestIEnumerableAlertLimit2DealerProductDto.md)|  | |

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

## `alertLimit2DealerDeleteProduct()`

```php
alertLimit2DealerDeleteProduct($dealer_code, $id): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->alertLimit2DealerDeleteProduct($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DealerDeleteProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
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

## `alertLimit2DealerGetDefault()`

```php
alertLimit2DealerGetDefault($code): \OpenAPI\Client\Model\ListResultResponseAlertLimit2DealerDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->alertLimit2DealerGetDefault($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DealerGetDefault: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseAlertLimit2DealerDto**](../Model/ListResultResponseAlertLimit2DealerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimit2DealerGetProduct()`

```php
alertLimit2DealerGetProduct($dealer_code, $id, $filter_text): \OpenAPI\Client\Model\ListResultResponseAlertLimit2DealerProductDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->alertLimit2DealerGetProduct($dealer_code, $id, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DealerGetProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
| **id** | **string**| Gets or sets the identifier. | |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseAlertLimit2DealerProductDto**](../Model/ListResultResponseAlertLimit2DealerProductDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimit2DealerGetProductList()`

```php
alertLimit2DealerGetProductList($code, $filter_text): \OpenAPI\Client\Model\ListResultResponseProductBaseDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the dealer code.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->alertLimit2DealerGetProductList($code, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DealerGetProductList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the dealer code. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseProductBaseDto**](../Model/ListResultResponseProductBaseDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimit2DealerUpdateDefault()`

```php
alertLimit2DealerUpdateDefault($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerDto

try {
    $result = $apiInstance->alertLimit2DealerUpdateDefault($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DealerUpdateDefault: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerDto**](../Model/UpdateRequestIEnumerableAlertLimit2DealerDto.md)|  | |

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

## `alertLimit2DealerUpdateProduct()`

```php
alertLimit2DealerUpdateProduct($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerProductDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerProductDto

try {
    $result = $apiInstance->alertLimit2DealerUpdateProduct($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DealerUpdateProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DealerProductDto**](../Model/UpdateRequestIEnumerableAlertLimit2DealerProductDto.md)|  | |

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

## `alertLimit2DeviceCreateDefault()`

```php
alertLimit2DeviceCreateDefault($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DeviceDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DeviceDto

try {
    $result = $apiInstance->alertLimit2DeviceCreateDefault($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DeviceCreateDefault: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DeviceDto**](../Model/UpdateRequestIEnumerableAlertLimit2DeviceDto.md)|  | |

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

## `alertLimit2DeviceDeleteDefaultForConsumable()`

```php
alertLimit2DeviceDeleteDefaultForConsumable($device_id, $supply_type, $color_type, $maintenance_kit_type_id, $maintenance_kit_color_id): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$device_id = 'device_id_example'; // string
$supply_type = 'supply_type_example'; // string
$color_type = 'color_type_example'; // string
$maintenance_kit_type_id = 56; // int
$maintenance_kit_color_id = 56; // int

try {
    $result = $apiInstance->alertLimit2DeviceDeleteDefaultForConsumable($device_id, $supply_type, $color_type, $maintenance_kit_type_id, $maintenance_kit_color_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DeviceDeleteDefaultForConsumable: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **device_id** | **string**|  | [optional] |
| **supply_type** | **string**|  | [optional] |
| **color_type** | **string**|  | [optional] |
| **maintenance_kit_type_id** | **int**|  | [optional] |
| **maintenance_kit_color_id** | **int**|  | [optional] |

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

## `alertLimit2DeviceGetDefault()`

```php
alertLimit2DeviceGetDefault($id): \OpenAPI\Client\Model\ListResultResponseAlertLimit2DeviceDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->alertLimit2DeviceGetDefault($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DeviceGetDefault: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseAlertLimit2DeviceDto**](../Model/ListResultResponseAlertLimit2DeviceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `alertLimit2DeviceUpdateDefault()`

```php
alertLimit2DeviceUpdateDefault($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DeviceDto(); // \OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DeviceDto

try {
    $result = $apiInstance->alertLimit2DeviceUpdateDefault($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DeviceUpdateDefault: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestIEnumerableAlertLimit2DeviceDto**](../Model/UpdateRequestIEnumerableAlertLimit2DeviceDto.md)|  | |

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

## `alertLimit2DisableAlertLimits()`

```php
alertLimit2DisableAlertLimits($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DisableAlertLimitsRequest(); // \OpenAPI\Client\Model\DisableAlertLimitsRequest

try {
    $result = $apiInstance->alertLimit2DisableAlertLimits($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2DisableAlertLimits: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DisableAlertLimitsRequest**](../Model/DisableAlertLimitsRequest.md)|  | |

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

## `alertLimit2GetAllLimits()`

```php
alertLimit2GetAllLimits($dealer_id, $customer_id, $device_id, $product_id, $alert_limit_source): \OpenAPI\Client\Model\ListResultResponseAlertLimit2SourceDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AlertLimit2ApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_id = 'dealer_id_example'; // string
$customer_id = 'customer_id_example'; // string
$device_id = 'device_id_example'; // string
$product_id = 'product_id_example'; // string
$alert_limit_source = 'alert_limit_source_example'; // string

try {
    $result = $apiInstance->alertLimit2GetAllLimits($dealer_id, $customer_id, $device_id, $product_id, $alert_limit_source);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AlertLimit2ApiApi->alertLimit2GetAllLimits: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_id** | **string**|  | [optional] |
| **customer_id** | **string**|  | [optional] |
| **device_id** | **string**|  | [optional] |
| **product_id** | **string**|  | [optional] |
| **alert_limit_source** | **string**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseAlertLimit2SourceDto**](../Model/ListResultResponseAlertLimit2SourceDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
