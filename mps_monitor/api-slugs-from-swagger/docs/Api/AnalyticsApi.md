# OpenAPI\Client\AnalyticsApi

All URIs are relative to https://localhost:34287, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**analyticsGetReportFileResult()**](AnalyticsApi.md#analyticsGetReportFileResult) | **GET** /Analytics/GetReportFileResult | Get result as file (Excel, CSV) from a saved report. |
| [**analyticsGetReportResult()**](AnalyticsApi.md#analyticsGetReportResult) | **GET** /Analytics/GetReportResult | Get result from a saved report. |


## `analyticsGetReportFileResult()`

```php
analyticsGetReportFileResult($id_report, $report_format): \OpenAPI\Client\Model\BaseHttpResponseMessage
```

Get result as file (Excel, CSV) from a saved report.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AnalyticsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id_report = 56; // int | The ID of a saved custom report.
$report_format = 'report_format_example'; // string | The export format of the result (Excel, CSV)

try {
    $result = $apiInstance->analyticsGetReportFileResult($id_report, $report_format);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AnalyticsApi->analyticsGetReportFileResult: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id_report** | **int**| The ID of a saved custom report. | |
| **report_format** | **string**| The export format of the result (Excel, CSV) | |

### Return type

[**\OpenAPI\Client\Model\BaseHttpResponseMessage**](../Model/BaseHttpResponseMessage.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `analyticsGetReportResult()`

```php
analyticsGetReportResult($id_report): \OpenAPI\Client\Model\SingleResultResponseTabularResultDto
```

Get result from a saved report.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\AnalyticsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id_report = 56; // int | The ID of a saved custom report.

try {
    $result = $apiInstance->analyticsGetReportResult($id_report);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AnalyticsApi->analyticsGetReportResult: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **id_report** | **int**| The ID of a saved custom report. | |

### Return type

[**\OpenAPI\Client\Model\SingleResultResponseTabularResultDto**](../Model/SingleResultResponseTabularResultDto.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `text/json`, `application/xml`, `text/xml`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
