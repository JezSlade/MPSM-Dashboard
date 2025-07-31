# Get Customers:
```
{
  "Url": "Customer/GetCustomers",
  "Request": {
    "DealerCode": "NY06AGDWUQ",
    "Code": null,
    "HasHpSds": null,
    "FilterText": null,
    "PageNumber": 1,
    "PageRows": 2147483647,
    "SortColumn": "Id",
    "SortOrder": 0
  },
  "Method": "POST"
}
```
## Get Customers (Example Response):
```
Customer List (76): 
 [
  {
    "DealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
    "DealerCode": "NY06AGDWUQ",
    "DealerDescription": "SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE",
    "Code": "KMZQLW08OV",
    "Description": "102403 REED LALLIER CHEVROLET",
    "CountryCode": "US",
    "CountryName": "United States",
    "CountryIsEu": false,
    "ExternalIdentifier": null,
    "Id": "IGqXz-HQDlikBkkKKpdJbw2"
  }
  ```

# Get List of Customer Devices:

```
{
  "Url": "Device/List",
  "Request": {
    "FilterDealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
    "FilterCustomerCodes": [
      "W9OPXL0YDK"
    ],
    "ProductBrand": null,
    "ProductModel": null,
    "OfficeId": null,
    "Status": 1,
    "FilterText": null,
    "PageNumber": 1,
    "PageRows": 50,
    "SortColumn": "Id",
    "SortOrder": 0
  },
  "Method": "POST"
}
```
## Get List of Customer Devices (Example response):
```
Devices (50): 
 [
  {
    "DealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
    "DealerCode": "NY06AGDWUQ",
    "DealerDescription": "SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE",
    "CustomerId": "0xUi5WEYLzOCrZ8ILowOvA2",
    "CustomerCode": "W9OPXL0YDK",
    "CustomerDescription": "CAPE FEAR VALLEY MED CTR.",
    "OfficeId": "wIqJlMhxYDkXg79UAibfIQ2",
    "OfficeCode": "DEFAULT",
    "OfficeDescription": "DEFAULT",
    "ProjectId": "Snims6JUjLSNsVeUlxXEBg2",
    "ProjectDescription": "",
    "Contact": null,
    "Department": null,
    "IsInsideProject": true,
    "IsAlertGenerator": true,
    "IsManageSupplies": false,
    "IsClassified": true,
    "LastUpdate": "2025-07-28T17:21:00Z",
    "Install": "2024-05-06T04:00:00Z",
    "BlackToner": 87,
    "BlackToner1": null,
    "BlackToner2": null,
    "BlackToner3": null,
    "BlackPhoto": null,
    "CyanToner": null,
    "CyanPhoto": null,
    "MagentaToner": null,
    "MagentaPhoto": null,
    "YellowToner": null,
    "YellowPhoto": null,
    "Note": null,
    "IsOffline": false,
    "Uninstall": null,
    "MonthlyMonoVolume": 1736,
    "MonthlyColorVolume": null,
    "Firmware": "MOGAMIXXXN002.2247A.00",
    "CounterMono": 92467,
    "CounterColor": null,
    "InstallCounterMono": 66552,
    "InstallCounterColor": null,
    "MailDeliveryToner": null,
    "HardwareOrderNumber": null,
    "TonerOrderNumber": null,
    "Product": {
      "Logo": null,
      "Color": 2,
      "FormatType": 3,
      "Model": "HP LASERJET PRO M404DN",
      "Brand": "HP",
      "Id": "AwGHqUa3hamQViWWXpAyOg2"
    },
    "DealerSupplySet": null,
    "ManageConsumables": true,
    "SdsDevice": {
      "LastContactTimeUtc": "2025-07-28T10:21:00Z",
      "LastMpsContactTimeUtc": "2024-06-18T18:57:25Z",
      "MskuAttributeState": "Absent",
      "HasGenuineHpCartridges": true,
      "GenuineHpCartridgeStatus": "GenuineHP",
      "IsHpManagedDevice": false,
      "HpManagedAuthorizationExpirationUtc": null,
      "EnhancedAccessExpirationUtc": null,
      "HasEarlyReplacement": false,
      "HasOpenCommonActions": false,
      "HasOpenPredictiveActions": false,
      "JamDeviceId": 155339422,
      "HostName": null,
      "SerialNumber": "JPBDM26300",
      "ModelName": "HP LASERJET PRO M404DN",
      "MacAddress": "6C02E0C16790",
      "IpAddress": "10.5.1.31",
      "IsUsbDevice": false,
      "Id": "gP-OjFfuMIrGnuTEWJPmBw2"
    },
    "ToWork": false,
    "MonoCoverage": 4.39,
    "ColorCoverage": null,
    "FirmawareVersionUpgradeAvailable": "MOGAMIXXXN002.2512A.00",
    "LastAssessRemediateOperationResult": null,
    "LastAssessRemediateOperationPending": false,
    "LastAssessRemediateOperationPolicyName": null,
    "LastAssessRemediateOperationAssessAndRemediate": null,
    "LastSetCredentialsOperationResult": null,
    "LastSetCredentialsOperationResultPending": false,
    "LastRapaOperationResult": null,
    "LastRapaOperationResultPending": false,
    "LastSetDeviceConfigResult": null,
    "LastSetDeviceConfigResultPending": false,
    "CanSetConfigItem": true,
    "SerialNumber": "JPBDM26300",
    "MacAddress": "6C-02-E0-C1-67-90",
    "IpAddress": "10.5.1.31",
    "SystemName": "HPC16790",
    "AssetNumber": null,
    "ExternalIdentifier": "FQ966",
    "Id": "gP-OjFfuMIrGnuTEWJPmBw2"
  }
  ```
