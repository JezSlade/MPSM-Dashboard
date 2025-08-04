# # UpdateDealerTaxRateRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The id of the dealer tax rate definition |
**dealer_code** | **string** | The dealer code |
**country_id** | **string** | The dealer code |
**code** | **string** | An internal code of the tax rate used by the portal UI |
**description** | **string** | An internal description of the tax rate used by the portal UI |
**label** | **string** | The label of the tax rate used in the invoice totals |
**additional_notes** | **string** | Additional notes printed in the invoice | [optional]
**definitions** | [**\OpenAPI\Client\Model\DealerTaxRateDefinitionDto[]**](DealerTaxRateDefinitionDto.md) | The rates definitions by validity dates |

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
