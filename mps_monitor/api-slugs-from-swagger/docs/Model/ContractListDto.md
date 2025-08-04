# # ContractListDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**is_active** | **bool** | True if the Contract is active, that is it will produce invoices | [optional]
**valid_from** | **\DateTime** | Date of contract start | [optional]
**valid_to** | **\DateTime** | Date of contract end | [optional]
**id_supplier** | **int** | ID of the dealer supplier | [optional]
**supplier_code** | **string** | Code of the dealer supplier | [optional]
**supplier_description** | **string** | Description of the dealer supplier | [optional]
**id_distributor** | **int** | Id of the dealer distributor | [optional]
**distributor_code** | **string** | Code of the dealer distributor | [optional]
**distributor_description** | **string** | Description of the dealer distributor | [optional]
**id_super_dealer** | **int** | Id of the dealer super dealer | [optional]
**super_dealer_code** | **string** | Code of the dealer super dealer | [optional]
**super_dealer_description** | **string** | Description of the dealer super dealer | [optional]
**dealer_codes** | **string[]** | Dealers codes | [optional]
**dealers** | **string[]** | Dealers descriptions | [optional]
**regions** | **string[]** | Dealers Regions | [optional]
**note** | **string** | Contract notes | [optional]
**billing_driver_ids** | **int[]** | IDs of the billing plans where the contract is included | [optional]
**service_codes** | **string[]** | Codes of the services included in the contract | [optional]
**number_of_devices** | **int** | The number of devices in the contract | [optional]
**has_planned_services** | **bool** | True means that all the services of the contract have a billing plan | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
