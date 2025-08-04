# # AccountDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**nominative** | **string** | Gets or sets the nominative. | [optional]
**created_at** | **string** | Gets or sets the creation date. | [optional]
**created_at_date** | **\DateTime** | Gets or sets the creation date. | [optional]
**email** | **string** | Gets or sets the email. | [optional]
**token** | **string** | Gets or sets the token. | [optional]
**role** | **string** | Gets or sets the role. | [optional]
**capabilities** | **string[]** | Capabilities | [optional]
**language** | **string** | Gets or sets the language. | [optional]
**short_language** | **string** | Gets or sets the short language. | [optional]
**is_active** | **bool** | Gets or sets a value indicating whether this instance is active. | [optional]
**force2fa** | **bool** | Gets or sets a value indicating whether this {MpsMonitor.Models.AccountDto} must use 2fa. | [optional]
**force_sso** | **bool** | Gets or sets a value indicating whether [force sso]. | [optional]
**use2fa** | **bool** | Gets or sets a value indicating whether this instance use 2 form factor auth | [optional]
**last_login_at** | **\DateTime** | Gets or sets the last login at. | [optional]
**is_deleted** | **bool** | Gets or sets a value indicating whether this instance is marked for cancellation. | [optional]
**exclude_from_warning_notifications** | **bool** | Exclude this account from eXplorer warning notifications | [optional]
**enabled_new_devices_notification** | **bool** | Enable new devices discovered notification | [optional]
**preferred_dealer** | [**\OpenAPI\Client\Model\DealerBaseDto**](DealerBaseDto.md) |  | [optional]
**default_dealer** | [**\OpenAPI\Client\Model\DealerBaseDto**](DealerBaseDto.md) |  | [optional]
**default_customer** | [**\OpenAPI\Client\Model\CustomerBaseDto**](CustomerBaseDto.md) |  | [optional]
**customers** | [**\OpenAPI\Client\Model\CustomerBaseDto[]**](CustomerBaseDto.md) | The list of the customers associated to this account | [optional]
**dealers** | [**\OpenAPI\Client\Model\DealerBaseDto[]**](DealerBaseDto.md) | The list of the dealers associated to this account | [optional]
**tags** | **string[]** | The list of the tags associated to this account | [optional]
**reporting_reports** | [**\OpenAPI\Client\Model\ReportBaseDto[]**](ReportBaseDto.md) | The list of customer reporting reports available | [optional]
**enable_password_expiration** | **bool** | Gets or sets a value indicating whether [enable password expiration]. | [optional]
**name** | **string** | Gets or sets the name. | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
