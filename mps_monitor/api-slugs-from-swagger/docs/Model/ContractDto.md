# # ContractDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**valid_from** | **\DateTime** | Contract is valid from | [optional]
**valid_to** | **\DateTime** | Contract is valid to | [optional]
**is_active** | **bool** | Is Active | [optional]
**has_subscriber_delegation** | **bool** | True means that the Subscriber at first level (Distributor or SuperDealer)              has a delegation for the ownership of the contract,              thus allowing any modification on behalf of the Supplier. | [optional]
**note** | **string** | Note | [optional]
**external_identifier** | **string** | External Identifier | [optional]
**supplier** | [**\OpenAPI\Client\Model\DealerDto**](DealerDto.md) |  | [optional]
**distributor** | [**\OpenAPI\Client\Model\DealerDto**](DealerDto.md) |  | [optional]
**super_dealer** | [**\OpenAPI\Client\Model\DealerDto**](DealerDto.md) |  | [optional]
**dealers** | [**\OpenAPI\Client\Model\DealerDto[]**](DealerDto.md) | Dealer(s), for B2B contracts | [optional]
**customers** | [**\OpenAPI\Client\Model\CustomerDto[]**](CustomerDto.md) | Customer(s), for B2C contracts | [optional]
**sales_agent** | [**\OpenAPI\Client\Model\AccountDto**](AccountDto.md) |  | [optional]
**purchase_order** | **string** | Purchase Order | [optional]
**contract_billable_services** | [**\OpenAPI\Client\Model\ContractBillableServiceDto[]**](ContractBillableServiceDto.md) | Billable services | [optional]
**has_any_billing_driver** | **bool** | True if at least one ContractBillableService has a Billing Driver | [optional]
**custom_field_values** | [**\OpenAPI\Client\Model\CustomFieldValueDto[]**](CustomFieldValueDto.md) | Gets or sets the custom fields | [optional]
**dealer_contract_documents** | [**\OpenAPI\Client\Model\DealerContractDocumentsDto[]**](DealerContractDocumentsDto.md) | The list of dealers associated to this contract with documents | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
