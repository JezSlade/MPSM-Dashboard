# # CustomerContractListDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**is_active** | **bool** | True if the Contract is active, that is it will produce invoices | [optional]
**valid_from** | **\DateTime** | Date of contract start | [optional]
**valid_to** | **\DateTime** | Date of contract end | [optional]
**id_supplier** | **int** | ID of the dealer supplier | [optional]
**supplier_code** | **string** | Code of the dealer supplier | [optional]
**id_customer** | **int** | Customer ID | [optional]
**customer_code** | **string** | Customer Code | [optional]
**note** | **string** | Contract notes | [optional]
**billing_driver_ids** | **int[]** | IDs of the billing plans where the contract is included | [optional]
**service_codes** | **string[]** | Codes of the services included in the contract | [optional]
**number_of_devices** | **int** | The number of devices in the contract | [optional]
**has_planned_services** | **bool** | True means that all the services of the contract have a billing plan | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
