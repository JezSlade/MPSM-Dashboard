# cURL Recipes

Copy-pasteable cURL commands for each endpoint.

**Note**: Replace `<REDACTED>` with actual values from your `.env` file.

## Account

### GET /Account/GetProfile
_Gets profile of current authenticated user._

**Operation ID**: `Account/GetProfile`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Account/GetProfile"
```

### GET /Account/GetPsk2faData
**Operation ID**: `Account/GetPsk2faData`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Account/GetPsk2faData?platform=MpsMonitor"
```

### GET /Account/GetPsk2faDataForAccount
**Operation ID**: `Account/GetPsk2faDataForAccount`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Account/GetPsk2faDataForAccount"
```

### GET /Account/GetPsk2faDataForProfile
**Operation ID**: `Account/GetPsk2faDataForProfile`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Account/GetPsk2faDataForProfile"
```

## AlertLimit2Api

### GET /AlertLimit2/Customer/GetDefault
**Operation ID**: `AlertLimit2/Customer/GetDefault`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit2/Customer/GetDefault?code=None"
```

### GET /AlertLimit2/Customer/GetProduct
**Operation ID**: `AlertLimit2/Customer/GetProduct`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit2/Customer/GetProduct?customerCode=None&id=None"
```

### GET /AlertLimit2/Customer/GetProductList
**Operation ID**: `AlertLimit2/Customer/GetProductList`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit2/Customer/GetProductList"
```

### GET /AlertLimit2/Dealer/GetDefault
**Operation ID**: `AlertLimit2/Dealer/GetDefault`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit2/Dealer/GetDefault?code=None"
```

### GET /AlertLimit2/Dealer/GetProduct
**Operation ID**: `AlertLimit2/Dealer/GetProduct`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit2/Dealer/GetProduct?dealerCode=None&id=None"
```

### GET /AlertLimit2/Dealer/GetProductList
**Operation ID**: `AlertLimit2/Dealer/GetProductList`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit2/Dealer/GetProductList"
```

### GET /AlertLimit2/GetAllLimits
**Operation ID**: `AlertLimit2/GetAllLimits`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit2/GetAllLimits"
```

### GET /AlertLimit2/Device/GetDefault
**Operation ID**: `AlertLimit2/Device/GetDefault`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit2/Device/GetDefault?id=None"
```

## AlertLimitApi

### GET /AlertLimit/Customer/Get
_Get customer Alert Limit settings_

**Operation ID**: `AlertLimit/Customer/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit/Customer/Get?code=None"
```

### GET /AlertLimit/Customer/Product/List
_Get dealers Alert Limit settings_

**Operation ID**: `AlertLimit/Customer/Product/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit/Customer/Product/List?code=None"
```

### GET /AlertLimit/Dealer/Get
_Get dealers Alert Limit settings_

**Operation ID**: `AlertLimit/Dealer/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit/Dealer/Get?code=None"
```

### GET /AlertLimit/Device/Get
_Get device Alert Limit settings_

**Operation ID**: `AlertLimit/Device/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//AlertLimit/Device/Get?id=None"
```

## Analytics

### GET /Analytics/GetReportFileResult
_Get result as file (Excel, CSV) from a saved report._

**Operation ID**: `Analytics/GetReportFileResult`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Analytics/GetReportFileResult?idReport=None&reportFormat=None"
```

### GET /Analytics/GetReportResult
_Get result from a saved report._

**Operation ID**: `Analytics/GetReportResult`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Analytics/GetReportResult?idReport=None"
```

## ApiClientApi

### GET /ApiClient/Account/List
_Get Api user list_

**Operation ID**: `ApiClient/Account/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//ApiClient/Account/List?id=None"
```

### GET /ApiClient/List
_Get Api Clients for Dealer._

**Operation ID**: `ApiClient/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//ApiClient/List?dealerCode=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /ApiClient/Account/Get
_GEt Api Client Detail_

**Operation ID**: `ApiClient/Account/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//ApiClient/Account/Get?id=None"
```

### GET /ApiClient/Get
_GEt Api Client Detail_

**Operation ID**: `ApiClient/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//ApiClient/Get?id=None"
```

## AzureADApi

### GET /azuread/GetCustomerAzureSettings
_Get Azure Ad customer configuration_

**Operation ID**: `azuread/GetCustomerAzureSettings`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//azuread/GetCustomerAzureSettings?code=None"
```

### GET /azuread/GetDealerAzureSettings
_Get Azure Ad configuration_

