# # SdsActionStatusReportDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**action_date_utc** | **\DateTime** | Date: Required, date time in UTC, the date and time the action was updated | [optional]
**state** | **string** | Optional, see Action State for definition, new state of the action.If not provided then Severity must be provided | [optional]
**severity** | **string** | Optional, see Action Severity for definition, new state of the action.If not provided then State must be provided | [optional]
**comments** | **string** | Optional, string, any additional comments related to the issue. | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
