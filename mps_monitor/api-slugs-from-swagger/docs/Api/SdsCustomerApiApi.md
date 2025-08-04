# OpenAPI\Client\SdsCustomerApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**sdsCustomerActivate()**](SdsCustomerApiApi.md#sdsCustomerActivate) | **POST** /SdsCustomer/Activate | Sets the credential. |
| [**sdsCustomerCreateAssessTemplate()**](SdsCustomerApiApi.md#sdsCustomerCreateAssessTemplate) | **POST** /SdsCustomer/CreateAssessTemplate |  |
| [**sdsCustomerDeactivate()**](SdsCustomerApiApi.md#sdsCustomerDeactivate) | **POST** /SdsCustomer/Deactivate | Deactivate Customer. |
| [**sdsCustomerDeleteAssessTemplate()**](SdsCustomerApiApi.md#sdsCustomerDeleteAssessTemplate) | **DELETE** /SdsCustomer/DeleteAssessTemplate |  |
| [**sdsCustomerDeleteCredential()**](SdsCustomerApiApi.md#sdsCustomerDeleteCredential) | **DELETE** /SdsCustomer/DeleteCredential | Deletes the credential. |
| [**sdsCustomerGetAssessTemplate()**](SdsCustomerApiApi.md#sdsCustomerGetAssessTemplate) | **GET** /SdsCustomer/GetAssessTemplate |  |
| [**sdsCustomerGetAssessTemplates()**](SdsCustomerApiApi.md#sdsCustomerGetAssessTemplates) | **GET** /SdsCustomer/GetAssessTemplates |  |
| [**sdsCustomerGetCredential()**](SdsCustomerApiApi.md#sdsCustomerGetCredential) | **GET** /SdsCustomer/GetCredential | Gets the credential. |
| [**sdsCustomerGetCustomerOperation()**](SdsCustomerApiApi.md#sdsCustomerGetCustomerOperation) | **GET** /SdsCustomer/GetCustomerOperation | Gets the customer operation. |
| [**sdsCustomerGetCustomerOperations()**](SdsCustomerApiApi.md#sdsCustomerGetCustomerOperations) | **GET** /SdsCustomer/GetCustomerOperations | Gets the customer operations. |
| [**sdsCustomerGetNewAssessTemplate()**](SdsCustomerApiApi.md#sdsCustomerGetNewAssessTemplate) | **GET** /SdsCustomer/GetNewAssessTemplate |  |
| [**sdsCustomerRunOperation()**](SdsCustomerApiApi.md#sdsCustomerRunOperation) | **PUT** /SdsCustomer/RunOperation | Run again a specific SDS operation by id |
| [**sdsCustomerSetCredential()**](SdsCustomerApiApi.md#sdsCustomerSetCredential) | **POST** /SdsCustomer/SetCredential | Sets the credential. |
| [**sdsCustomerUpdate()**](SdsCustomerApiApi.md#sdsCustomerUpdate) | **PATCH** /SdsCustomer/Update | Update Customer Jam settings |
| [**sdsCustomerUpdateAssessTemplate()**](SdsCustomerApiApi.md#sdsCustomerUpdateAssessTemplate) | **PUT** /SdsCustomer/UpdateAssessTemplate |  |
| [**sdsCustomerUploadVJamcDevices()**](SdsCustomerApiApi.md#sdsCustomerUploadVJamcDevices) | **POST** /SdsCustomer/UploadVJamcDevices | Upload the WPP payload file to onboard WPP SDS devices |


## `sdsCustomerActivate()`

```php
sdsCustomerActivate($request): \OpenAPI\Client\Model\BaseResponse
```

Sets the credential.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->sdsCustomerActivate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerActivate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

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

## `sdsCustomerCreateAssessTemplate()`

```php
sdsCustomerCreateAssessTemplate($request): \OpenAPI\Client\Model\SingleResultResponseSdsCustomerAssessTemplateDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateRequestSdsCustomerAssessTemplateDto(); // \OpenAPI\Client\Model\CreateRequestSdsCustomerAssessTemplateDto

try {
    $result = $apiInstance->sdsCustomerCreateAssessTemplate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerCreateAssessTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateRequestSdsCustomerAssessTemplateDto**](../Model/CreateRequestSdsCustomerAssessTemplateDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSdsCustomerAssessTemplateDto**](../Model/SingleResultResponseSdsCustomerAssessTemplateDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsCustomerDeactivate()`

```php
sdsCustomerDeactivate($request): \OpenAPI\Client\Model\BaseResponse
```

Deactivate Customer.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest | The request.

try {
    $result = $apiInstance->sdsCustomerDeactivate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerDeactivate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)| The request. | |

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

## `sdsCustomerDeleteAssessTemplate()`

```php
sdsCustomerDeleteAssessTemplate($customer_code, $id): \OpenAPI\Client\Model\SingleResultResponseBaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Gets or sets the CustomerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsCustomerDeleteAssessTemplate($customer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerDeleteAssessTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| Gets or sets the CustomerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseBaseResponse**](../Model/SingleResultResponseBaseResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsCustomerDeleteCredential()`

```php
sdsCustomerDeleteCredential($id, $credential_type): \OpenAPI\Client\Model\BaseResponse
```

Deletes the credential.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$credential_type = 'credential_type_example'; // string

try {
    $result = $apiInstance->sdsCustomerDeleteCredential($id, $credential_type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerDeleteCredential: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **credential_type** | **string**|  | [optional] |

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

## `sdsCustomerGetAssessTemplate()`

```php
sdsCustomerGetAssessTemplate($customer_code, $id): \OpenAPI\Client\Model\SingleResultResponseSdsCustomerAssessTemplateDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$customer_code = 'customer_code_example'; // string | Gets or sets the CustomerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsCustomerGetAssessTemplate($customer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerGetAssessTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **customer_code** | **string**| Gets or sets the CustomerCode. | |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSdsCustomerAssessTemplateDto**](../Model/SingleResultResponseSdsCustomerAssessTemplateDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsCustomerGetAssessTemplates()`

```php
sdsCustomerGetAssessTemplates($code, $page_number, $page_rows, $sort_column, $sort_order, $filter_text): \OpenAPI\Client\Model\PagedResultResponseSdsCustomerAssessTemplateDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$code = 'code_example'; // string | Gets or sets the code.
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->sdsCustomerGetAssessTemplates($code, $page_number, $page_rows, $sort_column, $sort_order, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerGetAssessTemplates: ', $e->getMessage(), PHP_EOL;
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
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseSdsCustomerAssessTemplateDto**](../Model/PagedResultResponseSdsCustomerAssessTemplateDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsCustomerGetCredential()`

```php
sdsCustomerGetCredential($id): \OpenAPI\Client\Model\SingleResultResponseSdsCredentialDto
```

Gets the credential.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->sdsCustomerGetCredential($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerGetCredential: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSdsCredentialDto**](../Model/SingleResultResponseSdsCredentialDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsCustomerGetCustomerOperation()`

```php
sdsCustomerGetCustomerOperation($id, $customer_id, $operation_type): \OpenAPI\Client\Model\SingleResultResponseCustomerSdsOperationDto
```

Gets the customer operation.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.
$customer_id = 'customer_id_example'; // string
$operation_type = 'operation_type_example'; // string

try {
    $result = $apiInstance->sdsCustomerGetCustomerOperation($id, $customer_id, $operation_type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerGetCustomerOperation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |
| **customer_id** | **string**|  | [optional] |
| **operation_type** | **string**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseCustomerSdsOperationDto**](../Model/SingleResultResponseCustomerSdsOperationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsCustomerGetCustomerOperations()`

```php
sdsCustomerGetCustomerOperations($page_number, $page_rows, $sort_column, $sort_order, $customer_id, $operation_type, $operation_status, $filter_text): \OpenAPI\Client\Model\PagedResultResponseCustomerSdsOperationDto
```

Gets the customer operations.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$page_number = 56; // int | Gets or sets the page number.
$page_rows = 56; // int | Gets or sets the page rows.
$sort_column = 'sort_column_example'; // string | Gets or sets the sort column.
$sort_order = 'sort_order_example'; // string | Gets or sets the sort order.
$customer_id = 'customer_id_example'; // string
$operation_type = 'operation_type_example'; // string
$operation_status = 'operation_status_example'; // string
$filter_text = 'filter_text_example'; // string | Gets or sets the filter text.

try {
    $result = $apiInstance->sdsCustomerGetCustomerOperations($page_number, $page_rows, $sort_column, $sort_order, $customer_id, $operation_type, $operation_status, $filter_text);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerGetCustomerOperations: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **page_number** | **int**| Gets or sets the page number. | |
| **page_rows** | **int**| Gets or sets the page rows. | |
| **sort_column** | **string**| Gets or sets the sort column. | |
| **sort_order** | **string**| Gets or sets the sort order. | |
| **customer_id** | **string**|  | [optional] |
| **operation_type** | **string**|  | [optional] |
| **operation_status** | **string**|  | [optional] |
| **filter_text** | **string**| Gets or sets the filter text. | [optional] |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseCustomerSdsOperationDto**](../Model/PagedResultResponseCustomerSdsOperationDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsCustomerGetNewAssessTemplate()`

```php
sdsCustomerGetNewAssessTemplate(): \OpenAPI\Client\Model\ListResultResponseSdsAssessDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->sdsCustomerGetNewAssessTemplate();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerGetNewAssessTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\OpenAPI\Client\Model\ListResultResponseSdsAssessDto**](../Model/ListResultResponseSdsAssessDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsCustomerRunOperation()`

```php
sdsCustomerRunOperation($request): \OpenAPI\Client\Model\BaseResponse
```

Run again a specific SDS operation by id

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdAndCustomerCodeRequest(); // \OpenAPI\Client\Model\GetByIdAndCustomerCodeRequest

try {
    $result = $apiInstance->sdsCustomerRunOperation($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerRunOperation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdAndCustomerCodeRequest**](../Model/GetByIdAndCustomerCodeRequest.md)|  | |

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

## `sdsCustomerSetCredential()`

```php
sdsCustomerSetCredential($request): \OpenAPI\Client\Model\BaseResponse
```

Sets the credential.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetJamCredentialRequest(); // \OpenAPI\Client\Model\SetJamCredentialRequest | The request.

try {
    $result = $apiInstance->sdsCustomerSetCredential($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerSetCredential: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetJamCredentialRequest**](../Model/SetJamCredentialRequest.md)| The request. | |

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

## `sdsCustomerUpdate()`

```php
sdsCustomerUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Update Customer Jam settings

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateSdsCustomerRequest(); // \OpenAPI\Client\Model\UpdateSdsCustomerRequest

try {
    $result = $apiInstance->sdsCustomerUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateSdsCustomerRequest**](../Model/UpdateSdsCustomerRequest.md)|  | |

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

## `sdsCustomerUpdateAssessTemplate()`

```php
sdsCustomerUpdateAssessTemplate($request): \OpenAPI\Client\Model\SingleResultResponseSdsCustomerAssessTemplateDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateRequestSdsCustomerAssessTemplateDto(); // \OpenAPI\Client\Model\UpdateRequestSdsCustomerAssessTemplateDto

try {
    $result = $apiInstance->sdsCustomerUpdateAssessTemplate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerUpdateAssessTemplate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateRequestSdsCustomerAssessTemplateDto**](../Model/UpdateRequestSdsCustomerAssessTemplateDto.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseSdsCustomerAssessTemplateDto**](../Model/SingleResultResponseSdsCustomerAssessTemplateDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sdsCustomerUploadVJamcDevices()`

```php
sdsCustomerUploadVJamcDevices($request): \OpenAPI\Client\Model\BaseResponse
```

Upload the WPP payload file to onboard WPP SDS devices

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\SdsCustomerApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UploadVJamcDevicesRequest(); // \OpenAPI\Client\Model\UploadVJamcDevicesRequest

try {
    $result = $apiInstance->sdsCustomerUploadVJamcDevices($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SdsCustomerApiApi->sdsCustomerUploadVJamcDevices: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UploadVJamcDevicesRequest**](../Model/UploadVJamcDevicesRequest.md)|  | |

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