**Operation ID**: `azuread/GetDealerAzureSettings`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//azuread/GetDealerAzureSettings?code=None"
```

## Billing

### GET /Billing/GetInvoiceCategories
_Get Invoice Categories_

**Operation ID**: `Billing/GetInvoiceCategories`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Billing/GetInvoiceCategories"
```

## CommunicationApi

### GET /Communication/GetPortalReleaseNotes
_Get Portal Release Notes_

**Operation ID**: `Communication/GetPortalReleaseNotes`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Communication/GetPortalReleaseNotes"
```

## ConsumableApi

## CostCenter

## Counter

### GET /Counter/Device/List
_Returns detailed counters_

**Operation ID**: `Counter/Device/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Counter/Device/List?fromDate=None&toDate=None&id=None"
```

### GET /Counter/ListMaintenanceKitCounters
_Returns maintenance kit counters_

**Operation ID**: `Counter/ListMaintenanceKitCounters`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Counter/ListMaintenanceKitCounters?id=None"
```

### GET /Counter/Device/Export
_Export detailed counters_

**Operation ID**: `Counter/Device/Export`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Counter/Device/Export?fromDate=None&toDate=None&id=None"
```

## CustomFieldApi

### GET /CustomField/List
_Returns the list of Custom Fields configured by the dealer_

**Operation ID**: `CustomField/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//CustomField/List?code=None"
```

### GET /CustomField/Get
_Returns a Custom Fields by Id_

**Operation ID**: `CustomField/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//CustomField/Get?id=None"
```

## Customer

### GET /Customer/Accessories/Get
_Gets the dealer alert settings_

**Operation ID**: `Customer/Accessories/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Customer/Accessories/Get?code=None"
```

### GET /Customer/AdvancedOptions/Get
_Gets the customer advanced options_

**Operation ID**: `Customer/AdvancedOptions/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Customer/AdvancedOptions/Get?code=None"
```

### GET /Customer/AlertSettings/Get
_Gets the dealer alert settings_

**Operation ID**: `Customer/AlertSettings/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Customer/AlertSettings/Get?code=None"
```

### GET /Customer/CustomerServicesStatus/Get
_Gets the customer services status._

**Operation ID**: `Customer/CustomerServicesStatus/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Customer/CustomerServicesStatus/Get?code=None"
```

### GET /Customer/EpsonSettings/Get
_Gets the epson ERS and USB settings_

**Operation ID**: `Customer/EpsonSettings/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Customer/EpsonSettings/Get?code=None"
```

### GET /Customer/EpsonUSBCustomerId/Get
_Get a new Epson USB customer ID for the email subject_

**Operation ID**: `Customer/EpsonUSBCustomerId/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Customer/EpsonUSBCustomerId/Get?code=None"
```

### GET /Customer/eXplorerSettings/Get
_Gets the customer eXplorer settings_

**Operation ID**: `Customer/eXplorerSettings/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Customer/eXplorerSettings/Get?code=None"
```

## CustomerCredentialsApi

## CustomerDashboard

### GET /CustomerDashboard
_Gets the customer's dashboard._

**Operation ID**: `CustomerDashboard`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//CustomerDashboard?code=None"
```

### GET /CustomerDashboard/Pages
**Operation ID**: `CustomerDashboard/Pages`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//CustomerDashboard/Pages?code=None"
```

## CustomerNotificationApi

### GET /CustomerNotification/Get
_Get notification_

**Operation ID**: `CustomerNotification/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//CustomerNotification/Get?id=None"
```

### GET /CustomerNotification/GetNotificationPlaceholders
_Get notification placeholders_

**Operation ID**: `CustomerNotification/GetNotificationPlaceholders`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//CustomerNotification/GetNotificationPlaceholders"
```

### GET /CustomerNotification/GetSampleNotification
_Get sample notification_

**Operation ID**: `CustomerNotification/GetSampleNotification`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//CustomerNotification/GetSampleNotification?id=None"
```

### GET /CustomerNotification/List
_GetNotificationList_

**Operation ID**: `CustomerNotification/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//CustomerNotification/List?code=None"
```

## Dealer

### GET /Dealer/AccountingSettings/Get
_Gets the dealer accounting settings._

**Operation ID**: `Dealer/AccountingSettings/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/AccountingSettings/Get?code=None"
```

### GET /Dealer/AdvancedOptions/Get
_Gets the dealer advanced options_

**Operation ID**: `Dealer/AdvancedOptions/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/AdvancedOptions/Get?code=None"
```

### GET /Dealer/AlertLimitOptions/Get
_Gets the alert limit options._

**Operation ID**: `Dealer/AlertLimitOptions/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/AlertLimitOptions/Get?code=None"
```

### GET /Dealer/AlertSettings/Get
_Gets the dealer alert settings_

**Operation ID**: `Dealer/AlertSettings/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/AlertSettings/Get?code=None"
```

### GET /Dealer/Customizations/Get
_Gets the dealer customizations._

**Operation ID**: `Dealer/Customizations/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/Customizations/Get?code=None"
```

### GET /Dealer/DealerServicesStatus/Get
_Gets the dealer services status._

**Operation ID**: `Dealer/DealerServicesStatus/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/DealerServicesStatus/Get?code=None"
```

### GET /Dealer/DistributorSettings/Get
_Get the Distributor dealer settings_

**Operation ID**: `Dealer/DistributorSettings/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/DistributorSettings/Get?code=None"
```

### GET /Dealer/ExportDealerTagsHierarchy
**Operation ID**: `Dealer/ExportDealerTagsHierarchy`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/ExportDealerTagsHierarchy?code=None"
```

