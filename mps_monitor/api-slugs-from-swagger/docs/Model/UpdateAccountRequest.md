# # UpdateAccountRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | Gets or sets the Account Id. | [optional]
**language** | **string** | Gets or sets the Language. | [optional]
**nominative** | **string** | Gets or sets the nominative. | [optional]
**email** | **string** | Gets or sets the Email. | [optional]
**account_role** | **string** | Gets or sets the Account role. | [optional]
**is_active** | **bool** | Gets or sets the IsActive. | [optional]
**force2fa** | **bool** | Gets or sets a value indicating whether this {MpsMonitor.Models.Contracts.Account.UpdateAccountRequest} must use 2fa. | [optional]
**force_sso** | **bool** | Gets or sets a value indicating whether [force sso]. | [optional]
**exclude_from_warning_notifications** | **bool** | Gets or sets the ExcludeFromWarningNotifications. | [optional]
**enabled_new_devices_notification** | **bool** | Gets or sets the EnabledNewDevicesNotification | [optional]
**selected_dealers_id** | **string[]** | Gets or sets the Dealers Id | [optional]
**selected_customers_id** | **string[]** | Gets or sets the Customers Id | [optional]
**selected_tags_name** | **string[]** | Gets or sets the name of the selected tags. | [optional]
**selected_reporting_reports** | **string[]** | Customer Reporting report to add | [optional]
**currend_dealer_code** | **string** | Gets or sets the currend dealer code. | [optional]
**account_capabilities** | [**\OpenAPI\Client\Model\CapabilityDto[]**](CapabilityDto.md) | AccountCapabilities | [optional]
**enable_password_expiration** | **bool** | Gets or sets a value indicating whether [enable password expiration]. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
