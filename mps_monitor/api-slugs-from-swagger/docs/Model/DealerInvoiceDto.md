# # DealerInvoiceDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**status** | **string** | The internal status of the process              NEW &#x3D; New invoice, in progress, to be computed              RDY &#x3D; Ready, the invoice is computed              MED &#x3D; Media ready, the invoice media files are ready (pdf, xml, ...)              SNT &#x3D; Sent, the invoice is sent to the recipient              ERR &#x3D; Error, the invoice has some errors | [optional]
**id_invoice_plan** | **int** | Id Invoice Plan | [optional]
**is_billing** | **bool** | True means a real invoice, false means a Proforma | [optional]
**is_credit** | **bool** | True means credit (payment from issuer to recipient) | [optional]
**sales_channel** | **string** | The Sales Channel (Dealer to Dealer, Dealer to Customer, ...) | [optional]
**billing_service_code** | **string** | The code of the billed service | [optional]
**issuer** | [**\OpenAPI\Client\Model\DealerBaseDto**](DealerBaseDto.md) |  | [optional]
**issuer_external_identifier** | **string** | The External Identifier of the Issuer | [optional]
**recipient** | [**\OpenAPI\Client\Model\DealerBaseDto**](DealerBaseDto.md) |  | [optional]
**recipient_external_identifier** | **string** | The External Identifier of the Recipient | [optional]
**bill_to** | [**\OpenAPI\Client\Model\CompanyDto**](CompanyDto.md) |  | [optional]
**recipient_payment** | **string** | The recipient payment type and term | [optional]
**invoice_template** | **string** | The invoice template | [optional]
**invoice_heading** | **string** | The main invoice description of services | [optional]
**payment_type** | **string** | The payment type | [optional]
**payment_term** | **string** | The payment term | [optional]
**reference_year** | **int** | A reference year of the related services | [optional]
**reference_month** | **int** | A reference month of the related services | [optional]
**issue_date** | **\DateTime** | The date of the invoice | [optional]
**due_date** | **\DateTime** | The due date of the invoice. | [optional]
**net_amount** | **float** | The net amount, before taxes. | [optional]
**is_dealer_tax_rate** | **bool** | True if the Tax Rate Code refers to a Dealer Tax Rate Code | [optional]
**tax_rate** | **float** | The tax percentage applied, if any. | [optional]
**tax_rate_code** | **string** | The tax rate code | [optional]
**tax** | **float** | The tax amount | [optional]
**total** | **float** | The end total of the invoice | [optional]
**language** | **string** | The language of the invoice | [optional]
**currency** | **string** | The currency ISO Code of the invoice | [optional]
**culture_info** | **string** | Culture Info for Dates and Numbers | [optional]
**sales_agent** | [**\OpenAPI\Client\Model\AccountBaseDto**](AccountBaseDto.md) |  | [optional]
**invoice_rows** | [**\OpenAPI\Client\Model\InvoiceRowDto[]**](InvoiceRowDto.md) | The list of items in the invoice | [optional]
**external_identifier** | **string** | The External Identifier of the invoice | [optional]
**id_invoice_header_parent** | **string** | When the invoice is a proforma with customers details, represents the Id of the parent invoice | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
