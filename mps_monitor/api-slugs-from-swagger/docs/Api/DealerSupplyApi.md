# OpenAPI\Client\DealerSupplyApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**dealerSupplyCount()**](DealerSupplyApi.md#dealerSupplyCount) | **GET** /DealerSupply/Count | Returns list of dealer supplies count |
| [**dealerSupplyCreate()**](DealerSupplyApi.md#dealerSupplyCreate) | **POST** /DealerSupply/Create | Create the dealer supply. |
| [**dealerSupplyCreateFromProjectVolumes()**](DealerSupplyApi.md#dealerSupplyCreateFromProjectVolumes) | **POST** /DealerSupply/CreateFromProjectVolumes | Create from project volumes |
| [**dealerSupplyCreateFromStandardSupply()**](DealerSupplyApi.md#dealerSupplyCreateFromStandardSupply) | **POST** /DealerSupply/CreateFromStandardSupply | Create the dealer supply from the standardSupply. |
| [**dealerSupplyDelete()**](DealerSupplyApi.md#dealerSupplyDelete) | **DELETE** /DealerSupply/Delete | Deletes the specified supply. |
| [**dealerSupplyExport()**](DealerSupplyApi.md#dealerSupplyExport) | **GET** /DealerSupply/Export | Returns list of dealer supplies |
| [**dealerSupplyGet()**](DealerSupplyApi.md#dealerSupplyGet) | **GET** /DealerSupply/Get | Gets the dealer supply. |
| [**dealerSupplyList()**](DealerSupplyApi.md#dealerSupplyList) | **GET** /DealerSupply/List | Returns list of dealer supplies |
| [**dealerSupplyListSuggested()**](DealerSupplyApi.md#dealerSupplyListSuggested) | **POST** /DealerSupply/ListSuggested | Returns list of suggested standard supplies for the given product ids |
| [**dealerSupplyUpdate()**](DealerSupplyApi.md#dealerSupplyUpdate) | **PUT** /DealerSupply/Update | Update the dealer supply. |
| [**dealerSupplyUploadSupplies()**](DealerSupplyApi.md#dealerSupplyUploadSupplies) | **POST** /DealerSupply/UploadSupplies | Gets the dealer supply. |


## `dealerSupplyCount()`

```php
dealerSupplyCount($code): \OpenAPI\Client\Model\BaseResponse
```

Returns list of dealer supplies count

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerSupplyCount($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyCount: ', $e->getMessage(), PHP_EOL;
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

## `dealerSupplyCreate()`

```php
dealerSupplyCreate($request): \OpenAPI\Client\Model\SingleResultResponseDealerSupplyDto
```

Create the dealer supply.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestDealerSupplyDto(); // \OpenAPI\Client\Model\CreateRequestDealerSupplyDto | The request.

try {
    $result = $apiInstance->dealerSupplyCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestDealerSupplyDto**](../Model/CreateRequestDealerSupplyDto.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupplyDto**](../Model/SingleResultResponseDealerSupplyDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplyCreateFromProjectVolumes()`

```php
dealerSupplyCreateFromProjectVolumes($request): \OpenAPI\Client\Model\BaseResponse
```

Create from project volumes

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByCodeRequest(); // \OpenAPI\Client\Model\GetByCodeRequest | The request.

try {
    $result = $apiInstance->dealerSupplyCreateFromProjectVolumes($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyCreateFromProjectVolumes: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByCodeRequest**](../Model/GetByCodeRequest.md)| The request. | |

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

## `dealerSupplyCreateFromStandardSupply()`

```php
dealerSupplyCreateFromStandardSupply($request): \OpenAPI\Client\Model\BaseResponse
```

Create the dealer supply from the standardSupply.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateFromStandardSupplyRequest(); // \OpenAPI\Client\Model\CreateFromStandardSupplyRequest | The request.

try {
    $result = $apiInstance->dealerSupplyCreateFromStandardSupply($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyCreateFromStandardSupply: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateFromStandardSupplyRequest**](../Model/CreateFromStandardSupplyRequest.md)| The request. | |

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

## `dealerSupplyDelete()`

```php
dealerSupplyDelete($id, $code): \OpenAPI\Client\Model\BaseResponse
```

Deletes the specified supply.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerSupplyDelete($id, $code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **code** | **string**| Gets or sets the code. | [optional] |

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

## `dealerSupplyExport()`

```php
dealerSupplyExport($code, $page_number, $page_rows, $sort_column, $sort_order, $color_type, $color, $supply_type, $language, $filter_text): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

Returns list of dealer supplies

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$color_type = 'color_type_example'; // string
$color = 'color_example'; // string
$supply_type = 'supply_type_example'; // string
$language = 'language_example'; // string
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->dealerSupplyExport($code, $page_number, $page_rows, $sort_column, $sort_order, $color_type, $color, $supply_type, $language, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyExport: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **color_type** | **string**|  | [optional] |
| **color** | **string**|  | [optional] |
| **supply_type** | **string**|  | [optional] |
| **language** | **string**|  | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

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

## `dealerSupplyGet()`

```php
dealerSupplyGet($id, $code): \OpenAPI\Client\Model\SingleResultResponseDealerSupplyDto
```

Gets the dealer supply.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerSupplyGet($id, $code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **code** | **string**| Gets or sets the code. | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupplyDto**](../Model/SingleResultResponseDealerSupplyDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplyList()`

```php
dealerSupplyList($code, $page_number, $page_rows, $sort_column, $sort_order, $color_type, $color, $supply_type, $language, $filter_text): \OpenAPI\Client\Model\PagedResultResponseDealerSupplyListDto
```

Returns list of dealer supplies

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$color_type = 'color_type_example'; // string
$color = 'color_example'; // string
$supply_type = 'supply_type_example'; // string
$language = 'language_example'; // string
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->dealerSupplyList($code, $page_number, $page_rows, $sort_column, $sort_order, $color_type, $color, $supply_type, $language, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **color_type** | **string**|  | [optional] |
| **color** | **string**|  | [optional] |
| **supply_type** | **string**|  | [optional] |
| **language** | **string**|  | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDealerSupplyListDto**](../Model/PagedResultResponseDealerSupplyListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplyListSuggested()`

```php
dealerSupplyListSuggested($request): \OpenAPI\Client\Model\ListResultResponseStandardSupplyDto
```

Returns list of suggested standard supplies for the given product ids

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetSuggestedStandardSuppliesRequest(); // \OpenAPI\Client\Model\GetSuggestedStandardSuppliesRequest

try {
    $result = $apiInstance->dealerSupplyListSuggested($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyListSuggested: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetSuggestedStandardSuppliesRequest**](../Model/GetSuggestedStandardSuppliesRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseStandardSupplyDto**](../Model/ListResultResponseStandardSupplyDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplyUpdate()`

```php
dealerSupplyUpdate($request): \OpenAPI\Client\Model\SingleResultResponseDealerSupplyDto
```

Update the dealer supply.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerSupplyDto(); // \OpenAPI\Client\Model\UpdateRequestDealerSupplyDto | The request.

try {
    $result = $apiInstance->dealerSupplyUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerSupplyDto**](../Model/UpdateRequestDealerSupplyDto.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupplyDto**](../Model/SingleResultResponseDealerSupplyDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplyUploadSupplies()`

```php
dealerSupplyUploadSupplies($request): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

Gets the dealer supply.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplyApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UploadSuppliesRequest(); // \OpenAPI\Client\Model\UploadSuppliesRequest | The request.

try {
    $result = $apiInstance->dealerSupplyUploadSupplies($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplyApi->dealerSupplyUploadSupplies: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UploadSuppliesRequest**](../Model/UploadSuppliesRequest.md)| The request. | |

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
