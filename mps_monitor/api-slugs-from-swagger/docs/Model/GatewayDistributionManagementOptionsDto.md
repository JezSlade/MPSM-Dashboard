# # GatewayDistributionManagementOptionsDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**custom_schedule** | **bool** | Custom schedule | [optional]
**app_environment** | **string** | App Environment (production, sandbox) | [optional]
**include_unclassified** | **bool** | default false: True means that classified and uncliassified devices will be sent. | [optional]
**discard_counters_older_than_hours** | **int** | Gets or sets the discard counters older than hours. | [optional]
**customer_ids** | **object[]** | If empty, all customers will be sent.              If set, only the customers in the list will be sent.              Array of int or encrypted Ids | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
