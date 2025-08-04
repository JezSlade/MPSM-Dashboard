# # DealerInvoicesListDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**billing_service_code** | **string** | Billing Service Code | [optional]
**id_invoice_plan** | **int** | Id Invoice Plan | [optional]
**is_billing** | **bool** | True means a real invoice, false means a Proforma | [optional]
**is_credit** | **bool** | True means credit (payment from issuer to recipient) | [optional]
**status** | **string** | Status | [optional]
**issuer_code** | **string** | Issuer Code | [optional]
**issuer_description** | **string** | Issuer Description | [optional]
**issuer_external_identifier** | **string** | The External Identifier of the Issuer | [optional]
**recipient_code** | **string** | Recipient Code | [optional]
**recipient_description** | **string** | Recipient Description | [optional]
**recipient_external_identifier** | **string** | The External Identifier of the Recipient | [optional]
**issue_date** | **\DateTime** | Issue Date | [optional]
**due_date** | **\DateTime** | Due Date | [optional]
**net_amount** | **float** | Net Amount | [optional]
**tax** | **float** | Tax | [optional]
**total** | **float** | Total | [optional]
**tax_rate_code** | **string** | Tax Rate Code | [optional]
**currency** | **string** | Currency Code | [optional]
**payment_type** | **string** | Payment Type | [optional]
**payment_term** | **string** | Payment Term | [optional]
**external_identifier** | **string** | The External Identifier of the invoice | [optional]
**id_invoice_header_parent** | **string** | When the invoice is a proforma with customers details, represents the Id of the parent invoice | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
