# # PanelMessageAlertListDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**customer** | [**\OpenAPI\Client\Model\CustomerBaseDto**](CustomerBaseDto.md) |  | [optional]
**device** | [**\OpenAPI\Client\Model\InstalledProductBaseDto**](InstalledProductBaseDto.md) |  | [optional]
**brands** | **string[]** | Gets or sets the Brands | [optional]
**products** | [**\OpenAPI\Client\Model\ProductBaseDto[]**](ProductBaseDto.md) | Gets or sets the Products | [optional]
**error_codes** | [**\OpenAPI\Client\Model\PanelMessageAlertCodeDto[]**](PanelMessageAlertCodeDto.md) | Gets or sets the Error codes | [optional]
**filtered_descriptions** | **string[]** | Gets or sets the filtered descriptions | [optional]
**description** | **string** | Gets or sets the description. | [optional]
**created_at_utc** | **\DateTime** | Gets or sets the createdAt. | [optional]
**updated_at_utc** | **\DateTime** | Gets or sets the updatedAt. | [optional]
**deleted_at_utc** | **\DateTime** | Gets or sets the deletedAtUTC. | [optional]
**supply_type** | [**\OpenAPI\Client\Model\EntityIdDescIntDto**](EntityIdDescIntDto.md) |  | [optional]
**color_type** | [**\OpenAPI\Client\Model\EntityIdDescIntDto**](EntityIdDescIntDto.md) |  | [optional]
**maintenance_kit_type** | [**\OpenAPI\Client\Model\EntityIdDescIntDto**](EntityIdDescIntDto.md) |  | [optional]
**maintenance_kit_color** | [**\OpenAPI\Client\Model\EntityIdDescIntDto**](EntityIdDescIntDto.md) |  | [optional]
**send_mail** | **bool** | Gets or sets the send mail option. | [optional]
**open_alert** | **bool** | Gets or sets the open alert option. | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
