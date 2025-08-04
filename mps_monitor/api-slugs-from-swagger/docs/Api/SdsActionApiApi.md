# OpenAPI\Client\SdsActionApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**sdsActionChangeDeviceActionStatus()**](SdsActionApiApi.md#sdsActionChangeDeviceActionStatus) | **POST** /SdsAction/ChangeDeviceActionStatus | Changes the device action status. |
| [**sdsActionDeleteDeviceAction()**](SdsActionApiApi.md#sdsActionDeleteDeviceAction) | **DELETE** /SdsAction/DeleteDeviceAction |  |
| [**sdsActionGetDeviceAction()**](SdsActionApiApi.md#sdsActionGetDeviceAction) | **GET** /SdsAction/GetDeviceAction | Gets the device action. |
| [**sdsActionGetDeviceActions()**](SdsActionApiApi.md#sdsActionGetDeviceActions) | **GET** /SdsAction/GetDeviceActions | Gets the device actions. |
| [**sdsActionGetDeviceActionsDashboard()**](SdsActionApiApi.md#sdsActionGetDeviceActionsDashboard) | **GET** /SdsAction/GetDeviceActionsDashboard | Gets the device actions dashboard. |


## `sdsActionChangeDeviceActionStatus()`

```php
sdsActionChangeDeviceActionStatus($request): \OpenAPI\Client\Model\BaseResponse
```

Changes the device action status.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsActionApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\ChangeDeviceActionStatusRequest(); // \OpenAPI\Client\Model\ChangeDeviceActionStatusRequest | The request.

try {
    $result = $apiInstance->sdsActionChangeDeviceActionStatus($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsActionApiApi->sdsActionChangeDeviceActionStatus: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\ChangeDeviceActionStatusRequest**](../Model/ChangeDeviceActionStatusRequest.md)| The request. | |

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

## `sdsActionDeleteDeviceAction()`

```php
sdsActionDeleteDeviceAction($id): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsActionApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsActionDeleteDeviceAction($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsActionApiApi->sdsActionDeleteDeviceAction: ', $e->getMessage(), PHP_EOL;
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

## `sdsActionGetDeviceAction()`

```php
sdsActionGetDeviceAction($id): \OpenAPI\Client\Model\SingleResultResponseSdsDeviceActionDto
```

Gets the device action.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsActionApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsActionGetDeviceAction($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsActionApiApi->sdsActionGetDeviceAction: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSdsDeviceActionDto**](../Model/SingleResultResponseSdsDeviceActionDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsActionGetDeviceActions()`

```php
sdsActionGetDeviceActions($page_number, $page_rows, $sort_column, $sort_order, $device_id, $dealer_id, $customer_id, $dealer_code, $customer_code, $state, $is_open, $severity, $is_predictive, $action_type, $filter_text): \OpenAPI\Client\Model\PagedResultResponseSdsDeviceActionDto
```

Gets the device actions.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsActionApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$device_id = 'device_id_example'; // string | Gets or sets the device identifier.
$dealer_id = 'dealer_id_example'; // string | Gets or sets the dealer identifier.
$customer_id = 'customer_id_example'; // string | Gets or sets the customer identifier.
$dealer_code = 'dealer_code_example'; // string | Gets or sets the dealer identifier.
$customer_code = 'customer_code_example'; // string | Gets or sets the customer identifier.
$state = 'state_example'; // string | Gets or sets the state.
$is_open = True; // bool | Gets or sets the is open.
$severity = 'severity_example'; // string | Gets or sets the severity.
$is_predictive = True; // bool | Gets or sets the is predictive.
$action_type = 'action_type_example'; // string | Gets or sets the type of the action.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->sdsActionGetDeviceActions($page_number, $page_rows, $sort_column, $sort_order, $device_id, $dealer_id, $customer_id, $dealer_code, $customer_code, $state, $is_open, $severity, $is_predictive, $action_type, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsActionApiApi->sdsActionGetDeviceActions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **device_id** | **string**| Gets or sets the device identifier. | [optional] |
| **dealer_id** | **string**| Gets or sets the dealer identifier. | [optional] |
| **customer_id** | **string**| Gets or sets the customer identifier. | [optional] |
| **dealer_code** | **string**| Gets or sets the dealer identifier. | [optional] |
| **customer_code** | **string**| Gets or sets the customer identifier. | [optional] |
| **state** | **string**| Gets or sets the state. | [optional] |
| **is_open** | **bool**| Gets or sets the is open. | [optional] |
| **severity** | **string**| Gets or sets the severity. | [optional] |
| **is_predictive** | **bool**| Gets or sets the is predictive. | [optional] |
| **action_type** | **string**| Gets or sets the type of the action. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSdsDeviceActionDto**](../Model/PagedResultResponseSdsDeviceActionDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsActionGetDeviceActionsDashboard()`

```php
sdsActionGetDeviceActionsDashboard($device_id, $dealer_id, $group, $state, $is_open, $severity, $action_type, $is_predictive, $filter_text): \OpenAPI\Client\Model\ListResultResponseDashboardItemDto
```

Gets the device actions dashboard.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsActionApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$device_id = 'device_id_example'; // string | Gets or sets the device identifier.
$dealer_id = 'dealer_id_example'; // string | Gets or sets the dealer identifier.
$group = 'group_example'; // string | Gets or sets the group.
$state = 'state_example'; // string | Gets or sets the state.
$is_open = True; // bool | Gets or sets the is open.
$severity = 'severity_example'; // string | Gets or sets the severity.
$action_type = 'action_type_example'; // string | Gets or sets the type of the action.
$is_predictive = True; // bool | Gets or sets the is predictive.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->sdsActionGetDeviceActionsDashboard($device_id, $dealer_id, $group, $state, $is_open, $severity, $action_type, $is_predictive, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsActionApiApi->sdsActionGetDeviceActionsDashboard: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **device_id** | **string**| Gets or sets the device identifier. | [optional] |
| **dealer_id** | **string**| Gets or sets the dealer identifier. | [optional] |
| **group** | **string**| Gets or sets the group. | [optional] |
| **state** | **string**| Gets or sets the state. | [optional] |
| **is_open** | **bool**| Gets or sets the is open. | [optional] |
| **severity** | **string**| Gets or sets the severity. | [optional] |
| **action_type** | **string**| Gets or sets the type of the action. | [optional] |
| **is_predictive** | **bool**| Gets or sets the is predictive. | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseDashboardItemDto**](../Model/ListResultResponseDashboardItemDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