### GET /Dealer/GetDealerHierarchy
_Gets the dealer._

**Operation ID**: `Dealer/GetDealerHierarchy`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/GetDealerHierarchy?code=None"
```

### GET /Dealer/GetDealerTagsHierarchy
**Operation ID**: `Dealer/GetDealerTagsHierarchy`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/GetDealerTagsHierarchy?code=None"
```

### GET /Dealer/Onboarding/Get
_Get the dealer onboarding survey_

**Operation ID**: `Dealer/Onboarding/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/Onboarding/Get?dealerCode=None"
```

### GET /Dealer/RemoteOfflineCountersSettings/Get
_Gets the dealer remote offline counters settings._

**Operation ID**: `Dealer/RemoteOfflineCountersSettings/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/RemoteOfflineCountersSettings/Get?code=None"
```

### GET /Dealer/eXplorerSettings/Get
_Gets the dealer eXplorer settings_

**Operation ID**: `Dealer/eXplorerSettings/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/eXplorerSettings/Get?code=None"
```

## DealerCounterBlendApi

### GET /Dealer/CounterBlend/Get
_Return a counter blend_

**Operation ID**: `Dealer/CounterBlend/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/CounterBlend/Get?dealerCode=None&id=None"
```

### GET /Dealer/CounterBlend/List
_Returns list of dealer counters detailed tags_

**Operation ID**: `Dealer/CounterBlend/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/CounterBlend/List?dealerCode=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Dealer/CounterBlend/Search
_Search form available counters detailed TAG_

**Operation ID**: `Dealer/CounterBlend/Search`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/CounterBlend/Search?dealerCode=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

## DealerCounterBlendToStandardApi

### GET /Dealer/CounterBlendToStandard/Get
_Gets the specified request._

**Operation ID**: `Dealer/CounterBlendToStandard/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/CounterBlendToStandard/Get?id=None"
```

### GET /Dealer/CounterBlendToStandard/GetByDevice
_Gets the by device._

**Operation ID**: `Dealer/CounterBlendToStandard/GetByDevice`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/CounterBlendToStandard/GetByDevice?id=None"
```

### GET /Dealer/CounterBlendToStandard/List
_Lists the specified request._

**Operation ID**: `Dealer/CounterBlendToStandard/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Dealer/CounterBlendToStandard/List?dealerCode=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

## DealerNotificationApi

### GET /DealerNotification/Get
_Get notification_

**Operation ID**: `DealerNotification/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerNotification/Get?id=None"
```

### GET /DealerNotification/GetNotificationPlaceholders
_Get notification placeholders_

**Operation ID**: `DealerNotification/GetNotificationPlaceholders`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerNotification/GetNotificationPlaceholders"
```

### GET /DealerNotification/GetSampleNotification
_Get sample notification_

**Operation ID**: `DealerNotification/GetSampleNotification`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerNotification/GetSampleNotification?id=None"
```

### GET /DealerNotification/List
**Operation ID**: `DealerNotification/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerNotification/List?dealerCode=None"
```

### GET /DealerNotification/Template/Get
_Get the dealer template base_

**Operation ID**: `DealerNotification/Template/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerNotification/Template/Get?code=None"
```

## DealerProduct

### GET /DealerProduct/Get
_Gets the specified request._

**Operation ID**: `DealerProduct/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerProduct/Get?dealerCode=None&id=None"
```

### GET /DealerProduct/List
_Gets the dealer list_

**Operation ID**: `DealerProduct/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerProduct/List?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

## DealerSupply

### GET /DealerSupply/Count
_Returns list of dealer supplies count_

