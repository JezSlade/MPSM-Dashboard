# # DealerBillingConfigurationDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**billable_services_overridable** | **string[]** | Gets or sets the billable services overridable. | [optional]
**device_identifier** | **string** | Gets or sets the device identifier. | [optional]
**device_identifier_placeholders** | **string[]** | Gets or sets the device identifier placeholders. | [optional]
**invoice_number** | **string** | Gets or sets the invoice number. | [optional]
**invoice_number_placeholders** | **string[]** | Gets or sets the invoice number placeholders. | [optional]
**enable_invoice_numbering** | **bool** | Gets or sets a value indicating whether [enable invoice numbering]. | [optional]
**billable_service_overrides** | [**\OpenAPI\Client\Model\KeyValue[]**](KeyValue.md) | Gets or sets the billable service overrides. | [optional]
**billable_service_override_placeholders** | [**\OpenAPI\Client\Model\CodeValueDtoStringIEnumerableString[]**](CodeValueDtoStringIEnumerableString.md) | Gets or sets the billable service override placeholders. | [optional]
**document_name_invoice** | **string** | Gets or sets the Document name in case of Invoice. | [optional]
**document_name_invoice_proforma** | **string** | Gets or sets the Document name in case of Invoice Proforma. | [optional]
**document_name_credit_note** | **string** | Gets or sets the Document name in case of Credit Note. | [optional]
**document_name_credit_note_proforma** | **string** | Gets or sets the Document name in case of Credit Note Proforma. | [optional]
**meter_reading_dates_type** | **string** | Gets or sets the Meter Reading Dates Type for the timeframe start date and finish date. | [optional]
**invoice_rows_sorting** | **string** | Defines how to sort invoice rows              0: by billable service then by device identifier              1: by device identifier and then by billable service              Default is 0 | [optional]
**invoice_culture_info** | **string** | The culture info for dates and numbers formatting | [optional]
**available_culture_infos** | [**\OpenAPI\Client\Model\CodeValueDtoStringString[]**](CodeValueDtoStringString.md) | The available culture info | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
