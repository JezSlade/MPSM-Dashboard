# # BillingDriverListDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**is_billing** | **bool** | True if it will produce only real invoices, False if it will produce only proforma invoices | [optional]
**has_proforma** | **bool** | True if the real invoice will also have a proforma | [optional]
**is_active** | **bool** | True if the Billing Driver is active, that is it will produce invoices | [optional]
**id_issuer** | **int** | The dealer issuer ID | [optional]
**issuer_code** | **string** | The dealer issuer Code | [optional]
**issuer_description** | **string** | The dealer issuer description | [optional]
**id_recipient** | **int** | The dealer recipient ID (for B2B invoices) | [optional]
**recipient_code** | **string** | The dealer recipient ID (for B2B invoices) | [optional]
**recipient_description** | **string** | The dealer recipient description (for B2B invoices) | [optional]
**id_distributor** | **int** | The dealer distributor ID, if any | [optional]
**distributor_code** | **string** | The dealer distributor code, if any | [optional]
**distributor_description** | **string** | The dealer distributor description, if any | [optional]
**id_super_dealer** | **int** | The dealer super dealer ID, if any | [optional]
**super_dealer_code** | **string** | The dealer super dealer code, if any | [optional]
**super_dealer_description** | **string** | The dealer super dealer description, if any | [optional]
**dealer_codes** | **string[]** | Dealers codes | [optional]
**dealers** | **string[]** | Dealers descriptions | [optional]
**id_bill_to** | **int** | The Bill To ID, if any | [optional]
**bill_to_code** | **string** | The Bill To Code, if any | [optional]
**bill_to_description** | **string** | The Bill To description, if any | [optional]
**day_of_month** | **int** | The day of month when the invoice will be generated | [optional]
**months** | **string** | The months when the invoice will be generated | [optional]
**hour** | **int** | The hour when the invoice will be generated | [optional]
**invoice_template** | **string** | The invoice template | [optional]
**invoice_heading** | **string** | The invoice description | [optional]
**payment_type** | **string** | The invoice Payment Type | [optional]
**payment_term** | **string** | The invoice Payment Term | [optional]
**service_codes** | **string[]** | The Billable Services codes list | [optional]
**contract_ids** | **int[]** | The Contracts Ids ist | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
