# # GetPassiveInvoicesRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**from_issue_date** | **\DateTime** | Gets or sets from issue date. |
**to_issue_date** | **\DateTime** | Gets or sets to issue date. |
**id_invoice_category** | **string** | Id Invoice Category.              Missing or -1 means all.              -2 means passive invoices without category. | [optional]
**recipient_code** | **string** | Gets or sets RecipientCode | [optional]
**issuer_or_recipient_code** | **string** | Gets or sets the Issuer or Recipient Code | [optional]
**is_paid** | **bool** | Gets or sets Is Paid | [optional]
**filter_text** | **string** | Gets or sets the filter text. | [optional]
**page_number** | **int** | Gets or sets the page number. |
**page_rows** | **int** | Gets or sets the page rows. |
**sort_column** | **string** | Gets or sets the sort column. |
**sort_order** | **string** | Gets or sets the sort order. |

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
