# # RiBaDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**issuer** | [**\OpenAPI\Client\Model\DealerBaseDto**](DealerBaseDto.md) |  | [optional]
**mittente** | **string** | Issuer | [optional]
**abi_ricevente** | **string** | ABI Recipient | [optional]
**cab_ricevente** | **string** | CAB Recipient | [optional]
**banca_ricevente_descr** | **string** | Recipient Bank description | [optional]
**banca_ricevente_conto** | **string** | Recipient Bank account | [optional]
**nome_supporto** | **string** | Name of the RiBa file | [optional]
**content_base64** | **string** | Content file bytes | [optional]
**data_creazione** | **\DateTime** | Creation date | [optional]
**tot_importi_positivi** | **float** | Positive amount | [optional]
**tot_importi_negativi** | **float** | Negative amount | [optional]
**dealer_bank_account** | [**\OpenAPI\Client\Model\DealerBankAccountDto**](DealerBankAccountDto.md) |  | [optional]
**invoice_headers** | [**\OpenAPI\Client\Model\InvoiceHeaderDto[]**](InvoiceHeaderDto.md) | All invoices related to this RiBa | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
