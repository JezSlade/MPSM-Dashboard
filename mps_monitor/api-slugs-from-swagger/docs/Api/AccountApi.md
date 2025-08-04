# OpenAPI\Client\AccountApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**accountChangeLanguage()**](AccountApi.md#accountChangeLanguage) | **POST** /Account/ChangeLanguage | Change language |
| [**accountChangePassword()**](AccountApi.md#accountChangePassword) | **POST** /Account/ChangePassword | Changes the password of the connected account |
| [**accountCreate()**](AccountApi.md#accountCreate) | **POST** /Account/Create | Create an account |
| [**accountDelete()**](AccountApi.md#accountDelete) | **DELETE** /Account/Delete | This operation deletes an account |
| [**accountDelete2fa()**](AccountApi.md#accountDelete2fa) | **DELETE** /Account/Delete2fa | This operation deletes the two factor authentication for a user |
| [**accountDeleteProfile2fa()**](AccountApi.md#accountDeleteProfile2fa) | **DELETE** /Account/DeleteProfile2fa | This operation deletes the two factor authentication for a user |
| [**accountEnable2faForAccount()**](AccountApi.md#accountEnable2faForAccount) | **POST** /Account/Enable2faForAccount |  |
| [**accountEnable2faForProfile()**](AccountApi.md#accountEnable2faForProfile) | **POST** /Account/Enable2faForProfile |  |
| [**accountGetAccount()**](AccountApi.md#accountGetAccount) | **POST** /Account/GetAccount | Gets the account. |
| [**accountGetAccounts()**](AccountApi.md#accountGetAccounts) | **POST** /Account/GetAccounts | Gets the accounts. |
| [**accountGetProfile()**](AccountApi.md#accountGetProfile) | **GET** /Account/GetProfile | Gets profile of current authenticated user. |
| [**accountGetPsk2faData()**](AccountApi.md#accountGetPsk2faData) | **GET** /Account/GetPsk2faData |  |
| [**accountGetPsk2faDataForAccount()**](AccountApi.md#accountGetPsk2faDataForAccount) | **GET** /Account/GetPsk2faDataForAccount |  |
| [**accountGetPsk2faDataForProfile()**](AccountApi.md#accountGetPsk2faDataForProfile) | **GET** /Account/GetPsk2faDataForProfile |  |
| [**accountLogout()**](AccountApi.md#accountLogout) | **POST** /Account/Logout |  |
| [**accountRefreshAuthCookie()**](AccountApi.md#accountRefreshAuthCookie) | **POST** /Account/RefreshAuthCookie |  |
| [**accountResetPassword()**](AccountApi.md#accountResetPassword) | **POST** /Account/ResetPassword | Resets the password. |
| [**accountResetPasswordVerifyToken()**](AccountApi.md#accountResetPasswordVerifyToken) | **POST** /Account/ResetPasswordVerifyToken | Verify the Resets password auth token. |
| [**accountSendOtpEmailForAccount()**](AccountApi.md#accountSendOtpEmailForAccount) | **POST** /Account/SendOtpEmailForAccount |  |
| [**accountSetPreferredDealer()**](AccountApi.md#accountSetPreferredDealer) | **POST** /Account/SetPreferredDealer | Gets profile of current authenticated account. |
| [**accountUpdate()**](AccountApi.md#accountUpdate) | **POST** /Account/Update | Update an account |
| [**accountUpdateProfile()**](AccountApi.md#accountUpdateProfile) | **POST** /Account/UpdateProfile | Update profile of current authenticated user. |


## `accountChangeLanguage()`

```php
accountChangeLanguage($request): \OpenAPI\Client\Model\SingleResultResponseAccountDto
```

Change language

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\ChangeLanguageRequest(); // \OpenAPI\Client\Model\ChangeLanguageRequest | The request.

try {
    $result = $apiInstance->accountChangeLanguage($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountChangeLanguage: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\ChangeLanguageRequest**](../Model/ChangeLanguageRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAccountDto**](../Model/SingleResultResponseAccountDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountChangePassword()`

```php
accountChangePassword($request): \OpenAPI\Client\Model\BaseResponse
```

Changes the password of the connected account

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\ChangePasswordRequest(); // \OpenAPI\Client\Model\ChangePasswordRequest | The request.

try {
    $result = $apiInstance->accountChangePassword($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountChangePassword: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\ChangePasswordRequest**](../Model/ChangePasswordRequest.md)| The request. | |

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

## `accountCreate()`

```php
accountCreate($request): \OpenAPI\Client\Model\SingleResultResponseAccountDto
```

Create an account

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\CreateAccountRequest(); // \OpenAPI\Client\Model\CreateAccountRequest | The request.

try {
    $result = $apiInstance->accountCreate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountCreate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\CreateAccountRequest**](../Model/CreateAccountRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAccountDto**](../Model/SingleResultResponseAccountDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountDelete()`

```php
accountDelete($dealer_code, $id): \OpenAPI\Client\Model\BaseResponse
```

This operation deletes an account

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->accountDelete($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountDelete: ', $e->getMessage(), PHP_EOL;
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

## `accountDelete2fa()`

```php
accountDelete2fa($dealer_code, $id): \OpenAPI\Client\Model\BaseResponse
```

This operation deletes the two factor authentication for a user

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$dealer_code = 'dealer_code_example'; // string | Gets or sets the DealerCode.
$id = 'id_example'; // string | Gets or sets the identifier.

try {
    $result = $apiInstance->accountDelete2fa($dealer_code, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountDelete2fa: ', $e->getMessage(), PHP_EOL;
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

## `accountDeleteProfile2fa()`

```php
accountDeleteProfile2fa(): \OpenAPI\Client\Model\BaseResponse
```

This operation deletes the two factor authentication for a user

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->accountDeleteProfile2fa();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountDeleteProfile2fa: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

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

## `accountEnable2faForAccount()`

```php
accountEnable2faForAccount($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\Enable2faForAccountRequest(); // \OpenAPI\Client\Model\Enable2faForAccountRequest

try {
    $result = $apiInstance->accountEnable2faForAccount($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountEnable2faForAccount: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\Enable2faForAccountRequest**](../Model/Enable2faForAccountRequest.md)|  | |

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

## `accountEnable2faForProfile()`

```php
accountEnable2faForProfile($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\Enable2faForProfileRequest(); // \OpenAPI\Client\Model\Enable2faForProfileRequest

try {
    $result = $apiInstance->accountEnable2faForProfile($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountEnable2faForProfile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\Enable2faForProfileRequest**](../Model/Enable2faForProfileRequest.md)|  | |

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

## `accountGetAccount()`

```php
accountGetAccount($request): \OpenAPI\Client\Model\SingleResultResponseAccountDto
```

Gets the account.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetAccountRequest(); // \OpenAPI\Client\Model\GetAccountRequest

try {
    $result = $apiInstance->accountGetAccount($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountGetAccount: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetAccountRequest**](../Model/GetAccountRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAccountDto**](../Model/SingleResultResponseAccountDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountGetAccounts()`

```php
accountGetAccounts($request): \OpenAPI\Client\Model\PagedResultResponseAccountDto
```

Gets the accounts.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\GetAccountsRequest(); // \OpenAPI\Client\Model\GetAccountsRequest | The request.

try {
    $result = $apiInstance->accountGetAccounts($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountGetAccounts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\GetAccountsRequest**](../Model/GetAccountsRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\PagedResultResponseAccountDto**](../Model/PagedResultResponseAccountDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountGetProfile()`

```php
accountGetProfile(): \OpenAPI\Client\Model\SingleResultResponseAccountDto
```

Gets profile of current authenticated user.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->accountGetProfile();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountGetProfile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAccountDto**](../Model/SingleResultResponseAccountDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountGetPsk2faData()`

```php
accountGetPsk2faData($platform): \OpenAPI\Client\Model\SingleResultResponsePsk2faDataDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$platform = 'platform_example'; // string

try {
    $result = $apiInstance->accountGetPsk2faData($platform);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountGetPsk2faData: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **platform** | **string**|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponsePsk2faDataDto**](../Model/SingleResultResponsePsk2faDataDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountGetPsk2faDataForAccount()`

```php
accountGetPsk2faDataForAccount($platform, $user_name, $password, $psk2fa): \OpenAPI\Client\Model\SingleResultResponsePsk2faDataDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$platform = 'platform_example'; // string
$user_name = 'user_name_example'; // string
$password = 'password_example'; // string
$psk2fa = 'psk2fa_example'; // string

try {
    $result = $apiInstance->accountGetPsk2faDataForAccount($platform, $user_name, $password, $psk2fa);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountGetPsk2faDataForAccount: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **platform** | **string**|  | [optional] |
| **user_name** | **string**|  | [optional] |
| **password** | **string**|  | [optional] |
| **psk2fa** | **string**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponsePsk2faDataDto**](../Model/SingleResultResponsePsk2faDataDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountGetPsk2faDataForProfile()`

```php
accountGetPsk2faDataForProfile($platform): \OpenAPI\Client\Model\SingleResultResponsePsk2faDataDto
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$platform = 'platform_example'; // string

try {
    $result = $apiInstance->accountGetPsk2faDataForProfile($platform);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountGetPsk2faDataForProfile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **platform** | **string**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponsePsk2faDataDto**](../Model/SingleResultResponsePsk2faDataDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountLogout()`

```php
accountLogout(): object
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->accountLogout();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountLogout: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

**object**

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountRefreshAuthCookie()`

```php
accountRefreshAuthCookie(): object
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->accountRefreshAuthCookie();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountRefreshAuthCookie: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

**object**

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountResetPassword()`

```php
accountResetPassword($request): \OpenAPI\Client\Model\BaseResponse
```

Resets the password.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\ResetPasswordRequest(); // \OpenAPI\Client\Model\ResetPasswordRequest | The request.

try {
    $result = $apiInstance->accountResetPassword($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountResetPassword: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\ResetPasswordRequest**](../Model/ResetPasswordRequest.md)| The request. | |

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

## `accountResetPasswordVerifyToken()`

```php
accountResetPasswordVerifyToken($request): \OpenAPI\Client\Model\BaseResponse
```

Verify the Resets password auth token.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\ResetPasswordVerifyTokenRequest(); // \OpenAPI\Client\Model\ResetPasswordVerifyTokenRequest | The request.

try {
    $result = $apiInstance->accountResetPasswordVerifyToken($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountResetPasswordVerifyToken: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\ResetPasswordVerifyTokenRequest**](../Model/ResetPasswordVerifyTokenRequest.md)| The request. | |

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

## `accountSendOtpEmailForAccount()`

```php
accountSendOtpEmailForAccount($request): \OpenAPI\Client\Model\BaseResponse
```



### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SendOtpEmailForAccountRequest(); // \OpenAPI\Client\Model\SendOtpEmailForAccountRequest

try {
    $result = $apiInstance->accountSendOtpEmailForAccount($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountSendOtpEmailForAccount: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SendOtpEmailForAccountRequest**](../Model/SendOtpEmailForAccountRequest.md)|  | |

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

## `accountSetPreferredDealer()`

```php
accountSetPreferredDealer($request): \OpenAPI\Client\Model\SingleResultResponseAccountDto
```

Gets profile of current authenticated account.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\SetPreferredDealerRequest(); // \OpenAPI\Client\Model\SetPreferredDealerRequest

try {
    $result = $apiInstance->accountSetPreferredDealer($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountSetPreferredDealer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\SetPreferredDealerRequest**](../Model/SetPreferredDealerRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAccountDto**](../Model/SingleResultResponseAccountDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountUpdate()`

```php
accountUpdate($request): \OpenAPI\Client\Model\SingleResultResponseAccountDto
```

Update an account

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateAccountRequest(); // \OpenAPI\Client\Model\UpdateAccountRequest | The request.

try {
    $result = $apiInstance->accountUpdate($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountUpdate: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateAccountRequest**](../Model/UpdateAccountRequest.md)| The request. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAccountDto**](../Model/SingleResultResponseAccountDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `accountUpdateProfile()`

```php
accountUpdateProfile($request): \OpenAPI\Client\Model\SingleResultResponseAccountDto
```

Update profile of current authenticated user.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$request = new \OpenAPI\Client\Model\UpdateProfileRequest(); // \OpenAPI\Client\Model\UpdateProfileRequest

try {
    $result = $apiInstance->accountUpdateProfile($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AccountApi->accountUpdateProfile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\UpdateProfileRequest**](../Model/UpdateProfileRequest.md)|  | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseAccountDto**](../Model/SingleResultResponseAccountDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