# Get All Alerts:
```{
  "Url": "SupplyAlert/List",
  "Request": {
    "DealerCode": "NY06AGDWUQ",
    "DeviceId": null,
    "SerialNumber": null,
    "AssetNumber": null,
    "InitialFrom": null,
    "InitialTo": null,
    "ExhaustedFrom": null,
    "ExhaustedTo": null,
    "Brand": null,
    "Model": null,
    "OfficeDescription": null,
    "SupplySetDescription": null,
    "CustomerCode": "W9OPXL0YDK",
    "FilterCustomerText": null,
    "ManageOption": null,
    "InstallationOption": null,
    "CancelOption": null,
    "HiddenOption": null,
    "SupplyType": null,
    "ColorType": null,
    "ExcludeForStockShippedSupplies": false,
    "FilterText": null,
    "PageNumber": 1,
    "PageRows": 50,
    "SortColumn": "InitialDate",
    "SortOrder": 0
  },
  "Method": "POST"
}
```
## Get All Alerts (Example Response):
```
Alerts count(50) ManageOption= - HiddenOption=: 
 [
  {
    "CustomerId": "0xUi5WEYLzOCrZ8ILowOvA2",
    "CustomerCode": "W9OPXL0YDK",
    "CustomerDescription": "CAPE FEAR VALLEY MED CTR.",
    "DealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
    "DealerCode": "NY06AGDWUQ",
    "DealerDescription": "SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE",
    "DeviceId": "LLk2scitkMz7jtebFfBcsg2",
    "SerialNumber": "A1UE011023015",
    "MacAddress": "00-20-6B-7E-43-2B",
    "IpAddress": "10.5.13.33",
    "AssetNumber": null,
    "SystemName": null,
    "Department": null,
    "IsShipped": false,
    "Product": {
      "Model": "KONICA MINOLTA BIZHUB 363",
      "Brand": "KONICA",
      "Id": "ftIH4cT0kxUB_Cr-17Mnqg2"
    },
    "Project": {
      "Description": "NEW",
      "Start": "0001-01-01T00:00:00Z",
      "Finish": "0001-01-01T00:00:00Z",
      "CustomFieldValues": [],
      "ContractFileName": null,
      "EnableMassiveShipping": false,
      "MassiveShippingSendNotification": false,
      "MassiveShippingSendLogistic": false,
      "Id": "Snims6JUjLSNsVeUlxXEBg2"
    },
    "ProductBrand": "KONICA",
    "ProductModel": "KONICA MINOLTA BIZHUB 363",
    "DealerSupplySetDescription": null,
    "Office": {
      "Code": "DEFAULT",
      "Description": "DEFAULT",
      "Id": "wIqJlMhxYDkXg79UAibfIQ2"
    },
    "Warning": "Developer (Black)",
    "InitialDate": "2024-05-06T17:28:00Z",
    "InitialMono": 0,
    "InitialColor": 0,
    "InitialTotal": 0,
    "TimeZone": "(UTC-05:00) Eastern Time (US & Canada)",
    "TimeZoneIana": "America/New_York",
    "InitialResidualPercentage": 0,
    "HideAlertLimit": null,
    "ActualDate": "2024-05-21T12:34:00Z",
    "ActualMono": 718480,
    "ActualColor": 0,
    "ActualTotal": 0,
    "ActualResidualPercentage": 0,
    "CanceledOn": null,
    "TonerDelivered": null,
    "TonerInstalled": "2024-05-21T16:12:00Z",
    "IsHidden": false,
    "ColorType": 1,
    "SupplyType": 1,
    "MaintenanceKitType": {
      "Id": 10,
      "Description": "Developer"
    },
    "MaintenanceKitColor": {
      "Id": 2,
      "Description": "Black"
    },
    "ExhaustedExpiration": null,
    "SuggestedPartNumber": null,
    "ShippedSupplyId": null,
    "ShippedSupplyQuantity": 0,
    "ShippedSupplyOrderNumber": null,
    "ShippedSupplyDocumentNumber": null,
    "ShippedSupplyGenerationType": null,
    "ShippedSupplyCreation": null,
    "Id": "yiPOIj1cdN09Xkdz30slBoOB5yS16atNs9Rbn3JeRTc1"
  }
```
# Get Customer Details
```
{
  "Url": "Customer/GetCustomerByCode",
  "Request": {
    "Code": "W9OPXL0YDK"
  },
  "Method": "POST"
}
```
## Get Customer Details (Example Response):
```
Customer Details: 
 {
  "DeliveryTonerMails": null,
  "ContractExpires": "2023-04-07T00:00:00Z",
  "Telephone": "910-615-7523",
  "MonthlyVolumeMono": 2843881,
  "MonthlyVolumeColor": 248066,
  "EnableMassiveShipping": false,
  "MassiveShippingSendNotification": false,
  "MassiveShippingSendLogistic": false,
  "VatNumber": null,
  "FiscalCode": null,
  "Note": null,
  "Creation": "2022-04-07T04:01:02Z",
  "Email": "mchase@capefearvalley.com",
  "Nominative": "Michael Chase",
  "TimeZone": "(UTC-05:00) Eastern Time (US & Canada)",
  "ShortLanguage": "en-US",
  "NoteForHomepage": null,
  "CustomFieldValues": [],
  "Dealer": {
    "Code": "NY06AGDWUQ",
    "Description": "SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE",
    "Id": "SZ13qRwU5GtFLj0i_CbEgQ2"
  },
  "TimeZoneIana": "America/New_York",
  "JamEnabled": true,
  "JamIsEu": false,
  "DealerJamEnabled": true,
  "ZztContractNumber": null,
  "PrintReleafId": null,
  "IsPrintReleafEnable": false,
  "EmailExplorerInstallationSentAt": "2024-05-01T14:08:51Z",
  "SdsCustomer": {
    "IsEnabled": true,
    "EnableRemoteEws": true,
    "CSRFProtection": true,
    "EnableDeviceAnnouncement": true
  },
  "Code": "W9OPXL0YDK",
  "Description": "CAPE FEAR VALLEY MED CTR.",
  "CountryCode": "US",
  "CountryName": "United States",
  "CountryIsEu": false,
  "ExternalIdentifier": null,
  "Id": "0xUi5WEYLzOCrZ8ILowOvA2"
}
```
# Get HP SDS Service Request:
```
{
  "Url": "SdsAction/GetDeviceActions",
  "Request": {
    "DeviceId": null,
    "DealerId": null,
    "CustomerId": null,
    "DealerCode": "NY06AGDWUQ",
    "CustomerCode": "W9OPXL0YDK",
    "State": null,
    "Severity": null,
    "ActionType": null,
    "FilterText": null,
    "PageNumber": 1,
    "PageRows": 50,
    "SortColumn": "Id",
    "SortOrder": 0
  },
  "Method": "GET"
}
```
## Get HP SDS Service Request (Example Response):
```
SDS Service Request count(14) : 
 [
  {
    "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
    "CustomerCode": "S8COQ6NPQZ",
    "CustomerDescription": "MOORE COUNTY",
    "DealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
    "DealerCode": "NY06AGDWUQ",
    "DealerDescription": "SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE",
    "InstalledProductSerialNumber": "CNB3P1C8GW",
    "Brand": "HP",
    "Model": "HP COLOR LASERJET FLOW E78330",
    "ActionJamId": "fbfeb5b7-5409-4927-b826-36444487fbf9",
    "DeviceId": "bdCjDNK4L1k8-c1hG1psNQ2",
    "Code": "TriageFuser",
    "EventCodeContext": "50.*",
    "ActionDateUtc": "2024-05-06T12:14:00Z",
    "Severity": 2,
    "CurrentState": 1,
    "StatusReports": null,
    "Title": null,
    "MeanTimeToRepair": null,
    "ServiceLevel": null,
    "Tools": "null",
    "Parts": "null",
    "Link": null,
    "TotalImpressions": 109549,
    "FirmwareVersion": "2502507_000153",
    "ActionType": "ExpertRules",
    "PredictiveData": null,
    "CustomerReportedProblemData": null,
    "Id": "afHJW0XD5DZDz9chXcpY5znmx5pwEnWGVne8QKhbV501"
  },
```
# Get Supplies:
```
{
  "Url": "DealerSupply/List",
  "Request": {
    "ColorType": null,
    "Language": null,
    "Code": "NY06AGDWUQ",
    "FilterText": null,
    "PageNumber": 1,
    "PageRows": 50,
    "SortColumn": "Id",
    "SortOrder": 0
  },
  "Method": "GET"
}
```
```
Supplies count(50) : 
 [
  {
    "PartNumber": "841925RV",
    "Description": "Black Toner",
    "DescriptionLocalized": "Black Toner",
    "SupplyType": 3,
    "ColorType": 2,
    "MaintenanceKitType": null,
    "MaintenanceKitColor": null,
    "Duration": 12500,
    "IsInherited": true,
    "Id": "rzzZz2QesdGJacyHQ5YnBw2"
  }
  ```
  
