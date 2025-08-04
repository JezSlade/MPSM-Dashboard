# # CustomerContractDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**valid_from** | **\DateTime** | Contract is valid from | [optional]
**valid_to** | **\DateTime** | Contract is valid to | [optional]
**is_active** | **bool** | Is Active | [optional]
**note** | **string** | Note | [optional]
**external_identifier** | **string** | External Identifier | [optional]
**supplier** | [**\OpenAPI\Client\Model\DealerBaseDto**](DealerBaseDto.md) |  | [optional]
**customer** | [**\OpenAPI\Client\Model\CustomerBaseDto**](CustomerBaseDto.md) |  | [optional]
**contract_billable_services** | [**\OpenAPI\Client\Model\CustomerContractBillableServiceDto[]**](CustomerContractBillableServiceDto.md) | Billable services | [optional]
**has_any_billing_driver** | **bool** | True if at least one ContractBillableService has a Billing Driver | [optional]
**custom_field_values** | [**\OpenAPI\Client\Model\CustomFieldValueDto[]**](CustomFieldValueDto.md) | Gets or sets the custom fields | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
