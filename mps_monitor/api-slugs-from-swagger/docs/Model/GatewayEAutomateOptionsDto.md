# # GatewayEAutomateOptionsDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**user** | **string** | PIP user | [optional]
**password** | **string** | PIP Password | [optional]
**company_id** | **string** | EAutomate Company Id | [optional]
**version** | **string** | PIP Version | [optional]
**partner_token** | **string** |  | [optional]
**endpoint** | **string** | PIP Endpoint | [optional]
**meter_source** | **string** | EAutomate Meter Source Code [MPSMonitor, External] | [optional]
**meter_types** | [**\OpenAPI\Client\Model\MeterType[]**](MeterType.md) | EAutomate Meter Types Code for mapping [mono:B\\W, color:ColouSr] | [optional]
**include_unclassified** | **bool** | default false: True means that classified and uncliassified devices will be joined. | [optional]
**join_devices_type** | **string** | Join Devices Type | [optional]
**discard_counters_older_than_days** | **int** | Gets or sets the discard counters older than days. | [optional]
**match_rules** | [**\OpenAPI\Client\Model\MatchRule[]**](MatchRule.md) | Additional device matching rules | [optional]
**custom_schedule** | **bool** | Custom schedule | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
