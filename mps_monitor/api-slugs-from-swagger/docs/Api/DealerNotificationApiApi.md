# OpenAPI\Client\DealerNotificationApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**dealerNotificationCreate()**](DealerNotificationApiApi.md#dealerNotificationCreate) | **POST** /DealerNotification/Create | Create a dealer notification |
| [**dealerNotificationDelete()**](DealerNotificationApiApi.md#dealerNotificationDelete) | **DELETE** /DealerNotification/Delete | Create a dealer notification |
| [**dealerNotificationGet()**](DealerNotificationApiApi.md#dealerNotificationGet) | **GET** /DealerNotification/Get | Get notification |
| [**dealerNotificationGetNotificationPlaceholders()**](DealerNotificationApiApi.md#dealerNotificationGetNotificationPlaceholders) | **GET** /DealerNotification/GetNotificationPlaceholders | Get notification placeholders |
| [**dealerNotificationGetSampleNotification()**](DealerNotificationApiApi.md#dealerNotificationGetSampleNotification) | **GET** /DealerNotification/GetSampleNotification | Get sample notification |
| [**dealerNotificationList()**](DealerNotificationApiApi.md#dealerNotificationList) | **GET** /DealerNotification/List |  |
| [**dealerNotificationRemoveMailAddressFromCustomers()**](DealerNotificationApiApi.md#dealerNotificationRemoveMailAddressFromCustomers) | **PUT** /DealerNotification/RemoveMailAddressFromCustomers | Remove EMail Address From Customers notifications fields |
| [**dealerNotificationTemplateGet()**](DealerNotificationApiApi.md#dealerNotificationTemplateGet) | **GET** /DealerNotification/Template/Get | Get the dealer template base |
| [**dealerNotificationTemplateUpdate()**](DealerNotificationApiApi.md#dealerNotificationTemplateUpdate) | **PUT** /DealerNotification/Template/Update | Update the dealer template base |
| [**dealerNotificationUpdate()**](DealerNotificationApiApi.md#dealerNotificationUpdate) | **PUT** /DealerNotification/Update | Get notification |


## `dealerNotificationCreate()`

```php
dealerNotificationCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Create a dealer notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateDealerNotificationRequest(); // \OpenAPI\Client\Model\CreateDealerNotificationRequest

try {
    $result = $apiInstance->dealerNotificationCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerNotificationApiApi->dealerNotificationCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateDealerNotificationRequest**](../Model/CreateDealerNotificationRequest.md)|  | |

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

## `dealerNotificationDelete()`

```php
dealerNotificationDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Create a dealer notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerNotificationDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerNotificationApiApi->dealerNotificationDelete: ', $e->getMessage(), PHP_EOL;
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

## `dealerNotificationGet()`

```php
dealerNotificationGet($id): \OpenAPI\Client\Model\SingleResultResponseDealerNotificationDto2
```

Get notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerNotificationGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerNotificationApiApi->dealerNotificationGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerNotificationDto2**](../Model/SingleResultResponseDealerNotificationDto2.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerNotificationGetNotificationPlaceholders()`

```php
dealerNotificationGetNotificationPlaceholders($notification_type, $notification_mode): \OpenAPI\Client\Model\ListResultResponseNotificationPlaceholderDto
```

Get notification placeholders

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$notification_type = 'notification_type_example'; // string
$notification_mode = 'notification_mode_example'; // string

try {
    $result = $apiInstance->dealerNotificationGetNotificationPlaceholders($notification_type, $notification_mode);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerNotificationApiApi->dealerNotificationGetNotificationPlaceholders: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **notification_type** | **string**|  | [optional] |
| **notification_mode** | **string**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseNotificationPlaceholderDto**](../Model/ListResultResponseNotificationPlaceholderDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerNotificationGetSampleNotification()`

```php
dealerNotificationGetSampleNotification($id): \OpenAPI\Client\Model\ListResultResponseCodeDesc
```

Get sample notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerNotificationGetSampleNotification($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerNotificationApiApi->dealerNotificationGetSampleNotification: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseCodeDesc**](../Model/ListResultResponseCodeDesc.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerNotificationList()`

```php
dealerNotificationList($dealer_code, $notification_type, $notification_mode, $language, $is_active): \OpenAPI\Client\Model\ListResultResponseNotificationListDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.
$notification_type = 'notification_type_example'; // string
$notification_mode = 'notification_mode_example'; // string
$language = 'language_example'; // string
$is_active = True; // bool

try {
    $result = $apiInstance->dealerNotificationList($dealer_code, $notification_type, $notification_mode, $language, $is_active);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerNotificationApiApi->dealerNotificationList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the code. | |
| **notification_type** | **string**|  | [optional] |
| **notification_mode** | **string**|  | [optional] |
| **language** | **string**|  | [optional] |
| **is_active** | **bool**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseNotificationListDto**](../Model/ListResultResponseNotificationListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerNotificationRemoveMailAddressFromCustomers()`

```php
dealerNotificationRemoveMailAddressFromCustomers($request): \OpenAPI\Client\Model\BaseResponse
```

Remove EMail Address From Customers notifications fields

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DealerRemoveMailAddressFromCustomersRequest(); // \OpenAPI\Client\Model\DealerRemoveMailAddressFromCustomersRequest

try {
    $result = $apiInstance->dealerNotificationRemoveMailAddressFromCustomers($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerNotificationApiApi->dealerNotificationRemoveMailAddressFromCustomers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DealerRemoveMailAddressFromCustomersRequest**](../Model/DealerRemoveMailAddressFromCustomersRequest.md)|  | |

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

## `dealerNotificationTemplateGet()`

```php
dealerNotificationTemplateGet($code): \OpenAPI\Client\Model\SingleResultResponseDealerNotificationTemplateDto
```

Get the dealer template base

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerNotificationTemplateGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerNotificationApiApi->dealerNotificationTemplateGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerNotificationTemplateDto**](../Model/SingleResultResponseDealerNotificationTemplateDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerNotificationTemplateUpdate()`

```php
dealerNotificationTemplateUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Update the dealer template base

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerNotificationTemplateDto(); // \OpenAPI\Client\Model\UpdateRequestDealerNotificationTemplateDto

try {
    $result = $apiInstance->dealerNotificationTemplateUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerNotificationApiApi->dealerNotificationTemplateUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerNotificationTemplateDto**](../Model/UpdateRequestDealerNotificationTemplateDto.md)|  | |

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

## `dealerNotificationUpdate()`

```php
dealerNotificationUpdate($request): \OpenAPI\Client\Model\ListResultResponseCodeDesc
```

Get notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateDealerNotificationRequest(); // \OpenAPI\Client\Model\UpdateDealerNotificationRequest

try {
    $result = $apiInstance->dealerNotificationUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerNotificationApiApi->dealerNotificationUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateDealerNotificationRequest**](../Model/UpdateDealerNotificationRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseCodeDesc**](../Model/ListResultResponseCodeDesc.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
