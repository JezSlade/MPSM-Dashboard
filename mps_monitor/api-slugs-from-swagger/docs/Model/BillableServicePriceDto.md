# # BillableServicePriceDto

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**billable_service_code** | **string** | BillableService Code | [optional]
**selling_level** | **string** | SellingLevel (Distributor, SuperDealer, Dealer) | [optional]
**lower_band** | **int** | Lower Band | [optional]
**upper_band** | **int** | Upper Band | [optional]
**lower_limit** | **int** | Lower Limit | [optional]
**upper_limit** | **int** | Upper Limit | [optional]
**price_distributor** | **float** | Price at Distributor level | [optional]
**price_super_dealer** | **float** | Price at SuperDealer level | [optional]
**price_dealer** | **float** | Price at Dealer level | [optional]
**price_customer** | **float** | Price at Customer level | [optional]
**price_discount_dealer** | **float** | Price Discount in money at dealer Dealer level | [optional]
**price_discount_distributor** | **float** | Price Discount in money at Distributor level | [optional]
**price_discount_super_dealer** | **float** | Price Discount in money at SuperDealer level | [optional]
**price_discount_percentage_dealer** | **float** | Price Discount percentage at Dealer level | [optional]
**price_discount_percentage_distributor** | **float** | Price Discount percentage at Distributor level | [optional]
**price_discount_percentage_super_dealer** | **float** | Price Discount percentage at SuperDealer level | [optional]
**code** | **string** | Service code | [optional]
**description** | **string** | Service description | [optional]
**quantity** | **float** | Quantity | [optional]
**scheduled_date** | **\DateTime** | One shot scheduled date | [optional]
**device** | [**\OpenAPI\Client\Model\DeviceBaseDto**](DeviceBaseDto.md) |  | [optional]
**id** | **string** | Gets or sets the identifier. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