**Operation ID**: `DealerSupply/Count`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupply/Count?code=None"
```

### GET /DealerSupply/Export
_Returns list of dealer supplies_

**Operation ID**: `DealerSupply/Export`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupply/Export?code=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /DealerSupply/Get
_Gets the dealer supply._

**Operation ID**: `DealerSupply/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupply/Get?id=None"
```

### GET /DealerSupply/List
_Returns list of dealer supplies_

**Operation ID**: `DealerSupply/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupply/List?code=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

## DealerSupplyPriceListingApi

### GET /DealerSupplyPriceListing/Get
_Get tradingPartnerSupplyListing_

**Operation ID**: `DealerSupplyPriceListing/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupplyPriceListing/Get?dealerCode=None&id=None"
```

### GET /DealerSupplyPriceListing/List
_Get tradingPartnerSuppliesListing_

**Operation ID**: `DealerSupplyPriceListing/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupplyPriceListing/List?dealerCode=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

## DealerSupplySetApi

### GET /DealerSupplySet/AssociateByDealerSupplySetAndRelativeProducts
_Automatically associate the devices (ONLY with a specific model) to a specific SupplySet (the customer is optional)_

**Operation ID**: `DealerSupplySet/AssociateByDealerSupplySetAndRelativeProducts`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupplySet/AssociateByDealerSupplySetAndRelativeProducts"
```

### GET /DealerSupplySet/Count
_Gets the Dealer Supplies set count._

**Operation ID**: `DealerSupplySet/Count`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupplySet/Count?code=None"
```

### GET /DealerSupplySet/CountDealerSupplySetAndDevicesPotentialAssociations
_Count the devices affected by the association of a supply set (the customer is optional)_

**Operation ID**: `DealerSupplySet/CountDealerSupplySetAndDevicesPotentialAssociations`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupplySet/CountDealerSupplySetAndDevicesPotentialAssociations"
```

### GET /DealerSupplySet/Export
_Deletes the specified supply set._

**Operation ID**: `DealerSupplySet/Export`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupplySet/Export?code=None"
```

### GET /DealerSupplySet/ExportExcel
_Deletes the specified supply set._

**Operation ID**: `DealerSupplySet/ExportExcel`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupplySet/ExportExcel?code=None"
```

### GET /DealerSupplySet/Get
_Gets the Dealer Supply Set._

**Operation ID**: `DealerSupplySet/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupplySet/Get?id=None"
```

### GET /DealerSupplySet/List
_Gets the Dealer Supplies set._

**Operation ID**: `DealerSupplySet/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupplySet/List?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /DealerSupplySet/ListDealerSupplySetFromStandardModels
_Gets the Supplies set creatable from standard model._

**Operation ID**: `DealerSupplySet/ListDealerSupplySetFromStandardModels`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//DealerSupplySet/ListDealerSupplySetFromStandardModels?dealerCode=None"
```

## Device

### GET /Device/Deleted/List
_This operation gets lists of devices paged and filtered_

**Operation ID**: `Device/Deleted/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/Deleted/List?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Device/Deleted/ListByDealer
_This operation gets lists of devices paged and filtered by dealer_

**Operation ID**: `Device/Deleted/ListByDealer`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/Deleted/ListByDealer?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Device/ExplorerDataAffinities/List
_Returns a list of DeviceExplorerDataAffinities filtered by idDevice_

**Operation ID**: `Device/ExplorerDataAffinities/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/ExplorerDataAffinities/List?id=None"
```

### GET /Device/MaintenanceAlerts/List
_Returns a list of maintenanceAlert device._

**Operation ID**: `Device/MaintenanceAlerts/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/MaintenanceAlerts/List?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Device/GetDeviceAdditionalInfos
**Operation ID**: `Device/GetDeviceAdditionalInfos`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/GetDeviceAdditionalInfos?id=None"
```

### GET /Device/GetDeviceGapInfos
**Operation ID**: `Device/GetDeviceGapInfos`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/GetDeviceGapInfos?id=None"
```

### GET /Device/GetLfpCounters
**Operation ID**: `Device/GetLfpCounters`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/GetLfpCounters?id=None"
```

### GET /Device/GetSuppliesDetails
_Returns a device by request parameters_

**Operation ID**: `Device/GetSuppliesDetails`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/GetSuppliesDetails?id=None"
```

### GET /Device/GetSuppliesDetailsInfo
_Gets current forecast and history consumable details for a specific device, consumable and consumable color type_

**Operation ID**: `Device/GetSuppliesDetailsInfo`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/GetSuppliesDetailsInfo?id=None"
```

### GET /Device/GetSuppliesDetailsSummary
_Get toners and photoconductors forecast details for a specific device_

**Operation ID**: `Device/GetSuppliesDetailsSummary`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/GetSuppliesDetailsSummary?id=None"
```

