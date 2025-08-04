# # CreateShippingSupplyRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**device_id** | **string** | Gets or sets the device identifier. | [optional]
**price** | **float** | Gets or sets the price. | [optional]
**supply** | [**\OpenAPI\Client\Model\DealerSupplyDto**](DealerSupplyDto.md) |  | [optional]
**replace_supply_in_custom_set** | **bool** | In case of Custom Supply Set associated to the device, the Supply specified will replace the same partnumber type. | [optional]
**add_supply_to_supply_set** | **bool** | If device is attached to a SupplySet,add the supply into this (if it is not associated) | [optional]
**quantity** | **int** | Gets or sets the quantity. | [optional]
**counter** | **int** | Gets or sets the counter. | [optional]
**creation** | **\DateTime** | Gets or sets the creation. | [optional]
**document_number** | **string** | Gets or sets the document number. | [optional]
**order_number** | **string** | Gets or sets the order number. | [optional]
**department** | **string** | Gets or sets the department. | [optional]
**contact** | **string** | Gets or sets the contact. | [optional]
**send_customer_notification_email** | **bool** | Send notification email to customer if enabled | [optional]
**activate_logistic_notification** | **bool** | Activate logistic notification process | [optional]
**dealer_gateway_ids** | **string[]** | Logistic gateway id | [optional]
**supply_price_id** | **string** | TradingPartnerSuppliesListing Id | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
