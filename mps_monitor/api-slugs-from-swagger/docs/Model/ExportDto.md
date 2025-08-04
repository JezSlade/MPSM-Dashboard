# # ExportDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**report_type** | **string** | The Report type (Standard Export, Self Service) | [optional]
**report_format** | **string** | The Report format (Excel, CSV) | [optional]
**name** | **string** | The Export Name | [optional]
**description** | **string** | Description | [optional]
**abstract** | **string** | Abstract | [optional]
**columns** | [**\OpenAPI\Client\Model\ExportColumnDto[]**](ExportColumnDto.md) | The available Fields in the result | [optional]
**filters** | [**\OpenAPI\Client\Model\ExportFilterDto[]**](ExportFilterDto.md) | Filters to filter the result | [optional]
**schedule** | [**\OpenAPI\Client\Model\ExportScheduleDto**](ExportScheduleDto.md) |  | [optional]
**id_dealer_notification** | **string** | Template for the export | [optional]
**is_system_report** | **bool** | True means system report for admins | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
