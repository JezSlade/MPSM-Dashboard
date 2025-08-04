# # GetAnalyticsResultRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id_dealer** | **string** | The Dealer Id. |
**id_customer** | **string** | The Customer Id. |
**id** | **string** | If set, it is the Id of a saved custom report. |
**tabular_tables** | [**\OpenAPI\Client\Model\TabularTableDto[]**](TabularTableDto.md) | Gets or sets the export filters and fields. |
**report_format** | **string** | Gets or sets the export format (Excel, CSV, ...) | [optional]
**use_dealer_hierarchy** | **bool** | True means that the report will show dealer&#39;s hierarchy data | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