### GET /Device/GetZebraSuppliesDetailsSummary
**Operation ID**: `Device/GetZebraSuppliesDetailsSummary`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Device/GetZebraSuppliesDetailsSummary?id=None"
```

## Explorer

### GET /Explorer/Cluster/List
_This operation gets explorer clusters from all dealer customer_

**Operation ID**: `Explorer/Cluster/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/Cluster/List?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Explorer/Configuration/List
_Returns eXplorer Configurations_

**Operation ID**: `Explorer/Configuration/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/Configuration/List?customerCode=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Explorer/ExplorerDataCommand/List
_This operation gets explorer command list_

**Operation ID**: `Explorer/ExplorerDataCommand/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/ExplorerDataCommand/List?customerCode=None&id=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Explorer/ExplorerDataInfo/List
_This operation gets explorer environment infos_

**Operation ID**: `Explorer/ExplorerDataInfo/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/ExplorerDataInfo/List?customerCode=None&id=None"
```

### GET /Explorer/License/List
**Operation ID**: `Explorer/License/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/License/List?customerCode=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Explorer/Staging/List
_Get the staging connector list for a customer_

**Operation ID**: `Explorer/Staging/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/Staging/List?code=None"
```

### GET /Explorer/Cluster/AutoClusters
_This operation suggests explorer clusters from all dealer customer_

**Operation ID**: `Explorer/Cluster/AutoClusters`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/Cluster/AutoClusters?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Explorer/Cluster/Get
_This operation gets an explorer cluster_

**Operation ID**: `Explorer/Cluster/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/Cluster/Get?id=None"
```

### GET /Explorer/Configuration/Get
_Returns eXplorer configuration with subnets and schedules_

**Operation ID**: `Explorer/Configuration/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/Configuration/Get?customerCode=None&configurationId=None"
```

### GET /Explorer/Configuration/GetTestTableVersions
**Operation ID**: `Explorer/Configuration/GetTestTableVersions`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/Configuration/GetTestTableVersions?id=None"
```

### GET /Explorer/DataPings
**Operation ID**: `Explorer/DataPings`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/DataPings?id=None"
```

### GET /Explorer/DownloadLogs
_This operation gets explorer data and clusters_

**Operation ID**: `Explorer/DownloadLogs`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/DownloadLogs?id=None"
```

### GET /Explorer/GetClusterCounters
_Returns a customer's cluster counters (number of clusters, masters and slaves)_

**Operation ID**: `Explorer/GetClusterCounters`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/GetClusterCounters?code=None"
```

### GET /Explorer/GetConnectorEndpoints
_This operations gets the required web endpoints for a specific connector or group of connectors_

**Operation ID**: `Explorer/GetConnectorEndpoints`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/GetConnectorEndpoints?code=None"
```

### GET /Explorer/GetConnectors
_This operation gets explorer data and clusters_

**Operation ID**: `Explorer/GetConnectors`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/GetConnectors?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Explorer/GetDca4Otp
**Operation ID**: `Explorer/GetDca4Otp`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/GetDca4Otp?id=None"
```

### GET /Explorer/GetDcaCurrentVersion
_Get the release table versions of a specific DCA_

**Operation ID**: `Explorer/GetDcaCurrentVersion`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/GetDcaCurrentVersion?code=None"
```

### GET /Explorer/GetDcaReleaseNotes
_Get the release table versions of a specific DCA_

**Operation ID**: `Explorer/GetDcaReleaseNotes`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/GetDcaReleaseNotes?code=None"
```

### GET /Explorer/GetEndpointsLink
_Get Endpoints Link_

**Operation ID**: `Explorer/GetEndpointsLink`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/GetEndpointsLink?platform=None"
```

### GET /Explorer/GetExplorerDatas
_This operation gets explorer data from all dealer customer_

**Operation ID**: `Explorer/GetExplorerDatas`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/GetExplorerDatas?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Explorer/GetExplorerSetupLink
_Get Explorer Setup Link_

**Operation ID**: `Explorer/GetExplorerSetupLink`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/GetExplorerSetupLink?customerCode=None&code=None"
```

### GET /Explorer/GetJamcSetupLink
_Get Jamc Setup Link_

**Operation ID**: `Explorer/GetJamcSetupLink`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/GetJamcSetupLink?customerCode=None&code=None"
```

### GET /Explorer/RequestSendLogs
_This operation gets explorer data and clusters_

