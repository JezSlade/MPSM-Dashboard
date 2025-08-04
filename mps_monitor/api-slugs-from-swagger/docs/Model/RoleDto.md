# # RoleDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** | Name of the role | [optional]
**description** | **string** | Description of the role | [optional]
**code** | **string** | Code of the role | [optional]
**capabilities** | [**\OpenAPI\Client\Model\CapabilityDto[]**](CapabilityDto.md) | Capabilities associated to the role | [optional]
**dealer_code** | **string** | Dealer associated to that role | [optional]
**is_custom_role** | **bool** | True if it is a custom role | [optional]
**is_shared** | **bool** | True if it is a shared role | [optional]
**is_shared_by_current_dealer** | **bool** | True if it is the role is shared by the requesting dealer | [optional]
**force2fa** | **bool** | Gets or sets a value indicating whether this {MpsMonitor.Models.RoleDto} must use 2fa. | [optional]
**force_sso** | **bool** | Gets or sets a value indicating whether [force sso]. | [optional]
**max_login_failed_attempts** | **int** | Gets or sets the maximum login failed attempts. | [optional]
**disable_after_inactives_days** | **int** | Gets or sets the disable after inactives days. | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
