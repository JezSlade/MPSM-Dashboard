# OpenAPI\Client\CommunicationApiApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**communicationGetPortalReleaseNotes()**](CommunicationApiApi.md#communicationGetPortalReleaseNotes) | **GET** /Communication/GetPortalReleaseNotes | Get Portal Release Notes |


## `communicationGetPortalReleaseNotes()`

```php
communicationGetPortalReleaseNotes(): \OpenAPI\Client\Model\ListResultResponsePortalReleaseNoteDto
```

Get Portal Release Notes

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\CommunicationApiApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->communicationGetPortalReleaseNotes();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CommunicationApiApi->communicationGetPortalReleaseNotes: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\OpenAPI\Client\Model\ListResultResponsePortalReleaseNoteDto**](../Model/ListResultResponsePortalReleaseNoteDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `application/vnd.ms-excel`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