**Operation ID**: `Explorer/RequestSendLogs`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/RequestSendLogs?id=None"
```

### GET /Explorer/V3/ReleaseNotes
_Get the eXplorer V3 Release Notes_

**Operation ID**: `Explorer/V3/ReleaseNotes`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Explorer/V3/ReleaseNotes"
```

## IntegrationsApi

### GET /Integrations/GetJoinedCustomers
_Get current joined customers summary_

**Operation ID**: `Integrations/GetJoinedCustomers`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Integrations/GetJoinedCustomers"
```

### GET /Integrations/List
_List of available and configured integration_

**Operation ID**: `Integrations/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Integrations/List?code=None"
```

### GET /Integrations/Get
_Get an integration configuration_

**Operation ID**: `Integrations/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Integrations/Get?dealerCode=None&id=None"
```

### GET /Integrations/GetJoinedDevices
_Get current joined devices summary_

**Operation ID**: `Integrations/GetJoinedDevices`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Integrations/GetJoinedDevices"
```

### GET /Integrations/GetLogisticPlaceholders
_Get logistic placeholders_

**Operation ID**: `Integrations/GetLogisticPlaceholders`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Integrations/GetLogisticPlaceholders"
```

### GET /Integrations/GetNew
_Get a new integration configuration_

**Operation ID**: `Integrations/GetNew`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Integrations/GetNew"
```

### GET /Integrations/eautomate/GetEAutomateLog
_Gets the top 10 recent eAutomate log entries._

**Operation ID**: `Integrations/eautomate/GetEAutomateLog`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Integrations/eautomate/GetEAutomateLog"
```

### GET /Integrations/eautomate/runjoin
_Runs eAutomate devices join_

**Operation ID**: `Integrations/eautomate/runjoin`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Integrations/eautomate/runjoin"
```

## Office

### GET /Office/OfficeFloor/List
**Operation ID**: `Office/OfficeFloor/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Office/OfficeFloor/List?id=None"
```

### GET /Office/OfficeFloor/GetPin
**Operation ID**: `Office/OfficeFloor/GetPin`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Office/OfficeFloor/GetPin?id=None"
```

## OktaApi

### GET /okta/GetCustomerOktaSettings
_Get the Okta settings for the customer_

**Operation ID**: `okta/GetCustomerOktaSettings`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//okta/GetCustomerOktaSettings?code=None"
```

### GET /okta/GetDealerOktaSettings
_Get the Okta settings for the dealer_

**Operation ID**: `okta/GetDealerOktaSettings`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//okta/GetDealerOktaSettings?code=None"
```

## Order

### GET /Orders/GetOrderLineStatuses
_Gets the dealers list_

**Operation ID**: `Orders/GetOrderLineStatuses`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Orders/GetOrderLineStatuses"
```

## PanelMessageAlertApi

## Product

### GET /Product/Customer/List
_Get the products of the Customer_

**Operation ID**: `Product/Customer/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Product/Customer/List?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Product/Dealer/List
_Get the products of the Dealer_

**Operation ID**: `Product/Dealer/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Product/Dealer/List?dealerCode=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Product/Dealer/ListBrands
_Get the brands of the Dealer_

**Operation ID**: `Product/Dealer/ListBrands`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Product/Dealer/ListBrands?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Product/Dealer/ListModels
_Ge the models of the Dealer_

**Operation ID**: `Product/Dealer/ListModels`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Product/Dealer/ListModels?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Product/GetBrands
_Gets the brands related to all dealers_

**Operation ID**: `Product/GetBrands`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Product/GetBrands?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Product/GetModels
_Gets the models related to all dealers_

**Operation ID**: `Product/GetModels`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Product/GetModels?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Product/GetSnmpDiscoveryBrands
_Gets the Snmp discovery brands_

**Operation ID**: `Product/GetSnmpDiscoveryBrands`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Product/GetSnmpDiscoveryBrands?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

## Project

### GET /Project/GetContractFile
_Gets the project contract file._

**Operation ID**: `Project/GetContractFile`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Project/GetContractFile?id=None"
```

### GET /Project/GetDetail
_Gets the project._

**Operation ID**: `Project/GetDetail`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Project/GetDetail?id=None"
```

## RoleApi

### GET /Role/GetAllCapabilities
_Get the all available capabilities_

**Operation ID**: `Role/GetAllCapabilities`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Role/GetAllCapabilities?isForAccount=None&code=None"
```

### GET /Role/List
_Get the list of available capability sets (roles)_

**Operation ID**: `Role/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Role/List?dealerCode=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /Role/Get
_Get a capability set role by Id and Dealer Code_

**Operation ID**: `Role/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//Role/Get?dealerCode=None&id=None"
```

## Saga

## SdsActionApi

