# # SdsDeviceEventDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**event_type** | **string** |  | [optional]
**event_code** | **string** |  | [optional]
**event_date_utc** | **\DateTime** |  | [optional]
**sequence_number** | **int** | Required, integer, an identifier to differentiate different occurrences of the same event | [optional]
**event_occurrences** | **int** | Required, integer, the number of these events that have occurred without another event between them. | [optional]
**total_impressions** | **float** | Required, long unsigned integer, the number of impressions on the device at the time of the error. | [optional]
**firmware_version** | **string** | Required, string, the version of firmware on the device at the time of the error. | [optional]
**event_description** | **string** | Optional, Localized short description of the event. | [optional]
**link** | **string** | Optional, URI,  fully qualified path to the documentation for this error. | [optional]
**is_hidden** | **bool** | Flag set by Dealer that ignore this event | [optional]
**customer_id** | **string** | Gets or sets the customer identifier. | [optional]
**dealer_id** | **string** | Gets or sets the dealer identifier. | [optional]
**device_id** | **string** | Gets or sets the device identifier. | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
