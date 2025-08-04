# OpenAPI\Client\ProjectApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**projectCreate()**](ProjectApi.md#projectCreate) | **POST** /Project/Create | Create the project |
| [**projectDelete()**](ProjectApi.md#projectDelete) | **DELETE** /Project/Delete | Delete  project |
| [**projectGet()**](ProjectApi.md#projectGet) | **POST** /Project/Get |  |
| [**projectGetContractFile()**](ProjectApi.md#projectGetContractFile) | **GET** /Project/GetContractFile | Gets the project contract file. |
| [**projectGetDetail()**](ProjectApi.md#projectGetDetail) | **GET** /Project/GetDetail | Gets the project. |
| [**projectList()**](ProjectApi.md#projectList) | **POST** /Project/List | Gets the projects. |
| [**projectManageDevicesAssociation()**](ProjectApi.md#projectManageDevicesAssociation) | **PUT** /Project/ManageDevicesAssociation | Associate devices to project |
| [**projectSetRelatedDevicesAlertGenerator()**](ProjectApi.md#projectSetRelatedDevicesAlertGenerator) | **PUT** /Project/SetRelatedDevicesAlertGenerator | Set Related Devices AlertGenerator |
| [**projectUpdate()**](ProjectApi.md#projectUpdate) | **PUT** /Project/Update | Updates the project. |


## `projectCreate()`

```php
projectCreate($request): \OpenAPI\Client\Model\BaseResponse
```

Create the project

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProjectApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateProjectRequest(); // \OpenAPI\Client\Model\CreateProjectRequest | The request.

try {
    $result = $apiInstance->projectCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectApi->projectCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateProjectRequest**](../Model/CreateProjectRequest.md)| The request. | |

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

## `projectDelete()`

```php
projectDelete($id): \OpenAPI\Client\Model\BaseResponse
```

Delete  project

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProjectApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->projectDelete($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectApi->projectDelete: ', $e->getMessage(), PHP_EOL;
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

## `projectGet()`

```php
projectGet($request): \OpenAPI\Client\Model\SingleResultResponseProjectFullDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProjectApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetByIdRequest(); // \OpenAPI\Client\Model\GetByIdRequest

try {
    $result = $apiInstance->projectGet($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectApi->projectGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetByIdRequest**](../Model/GetByIdRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseProjectFullDto**](../Model/SingleResultResponseProjectFullDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `projectGetContractFile()`

```php
projectGetContractFile($id): \OpenAPI\Client\Model\SingleResultResponseFileInfoDto
```

Gets the project contract file.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProjectApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->projectGetContractFile($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectApi->projectGetContractFile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

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

## `projectGetDetail()`

```php
projectGetDetail($id): \OpenAPI\Client\Model\SingleResultResponseProjectFullDto
```

Gets the project.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProjectApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->projectGetDetail($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectApi->projectGetDetail: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id** | **string**| Gets or sets the identifier. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseProjectFullDto**](../Model/SingleResultResponseProjectFullDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `projectList()`

```php
projectList($request): \OpenAPI\Client\Model\PagedResultResponseProjectListDto
```

Gets the projects.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProjectApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetProjectsRequest(); // \OpenAPI\Client\Model\GetProjectsRequest | The request.

try {
    $result = $apiInstance->projectList($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectApi->projectList: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetProjectsRequest**](../Model/GetProjectsRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseProjectListDto**](../Model/PagedResultResponseProjectListDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `projectManageDevicesAssociation()`

```php
projectManageDevicesAssociation($request): \OpenAPI\Client\Model\BaseResponse
```

Associate devices to project

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProjectApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\ManageDevicesAssociationRequest(); // \OpenAPI\Client\Model\ManageDevicesAssociationRequest | The request.

try {
    $result = $apiInstance->projectManageDevicesAssociation($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectApi->projectManageDevicesAssociation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\ManageDevicesAssociationRequest**](../Model/ManageDevicesAssociationRequest.md)| The request. | |

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

## `projectSetRelatedDevicesAlertGenerator()`

```php
projectSetRelatedDevicesAlertGenerator($request): \OpenAPI\Client\Model\BaseResponse
```

Set Related Devices AlertGenerator

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProjectApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetAlertGeneratorRequest(); // \OpenAPI\Client\Model\SetAlertGeneratorRequest | The request.

try {
    $result = $apiInstance->projectSetRelatedDevicesAlertGenerator($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectApi->projectSetRelatedDevicesAlertGenerator: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetAlertGeneratorRequest**](../Model/SetAlertGeneratorRequest.md)| The request. | |

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

## `projectUpdate()`

```php
projectUpdate($request): \OpenAPI\Client\Model\BaseResponse
```

Updates the project.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\ProjectApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateProjectRequest(); // \OpenAPI\Client\Model\UpdateProjectRequest | The request.

try {
    $result = $apiInstance->projectUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectApi->projectUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateProjectRequest**](../Model/UpdateProjectRequest.md)| The request. | |

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
