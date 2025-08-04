# # InvoiceRowDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | Id of the invoice row | [optional]
**index** | **int** | Index of the invoice row | [optional]
**grouping_tag** | **string** | Defines an optional grouping of a set of rows | [optional]
**code** | **string** | Code of the item in the invoice row | [optional]
**description** | **string** | Description of the item in the invoice row | [optional]
**quantity** | **float** | The quantity | [optional]
**unit_price** | **float** | The unit price of the item | [optional]
**amount** | **float** | The amount, that is Quantity * UnitPrice | [optional]
**price_decimals** | **int** | The number of decimals to be printed for the Unit Price in the invoice row | [optional]
**service_start_date** | **\DateTime** | Service Start Date | [optional]
**service_end_date** | **\DateTime** | Service End Date | [optional]
**issues_per_year** | **int** | Number of invoices per year, according to the billing driver months | [optional]
**is_recurrent_billing** | **bool** | True if the billed service is recurrent | [optional]
**invoice_row_info_device_counters** | [**\OpenAPI\Client\Model\InvoiceRowInfoDeviceCountersDto[]**](InvoiceRowInfoDeviceCountersDto.md) | The list of items in the invoice | [optional]
**invoice_row_infos** | [**\OpenAPI\Client\Model\InvoiceRowInfoDto[]**](InvoiceRowInfoDto.md) | The list of additional row info | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
