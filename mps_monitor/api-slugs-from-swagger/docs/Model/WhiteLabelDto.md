# # WhiteLabelDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**dealer_code** | **string** | The dealer code | [optional]
**is_active** | **bool** | Defines is the Whitelabel configuration is active or not | [optional]
**is_portal_active** | **bool** | Defines if the Whitelabel can configure the Portal. Is enabled by Administrators | [optional]
**is_dca_active** | **bool** | Defines if the Whitelabel can configure the DCA. Is enabled by Administrators | [optional]
**demo_days** | **int** | Defines the default demo days | [optional]
**portal_email_templates** | [**\OpenAPI\Client\Model\WhiteLabelPortalEmailTemplateDto[]**](WhiteLabelPortalEmailTemplateDto.md) | Defines the list of email template customized | [optional]
**portal_name** | **string** | The Portal Name and Page Title | [optional]
**portal_urls** | **string** | Reverse proxy URLS separated by ; | [optional]
**portal_style** | [**\OpenAPI\Client\Model\WhiteLabelPortalStyleDto**](WhiteLabelPortalStyleDto.md) |  | [optional]
**setup_name** | **string** |  | [optional]
**setup_image** | [**\OpenAPI\Client\Model\FileInfoDto**](FileInfoDto.md) |  | [optional]
**setup_image_small** | [**\OpenAPI\Client\Model\FileInfoDto**](FileInfoDto.md) |  | [optional]
**delete_setup_image** | **bool** |  | [optional]
**delete_setup_image_small** | **bool** |  | [optional]
**delete_portal_style_logo** | **bool** |  | [optional]
**delete_portal_style_logo_login** | **bool** |  | [optional]
**delete_portal_style_back_covers_login1** | **bool** |  | [optional]
**delete_portal_style_back_covers_login2** | **bool** |  | [optional]
**delete_portal_style_back_covers_login3** | **bool** |  | [optional]
**force_hp_jamc_installation** | **bool** | Force Jamc Installation | [optional]
**dca_licenses** | [**\OpenAPI\Client\Model\WhiteLabelDcaLicenseDto[]**](WhiteLabelDcaLicenseDto.md) |  | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
