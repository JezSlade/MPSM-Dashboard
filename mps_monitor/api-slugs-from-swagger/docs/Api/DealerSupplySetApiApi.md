# OpenAPI\Client\DealerSupplySetApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**dealerSupplySetAssociateByDealerSupplySetAndRelativeProducts()**](DealerSupplySetApiApi.md#dealerSupplySetAssociateByDealerSupplySetAndRelativeProducts) | **GET** /DealerSupplySet/AssociateByDealerSupplySetAndRelativeProducts | Automatically associate the devices (ONLY with a specific model) to a specific SupplySet (the customer is optional) |
| [**dealerSupplySetCount()**](DealerSupplySetApiApi.md#dealerSupplySetCount) | **GET** /DealerSupplySet/Count | Gets the Dealer Supplies set count. |
| [**dealerSupplySetCountDealerSupplySetAndDevicesPotentialAssociations()**](DealerSupplySetApiApi.md#dealerSupplySetCountDealerSupplySetAndDevicesPotentialAssociations) | **GET** /DealerSupplySet/CountDealerSupplySetAndDevicesPotentialAssociations | Count the devices affected by the association of a supply set (the customer is optional) |
| [**dealerSupplySetCreateFromProjectVolumes()**](DealerSupplySetApiApi.md#dealerSupplySetCreateFromProjectVolumes) | **POST** /DealerSupplySet/CreateFromProjectVolumes | Create from project volumes |
| [**dealerSupplySetCreateFromStandardModels()**](DealerSupplySetApiApi.md#dealerSupplySetCreateFromStandardModels) | **POST** /DealerSupplySet/CreateFromStandardModels | Create from standard models |
| [**dealerSupplySetDelete()**](DealerSupplySetApiApi.md#dealerSupplySetDelete) | **DELETE** /DealerSupplySet/Delete | Deletes the specified supply set. |
| [**dealerSupplySetExport()**](DealerSupplySetApiApi.md#dealerSupplySetExport) | **GET** /DealerSupplySet/Export | Deletes the specified supply set. |
| [**dealerSupplySetExportExcel()**](DealerSupplySetApiApi.md#dealerSupplySetExportExcel) | **GET** /DealerSupplySet/ExportExcel | Deletes the specified supply set. |
| [**dealerSupplySetGet()**](DealerSupplySetApiApi.md#dealerSupplySetGet) | **GET** /DealerSupplySet/Get | Gets the Dealer Supply Set. |
| [**dealerSupplySetImport()**](DealerSupplySetApiApi.md#dealerSupplySetImport) | **POST** /DealerSupplySet/Import | Deletes the specified supply set. |
| [**dealerSupplySetList()**](DealerSupplySetApiApi.md#dealerSupplySetList) | **GET** /DealerSupplySet/List | Gets the Dealer Supplies set. |
| [**dealerSupplySetListDealerSupplySetFromStandardModels()**](DealerSupplySetApiApi.md#dealerSupplySetListDealerSupplySetFromStandardModels) | **GET** /DealerSupplySet/ListDealerSupplySetFromStandardModels | Gets the Supplies set creatable from standard model. |
| [**dealerSupplySetSaveSupplySet()**](DealerSupplySetApiApi.md#dealerSupplySetSaveSupplySet) | **PUT** /DealerSupplySet/SaveSupplySet | Saves the supply set. |
| [**dealerSupplySetUploadSupplySet()**](DealerSupplySetApiApi.md#dealerSupplySetUploadSupplySet) | **POST** /DealerSupplySet/UploadSupplySet | Gets the dealer supply set. |


## `dealerSupplySetAssociateByDealerSupplySetAndRelativeProducts()`

```php
dealerSupplySetAssociateByDealerSupplySetAndRelativeProducts($dealer_code, $id_dealer_supply_set, $id_customer): \OpenAPI\Client\Model\BaseResponse
```

Automatically associate the devices (ONLY with a specific model) to a specific SupplySet (the customer is optional)

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string
$id_dealer_supply_set = 'id_dealer_supply_set_example'; // string
$id_customer = 'id_customer_example'; // string

try {
    $result = $apiInstance->dealerSupplySetAssociateByDealerSupplySetAndRelativeProducts($dealer_code, $id_dealer_supply_set, $id_customer);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetAssociateByDealerSupplySetAndRelativeProducts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**|  | [optional] |
| **id_dealer_supply_set** | **string**|  | [optional] |
| **id_customer** | **string**|  | [optional] |

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

## `dealerSupplySetCount()`

```php
dealerSupplySetCount($code): \OpenAPI\Client\Model\BaseResponse
```

Gets the Dealer Supplies set count.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerSupplySetCount($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetCount: ', $e->getMessage(), PHP_EOL;
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

## `dealerSupplySetCountDealerSupplySetAndDevicesPotentialAssociations()`

```php
dealerSupplySetCountDealerSupplySetAndDevicesPotentialAssociations($dealer_code, $id_dealer_supply_set, $id_customer): \OpenAPI\Client\Model\BaseResponse
```

Count the devices affected by the association of a supply set (the customer is optional)

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string
$id_dealer_supply_set = 'id_dealer_supply_set_example'; // string
$id_customer = 'id_customer_example'; // string

try {
    $result = $apiInstance->dealerSupplySetCountDealerSupplySetAndDevicesPotentialAssociations($dealer_code, $id_dealer_supply_set, $id_customer);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetCountDealerSupplySetAndDevicesPotentialAssociations: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**|  | [optional] |
| **id_dealer_supply_set** | **string**|  | [optional] |
| **id_customer** | **string**|  | [optional] |

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

## `dealerSupplySetCreateFromProjectVolumes()`

```php
dealerSupplySetCreateFromProjectVolumes($request): \OpenAPI\Client\Model\BaseResponse
```

Create from project volumes

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByCodeRequest(); // \OpenAPI\Client\Model\GetByCodeRequest | The request.

try {
    $result = $apiInstance->dealerSupplySetCreateFromProjectVolumes($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetCreateFromProjectVolumes: ', $e->getMessage(), PHP_EOL;
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

## `dealerSupplySetCreateFromStandardModels()`

```php
dealerSupplySetCreateFromStandardModels($request): \OpenAPI\Client\Model\BaseResponse
```

Create from standard models

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateDealerSupplySetFromStandardModelsRequest(); // \OpenAPI\Client\Model\CreateDealerSupplySetFromStandardModelsRequest | The request.

try {
    $result = $apiInstance->dealerSupplySetCreateFromStandardModels($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetCreateFromStandardModels: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateDealerSupplySetFromStandardModelsRequest**](../Model/CreateDealerSupplySetFromStandardModelsRequest.md)| The request. | |

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

## `dealerSupplySetDelete()`

```php
dealerSupplySetDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Deletes the specified supply set.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerSupplySetDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetDelete: ', $e->getMessage(), PHP_EOL;
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

## `dealerSupplySetExport()`

```php
dealerSupplySetExport($code): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

Deletes the specified supply set.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerSupplySetExport($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetExport: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

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

## `dealerSupplySetExportExcel()`

```php
dealerSupplySetExportExcel($code): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

Deletes the specified supply set.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerSupplySetExportExcel($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetExportExcel: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

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

## `dealerSupplySetGet()`

```php
dealerSupplySetGet($id): \OpenAPI\Client\Model\SingleResultResponseDealerSupplySetDto
```

Gets the Dealer Supply Set.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerSupplySetGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupplySetDto**](../Model/SingleResultResponseDealerSupplySetDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplySetImport()`

```php
dealerSupplySetImport($request): \OpenAPI\Client\Model\BaseResponse
```

Deletes the specified supply set.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UploadSupplySetRequest(); // \OpenAPI\Client\Model\UploadSupplySetRequest | The request.

try {
    $result = $apiInstance->dealerSupplySetImport($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetImport: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UploadSupplySetRequest**](../Model/UploadSupplySetRequest.md)| The request. | |

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

## `dealerSupplySetList()`

```php
dealerSupplySetList($page_number, $page_rows, $sort_column, $sort_order, $brand, $model, $has_devices_to_associate, $is_standard, $dealer_code, $filter_text): \OpenAPI\Client\Model\PagedResultResponseDealerSupplySetForListDto
```

Gets the Dealer Supplies set.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$brand = 'brand_example'; // string | Gets or sets the brand.
$model = 'model_example'; // string | Gets or sets the model.
$has_devices_to_associate = True; // bool | Gets or sets Has Devices To Associate flag.
$is_standard = True; // bool | Gets or sets Is standard flag.
$dealer_code = 'dealer_code_example'; // string | Gets or sets the dealer code.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->dealerSupplySetList($page_number, $page_rows, $sort_column, $sort_order, $brand, $model, $has_devices_to_associate, $is_standard, $dealer_code, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **brand** | **string**| Gets or sets the brand. | [optional] |
| **model** | **string**| Gets or sets the model. | [optional] |
| **has_devices_to_associate** | **bool**| Gets or sets Has Devices To Associate flag. | [optional] |
| **is_standard** | **bool**| Gets or sets Is standard flag. | [optional] |
| **dealer_code** | **string**| Gets or sets the dealer code. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDealerSupplySetForListDto**](../Model/PagedResultResponseDealerSupplySetForListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplySetListDealerSupplySetFromStandardModels()`

```php
dealerSupplySetListDealerSupplySetFromStandardModels($dealer_code): \OpenAPI\Client\Model\ListResultResponseDealerSupplySetFromStandardModelDto
```

Gets the Supplies set creatable from standard model.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerSupplySetListDealerSupplySetFromStandardModels($dealer_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetListDealerSupplySetFromStandardModels: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseDealerSupplySetFromStandardModelDto**](../Model/ListResultResponseDealerSupplySetFromStandardModelDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplySetSaveSupplySet()`

```php
dealerSupplySetSaveSupplySet($request): \OpenAPI\Client\Model\SingleResultResponseDealerSupplySetDto
```

Saves the supply set.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerSupplySetDto(); // \OpenAPI\Client\Model\UpdateRequestDealerSupplySetDto | The request.

try {
    $result = $apiInstance->dealerSupplySetSaveSupplySet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetSaveSupplySet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerSupplySetDto**](../Model/UpdateRequestDealerSupplySetDto.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupplySetDto**](../Model/SingleResultResponseDealerSupplySetDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerSupplySetUploadSupplySet()`

```php
dealerSupplySetUploadSupplySet($request): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

Gets the dealer supply set.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerSupplySetApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UploadSupplySetRequest(); // \OpenAPI\Client\Model\UploadSupplySetRequest | The request.

try {
    $result = $apiInstance->dealerSupplySetUploadSupplySet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerSupplySetApiApi->dealerSupplySetUploadSupplySet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UploadSupplySetRequest**](../Model/UploadSupplySetRequest.md)| The request. | |

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
