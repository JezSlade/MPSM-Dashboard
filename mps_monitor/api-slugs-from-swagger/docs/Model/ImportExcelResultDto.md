# # ImportExcelResultDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**excel_columns** | [**\OpenAPI\Client\Model\ImportExcelColumnDto[]**](ImportExcelColumnDto.md) |  | [optional]
**preview_excel_rows** | **object[][]** |  | [optional]
**import_type_properties** | [**\OpenAPI\Client\Model\ImportTypePropertyMapDto[]**](ImportTypePropertyMapDto.md) |  | [optional]
**skip_first_row** | **bool** |  | [optional]
**date_format** | **string** |  | [optional]
**bool_format** | **string** |  | [optional]
**decimal_separator** | **string** |  | [optional]
**import_submitted** | **bool** |  | [optional]
**operation_id** | **string** |  | [optional]
**total_rows** | **int** |  | [optional]
**rows_in_error** | [**\OpenAPI\Client\Model\ImportExcelResultRowErrorDto[]**](ImportExcelResultRowErrorDto.md) |  | [optional]
**is_file_in_error** | **bool** |  | [optional] [readonly]
**all_property_map_auto_assigned** | **bool** |  | [optional] [readonly]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
