# # CustomerContractBillableServiceDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**billable_service** | [**\OpenAPI\Client\Model\CustomerBillableServiceDto**](CustomerBillableServiceDto.md) |  | [optional]
**pooled_devices** | [**\OpenAPI\Client\Model\DeviceBaseDto[]**](DeviceBaseDto.md) | Devices of a pool, for customers contracts | [optional]
**color** | **string** | May specify if the billable service in the contract refers to a specific color (mono/color) | [optional]
**reference_term** | **int** | May specify if the billable service in the contract will be billed in advance of at final of the reference invoice date | [optional]
**billable_service_prices** | [**\OpenAPI\Client\Model\CustomerBillableServicePriceDto[]**](CustomerBillableServicePriceDto.md) | Contract Billable services prices | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
