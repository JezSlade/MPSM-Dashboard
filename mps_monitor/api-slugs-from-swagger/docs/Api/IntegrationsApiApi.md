# OpenAPI\Client\IntegrationsApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**integrationsCreate()**](IntegrationsApiApi.md#integrationsCreate) | **POST** /Integrations/Create | Create an integration configuration |
| [**integrationsDelete()**](IntegrationsApiApi.md#integrationsDelete) | **DELETE** /Integrations/Delete | Delete an integration configuration |
| [**integrationsEautomateGetEAutomateLog()**](IntegrationsApiApi.md#integrationsEautomateGetEAutomateLog) | **GET** /Integrations/eautomate/GetEAutomateLog | Gets the top 10 recent eAutomate log entries. |
| [**integrationsEautomateRunjoin()**](IntegrationsApiApi.md#integrationsEautomateRunjoin) | **GET** /Integrations/eautomate/runjoin | Runs eAutomate devices join |
| [**integrationsGet()**](IntegrationsApiApi.md#integrationsGet) | **GET** /Integrations/Get | Get an integration configuration |
| [**integrationsGetJoinedCustomers()**](IntegrationsApiApi.md#integrationsGetJoinedCustomers) | **GET** /Integrations/GetJoinedCustomers | Get current joined customers summary |
| [**integrationsGetJoinedDevices()**](IntegrationsApiApi.md#integrationsGetJoinedDevices) | **GET** /Integrations/GetJoinedDevices | Get current joined devices summary |
| [**integrationsGetLogisticPlaceholders()**](IntegrationsApiApi.md#integrationsGetLogisticPlaceholders) | **GET** /Integrations/GetLogisticPlaceholders | Get logistic placeholders |
| [**integrationsGetNew()**](IntegrationsApiApi.md#integrationsGetNew) | **GET** /Integrations/GetNew | Get a new integration configuration |
| [**integrationsList()**](IntegrationsApiApi.md#integrationsList) | **GET** /Integrations/List | List of available and configured integration |
| [**integrationsToServCreate()**](IntegrationsApiApi.md#integrationsToServCreate) | **POST** /Integrations/ToServ/Create | Create an integration configuration for 2serv |
| [**integrationsUpdate()**](IntegrationsApiApi.md#integrationsUpdate) | **PUT** /Integrations/Update | Update an integration configuration |
| [**integrationsVantageOnlineCreate()**](IntegrationsApiApi.md#integrationsVantageOnlineCreate) | **POST** /Integrations/VantageOnline/Create | Create an integration configuration for VantageOnline |


## `integrationsCreate()`

```php
integrationsCreate($request): \OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto
```

Create an integration configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateDealerGatewayRequest(); // \OpenAPI\Client\Model\CreateDealerGatewayRequest

try {
    $result = $apiInstance->integrationsCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateDealerGatewayRequest**](../Model/CreateDealerGatewayRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto**](../Model/SingleResultResponseDealerGatewayDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `integrationsDelete()`

```php
integrationsDelete($dealer_code, $id): \OpenAPI\Client\Model\BaseResponse
```

Delete an integration configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->integrationsDelete($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsDelete: ', $e->getMessage(), PHP_EOL;
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

## `integrationsEautomateGetEAutomateLog()`

```php
integrationsEautomateGetEAutomateLog($dealer_code, $customer_code): \OpenAPI\Client\Model\ListResultResponseSagaOperationDto
```

Gets the top 10 recent eAutomate log entries.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Dealer code
$customer_code = 'customer_code_example'; // string | Customer code

try {
    $result = $apiInstance->integrationsEautomateGetEAutomateLog($dealer_code, $customer_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsEautomateGetEAutomateLog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Dealer code | [optional] |
| **customer_code** | **string**| Customer code | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseSagaOperationDto**](../Model/ListResultResponseSagaOperationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `integrationsEautomateRunjoin()`

```php
integrationsEautomateRunjoin($dealer_code, $customer_code): \OpenAPI\Client\Model\BaseResponse
```

Runs eAutomate devices join

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Dealer code
$customer_code = 'customer_code_example'; // string | Customer code

try {
    $result = $apiInstance->integrationsEautomateRunjoin($dealer_code, $customer_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsEautomateRunjoin: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Dealer code | [optional] |
| **customer_code** | **string**| Customer code | [optional] |

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

## `integrationsGet()`

```php
integrationsGet($dealer_code, $id): \OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto
```

Get an integration configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->integrationsGet($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Gets or sets the DealerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto**](../Model/SingleResultResponseDealerGatewayDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `integrationsGetJoinedCustomers()`

```php
integrationsGetJoinedCustomers($dealer_code, $customer_code): \OpenAPI\Client\Model\ListResultResponseCodeDesc
```

Get current joined customers summary

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Dealer code
$customer_code = 'customer_code_example'; // string | Customer code

try {
    $result = $apiInstance->integrationsGetJoinedCustomers($dealer_code, $customer_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsGetJoinedCustomers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Dealer code | [optional] |
| **customer_code** | **string**| Customer code | [optional] |

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

## `integrationsGetJoinedDevices()`

```php
integrationsGetJoinedDevices($dealer_code, $customer_code): \OpenAPI\Client\Model\ListResultResponseCodeDesc
```

Get current joined devices summary

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Dealer code
$customer_code = 'customer_code_example'; // string | Customer code

try {
    $result = $apiInstance->integrationsGetJoinedDevices($dealer_code, $customer_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsGetJoinedDevices: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_code** | **string**| Dealer code | [optional] |
| **customer_code** | **string**| Customer code | [optional] |

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

## `integrationsGetLogisticPlaceholders()`

```php
integrationsGetLogisticPlaceholders($filter_text): \OpenAPI\Client\Model\ListResultResponseLogisticPlaceholderDto
```

Get logistic placeholders

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->integrationsGetLogisticPlaceholders($filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsGetLogisticPlaceholders: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseLogisticPlaceholderDto**](../Model/ListResultResponseLogisticPlaceholderDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `integrationsGetNew()`

```php
integrationsGetNew($dealer_gateway_type, $topic): \OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto
```

Get a new integration configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_gateway_type = 'dealer_gateway_type_example'; // string
$topic = 'topic_example'; // string

try {
    $result = $apiInstance->integrationsGetNew($dealer_gateway_type, $topic);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsGetNew: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **dealer_gateway_type** | **string**|  | [optional] |
| **topic** | **string**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto**](../Model/SingleResultResponseDealerGatewayDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `integrationsList()`

```php
integrationsList($code, $is_active, $is_for_delivery, $is_for_meters, $is_update_supplies, $is_for_notification): \OpenAPI\Client\Model\ListResultResponseDealerGatewayDto
```

List of available and configured integration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.
$is_active = True; // bool
$is_for_delivery = True; // bool
$is_for_meters = True; // bool
$is_update_supplies = True; // bool
$is_for_notification = True; // bool

try {
    $result = $apiInstance->integrationsList($code, $is_active, $is_for_delivery, $is_for_meters, $is_update_supplies, $is_for_notification);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **code** | **string**| Gets or sets the code. | |
| **is_active** | **bool**|  | [optional] |
| **is_for_delivery** | **bool**|  | [optional] |
| **is_for_meters** | **bool**|  | [optional] |
| **is_update_supplies** | **bool**|  | [optional] |
| **is_for_notification** | **bool**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\ListResultResponseDealerGatewayDto**](../Model/ListResultResponseDealerGatewayDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `integrationsToServCreate()`

```php
integrationsToServCreate($request): \OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto
```

Create an integration configuration for 2serv

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateDealerGatewayToServRequest(); // \OpenAPI\Client\Model\CreateDealerGatewayToServRequest

try {
    $result = $apiInstance->integrationsToServCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsToServCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateDealerGatewayToServRequest**](../Model/CreateDealerGatewayToServRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto**](../Model/SingleResultResponseDealerGatewayDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `integrationsUpdate()`

```php
integrationsUpdate($request): \OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto
```

Update an integration configuration

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateDealerGatewayRequest(); // \OpenAPI\Client\Model\UpdateDealerGatewayRequest

try {
    $result = $apiInstance->integrationsUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateDealerGatewayRequest**](../Model/UpdateDealerGatewayRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto**](../Model/SingleResultResponseDealerGatewayDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `integrationsVantageOnlineCreate()`

```php
integrationsVantageOnlineCreate($request): \OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto
```

Create an integration configuration for VantageOnline

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\IntegrationsApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateDealerGatewayVantageOnlineRequest(); // \OpenAPI\Client\Model\CreateDealerGatewayVantageOnlineRequest

try {
    $result = $apiInstance->integrationsVantageOnlineCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApiApi->integrationsVantageOnlineCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateDealerGatewayVantageOnlineRequest**](../Model/CreateDealerGatewayVantageOnlineRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseDealerGatewayDto**](../Model/SingleResultResponseDealerGatewayDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
