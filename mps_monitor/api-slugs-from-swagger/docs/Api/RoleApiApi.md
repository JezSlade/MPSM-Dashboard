# OpenAPI\Client\RoleApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**roleCopy()**](RoleApiApi.md#roleCopy) | **POST** /Role/Copy | Copies the specified request. |
| [**roleCreate()**](RoleApiApi.md#roleCreate) | **POST** /Role/Create | Create a custom capability set (custom role) |
| [**roleDelete()**](RoleApiApi.md#roleDelete) | **DELETE** /Role/Delete | Delete a custom capability set (custom role) |
| [**roleGet()**](RoleApiApi.md#roleGet) | **GET** /Role/Get | Get a capability set role by Id and Dealer Code |
| [**roleGetAllCapabilities()**](RoleApiApi.md#roleGetAllCapabilities) | **GET** /Role/GetAllCapabilities | Get the all available capabilities |
| [**roleList()**](RoleApiApi.md#roleList) | **GET** /Role/List | Get the list of available capability sets (roles) |
| [**roleUpdate()**](RoleApiApi.md#roleUpdate) | **PUT** /Role/Update | Update a custom capability set (custom role) |


## `roleCopy()`

```php
roleCopy($request): \OpenAPI\Client\Model\SingleResultResponseRoleDto
```

Copies the specified request.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\RoleApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CopyRoleRequest(); // \OpenAPI\Client\Model\CopyRoleRequest | The request.

try {
    $result = $apiInstance->roleCopy($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RoleApiApi->roleCopy: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CopyRoleRequest**](../Model/CopyRoleRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseRoleDto**](../Model/SingleResultResponseRoleDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `roleCreate()`

```php
roleCreate($request): \OpenAPI\Client\Model\SingleResultResponseRoleDto
```

Create a custom capability set (custom role)

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\RoleApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestRoleDto(); // \OpenAPI\Client\Model\CreateRequestRoleDto

try {
    $result = $apiInstance->roleCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RoleApiApi->roleCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestRoleDto**](../Model/CreateRequestRoleDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseRoleDto**](../Model/SingleResultResponseRoleDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `roleDelete()`

```php
roleDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Delete a custom capability set (custom role)

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\RoleApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->roleDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RoleApiApi->roleDelete: ', $e->getMessage(), PHP_EOL;
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

## `roleGet()`

```php
roleGet($dealer_code, $id): \OpenAPI\Client\Model\SingleResultResponseRoleDto
```

Get a capability set role by Id and Dealer Code

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\RoleApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->roleGet($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RoleApiApi->roleGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseRoleDto**](../Model/SingleResultResponseRoleDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `roleGetAllCapabilities()`

```php
roleGetAllCapabilities($is_for_account, $code): \OpenAPI\Client\Model\ListResultResponseCapabilityDto
```

Get the all available capabilities

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\RoleApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$is_for_account = True; // bool
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->roleGetAllCapabilities($is_for_account, $code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RoleApiApi->roleGetAllCapabilities: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **is_for_account** | **bool**|  | |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseCapabilityDto**](../Model/ListResultResponseCapabilityDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `roleList()`

```php
roleList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $show_only_roles_for_customer, $filter_text): \OpenAPI\Client\Model\PagedResultResponseRoleDto
```

Get the list of available capability sets (roles)

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\RoleApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$show_only_roles_for_customer = True; // bool
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->roleList($dealer_code, $page_number, $page_rows, $sort_column, $sort_order, $show_only_roles_for_customer, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RoleApiApi->roleList: ', $e->getMessage(), PHP_EOL;
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
| **show_only_roles_for_customer** | **bool**|  | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseRoleDto**](../Model/PagedResultResponseRoleDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `roleUpdate()`

```php
roleUpdate($request): \OpenAPI\Client\Model\SingleResultResponseRoleDto
```

Update a custom capability set (custom role)

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\RoleApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestRoleDto(); // \OpenAPI\Client\Model\UpdateRequestRoleDto

try {
    $result = $apiInstance->roleUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RoleApiApi->roleUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestRoleDto**](../Model/UpdateRequestRoleDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseRoleDto**](../Model/SingleResultResponseRoleDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