### GET /SdsAction/GetDeviceAction
_Gets the device action._

**Operation ID**: `SdsAction/GetDeviceAction`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsAction/GetDeviceAction?id=None"
```

### GET /SdsAction/GetDeviceActions
_Gets the device actions._

**Operation ID**: `SdsAction/GetDeviceActions`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsAction/GetDeviceActions?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /SdsAction/GetDeviceActionsDashboard
_Gets the device actions dashboard._

**Operation ID**: `SdsAction/GetDeviceActionsDashboard`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsAction/GetDeviceActionsDashboard"
```

## SdsConnectorApi

### GET /SdsConnector/GetConnector
_Get a connector._

**Operation ID**: `SdsConnector/GetConnector`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsConnector/GetConnector?id=None"
```

### GET /SdsConnector/GetConnectors
_Gets the connectors._

**Operation ID**: `SdsConnector/GetConnectors`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsConnector/GetConnectors?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /SdsConnector/GetJamcConnectors
_Gets the jamc connectors._

**Operation ID**: `SdsConnector/GetJamcConnectors`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsConnector/GetJamcConnectors"
```

### GET /SdsConnector/GetLogs
_Invoke the log request to the JAMC_

**Operation ID**: `SdsConnector/GetLogs`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsConnector/GetLogs"
```

### GET /SdsConnector/GetWppConnectors
_Gets the wpp connectors._

**Operation ID**: `SdsConnector/GetWppConnectors`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsConnector/GetWppConnectors"
```

## SdsCustomerApi

### GET /SdsCustomer/GetAssessTemplate
**Operation ID**: `SdsCustomer/GetAssessTemplate`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsCustomer/GetAssessTemplate?customerCode=None&id=None"
```

### GET /SdsCustomer/GetAssessTemplates
**Operation ID**: `SdsCustomer/GetAssessTemplates`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsCustomer/GetAssessTemplates?code=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /SdsCustomer/GetCredential
_Gets the credential._

**Operation ID**: `SdsCustomer/GetCredential`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsCustomer/GetCredential?id=None"
```

### GET /SdsCustomer/GetCustomerOperation
_Gets the customer operation._

**Operation ID**: `SdsCustomer/GetCustomerOperation`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsCustomer/GetCustomerOperation?id=None"
```

### GET /SdsCustomer/GetCustomerOperations
_Gets the customer operations._

**Operation ID**: `SdsCustomer/GetCustomerOperations`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsCustomer/GetCustomerOperations?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /SdsCustomer/GetNewAssessTemplate
**Operation ID**: `SdsCustomer/GetNewAssessTemplate`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsCustomer/GetNewAssessTemplate"
```

## SdsDeviceApi

### GET /SdsDevice/GetAssessTemplate
**Operation ID**: `SdsDevice/GetAssessTemplate`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsDevice/GetAssessTemplate?id=None"
```

### GET /SdsDevice/GetConfigItems
_Gets the configuration items._

**Operation ID**: `SdsDevice/GetConfigItems`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsDevice/GetConfigItems?id=None"
```

### GET /SdsDevice/GetCounters
_Gets the counters._

**Operation ID**: `SdsDevice/GetCounters`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsDevice/GetCounters?id=None"
```

### GET /SdsDevice/GetDeviceOperation
_Gets the device operation._

**Operation ID**: `SdsDevice/GetDeviceOperation`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsDevice/GetDeviceOperation?id=None"
```

### GET /SdsDevice/GetDeviceRemoteEws
_Gets the device remote ews._

**Operation ID**: `SdsDevice/GetDeviceRemoteEws`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsDevice/GetDeviceRemoteEws?id=None"
```

### GET /SdsDevice/GetDevicesOperations
_Gets the devices operations._

**Operation ID**: `SdsDevice/GetDevicesOperations`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsDevice/GetDevicesOperations?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /SdsDevice/GetOnDeviceServices
_Gets the on device services._

**Operation ID**: `SdsDevice/GetOnDeviceServices`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsDevice/GetOnDeviceServices?id=None"
```

### GET /SdsDevice/GetSupplyDetails
_Gets the supply details._

**Operation ID**: `SdsDevice/GetSupplyDetails`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsDevice/GetSupplyDetails?id=None"
```

### GET /SdsDevice/GetZendeskTicketInfo
**Operation ID**: `SdsDevice/GetZendeskTicketInfo`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsDevice/GetZendeskTicketInfo?id=None"
```

## SdsEventApi

### GET /SdsEvent/GetDeviceEvent
_Gets the device event._

