# # SdsSetDevicesUpdateFirmwareResponse

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**devices_set_firmware_with_success** | [**\OpenAPI\Client\Model\DevicesIdAndFirmware[]**](DevicesIdAndFirmware.md) | Gets or sets the Set Firmware Operation success | [optional]
**devices_set_firmware_with_fail** | [**\OpenAPI\Client\Model\DevicesIdAndFirmware[]**](DevicesIdAndFirmware.md) | Gets or sets the Set Firmware Operation fail | [optional]
**is_valid** | **bool** | Returns true if the response is valid (No errors). | [optional]
**errors** | [**\OpenAPI\Client\Model\CodeDesc[]**](CodeDesc.md) | Gets or sets the errors. The list is empty if the response is valid | [optional]
**return_value** | **string** | Gets or sets the generic string return value. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
