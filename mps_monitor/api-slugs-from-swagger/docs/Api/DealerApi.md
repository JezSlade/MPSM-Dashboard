# OpenAPI\Client\DealerApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**dealerAccountingSettingsGet()**](DealerApi.md#dealerAccountingSettingsGet) | **GET** /Dealer/AccountingSettings/Get | Gets the dealer accounting settings. |
| [**dealerAccountingSettingsUpdate()**](DealerApi.md#dealerAccountingSettingsUpdate) | **PUT** /Dealer/AccountingSettings/Update | set the dealer accounting settings. |
| [**dealerAdvancedOptionsGet()**](DealerApi.md#dealerAdvancedOptionsGet) | **GET** /Dealer/AdvancedOptions/Get | Gets the dealer advanced options |
| [**dealerAlertLimitOptionsGet()**](DealerApi.md#dealerAlertLimitOptionsGet) | **GET** /Dealer/AlertLimitOptions/Get | Gets the alert limit options. |
| [**dealerAlertSettingsGet()**](DealerApi.md#dealerAlertSettingsGet) | **GET** /Dealer/AlertSettings/Get | Gets the dealer alert settings |
| [**dealerAlertSettingsUpdate()**](DealerApi.md#dealerAlertSettingsUpdate) | **PUT** /Dealer/AlertSettings/Update | set the dealer alert settings |
| [**dealerCustomizationsGet()**](DealerApi.md#dealerCustomizationsGet) | **GET** /Dealer/Customizations/Get | Gets the dealer customizations. |
| [**dealerCustomizationsUpdate()**](DealerApi.md#dealerCustomizationsUpdate) | **PUT** /Dealer/Customizations/Update | set the dealer customizations. |
| [**dealerDealerServicesStatusGet()**](DealerApi.md#dealerDealerServicesStatusGet) | **GET** /Dealer/DealerServicesStatus/Get | Gets the dealer services status. |
| [**dealerDeclineDemoRequest()**](DealerApi.md#dealerDeclineDemoRequest) | **PUT** /Dealer/DeclineDemoRequest | Decline demo request |
| [**dealerDemoRequestGet()**](DealerApi.md#dealerDemoRequestGet) | **GET** /Dealer/DemoRequest/Get | GetDemoRequest |
| [**dealerDemoRequestList()**](DealerApi.md#dealerDemoRequestList) | **GET** /Dealer/DemoRequest/List | GetDemoRequests |
| [**dealerDistributorSettingsGet()**](DealerApi.md#dealerDistributorSettingsGet) | **GET** /Dealer/DistributorSettings/Get | Get the Distributor dealer settings |
| [**dealerDistributorSettingsUpdate()**](DealerApi.md#dealerDistributorSettingsUpdate) | **PUT** /Dealer/DistributorSettings/Update | Set the Distributor dealer settings |
| [**dealerEXplorerSettingsGet()**](DealerApi.md#dealerEXplorerSettingsGet) | **GET** /Dealer/eXplorerSettings/Get | Gets the dealer eXplorer settings |
| [**dealerEXplorerSettingsUpdate()**](DealerApi.md#dealerEXplorerSettingsUpdate) | **PUT** /Dealer/eXplorerSettings/Update | set the dealer eXplorer settings |
| [**dealerExportDealerTagsHierarchy()**](DealerApi.md#dealerExportDealerTagsHierarchy) | **GET** /Dealer/ExportDealerTagsHierarchy |  |
| [**dealerGet()**](DealerApi.md#dealerGet) | **POST** /Dealer/Get | Gets the dealer. |
| [**dealerGetDealer()**](DealerApi.md#dealerGetDealer) | **POST** /Dealer/GetDealer | Gets the dealer. |
| [**dealerGetDealerByCode()**](DealerApi.md#dealerGetDealerByCode) | **POST** /Dealer/GetDealerByCode | Gets the dealer. |
| [**dealerGetDealerHierarchy()**](DealerApi.md#dealerGetDealerHierarchy) | **GET** /Dealer/GetDealerHierarchy | Gets the dealer. |
| [**dealerGetDealerSupportTicketConfiguration()**](DealerApi.md#dealerGetDealerSupportTicketConfiguration) | **POST** /Dealer/GetDealerSupportTicketConfiguration | Gets the dealer support ticket configuration. |
| [**dealerGetDealerTagsHierarchy()**](DealerApi.md#dealerGetDealerTagsHierarchy) | **GET** /Dealer/GetDealerTagsHierarchy |  |
| [**dealerGetDealers()**](DealerApi.md#dealerGetDealers) | **POST** /Dealer/GetDealers | Gets the dealers list |
| [**dealerGetDealersInfo()**](DealerApi.md#dealerGetDealersInfo) | **POST** /Dealer/GetDealersInfo | Get the dealers list with contract, preferences infos |
| [**dealerGetDealersWithoutContract()**](DealerApi.md#dealerGetDealersWithoutContract) | **POST** /Dealer/GetDealersWithoutContract | Gets the dealers that do not have a contract. |
| [**dealerOnboardingGet()**](DealerApi.md#dealerOnboardingGet) | **GET** /Dealer/Onboarding/Get | Get the dealer onboarding survey |
| [**dealerOnboardingUpdate()**](DealerApi.md#dealerOnboardingUpdate) | **PUT** /Dealer/Onboarding/Update | Update the dealer onboarding survey |
| [**dealerPostponeDemoExpiration()**](DealerApi.md#dealerPostponeDemoExpiration) | **PUT** /Dealer/PostponeDemoExpiration | Postpones the demo expiration. |
| [**dealerRemoteOfflineCountersSettingsGet()**](DealerApi.md#dealerRemoteOfflineCountersSettingsGet) | **GET** /Dealer/RemoteOfflineCountersSettings/Get | Gets the dealer remote offline counters settings. |
| [**dealerRemoteOfflineCountersSettingsUpdate()**](DealerApi.md#dealerRemoteOfflineCountersSettingsUpdate) | **PUT** /Dealer/RemoteOfflineCountersSettings/Update | set the dealer remote offline counters settings. |
| [**dealerSaveDealerTagsHierarchy()**](DealerApi.md#dealerSaveDealerTagsHierarchy) | **POST** /Dealer/SaveDealerTagsHierarchy |  |
| [**dealerUpdate()**](DealerApi.md#dealerUpdate) | **PUT** /Dealer/Update | Update dealer main data |
| [**dealerUpdateDealerSupportTicketConfiguration()**](DealerApi.md#dealerUpdateDealerSupportTicketConfiguration) | **POST** /Dealer/UpdateDealerSupportTicketConfiguration | Updates the dealer support ticket configuration. |


## `dealerAccountingSettingsGet()`

```php
dealerAccountingSettingsGet($code): \OpenAPI\Client\Model\SingleResultResponseDealerAccountingSettingsDto
```

Gets the dealer accounting settings.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerAccountingSettingsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerAccountingSettingsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerAccountingSettingsDto**](../Model/SingleResultResponseDealerAccountingSettingsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerAccountingSettingsUpdate()`

```php
dealerAccountingSettingsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

set the dealer accounting settings.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerAccountingSettingsDto(); // \OpenAPI\Client\Model\UpdateRequestDealerAccountingSettingsDto | The request.

try {
    $result = $apiInstance->dealerAccountingSettingsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerAccountingSettingsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerAccountingSettingsDto**](../Model/UpdateRequestDealerAccountingSettingsDto.md)| The request. | |

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

## `dealerAdvancedOptionsGet()`

```php
dealerAdvancedOptionsGet($code): \OpenAPI\Client\Model\SingleResultResponseDealerAdvancedOptionsDto
```

Gets the dealer advanced options

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerAdvancedOptionsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerAdvancedOptionsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerAdvancedOptionsDto**](../Model/SingleResultResponseDealerAdvancedOptionsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerAlertLimitOptionsGet()`

```php
dealerAlertLimitOptionsGet($code): \OpenAPI\Client\Model\SingleResultResponseDealerAlertLimitOptionsDto
```

Gets the alert limit options.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerAlertLimitOptionsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerAlertLimitOptionsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerAlertLimitOptionsDto**](../Model/SingleResultResponseDealerAlertLimitOptionsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerAlertSettingsGet()`

```php
dealerAlertSettingsGet($code): \OpenAPI\Client\Model\SingleResultResponseDealerAlertSettingsDto
```

Gets the dealer alert settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerAlertSettingsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerAlertSettingsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerAlertSettingsDto**](../Model/SingleResultResponseDealerAlertSettingsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerAlertSettingsUpdate()`

```php
dealerAlertSettingsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

set the dealer alert settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerAlertSettingsDto(); // \OpenAPI\Client\Model\UpdateRequestDealerAlertSettingsDto | The request.

try {
    $result = $apiInstance->dealerAlertSettingsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerAlertSettingsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerAlertSettingsDto**](../Model/UpdateRequestDealerAlertSettingsDto.md)| The request. | |

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

## `dealerCustomizationsGet()`

```php
dealerCustomizationsGet($code): \OpenAPI\Client\Model\SingleResultResponseDealerCustomizationsDto
```

Gets the dealer customizations.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerCustomizationsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerCustomizationsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerCustomizationsDto**](../Model/SingleResultResponseDealerCustomizationsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerCustomizationsUpdate()`

```php
dealerCustomizationsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

set the dealer customizations.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerCustomizationsDto(); // \OpenAPI\Client\Model\UpdateRequestDealerCustomizationsDto | The request.

try {
    $result = $apiInstance->dealerCustomizationsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerCustomizationsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerCustomizationsDto**](../Model/UpdateRequestDealerCustomizationsDto.md)| The request. | |

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

## `dealerDealerServicesStatusGet()`

```php
dealerDealerServicesStatusGet($code): \OpenAPI\Client\Model\SingleResultResponseDealerServicesStatusDto
```

Gets the dealer services status.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerDealerServicesStatusGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerDealerServicesStatusGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerServicesStatusDto**](../Model/SingleResultResponseDealerServicesStatusDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerDeclineDemoRequest()`

```php
dealerDeclineDemoRequest($request): \OpenAPI\Client\Model\BaseResponse
```

Decline demo request

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DeclineDemoRequest(); // \OpenAPI\Client\Model\DeclineDemoRequest

try {
    $result = $apiInstance->dealerDeclineDemoRequest($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerDeclineDemoRequest: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DeclineDemoRequest**](../Model/DeclineDemoRequest.md)|  | |

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

## `dealerDemoRequestGet()`

```php
dealerDemoRequestGet($dealer_code, $id): \OpenAPI\Client\Model\SingleResultResponseDemoContactDto
```

GetDemoRequest

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->dealerDemoRequestGet($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerDemoRequestGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDemoContactDto**](../Model/SingleResultResponseDemoContactDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerDemoRequestList()`

```php
dealerDemoRequestList($code, $page_number, $page_rows, $sort_column, $sort_order, $is_active, $is_declined, $filter_text): \OpenAPI\Client\Model\PagedResultResponseDemoContactDto
```

GetDemoRequests

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$is_active = True; // bool | Get Demo requests activate or not
$is_declined = True; // bool | Get Demo requests declinated or not
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->dealerDemoRequestList($code, $page_number, $page_rows, $sort_column, $sort_order, $is_active, $is_declined, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerDemoRequestList: ', $e->getMessage(), PHP_EOL;
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
| **is_active** | **bool**| Get Demo requests activate or not | [optional] |
| **is_declined** | **bool**| Get Demo requests declinated or not | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDemoContactDto**](../Model/PagedResultResponseDemoContactDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerDistributorSettingsGet()`

```php
dealerDistributorSettingsGet($code): \OpenAPI\Client\Model\SingleResultResponseDealerDistributorSettingsDto
```

Get the Distributor dealer settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerDistributorSettingsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerDistributorSettingsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerDistributorSettingsDto**](../Model/SingleResultResponseDealerDistributorSettingsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerDistributorSettingsUpdate()`

```php
dealerDistributorSettingsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Set the Distributor dealer settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerDistributorSettingsDto(); // \OpenAPI\Client\Model\UpdateRequestDealerDistributorSettingsDto

try {
    $result = $apiInstance->dealerDistributorSettingsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerDistributorSettingsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerDistributorSettingsDto**](../Model/UpdateRequestDealerDistributorSettingsDto.md)|  | |

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

## `dealerEXplorerSettingsGet()`

```php
dealerEXplorerSettingsGet($code): \OpenAPI\Client\Model\SingleResultResponseDealerExplorerSettingsDto
```

Gets the dealer eXplorer settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerEXplorerSettingsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerEXplorerSettingsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerExplorerSettingsDto**](../Model/SingleResultResponseDealerExplorerSettingsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerEXplorerSettingsUpdate()`

```php
dealerEXplorerSettingsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

set the dealer eXplorer settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerExplorerSettingsDto(); // \OpenAPI\Client\Model\UpdateRequestDealerExplorerSettingsDto | The request.

try {
    $result = $apiInstance->dealerEXplorerSettingsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerEXplorerSettingsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerExplorerSettingsDto**](../Model/UpdateRequestDealerExplorerSettingsDto.md)| The request. | |

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

## `dealerExportDealerTagsHierarchy()`

```php
dealerExportDealerTagsHierarchy($code): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerExportDealerTagsHierarchy($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerExportDealerTagsHierarchy: ', $e->getMessage(), PHP_EOL;
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

## `dealerGet()`

```php
dealerGet($request): \OpenAPI\Client\Model\SingleResultResponseDealerDto
```

Gets the dealer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByCodeRequest(); // \OpenAPI\Client\Model\GetByCodeRequest | The request.

try {
    $result = $apiInstance->dealerGet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByCodeRequest**](../Model/GetByCodeRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerDto**](../Model/SingleResultResponseDealerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerGetDealer()`

```php
dealerGetDealer($request): \OpenAPI\Client\Model\SingleResultResponseDealerDto
```

Gets the dealer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->dealerGetDealer($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerGetDealer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerDto**](../Model/SingleResultResponseDealerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerGetDealerByCode()`

```php
dealerGetDealerByCode($request): \OpenAPI\Client\Model\SingleResultResponseDealerDto
```

Gets the dealer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByCodeRequest(); // \OpenAPI\Client\Model\GetByCodeRequest | The request.

try {
    $result = $apiInstance->dealerGetDealerByCode($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerGetDealerByCode: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByCodeRequest**](../Model/GetByCodeRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerDto**](../Model/SingleResultResponseDealerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerGetDealerHierarchy()`

```php
dealerGetDealerHierarchy($code): \OpenAPI\Client\Model\SingleResultResponseDealerHierarchyDto
```

Gets the dealer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerGetDealerHierarchy($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerGetDealerHierarchy: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerHierarchyDto**](../Model/SingleResultResponseDealerHierarchyDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerGetDealerSupportTicketConfiguration()`

```php
dealerGetDealerSupportTicketConfiguration($request): \OpenAPI\Client\Model\SingleResultResponseDealerSupportTicketConfigurationDto
```

Gets the dealer support ticket configuration.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByCodeRequest(); // \OpenAPI\Client\Model\GetByCodeRequest | The request.

try {
    $result = $apiInstance->dealerGetDealerSupportTicketConfiguration($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerGetDealerSupportTicketConfiguration: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByCodeRequest**](../Model/GetByCodeRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerSupportTicketConfigurationDto**](../Model/SingleResultResponseDealerSupportTicketConfigurationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerGetDealerTagsHierarchy()`

```php
dealerGetDealerTagsHierarchy($code): \OpenAPI\Client\Model\SingleResultResponseDealerTagHierarchyDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerGetDealerTagsHierarchy($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerGetDealerTagsHierarchy: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerTagHierarchyDto**](../Model/SingleResultResponseDealerTagHierarchyDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerGetDealers()`

```php
dealerGetDealers($request): \OpenAPI\Client\Model\PagedResultResponseDealerListDto
```

Gets the dealers list

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\FilteredPagedRequest(); // \OpenAPI\Client\Model\FilteredPagedRequest | The request.

try {
    $result = $apiInstance->dealerGetDealers($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerGetDealers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\FilteredPagedRequest**](../Model/FilteredPagedRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDealerListDto**](../Model/PagedResultResponseDealerListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerGetDealersInfo()`

```php
dealerGetDealersInfo($request): \OpenAPI\Client\Model\PagedResultResponseDealerInfoDto
```

Get the dealers list with contract, preferences infos

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\FilteredPagedRequest(); // \OpenAPI\Client\Model\FilteredPagedRequest

try {
    $result = $apiInstance->dealerGetDealersInfo($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerGetDealersInfo: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\FilteredPagedRequest**](../Model/FilteredPagedRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDealerInfoDto**](../Model/PagedResultResponseDealerInfoDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerGetDealersWithoutContract()`

```php
dealerGetDealersWithoutContract($request): \OpenAPI\Client\Model\PagedResultResponseDealerDto
```

Gets the dealers that do not have a contract.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\FilteredPagedRequest(); // \OpenAPI\Client\Model\FilteredPagedRequest | The request.

try {
    $result = $apiInstance->dealerGetDealersWithoutContract($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerGetDealersWithoutContract: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\FilteredPagedRequest**](../Model/FilteredPagedRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseDealerDto**](../Model/PagedResultResponseDealerDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerOnboardingGet()`

```php
dealerOnboardingGet($dealer_code): \OpenAPI\Client\Model\SingleResultResponseDealerOnboardingDto
```

Get the dealer onboarding survey

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerOnboardingGet($dealer_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerOnboardingGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerOnboardingDto**](../Model/SingleResultResponseDealerOnboardingDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerOnboardingUpdate()`

```php
dealerOnboardingUpdate($request): \OpenAPI\Client\Model\SingleResultResponseDealerOnboardingDto
```

Update the dealer onboarding survey

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerOnboardingDto(); // \OpenAPI\Client\Model\UpdateRequestDealerOnboardingDto

try {
    $result = $apiInstance->dealerOnboardingUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerOnboardingUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerOnboardingDto**](../Model/UpdateRequestDealerOnboardingDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerOnboardingDto**](../Model/SingleResultResponseDealerOnboardingDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerPostponeDemoExpiration()`

```php
dealerPostponeDemoExpiration($expiration_request): \OpenAPI\Client\Model\BaseResponse
```

Postpones the demo expiration.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$expiration_request = new \OpenAPI\Client\Model\PostponeDemoExpirationRequest(); // \OpenAPI\Client\Model\PostponeDemoExpirationRequest | The request.

try {
    $result = $apiInstance->dealerPostponeDemoExpiration($expiration_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerPostponeDemoExpiration: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **expiration_request** | [**\OpenAPI\Client\Model\PostponeDemoExpirationRequest**](../Model/PostponeDemoExpirationRequest.md)| The request. | |

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

## `dealerRemoteOfflineCountersSettingsGet()`

```php
dealerRemoteOfflineCountersSettingsGet($code): \OpenAPI\Client\Model\SingleResultResponseDealerRemoteOfflineCountersSettingsDto
```

Gets the dealer remote offline counters settings.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.

try {
    $result = $apiInstance->dealerRemoteOfflineCountersSettingsGet($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerRemoteOfflineCountersSettingsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerRemoteOfflineCountersSettingsDto**](../Model/SingleResultResponseDealerRemoteOfflineCountersSettingsDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `dealerRemoteOfflineCountersSettingsUpdate()`

```php
dealerRemoteOfflineCountersSettingsUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

set the dealer remote offline counters settings.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerRemoteOfflineCountersSettingsDto(); // \OpenAPI\Client\Model\UpdateRequestDealerRemoteOfflineCountersSettingsDto | The request.

try {
    $result = $apiInstance->dealerRemoteOfflineCountersSettingsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerRemoteOfflineCountersSettingsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerRemoteOfflineCountersSettingsDto**](../Model/UpdateRequestDealerRemoteOfflineCountersSettingsDto.md)| The request. | |

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

## `dealerSaveDealerTagsHierarchy()`

```php
dealerSaveDealerTagsHierarchy($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SaveDealerTagsHierarchyRequest(); // \OpenAPI\Client\Model\SaveDealerTagsHierarchyRequest

try {
    $result = $apiInstance->dealerSaveDealerTagsHierarchy($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerSaveDealerTagsHierarchy: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SaveDealerTagsHierarchyRequest**](../Model/SaveDealerTagsHierarchyRequest.md)|  | |

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

## `dealerUpdate()`

```php
dealerUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Update dealer main data

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\DealerMainDataDtoRequest(); // \OpenAPI\Client\Model\DealerMainDataDtoRequest

try {
    $result = $apiInstance->dealerUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\DealerMainDataDtoRequest**](../Model/DealerMainDataDtoRequest.md)|  | |

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

## `dealerUpdateDealerSupportTicketConfiguration()`

```php
dealerUpdateDealerSupportTicketConfiguration($request): \OpenAPI\Client\Model\BaseResponse
```

Updates the dealer support ticket configuration.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DealerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestDealerSupportTicketConfigurationDto(); // \OpenAPI\Client\Model\UpdateRequestDealerSupportTicketConfigurationDto | The request.

try {
    $result = $apiInstance->dealerUpdateDealerSupportTicketConfiguration($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealerApi->dealerUpdateDealerSupportTicketConfiguration: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestDealerSupportTicketConfigurationDto**](../Model/UpdateRequestDealerSupportTicketConfigurationDto.md)| The request. | |

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
