# # ExportFilterDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | Id of the filter | [optional]
**name** | **string** | Filter name | [optional]
**field_type** | **string** | Filter type (string, number, date, ...) | [optional]
**description** | **string** | Filter description | [optional]
**value** | **string** | Filter value | [optional]
**string_value** | **string** | Filter value as string | [optional]
**multi_value** | [**\OpenAPI\Client\Model\KeyValuePairStringString[]**](KeyValuePairStringString.md) | Filter values in case of IsMultivalue | [optional]
**is_nullable** | **bool** | True if the filter is not mandatory | [optional]
**is_multivalue** | **bool** | True if the filter allows multi values | [optional]
**domain_values** | [**\OpenAPI\Client\Model\KeyValuePairStringString[]**](KeyValuePairStringString.md) | Filter available values. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
