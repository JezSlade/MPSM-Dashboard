# OpenAPI\Client\CustomerNotificationApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**customerNotificationApplyDealerTemplate()**](CustomerNotificationApiApi.md#customerNotificationApplyDealerTemplate) | **PUT** /CustomerNotification/ApplyDealerTemplate | Apply a dealer template to the customer notification |
| [**customerNotificationCreate()**](CustomerNotificationApiApi.md#customerNotificationCreate) | **POST** /CustomerNotification/Create | Create a customer notification |
| [**customerNotificationDelete()**](CustomerNotificationApiApi.md#customerNotificationDelete) | **DELETE** /CustomerNotification/Delete | Create a customer notification |
| [**customerNotificationGet()**](CustomerNotificationApiApi.md#customerNotificationGet) | **GET** /CustomerNotification/Get | Get notification |
| [**customerNotificationGetNotificationPlaceholders()**](CustomerNotificationApiApi.md#customerNotificationGetNotificationPlaceholders) | **GET** /CustomerNotification/GetNotificationPlaceholders | Get notification placeholders |
| [**customerNotificationGetSampleNotification()**](CustomerNotificationApiApi.md#customerNotificationGetSampleNotification) | **GET** /CustomerNotification/GetSampleNotification | Get sample notification |
| [**customerNotificationList()**](CustomerNotificationApiApi.md#customerNotificationList) | **GET** /CustomerNotification/List | GetNotificationList |
| [**customerNotificationUpdate()**](CustomerNotificationApiApi.md#customerNotificationUpdate) | **PUT** /CustomerNotification/Update | Get notification |


## `customerNotificationApplyDealerTemplate()`

```php
customerNotificationApplyDealerTemplate($request): \OpenAPI\Client\Model\BaseResponse
```

Apply a dealer template to the customer notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\ApplyDealerTemplateRequest(); // \OpenAPI\Client\Model\ApplyDealerTemplateRequest

try {
    $result = $apiInstance->customerNotificationApplyDealerTemplate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerNotificationApiApi->customerNotificationApplyDealerTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\ApplyDealerTemplateRequest**](../Model/ApplyDealerTemplateRequest.md)|  | |

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

## `customerNotificationCreate()`

```php
customerNotificationCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Create a customer notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateCustomerNotificationRequest(); // \OpenAPI\Client\Model\CreateCustomerNotificationRequest

try {
    $result = $apiInstance->customerNotificationCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerNotificationApiApi->customerNotificationCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateCustomerNotificationRequest**](../Model/CreateCustomerNotificationRequest.md)|  | |

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

## `customerNotificationDelete()`

```php
customerNotificationDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Create a customer notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->customerNotificationDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerNotificationApiApi->customerNotificationDelete: ', $e->getMessage(), PHP_EOL;
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

## `customerNotificationGet()`

```php
customerNotificationGet($id): \OpenAPI\Client\Model\SingleResultResponseNotificationDetailsDto
```

Get notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->customerNotificationGet($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerNotificationApiApi->customerNotificationGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseNotificationDetailsDto**](../Model/SingleResultResponseNotificationDetailsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customerNotificationGetNotificationPlaceholders()`

```php
customerNotificationGetNotificationPlaceholders($notification_type, $notification_mode): \OpenAPI\Client\Model\ListResultResponseNotificationPlaceholderDto
```

Get notification placeholders

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$notification_type = 'notification_type_example'; // string
$notification_mode = 'notification_mode_example'; // string

try {
    $result = $apiInstance->customerNotificationGetNotificationPlaceholders($notification_type, $notification_mode);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerNotificationApiApi->customerNotificationGetNotificationPlaceholders: ', $e->getMessage(), PHP_EOL;
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

## `customerNotificationGetSampleNotification()`

```php
customerNotificationGetSampleNotification($id): \OpenAPI\Client\Model\ListResultResponseCodeDesc
```

Get sample notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->customerNotificationGetSampleNotification($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerNotificationApiApi->customerNotificationGetSampleNotification: ', $e->getMessage(), PHP_EOL;
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

## `customerNotificationList()`

```php
customerNotificationList($code, $notification_type, $notification_mode, $language, $is_active): \OpenAPI\Client\Model\ListResultResponseNotificationListDto
```

GetNotificationList

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.
$notification_type = 'notification_type_example'; // string
$notification_mode = 'notification_mode_example'; // string
$language = 'language_example'; // string
$is_active = True; // bool

try {
    $result = $apiInstance->customerNotificationList($code, $notification_type, $notification_mode, $language, $is_active);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerNotificationApiApi->customerNotificationList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |
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

## `customerNotificationUpdate()`

```php
customerNotificationUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Get notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CustomerNotificationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateCustomerNotificationRequest(); // \OpenAPI\Client\Model\UpdateCustomerNotificationRequest

try {
    $result = $apiInstance->customerNotificationUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerNotificationApiApi->customerNotificationUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateCustomerNotificationRequest**](../Model/UpdateCustomerNotificationRequest.md)|  | |

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
