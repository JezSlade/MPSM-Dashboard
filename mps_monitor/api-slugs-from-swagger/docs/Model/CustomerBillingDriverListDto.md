# # CustomerBillingDriverListDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**is_billing** | **bool** | True if it will produce only real invoices, False if it will produce only proforma invoices | [optional]
**has_proforma** | **bool** | True if the real invoice will also have a proforma | [optional]
**is_active** | **bool** | True if the Billing Driver is active, that is it will produce invoices | [optional]
**id_issuer** | **int** | The dealer issuer ID | [optional]
**issuer_code** | **string** | The dealer issuer Code | [optional]
**id_recipient_customer** | **int** | The customer recipient ID (for B2C invoices) | [optional]
**customer_code** | **string** | The customer recipient ID (for B2C invoices) | [optional]
**day_of_month** | **int** | The day of month when the invoice will be generated | [optional]
**months** | **string** | The months when the invoice will be generated | [optional]
**hour** | **int** | The hour when the invoice will be generated | [optional]
**invoice_heading** | **string** | The invoice description | [optional]
**payment_type** | **string** | The invoice Payment Type | [optional]
**payment_term** | **string** | The invoice Payment Term | [optional]
**service_codes** | **string[]** | The Billable Services codes list | [optional]
**contract_ids** | **int[]** | The Contracts Ids ist | [optional]
**number_of_devices** | **int** | Number of devices | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
