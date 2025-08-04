# # ContractBillableServiceDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**billable_service** | [**\OpenAPI\Client\Model\BillableServiceDto**](BillableServiceDto.md) |  | [optional]
**contract** | [**\OpenAPI\Client\Model\ContractDto**](ContractDto.md) |  | [optional]
**devices** | [**\OpenAPI\Client\Model\DeviceBaseDto[]**](DeviceBaseDto.md) | Devices of a pool, for B2C contracts | [optional]
**color** | **int** | May specify if the billable service in the contract refers to a specific color (mono/color) | [optional]
**format_type** | **int** | May specify if the billable service in the contract refers to a specific format type (A4/A3) | [optional]
**id_blended** | **string** | May specify if the billable service in the contract refers to a specific bended counter | [optional]
**reference_term** | **int** | May specify if the billable service in the contract will be billed in advance of at final of the reference invoice date | [optional]
**billable_service_prices** | [**\OpenAPI\Client\Model\BillableServicePriceDto[]**](BillableServicePriceDto.md) | Contract Billable services prices | [optional]
**id_billing_driver** | **int** | If the Contract Billable Service is included in a Billing Driver, it is the ID Billing Driver | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
