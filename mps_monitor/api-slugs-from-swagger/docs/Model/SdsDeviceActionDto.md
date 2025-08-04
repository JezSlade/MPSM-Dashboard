# # SdsDeviceActionDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**customer_id** | **string** | Gets or sets the customer identifier. | [optional]
**customer_code** | **string** | Gets or sets the customer code. | [optional]
**customer_description** | **string** | Gets or sets the customer description. | [optional]
**dealer_id** | **string** | Gets or sets the dealer identifier. | [optional]
**dealer_code** | **string** | Gets or sets the dealer code. | [optional]
**dealer_description** | **string** | Gets or sets the dealer description. | [optional]
**installed_product_serial_number** | **string** |  | [optional]
**brand** | **string** | Gets or sets the brand. | [optional]
**model** | **string** | Gets or sets the model. | [optional]
**action_jam_id** | **string** | Required, guid, the actionJamid of the action | [optional]
**device_id** | **string** | Required, string, the id of the device | [optional]
**code** | **string** | Required, string, the code associated with the action. | [optional]
**event_code_context** | **string** | Optional, string, the event code associated with this action if available | [optional]
**action_date_utc** | **\DateTime** | Date: Required, date time in UTC, the date and time the action was created. | [optional]
**severity** | **string** | Gets or sets the severity. | [optional]
**current_state** | **string** | Gets or sets the state of the current. | [optional]
**status_reports** | [**\OpenAPI\Client\Model\SdsActionStatusReportDto[]**](SdsActionStatusReportDto.md) | Gets or sets the status reports. | [optional]
**has_genuine_hp_cartridges** | **bool** | Gets or sets a value indicating whether this instance has genuine hp cartridges. | [optional]
**title** | **string** | Optional, string, localized title of the action document. | [optional]
**description** | **string** | Optional, string, localized description of the action document. | [optional]
**mean_time_to_repair** | **string** | Optional, Localized string for repair time | [optional]
**service_level** | **string** | Optional, localized description of expertise required | [optional]
**tools** | **string** | Optional, localized array of strings that describe tools required. | [optional]
**parts** | **string** | Optional, list of updated replacement part numbers. | [optional]
**link** | **string** | Optional, URI, fully qualified path to the localized documentation for this action.Link expires after 7 days.A new link will be created for each call. | [optional]
**total_impressions** | **int** | Optional, long unsigned integer, the number of impressions on the device at the time of the action creation. | [optional]
**firmware_version** | **string** | Optional, string, the version of firmware on the device at the time of the action creation. | [optional]
**action_type** | **string** | Gets or sets the type of the action. | [optional]
**predictive_data** | [**\OpenAPI\Client\Model\SdsActionPredictiveDataDto**](SdsActionPredictiveDataDto.md) |  | [optional]
**customer_reported_problem_data** | [**\OpenAPI\Client\Model\SdsActionCustomerReportedProblemDataDto**](SdsActionCustomerReportedProblemDataDto.md) |  | [optional]
**office_id** | **string** | Gets or sets the office identifier. | [optional]
**office_code** | **string** | Gets or sets the office code. | [optional]
**office_description** | **string** | Gets or sets the office description. | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
