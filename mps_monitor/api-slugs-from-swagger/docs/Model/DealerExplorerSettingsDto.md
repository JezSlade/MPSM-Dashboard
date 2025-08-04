# # DealerExplorerSettingsDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**scans_number_morning** | **int** | Gets or set the number of scans between 9-13 | [optional]
**scans_number_afternoon** | **int** | Gets or set the number of scans between 13-16 | [optional]
**scans_number_evening** | **int** | Gets or set the number of scans between 16-20 | [optional]
**auto_enable_default_configuration** | **bool** | Gets or set the AutoEnableDefaultConfiguration | [optional]
**automatic_update** | **bool** | Gets or sets a value indicating whether [automatic update]. | [optional]
**use_dca4_as_default** | **bool** | Gets or sets a value indicating whether [use dca4 as default].              Used for installation proposal | [optional]
**dca4_stack** | **string** | Gets or sets the dca4 stack. | [optional]
**explorer_interval** | [**\OpenAPI\Client\Model\ExplorerIntervalDto**](ExplorerIntervalDto.md) |  | [optional]
**default_explorer_interval** | [**\OpenAPI\Client\Model\ExplorerIntervalDto**](ExplorerIntervalDto.md) |  | [optional]
**explorer_working_days** | [**\OpenAPI\Client\Model\ExplorerWorkingDayDto[]**](ExplorerWorkingDayDto.md) | Gets or sets the explorer working days. | [optional]
**available_snmp_discovery_brands** | [**\OpenAPI\Client\Model\KeyValue[]**](KeyValue.md) | Gets or sets the available SNMP discovery brands. | [optional]
**preferred_snmp_discovery_brands** | **string[]** | Gets or sets the preferred SNMP discovery brands. | [optional]
**dealer_code** | **string** | Gets or sets the DealerCode. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
