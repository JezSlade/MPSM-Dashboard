# # CreateTicketDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**dealer_code** | **string** | Dealer code | [optional]
**dealer_id** | **string** | Dealer Id | [optional]
**customer_code** | **string** | Customer Code | [optional]
**customer_id** | **string** | Customer Id | [optional]
**title** | **string** | Ticket title |
**category** | **string** | The Category of the ticket |
**sub_type** | **string** | The Subcategory of the ticket, based on Category selection |
**optional_mails** | **string** | The optionalMails separated by ; | [optional]
**serial_number** | **string** | The serialNumber | [optional]
**ip_address** | **string** | The IpAddress | [optional]
**message** | **string** | The Message |
**is_customer_assigner_to** | **bool** | Gets or sets a value indicating whether this instance is customer assigner to. | [optional]
**is_waiting_table_release** | **bool** | Gets or sets a value indicating whether this instance is waiting table release. | [optional]
**is_waiting_binary_release** | **bool** | Gets or sets a value indicating whether this instance is waiting binary release. | [optional]
**is_waiting_third_level** | **bool** | Gets or sets a value indicating whether this instance is waiting third level. | [optional]
**is_feature_request** | **bool** | Gets or sets a value indicating whether this instance is feature request. | [optional]
**attachments** | [**\OpenAPI\Client\Model\FileInfoDto[]**](FileInfoDto.md) | Gets or sets the attachments. | [optional]
**mile_stone** | **\DateTime** | Gets or sets the mile stone. | [optional]
**priority** | **string** | Gets or sets the priority. | [optional]
**resolve** | **string** | Gets or sets the resolve. | [optional]
**assigned_to_id** | **string** | OperatorId to assign the ticket | [optional]
**platform** | **string** | Gets or sets the platform. | [optional]
**git_commit** | **string** | Gets or sets the git commit. | [optional]
**oberon_ticket** | **string** | Gets or sets the oberon ticket. | [optional]
**sds_ticket** | **string** | Gets or sets the SDS ticket. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