**Operation ID**: `SdsEvent/GetDeviceEvent`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsEvent/GetDeviceEvent?id=None"
```

### GET /SdsEvent/GetDeviceEvents
**Operation ID**: `SdsEvent/GetDeviceEvents`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsEvent/GetDeviceEvents"
```

## SdsScanApi

### GET /SdsScan/ScanDevice
_Retrieve saved SDS CLOUD device data_

**Operation ID**: `SdsScan/ScanDevice`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsScan/ScanDevice?id=None"
```

### GET /SdsScan/ScanImmediate
_Retrieve all updated SDS device data. The operation will take about 20 minutes_

**Operation ID**: `SdsScan/ScanImmediate`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SdsScan/ScanImmediate?id=None"
```

## ShippedSupply

## StandardProduct

### GET /StandardProduct/ListDevicesInOperation
**Operation ID**: `StandardProduct/ListDevicesInOperation`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//StandardProduct/ListDevicesInOperation?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /StandardProduct/ListOperations
**Operation ID**: `StandardProduct/ListOperations`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//StandardProduct/ListOperations?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /StandardProduct/ListStandardProducts
**Operation ID**: `StandardProduct/ListStandardProducts`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//StandardProduct/ListStandardProducts?pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /StandardProduct/GetExcelReport
**Operation ID**: `StandardProduct/GetExcelReport`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//StandardProduct/GetExcelReport?dealerCode=None&id=None"
```

### GET /StandardProduct/GetOperation
**Operation ID**: `StandardProduct/GetOperation`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//StandardProduct/GetOperation?id=None"
```

### GET /StandardProduct/GetProductsToAssociate
**Operation ID**: `StandardProduct/GetProductsToAssociate`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//StandardProduct/GetProductsToAssociate?dealerCode=None"
```

### GET /StandardProduct/GetStandardProductsSummary
**Operation ID**: `StandardProduct/GetStandardProductsSummary`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//StandardProduct/GetStandardProductsSummary?dealerCode=None"
```

## SupplyAlert

### GET /SupplyAlert/GetAvailableMaintenanceKitColors
_Gets available Maintenance kit colors_

**Operation ID**: `SupplyAlert/GetAvailableMaintenanceKitColors`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SupplyAlert/GetAvailableMaintenanceKitColors"
```

### GET /SupplyAlert/GetAvailableMaintenanceKitTypes
_Gets available Maintenance kit types_

**Operation ID**: `SupplyAlert/GetAvailableMaintenanceKitTypes`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SupplyAlert/GetAvailableMaintenanceKitTypes"
```

### GET /SupplyAlert/GetAvailableSuppliesForADevice
_Gets available supplies for a device_

**Operation ID**: `SupplyAlert/GetAvailableSuppliesForADevice`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//SupplyAlert/GetAvailableSuppliesForADevice?deviceId=None&supplyType=ManteinanceKit&colorType=NotAvailable&maintenanceKitTypeId=None&maintenanceKitColorId=None&warning=None&language=Italiano"
```

## Ticket

## TraceVolume

### GET /TraceVolume/List
_Returns a list of TraceVolume by device_

**Operation ID**: `TraceVolume/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//TraceVolume/List?id=None"
```

### GET /TraceVolume/Get
_Gets a specific trace volume by its id_

**Operation ID**: `TraceVolume/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//TraceVolume/Get?id=None"
```

## TradingPartnerApi

### GET /TradingPartner/List
_Get tradingPartner_

**Operation ID**: `TradingPartner/List`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//TradingPartner/List?dealerCode=None&pageNumber=None&pageRows=None&sortColumn=None&sortOrder=Asc"
```

### GET /TradingPartner/Get
_Get tradingPartner_

**Operation ID**: `TradingPartner/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//TradingPartner/Get?dealerCode=None&id=None"
```

## WhiteLabel

### GET /WhiteLabel/Get
_Get the white label._

**Operation ID**: `WhiteLabel/Get`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//WhiteLabel/Get?code=None"
```

### GET /WhiteLabel/GetWhiteLabelCustomizationByUrl
_Get the whitelabel customizations by the caller URL_

**Operation ID**: `WhiteLabel/GetWhiteLabelCustomizationByUrl`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//WhiteLabel/GetWhiteLabelCustomizationByUrl"
```

### GET /WhiteLabel/GetWhitelabelPlaceholders
_Get whitelabel placeholders_

**Operation ID**: `WhiteLabel/GetWhitelabelPlaceholders`

```bash
curl -H "Authorization: Bearer <REDACTED>" "https://api.abassetmanagement.com/api3//WhiteLabel/GetWhitelabelPlaceholders"
```

## ZebraDeviceApi
