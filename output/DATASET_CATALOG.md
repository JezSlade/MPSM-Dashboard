# Complete MPSM API Dataset Catalog

Generated: 2025-10-23 10:25:24

## Summary

- **Total Endpoints**: 188
- **Successful**: 84 (44.7%)
- **Failed**: 104 (55.3%)

## Seed Data Collected

- **customerCodes**: 51 items
- **deviceIds**: 14 items
- **roleIds**: 13 items
- **supplyIds**: 50 items
- **officeIds**: 5 items
- **operationIds**: 20 items

## Successful Endpoints

### Account/GetProfile

- **Data Type**: dict
- **Item Count**: 27
- **Sample Data**:
```json
{
  "Nominative": "Jez Slade",
  "CreatedAt": "21/05/2024 20:44:41",
  "CreatedAtDate": "2024-05-21T20:44:41Z",
  "Email": "jez.slade@systeloa.com",
  "Token": "afb663c2-890d-4d89-97fc-57501bb3b06e",
  "Role": "DPU",
  "Capabilities": [
    115,
    112,
    113,
    111,
    114,
    116,
    110,
    231,
    232,
    230,
    65,
    64,
    62,
    63,
    60,
    61,
    52,
    53,
    51,
    58,
    56,
    54,
    55,
    50,
    220,
    122,
    123,
    121,
    120,
    252,
    251,
    250,
    103,
    101,
    104,
    100,
    11,
    13,
    12,
    9,
    17,
    18,
    14,
    15,
    10,
    16,
    90,
    41,
    43,
    45,
    42,
    44,
    40,
    152,
    153,
    151,
    150,
    22,
    21,
    23,
    20,
    83,
    81,
    80,
    84,
    202,
    200,
    203,
    201,
    31,
    33,
    32,
    30,
    241,
    243,
    242,
    240,
    171,
    173,
    172,
    170,
    72,
    71,
    70
  ],
  "Language": "English",
  "ShortLanguage": "en-US",
  "IsActive": true,
  "Force2fa": false,
  "ForceSso": false,
  "Use2fa": false,
  "LastLoginAt": "2025-10-23T14:12:57.207Z",
  "IsDeleted": false,
  "ExcludeFromWarningNotifications": true,
  "EnabledNewDevicesNotification": false,
  "PreferredDealer": null,
  "DefaultDealer": null,
  "DefaultCustomer": null,
  "Customers": null,
  "Dealers": null,
  "Tags": null,
  "ReportingReports": null,
  "EnablePasswordExpiration": false,
  "Name": "dashboard",
  "Id": "oE9k5vV9W-ccTsuPpMjyfQ2"
}
```

### AlertLimit/Customer/Get

- **Data Type**: dict
- **Item Count**: 12
- **Sample Data**:
```json
{
  "Id": null,
  "Product": null,
  "BlackTonerLimit": 20,
  "YellowTonerLimit": 15,
  "CyanTonerLimit": 15,
  "MagentaTonerLimit": 15,
  "BlackPhotoLimit": 0,
  "YellowPhotoLimit": 0,
  "CyanPhotoLimit": 0,
  "MagentaPhotoLimit": 0,
  "MaintenanceKitLimit": 0,
  "MaintenanceKits": [
    {
      "MaintenanceKitTypeId": 4,
      "MaintenanceKitColorId": 2,
      "Limit": 2
    },
    {
      "MaintenanceKitTypeId": 9,
      "MaintenanceKitColorId": 2,
      "Limit": 2
    },
    {
      "MaintenanceKitTypeId": 9,
      "MaintenanceKitColorId": 23,
      "Limit": 2
    },
    {
      "MaintenanceKitTypeId": 10,
      "MaintenanceKitColorId": 2,
      "Limit": 2
    },
    {
      "MaintenanceKitTypeId": 10,
      "MaintenanceKitColorId": 23,
      "Limit": 2
    },
    {
      "MaintenanceKitTypeId": 15,
      "MaintenanceKitColorId": 2,
      "Limit": 2
    },
    {
      "MaintenanceKitTypeId": 15,
      "MaintenanceKitColorId": 23,
      "Limit": 2
    },
    {
      "MaintenanceKitTypeId": 18,
      "MaintenanceKitColorId": 2,
      "Limit": 2
    },
    {
      "MaintenanceKitTypeId": 18,
      "MaintenanceKitColorId": 23,
      "Limit": 2
    }
  ]
}
```

### AlertLimit/Customer/Product/List

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### AlertLimit/Dealer/Get

- **Data Type**: dict
- **Item Count**: 13
- **Sample Data**:
```json
{
  "DealerCode": "NY06AGDWUQ",
  "OverwriteExistingCustomersAlertLimit": false,
  "BlackTonerLimit": 10,
  "YellowTonerLimit": 10,
  "CyanTonerLimit": 10,
  "MagentaTonerLimit": 10,
  "BlackPhotoLimit": 10,
  "YellowPhotoLimit": 10,
  "CyanPhotoLimit": 10,
  "MagentaPhotoLimit": 10,
  "MaintenanceKitLimit": 10,
  "MaintenanceKits": [],
  "Id": null
}
```

### AlertLimit2/Customer/GetDefault

- **Data Type**: list
- **Item Count**: 18
- **Sample Data**:
```json
{
  "IdCustomer": "USlIvWCpo-sF9xTjf2Fvog2",
  "AlertLimit": {
    "SupplyType": 1,
    "ColorType": null,
    "MaintenanceKitTypeId": null,
    "MaintenanceKitColorId": null,
    "AlertLimitType": 1,
    "CreationAlertLimit": null,
    "CreationAlertLimitStatus": 3,
    "PostAlertNotification": null,
    "PostAlertNotificationStatus": 2,
    "LastUpdateUTC": "2024-05-06T19:20:39Z",
    "CreationAlertLimitProximity": null,
    "CreationAlertLimitProximityStatus": 2
  }
}
```

### AlertLimit2/Customer/GetProduct

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### AlertLimit2/Dealer/GetDefault

- **Data Type**: list
- **Item Count**: 9
- **Sample Data**:
```json
{
  "IdDealer": "SZ13qRwU5GtFLj0i_CbEgQ2",
  "AlertLimit": {
    "SupplyType": 1,
    "ColorType": null,
    "MaintenanceKitTypeId": null,
    "MaintenanceKitColorId": null,
    "AlertLimitType": 1,
    "CreationAlertLimit": 10,
    "CreationAlertLimitStatus": 1,
    "PostAlertNotification": null,
    "PostAlertNotificationStatus": 2,
    "LastUpdateUTC": "2022-03-11T14:50:05Z",
    "CreationAlertLimitProximity": null,
    "CreationAlertLimitProximityStatus": 2
  }
}
```

### AlertLimit2/Dealer/GetProduct

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### ApiClient/List

- **Data Type**: list
- **Item Count**: 1
- **Sample Data**:
```json
{
  "Id": "Ga-Mt3FvZ7F5Qwd1Nr_3Qg2",
  "Name": "dashboard",
  "AppId": "9AT9j4UoU2BgLEqmiYCz",
  "AppSecret": "ghWlqYK7lejAihT7I+9qEBMWrAFMg4IBH/anLEfV290=",
  "ApplicationType": 1,
  "IsActive": true,
  "RefreshTokenLifeTime": 120,
  "AllowedOrigin": null,
  "DeveloperEmail": "jez.slade@systeloa.com",
  "DealerCode": "NY06AGDWUQ"
}
```

### CustomField/List

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### Customer/Accessories/Get

- **Data Type**: dict
- **Item Count**: 6
- **Sample Data**:
```json
{
  "Exclusions": ";CNC1N4602B;38-22-E2-8E-D6-81\r\n;CN96IDK0QR;80-E8-2C-0C-E9-D1\r\n;VNB3C31029;30-E1-71-3D-CD-0A\r\n;MXBCL201GP;F4-30-B9-F3-7C-F8\r\n;MXBCL4K0S3;10-E7-C6-5F-E9-8B\r\n;CNBRP3B2G1;6C-02-E0-FB-EC-4F\r\n;JPCCH641CQ;3C-A8-2A-F8-F0-A1\r\n;406347990G5ZF;00-21-B7-3E-F7-17\r\n;PHBCM971BD;\r\n;CNDCH3M1Q1;50-65-F3-5D-46-15\r\n;CNB8K5J043;3C-52-82-2A-D3-73\r\n;MXMCMBN0RL;04-0E-3C-E2-49-89\r\n;CNB1L5B0Z1;18-60-24-C5-0B-4C\r\n;CNB1P1L1G6;30-24-A9-C7-9C-9B\r\n;CNBRP3B29W;6C-02-E0-FB-EC-45\r\n;70-5A-0F-DA-8B-4B;70-5A-0F-DA-8B-4B",
  "Overrides": null,
  "OutOfDateLimitDevices": 2,
  "OutOfDateLimitConnectors": 2,
  "IdOrderReplacementLogic": null,
  "ExportPreferences": {
    "CsvDelimiter": null,
    "CsvQualifier": null,
    "BracketsInColumnName": true,
    "CsvDateTimeFormat": null
  }
}
```

### Customer/AdvancedOptions/Get

- **Data Type**: dict
- **Item Count**: 5
- **Sample Data**:
```json
{
  "ExportPreferences": {
    "CsvDelimiter": null,
    "CsvQualifier": null,
    "BracketsInColumnName": true,
    "CsvDateTimeFormat": null
  },
  "OutOfDateLimitDevices": 2,
  "OutOfDateLimitConnectors": 2,
  "Exclusions": ";CNC1N4602B;38-22-E2-8E-D6-81\r\n;CN96IDK0QR;80-E8-2C-0C-E9-D1\r\n;VNB3C31029;30-E1-71-3D-CD-0A\r\n;MXBCL201GP;F4-30-B9-F3-7C-F8\r\n;MXBCL4K0S3;10-E7-C6-5F-E9-8B\r\n;CNBRP3B2G1;6C-02-E0-FB-EC-4F\r\n;JPCCH641CQ;3C-A8-2A-F8-F0-A1\r\n;406347990G5ZF;00-21-B7-3E-F7-17\r\n;PHBCM971BD;\r\n;CNDCH3M1Q1;50-65-F3-5D-46-15\r\n;CNB8K5J043;3C-52-82-2A-D3-73\r\n;MXMCMBN0RL;04-0E-3C-E2-49-89\r\n;CNB1L5B0Z1;18-60-24-C5-0B-4C\r\n;CNB1P1L1G6;30-24-A9-C7-9C-9B\r\n;CNBRP3B29W;6C-02-E0-FB-EC-45\r\n;70-5A-0F-DA-8B-4B;70-5A-0F-DA-8B-4B",
  "Overrides": null
}
```

### Customer/AlertSettings/Get

- **Data Type**: dict
- **Item Count**: 6
- **Sample Data**:
```json
{
  "IsTonerEnabled": true,
  "IsPhotoEnabled": true,
  "IsMaintKitEnabled": true,
  "IsWasteTonerBoxEnabled": true,
  "IsTransferKitEnabled": true,
  "CustomerCode": "S8COQ6NPQZ"
}
```

### Customer/CustomerServicesStatus/Get

- **Data Type**: dict
- **Item Count**: 9
- **Sample Data**:
```json
{
  "PrintreleafActive": false,
  "CanActivatePrintreleaf": false,
  "HpSdsActive": true,
  "CanActivateHpSds": true,
  "CanActivatePaperCut": true,
  "PaperCutActive": false,
  "RemoteWsActive": true,
  "CanActivateSharpFSS": false,
  "SharpFSSActive": false
}
```

### Customer/EpsonSettings/Get

- **Data Type**: dict
- **Item Count**: 5
- **Sample Data**:
```json
{
  "EpsonERSCustomerId": null,
  "EpsonUSBCustomerGuid": null,
  "EpsonUSBCustomerDateFormat": "d/M/yyyy H:m",
  "EpsonUSBAvailableDateFormats": [
    {
      "Code": "d/M/yyyy H:m",
      "Description": "d/M/yyyy H:m"
    },
    {
      "Code": "M/d/yyyy H:m",
      "Description": "M/d/yyyy H:m"
    },
    {
      "Code": "d-M-yyyy H:m",
      "Description": "d-M-yyyy H:m"
    },
    {
      "Code": "M-d-yyyy H:m",
      "Description": "M-d-yyyy H:m"
    }
  ],
  "CustomerCode": "S8COQ6NPQZ"
}
```

### Customer/EpsonUSBCustomerId/Get

- **Data Type**: str
- **Item Count**: None
- **Sample Data**:
```json
"d30992d2-6af7-4db6-8aa4-bb9faee87c3b"
```

### Customer/eXplorerSettings/Get

- **Data Type**: dict
- **Item Count**: 2
- **Sample Data**:
```json
{
  "Dca4Stack": 0,
  "CustomerCode": "S8COQ6NPQZ"
}
```

### CustomerDashboard

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "SdsDashboard": {
    "TotalCustomers": 1,
    "TotalDevices": 104,
    "TotalConnectors": 1,
    "CommonActionsToComplete": 3,
    "PredictiveActionsToComplete": 0,
    "NonCommunicatingDevices": 9,
    "NonCommunicatingConnectors": 0,
    "NonGenuineHPDevices": 0,
    "EarlyReplacementDevices": 22,
    "DevicesWithErrors": 0,
    "DevicesWithWarnings": 0,
    "ConnectorsWithErrors": 0,
    "ConnectorsWithWarnings": 0,
    "CustomerIsEnable": true
  },
  "MpsDashboardCustomer": {
    "EnabledDevicesByContract": 0,
    "TotalConnectors": 1,
    "TotalManagedDevices": 104,
    "HasSentExplorerEmail": true,
    "EmailExplorerInstallationSentAt": "2021-06-17T21:55:30Z",
    "ContactedDevices": [
      {
        "Key": "Today",
        "Value": "0"
      },
      {
        "Key": "Yesterday",
        "Value": "95"
      },
      {
        "Key": "BeforeYesterday",
        "Value": "9"
      }
    ],
    "ToBeUpdated": [
      {
        "Type": "Project",
        "Id": 395410,
        "Count": 104
      },
      {
        "Type": "Office",
        "Id": 400315,
        "Count": 104
      }
    ],
    "Books": [
      {
        "Key": "Offices",
        "Value": "1"
      },
      {
        "Key": "Projects",
        "Value": "1"
      }
    ],
    "SupplyAlerts": [
      {
        "Key": "ToManage",
        "Value": "156"
      },
      {
        "Key": "Shipped",
        "Value": "0"
      },
      {
        "Key": "Delivered",
        "Value": "0"
      },
      {
        "Key": "Installed",
        "Value": "668"
      },
      {
        "Key": "Canceled",
        "Value": "74"
      }
    ],
    "Warnings": [],
    "SuppliesDelivery": []
  },
  "MustShowWelcome": false,
  "ExplorerSetupLink": null
}
```

### CustomerDashboard/Pages

- **Data Type**: dict
- **Item Count**: 5
- **Sample Data**:
```json
{
  "MonthlyMonoManaged": 294072,
  "MonthlyMonoUnManaged": 3555,
  "MonthlyColorManaged": 66123,
  "MonthlyColorUnManaged": 0,
  "AnomalousCounters": 0
}
```

### CustomerNotification/List

- **Data Type**: list
- **Item Count**: 1
- **Sample Data**:
```json
{
  "IsActive": true,
  "ActivateOnCustomer": false,
  "LastNotificationSentAtUtc": null,
  "NextNotificationSentAtUtc": null,
  "NotificationType": 2,
  "NotificationMode": 0,
  "OwnerCode": null,
  "Language": 1,
  "DealerTemplateId": null,
  "DealerTemplateName": null,
  "Name": "Moore County _Supply Level Alert",
  "Id": "UE70zGWBAvUoPyNwwN0NSw2"
}
```

### Dealer/AccountingSettings/Get

- **Data Type**: dict
- **Item Count**: 9
- **Sample Data**:
```json
{
  "SDICode": null,
  "SDIPEC": null,
  "Abi": null,
  "Cab": null,
  "Vat": "MISSING",
  "IBAN": null,
  "PaymentTermOverride": null,
  "CountryCode": null,
  "DealerCode": "NY06AGDWUQ"
}
```

### Dealer/AdvancedOptions/Get

- **Data Type**: dict
- **Item Count**: 13
- **Sample Data**:
```json
{
  "ShowRepeteadAlertManagement": false,
  "EnableRepeteadAlertManagementOnNewDevices": false,
  "DisableAlertGeneratorOnDeviceCreation": false,
  "DisableImporterMacAddressComparison": false,
  "notInheritSupplies": false,
  "notInheritProductReplacement": false,
  "AvailablePreferences": [
    "SupplyList",
    "CustomerContract",
    "CustomerOffice",
    "CustomerProject",
    "CustomerCostCenter",
    "CustomerReport",
    "AlertManagement",
    "SdsMassiveSetDeviceCredentials",
    "ManageInk",
    "HasDeviceUpdateWithManagedFlag",
    "HasDetailedCountersTags",
    "HasProjectBatchDelivery",
    "HasCustomerBatchDelivery",
    "HasCustomerBillBook",
    "HasCustomerProjectVolumes",
    "HasLogisticIntegration",
    "HasErpIntegration",
    "HasWhiteLabel",
    "ManageScanners",
    "EnablePagesDaysLimits",
    "HasNewAlertLimits",
    "HasPostAlertRules",
    "HasDeviceDetailWidget",
    "HasFreeSearchOfflineDevices",
    "HasStandardModels",
    "HasNotificationModeCallback",
    "HasRemoteOfflineCounters",
    "HasLAScanPc",
    "HasLAHPProxy",
    "HasLAKodakAlaris",
    "HasDealerDevicesBatchOperations",
    "HasCustomerDevicesBatchOperations",
    "HasSupplySuggestion",
    "HasEpsonERS",
    "HasEpsonUSB",
    "HasSharpFSS",
    "HasSharpSynappx",
    "HasSharpEmail",
    "HasRicohEmail",
    "HasRicohRemoteEmail",
    "HasLexmarkUSB",
    "HasCustomerGroups",
    "HasSpecifyCounterOnShippingCreation",
    "AutomaticallyUseStandardModelsSupplySets",
    "AutomaticallyAssociateNewDevicesToStandardSupplySets"
  ],
  "SelectedPreferences": [
    "SupplyList",
    "CustomerContract",
    "CustomerOffice",
    "CustomerProject",
    "AlertManagement",
    "HasLogisticIntegration",
    "HasErpIntegration",
    "HasNewAlertLimits",
    "HasStandardModels"
  ],
  "ExportPreferences": {
    "CsvDelimiter": null,
    "CsvQualifier": null,
    "BracketsInColumnName": true,
    "CsvDateTimeFormat": null
  },
  "OutOfDateLimitDevices": 2,
  "OutOfDateLimitConnectors": 2,
  "Settings": {
    "DashboardPreferencesTask": 63,
    "CustomerDashboardPreferencesTask": 7,
    "DealerCreateNewCustomerSettings": {
      "ActivateHPSds": false,
      "InstallationProposalDefaultSelection": false,
      "InstallationProposalDefaultTemplate": null,
      "ReadingProblemsDefaultSelection": false,
      "ReadingProblemsDefaultTemplate": null,
      "ReadingProblemsExplorerDefaultSelection": false,
      "ReadingProblemsExplorerDefaultTemplate": null,
      "DeliveryDefaultSelection": false,
      "DeliveryDefaultTemplate": null
    }
  },
  "DealerCode": "NY06AGDWUQ"
}
```

### Dealer/AlertLimitOptions/Get

- **Data Type**: dict
- **Item Count**: 12
- **Sample Data**:
```json
{
  "AlertLimitLevelEnabled": true,
  "AlertLimitResidualPagesEnabled": false,
  "AlertLimitResidualDaysEnabled": false,
  "PostAlertLevelEnabled": false,
  "PostAlertResidualPagesEnabled": false,
  "PostAlertResidualDaysEnabled": false,
  "FallbackLevelCreationAlertLimit": 10,
  "EnableBatchAlertMode": false,
  "BatchAlertProximityType": 0,
  "BatchAlertScheduledTimes": null,
  "BatchAlertScheduledHistories": null,
  "DealerCode": "NY06AGDWUQ"
}
```

### Dealer/AlertSettings/Get

- **Data Type**: dict
- **Item Count**: 6
- **Sample Data**:
```json
{
  "IsTonerEnabled": true,
  "IsPhotoEnabled": true,
  "IsMaintKitEnabled": true,
  "IsWasteTonerBoxEnabled": true,
  "IsTransferKitEnabled": true,
  "DealerCode": "NY06AGDWUQ"
}
```

### Dealer/CounterBlend/List

- **Data Type**: list
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Id": "fJCJ0sBgLgdUd3PHDMK0iw2",
  "Name": "A4 Equivalent Mono Impressions",
  "Sequence": 3,
  "Brands": [
    {
      "Brand": "HP",
      "Descriptions": [
        "TotalImpressionsCounts.ImpressionsMonochromeImpressions"
      ],
      "Details": [
        {
          "CounterDescription": "TotalImpressionsCounts.ImpressionsMonochromeImpressions",
          "CounterType": null,
          "ColumnOperator": 0,
          "RowOperator": null,
          "RowOperatorValue": 0
        }
      ]
    }
  ],
  "Source": 1,
  "DealerCode": "NY06AGDWUQ"
}
```

### Dealer/CounterBlendToStandard/List

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### Dealer/Customizations/Get

- **Data Type**: dict
- **Item Count**: 5
- **Sample Data**:
```json
{
  "AlternativeAssetDescription": null,
  "LogoUrl": "/media/Dealers/b97c1e73-b9ce-47c9-a077-680b8a2fd5e5.png",
  "DeleteLogo": false,
  "LogoFile": {
    "FileName": "b97c1e73-b9ce-47c9-a077-680b8a2fd5e5.png",
    "Base64Content": "iVBORw0KGgoAAAANSUhEUgAAASwAAABACAYAAACgPErgAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBNYWNpbnRvc2giIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MkFDQjkzNjE2NTNGMTFFNUI3QTg5NEIwOEIzRTEyMzYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MkFDQjkzNjI2NTNGMTFFNUI3QTg5NEIwOEIzRTEyMzYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoyQUNCOTM1RjY1M0YxMUU1QjdBODk0QjA4QjNFMTIzNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoyQUNCOTM2MDY1M0YxMUU1QjdBODk0QjA4QjNFMTIzNiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PhRsFrAAADb1SURBVHja7H0JnBxF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",
    "MimeType": "image/png"
  },
  "DealerCode": "NY06AGDWUQ"
}
```

### Dealer/DealerServicesStatus/Get

- **Data Type**: dict
- **Item Count**: 6
- **Sample Data**:
```json
{
  "PrintreleafActive": false,
  "CanActivatePrintreleaf": true,
  "ZzTonerActive": false,
  "CanActivateZzToner": false,
  "HpSdsActive": true,
  "CanActivateHpSds": false
}
```

### Dealer/GetDealerHierarchy

- **Data Type**: dict
- **Item Count**: 7
- **Sample Data**:
```json
{
  "Children": [],
  "Parents": [],
  "LogoUrl": "/media/Dealers/b97c1e73-b9ce-47c9-a077-680b8a2fd5e5.png",
  "AlternativeDealer": {
    "Code": "NHEZFOY36U",
    "Description": "SYSTEL BUSINESS EQUIPMENT",
    "Id": "bbFPVg7nGD6OhzSh3ACqjA2"
  },
  "Code": "NY06AGDWUQ",
  "Description": "SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE",
  "Id": "SZ13qRwU5GtFLj0i_CbEgQ2"
}
```

### Dealer/GetDealerTagsHierarchy

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### Dealer/Onboarding/Get

- **Data Type**: dict
- **Item Count**: 2
- **Sample Data**:
```json
{
  "DealerCode": "NY06AGDWUQ",
  "Steps": [
    {
      "Title": "Dealer\u2019s general information",
      "Groups": [
        {
          "Title": null,
          "Questions": [
            {
              "Code": "1",
              "Text": "How many devices you have in your Machines in Field?",
              "Required": false,
              "AnswerType": 2,
              "Orientation": 0,
              "AllowedValues": [
                {
                  "Key": "< 100",
                  "Value": null
                },
                {
                  "Key": "100-500",
                  "Value": null
                },
                {
                  "Key": "500-1000",
                  "Value": null
                },
                {
                  "Key": "1000-2000",
                  "Value": null
                },
                {
                  "Key": "2000-5000",
                  "Value": null
                },
                {
                  "Key": "> 5000",
                  "Value": null
                }
              ],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "1a",
              "Text": "How many devices?",
              "Required": false,
              "AnswerType": 1,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": {
                "QuestionCode": "1",
                "ContainsValue": "> 5000"
              },
              "Value": null
            },
            {
              "Code": "2",
              "Text": "Which brands?",
              "Required": false,
              "AnswerType": 3,
              "Orientation": 0,
              "AllowedValues": [
                {
                  "Key": "Brother",
                  "Value": null
                },
                {
                  "Key": "Canon",
                  "Value": null
                },
                {
                  "Key": "Hp",
                  "Value": null
                },
                {
                  "Key": "Konica Minolta",
                  "Value": null
                },
                {
                  "Key": "Kyocera",
                  "Value": null
                },
                {
                  "Key": "Lexmark",
                  "Value": null
                },
                {
                  "Key": "Ricoh",
                  "Value": null
                },
                {
                  "Key": "Samsung",
                  "Value": null
                },
                {
                  "Key": "Sharp",
                  "Value": null
                },
                {
                  "Key": "Toshiba",
                  "Value": null
                },
                {
                  "Key": "Xerox",
                  "Value": null
                },
                {
                  "Key": "Others",
                  "Value": null
                }
              ],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "2a",
              "Text": "Can you specify the other brands?",
              "Required": false,
              "AnswerType": 0,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": {
                "QuestionCode": "2",
                "ContainsValue": "Others"
              },
              "Value": null
            },
            {
              "Code": "3",
              "Text": "Types of MPS contracts?",
              "Required": false,
              "AnswerType": 3,
              "Orientation": 0,
              "AllowedValues": [
                {
                  "Key": "Cost per Page",
                  "Value": null
                },
                {
                  "Key": "Toner-Based",
                  "Value": null
                },
                {
                  "Key": "No contracts, only sales of toner",
                  "Value": null
                }
              ],
              "VisibilityRule": null,
              "Value": null
            }
          ]
        }
      ]
    },
    {
      "Title": "Current monitoring system in use",
      "Groups": [
        {
          "Title": null,
          "Questions": [
            {
              "Code": "4",
              "Text": "Are you already using a RMS (Remote Monitoring System)?",
              "Required": false,
              "AnswerType": 2,
              "Orientation": 0,
              "AllowedValues": [
                {
                  "Key": "No",
                  "Value": null
                },
                {
                  "Key": "Printfleet",
                  "Value": null
                },
                {
                  "Key": "PrintAudit",
                  "Value": null
                },
                {
                  "Key": "FMAudit",
                  "Value": null
                },
                {
                  "Key": "Jetadvice",
                  "Value": null
                },
                {
                  "Key": "Ekm",
                  "Value": null
                },
                {
                  "Key": "Other",
                  "Value": null
                }
              ],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "4a",
              "Text": "What RMS are you currently using?",
              "Required": false,
              "AnswerType": 0,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": {
                "QuestionCode": "4",
                "ContainsValue": "Other"
              },
              "Value": ""
            },
            {
              "Code": "5",
              "Text": "Is there anything in your current RMS that does not fully satisfy your needs?",
              "Required": false,
              "AnswerType": 3,
              "Orientation": 1,
              "AllowedValues": [
                {
                  "Key": "Support not good",
                  "Value": null
                },
                {
                  "Key": "Low quality and/or instability of data collection",
                  "Value": null
                },
                {
                  "Key": "Lack of integration with my systems",
                  "Value": null
                },
                {
                  "Key": "Poor usability",
                  "Value": null
                },
                {
                  "Key": "Lack of important features",
                  "Value": null
                },
                {
                  "Key": "Cost too high",
                  "Value": null
                }
              ],
              "VisibilityRule": null,
              "Value": null
            }
          ]
        }
      ]
    },
    {
      "Title": "Current contract management",
      "Groups": [
        {
          "Title": "Describe your current IT environment and the main Information Systems in use in your company",
          "Questions": [
            {
              "Code": "6",
              "Text": "We have an integrated ERP / administration system that manages contracts, logistics and services on the Managed Print environment",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "6a",
              "Text": "Which ERP do you use?",
              "Required": false,
              "AnswerType": 0,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": {
                "QuestionCode": "6",
                "ContainsValue": "true"
              },
              "Value": ""
            },
            {
              "Code": "7",
              "Text": "Our ERP / administration system contains all the serial numbers of the printers in MIF",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "8",
              "Text": "Our ERP / administration system stores all the information on contract economics, and it is able to make invoicing calculations based on meter readings",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "9",
              "Text": "Our ERP/ administration system manages service calls opening and closing",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            }
          ]
        }
      ]
    },
    {
      "Title": "Current consumable management process",
      "Groups": [
        {
          "Title": "Describe how you currently manage consumables",
          "Questions": [
            {
              "Code": "10",
              "Text": "We ship toner based on customer requests that we receive via email or phone calls",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "11",
              "Text": "We ship toner based on consumable alerts coming from the remote monitoring system we already use",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            }
          ]
        },
        {
          "Title": null,
          "Questions": [
            {
              "Code": "12",
              "Text": "Part Numbers management",
              "Required": false,
              "AnswerType": 2,
              "Orientation": 1,
              "AllowedValues": [
                {
                  "Key": "We use only the part numbers coming from the official vendor list price",
                  "Value": null
                },
                {
                  "Key": "We use only our own part numbers",
                  "Value": null
                },
                {
                  "Key": "We use both (vendor and own part numbers)",
                  "Value": null
                }
              ],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "13",
              "Text": "Warehouse management",
              "Required": false,
              "AnswerType": 2,
              "Orientation": 1,
              "AllowedValues": [
                {
                  "Key": "We ship consumable from our own warehouse only",
                  "Value": null
                },
                {
                  "Key": "We use an external logistic provider / warehouse to ship directly to customers",
                  "Value": null
                },
                {
                  "Key": "We use both (part from our internal warehouse and part from an external provider)",
                  "Value": null
                }
              ],
              "VisibilityRule": null,
              "Value": null
            }
          ]
        }
      ]
    },
    {
      "Title": "Expectations - Meters",
      "Groups": [
        {
          "Title": null,
          "Questions": [
            {
              "Code": "15",
              "Text": "I wish to collect base counters (total mono & total color) from devices",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "16",
              "Text": "I wish to collect detailed counters (Scan / A3 / other formats)",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "17",
              "Text": "I wish to send automatic reports via email to the customers with printed volumes for each printer",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "18",
              "Text": "I wish to export meters to my ERP / administration system via standard file formats ( CSV, XML, Excel)",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "19",
              "Text": "I wish to integrate meters data seamlessly in my ERP /administration system via APIs",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            }
          ]
        }
      ]
    },
    {
      "Title": "Expectations - Consumables",
      "Groups": [
        {
          "Title": "Describe what you expect from a new Remote Monitoring System regarding consumables management",
          "Questions": [
            {
              "Code": "20",
              "Text": "I wish to receive an alert when a toner is low on a printer",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "21",
              "Text": "I wish to have a daily operational dashboard, that tells me which are the consumables that I have to deliver, on which customer and printer, and where each printer is located",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "22",
              "Text": "I wish that, in each alert, the system tells me exactly which Part Number I have to ship, on which printer, where and when",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "23",
              "Text": "When I ship a consumable to a customer\u2019s office, I wish that the system tells me immediately if I should ship other consumables to the same office",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "24",
              "Text": "I wish to streamline my logistic process and to automate consumable shipments, by integrating the consumables alert management into my ERP system",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "25",
              "Text": "I wish to send automatic emails to my customers, to inform them that I have shipped the consumable to them",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "26",
              "Text": "I wish that the monitoring system may send a delivery order to the warehouse in a completely automatic and unattended way",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "27",
              "Text": "I wish to integrate seamlessly the information on consumable shipments with those in my ERP via APIs",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            }
          ]
        }
      ]
    },
    {
      "Title": "Expectations - Contracts",
      "Groups": [
        {
          "Title": "Describe what you expect from a new Remote Monitoring System regarding contract management",
          "Questions": [
            {
              "Code": "28",
              "Text": "I need a system that helps me to manage contracts",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "29",
              "Text": "I expect that the system will help me to make contract calculations, and will produce reports that include the amount of money to be invoiced for each printer / customer",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "30",
              "Text": "I expect that the contract management system will include a billbook with invoice planning for each contract /customer",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "31",
              "Text": "I wish to send automatic reports via email to customers with the details on how much money we are invoicing to them for each printer, based on the pages printed",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            }
          ]
        }
      ]
    },
    {
      "Title": "Expectations - Data collections",
      "Groups": [
        {
          "Title": "Describe what you expect from a new Remote Monitoring System regarding data collection",
          "Questions": [
            {
              "Code": "32",
              "Text": "I wish to collect printers\u2019 data using a multi-platform Data Collection Agent that can run on any Windows or Linux PC, MAC or, whenever possible, to run from the printer itself so that I don\u2019t need to install anything on the customer\u2019s systems",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "33",
              "Text": "I wish to install several Data Collection Agents into each customer, so that if one fails the others continue sending data, and I continue receiving information",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "34",
              "Text": "I wish to send automatic email notifications to customers, that inform them that the monitoring software on their PC is not working",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            }
          ]
        }
      ]
    },
    {
      "Title": "Expectations - Privacy and access",
      "Groups": [
        {
          "Title": null,
          "Questions": [
            {
              "Code": "35",
              "Text": "Describe what you expect from a new Remote Monitoring System regarding data privacy and access",
              "Required": false,
              "AnswerType": 2,
              "Orientation": 1,
              "AllowedValues": [
                {
                  "Key": "I wish to label customers with real company names and manage customer contacts",
                  "Value": null
                },
                {
                  "Key": "I wish to label customers only with codes, so that customer names and contacts are not stored in the cloud and nobody outside my company can access them",
                  "Value": null
                }
              ],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "36",
              "Text": "I wish to manage contact information (names and email addresses) of my customers into the monitoring system, in order to send automatic emails from the monitoring system to customers",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": {
                "QuestionCode": "35",
                "ContainsValue": "I wish to label customers with real company names and manage customer contacts"
              },
              "Value": null
            },
            {
              "Code": "37",
              "Text": "I wish to manage into the monitoring system the office locations of customers, so that I know exactly where each printer is and wher I have to ship consumables",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": {
                "QuestionCode": "35",
                "ContainsValue": "I wish to label customers with real company names and manage customer contacts"
              },
              "Value": null
            },
            {
              "Code": "38",
              "Text": "I wish to load into the system the economic details of contracts, to allow the system produce the needed reporting and invoicing calculations",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": null,
              "Value": null
            },
            {
              "Code": "39",
              "Text": "I wish to grant access to customers on their area on the monitoring portal, so that they can access the information on their own devices",
              "Required": false,
              "AnswerType": 4,
              "Orientation": 0,
              "AllowedValues": [],
              "VisibilityRule": {
                "QuestionCode": "35",
                "ContainsValue": "I wish to label customers with real company names and manage customer contacts"
              },
              "Value": null
            }
          ]
        }
      ]
    },
    {
      "Title": "Summary",
      "Groups": []
    }
  ]
}
```

### Dealer/RemoteOfflineCountersSettings/Get

- **Data Type**: dict
- **Item Count**: 15
- **Sample Data**:
```json
{
  "IsActive": false,
  "IsTest": false,
  "DeviceWithNoCommunicationDays": 0,
  "SendReminderDays": null,
  "AutoCalculateCounters": false,
  "AutoCalculateMonthDay": 0,
  "EmailTemplate": null,
  "AccountName": null,
  "EmailSubject": null,
  "EmailBcc": null,
  "EmailTemplateConfirm": null,
  "EmailSubjectConfirm": null,
  "EmailConfirmIsActive": false,
  "OnlyOfflineDevices": false,
  "DealerCode": "NY06AGDWUQ"
}
```

### Dealer/eXplorerSettings/Get

- **Data Type**: dict
- **Item Count**: 13
- **Sample Data**:
```json
{
  "ScansNumberMorning": 1,
  "ScansNumberAfternoon": 1,
  "ScansNumberEvening": 1,
  "AutoEnableDefaultConfiguration": true,
  "AutomaticUpdate": true,
  "UseDca4AsDefault": true,
  "Dca4Stack": 0,
  "ExplorerInterval": {
    "Discovery": 360,
    "Meters": 240,
    "Supplies": 60,
    "Errors": 60,
    "Attributes": 240,
    "All": 60
  },
  "DefaultExplorerInterval": {
    "Discovery": 360,
    "Meters": 240,
    "Supplies": 60,
    "Errors": 60,
    "Attributes": 240,
    "All": 60
  },
  "ExplorerWorkingDays": [
    {
      "DayOfWeek": 1,
      "IsDayEnabled": true,
      "Range1": {
        "Active": true,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 2,
      "IsDayEnabled": true,
      "Range1": {
        "Active": true,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 3,
      "IsDayEnabled": true,
      "Range1": {
        "Active": true,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 4,
      "IsDayEnabled": true,
      "Range1": {
        "Active": true,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 5,
      "IsDayEnabled": true,
      "Range1": {
        "Active": true,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 6,
      "IsDayEnabled": false,
      "Range1": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 0,
      "IsDayEnabled": false,
      "Range1": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    }
  ],
  "AvailableSNMPDiscoveryBrands": [
    {
      "Key": "19",
      "Value": "BARIX"
    },
    {
      "Key": "6",
      "Value": "BROTHER"
    },
    {
      "Key": "7",
      "Value": "CANON"
    },
    {
      "Key": "18",
      "Value": "DELL"
    },
    {
      "Key": "8",
      "Value": "EPSON"
    },
    {
      "Key": "2",
      "Value": "FUJIFILM"
    },
    {
      "Key": "4",
      "Value": "HP"
    },
    {
      "Key": "15",
      "Value": "IBASE"
    },
    {
      "Key": "36",
      "Value": "KATUN"
    },
    {
      "Key": "35",
      "Value": "KIP"
    },
    {
      "Key": "20",
      "Value": "KONICA"
    },
    {
      "Key": "23",
      "Value": "KYOCERA"
    },
    {
      "Key": "17",
      "Value": "LEXMARK"
    },
    {
      "Key": "37",
      "Value": "MURATEC"
    },
    {
      "Key": "13",
      "Value": "NEC"
    },
    {
      "Key": "25",
      "Value": "OKI"
    },
    {
      "Key": "21",
      "Value": "PANASONIC"
    },
    {
      "Key": "3",
      "Value": "PANTUM"
    },
    {
      "Key": "31",
      "Value": "PRINTRONIX"
    },
    {
      "Key": "16",
      "Value": "QISDA"
    },
    {
      "Key": "1",
      "Value": "RICOH"
    },
    {
      "Key": "14",
      "Value": "RISO"
    },
    {
      "Key": "5",
      "Value": "SAMSUNG"
    },
    {
      "Key": "26",
      "Value": "SEIKO"
    },
    {
      "Key": "30",
      "Value": "SERVICE AGENT-KODAK ALARIS"
    },
    {
      "Key": "29",
      "Value": "SERVICE AGENT-OBERON USB"
    },
    {
      "Key": "9",
      "Value": "SHARP"
    },
    {
      "Key": "24",
      "Value": "SINDORICOH"
    },
    {
      "Key": "11",
      "Value": "TOSHIBA"
    },
    {
      "Key": "34",
      "Value": "TSC"
    },
    {
      "Key": "27",
      "Value": "TYAN"
    },
    {
      "Key": "10",
      "Value": "XEROX"
    },
    {
      "Key": "28",
      "Value": "ZEBRA"
    }
  ],
  "PreferredSNMPDiscoveryBrands": [],
  "DealerCode": "NY06AGDWUQ"
}
```

### DealerNotification/GetSampleNotification

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### DealerNotification/List

- **Data Type**: list
- **Item Count**: 4
- **Sample Data**:
```json
{
  "IsActive": false,
  "ActivateOnCustomer": false,
  "LastNotificationSentAtUtc": null,
  "NextNotificationSentAtUtc": null,
  "NotificationType": 7,
  "NotificationMode": 0,
  "OwnerCode": null,
  "Language": 1,
  "DealerTemplateId": null,
  "DealerTemplateName": null,
  "Name": "MPS Monitor Installation invitation",
  "Id": "SjTqVDMBfTcCAZHFUsSKeQ2"
}
```

### DealerNotification/Template/Get

- **Data Type**: dict
- **Item Count**: 2
- **Sample Data**:
```json
{
  "EmailTemplateBase": "$BODY$",
  "DealerCode": "NY06AGDWUQ"
}
```

### DealerProduct/Get

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### DealerProduct/List

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### DealerSupply/Count

- **Data Type**: dict
- **Item Count**: 3
- **Sample Data**:
```json
{
  "IsValid": true,
  "Errors": [],
  "ReturnValue": "1593"
}
```

### DealerSupply/Export

- **Data Type**: dict
- **Item Count**: 3
- **Sample Data**:
```json
{
  "FileName": "file.xlsx",
  "Base64Content": "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UC9INeDGtvhp1ueOoIdDE0ptlQhqpaiNonaKOijqpKjriSomdLlvlT7/ASqplT3mamUPODChgAJ7T0TVE0pQL0ih91CEjV39M8iAQxNKHNubolaK2ihqp6iDok6Kup6ockLvS6UFLYu62jZhrlb2gEMTSv2AEqLAhFJLJUCh91D8NOu9J3o4NKHUUomgVoraKGqnqIOiToq6nqhyQu9LpQVtR8o3jY+vuPpzKODQhNYUWHsCCk0oda0SosrIHxCDm3nAwQmldkoEtVLURlE7RR0UdVLU9USVE9rdJxQtR/r6cyjm6s+hgEMTSt35DlBoQqlLlQAF/8oDbCx/7fkn5OCEUislglopaqOonaIOijop6nqiygm9r5QWtCvqa2OPudrYAw5NKPUbSoBCE0rtlAAF/8rDp1nvlNDDoQmldkoEtVLURlE7RR0UdVLU9UT9vxP69//8H3//+7/8w+/+8rt/+6///ef//J9+98c/fP7vP/z5T//8L/7xz//pT5//n2P3/1ys/Nf/13/xl//8f/z+3/zLP/7hn//yGfJ//tPv/8PnyP5X7D/8+Z/+9//0x981//Zf/rs//+n3//R353/881/+/Nuf//Tv/9M//uXP//R32+/+9Jff/+FPv/vTP/7+/Ye//Mt//ff/P/+v//6vE5jE3/46cf/zX/6H/+t3f/jj7/6XP/7+7z7++Lt//N/+7rf//Ls/fYb+r7//019+93f/8+//+Mc//9//raH/8Feh/2b73R8+n9L/94z+3Sf/z//dv/r47//Vx39jzP8Yxfz25z/++Z9ycj46eWudo7VO01oUk9laL2+td7TWa1qLYjJbG+StDY7WBk1rUUxma6O8tdHR2qhpLYrJbG2StzY5Wps0rUUxma3N8tZmR2uzprUoJrO1Rd7a4mht0bQWxWS21nyT11ZEinr769S/XXFhTmpzjb65xtJcI2ouykltTi9JGoslaUSaJMxJbU4vShqLKWlEqiTMSW1OL0saiy1pRLokzEltTi9MGosxaUTKJMxJbU4vTRqLNWlE2iTMSW1OL04aizlpROokzEltTi9PGos9aUT6JMxJbU4vUBqLQWlECiXMyWyu1TuU1uJQWpFDCXNSm9M7lNbiUFqRQwlzUpsznGjiOdNEdaqJyqG0eofSWhxKK3IoYU5qc3qH0locSityKGFOanN6h9JaHEor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CYNy2zO4ls7jWjqVawmDctszuJbO41o6lWsJg1Lb6w2upfe4ll7lWsKg3PYMrqX3uJZe5VrCoNz2DK6l97iWXuVawqDc9gyupfe4ll7lWsKg3PYMrqX3uJZe5VrCoNz2DK6l97iWXuVawqDc9gyupfe4ll7lWsKg3PYMrqX3uJZe5VrCoNz2DK6l97iWXuVawqDc9gyupfe4ll7lWsKg1PYGg2sZPK5lULmWMCi3PYNrGTyuZVC5ljAotz2Daxk8rmVQuZYwKLc9g2sZPK5lULmWMCi3PYNrGTyuZVC5ljAotz2Daxk8rmVQuZYwKLc9g2sZPK5lULmWMCi3PYNrGTyuZVC5ljAotz2Daxk8rmVQuZYwKLc9g2sZPK5lULmWMCi1vdHgWkaPaxlVriUMym3P4FpGj2sZVa4lDMptz+BaRo9rGVWuJQzKbc/gWkaPaxlVriUMym3P4FpGj2sZVa4lDMptz+BaRo9rGVWuJQzKbc/gWkaPaxlVriUMym3P4FpGj2sZVa4lDMptz+BaRo9rGVWuJQzKbc/gWkaPaxlVriUMSm1vMriWyeNaJpVrCYNy2zO4lsnjWiaVawmDctszuJbJ41omlWsJg3LbM7iWyeNaJpVrCYNy2zO4lsnjWiaVawmDctszuJbJ41omlWsJg3LbM7iWyeNaJpVrCYNy2zO4lsnjWiaVawmDctszuJbJ41omlWsJg3LbM7iWyeNaJpVrCYNS25sNrmX2uJZZ5VrCoNz2DK5l9riWWeVawqDc9gyuZfa4llnlWsKg3PYMrmX2uJZZ5VrCoNz2DK5l9riWWeVawqDc9gyuZfa4llnlWsKg3PYMrmX2uJZZ5VrCoNz2DK5l9riWWeVawqDc9gyuZfa4llnlWsKg3PYMrmX2uJZZ5VrCoNT2FoNrWTyuZVG5ljAotz2Da1k8rmVRuZYwKLc9g2tZPK5lUbmWMCi3PYNrWTyuZVG5ljAotz2Da1k8rmVRuZYwKLc9g2tZPK5lUbmWMCi3PYNrWTyuZVG5ljAotz2Da1k8rmVRuZYwKLc9g2tZPK5lUbmWMCi3PYNrWTyuZVG5ljAos73hm961lJmi9orYv117cVBue3rXUmbK2hO5ljgotz29aykzZe2JXEsclNue3rWUmbL2RK4lDsptT+9aykxZeyLXEgfltqd3LWWmrD2Ra4mDctvTu5Yy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JQzKbc/gWhqPa2lUriUMSm2vNbiW1uNaWpVrCYNy2zO4ltbjWlqVawmDctszuJbW41palWsJg3LbM7iW1uNaWpVrCYNy2zO4ltbjWlqVawmDctszuJbW41palWsJg3LbM7iW1uNaWpVrCYNy2zO4ltbjWlqVawmDctszuJbW41palWsJg3LbM7iW1uNaWpVrCYNS2+sMrqXzuJZO5VrCoNz2DK6l87iWTuVawqDc9gyupfO4lk7lWsKg3PYMrqXzuJZO5VrCoNz2DK6l87iWTuVawqDc9gyupfO4lk7lWsKg3PYMrqXzuJZO5VrCoNz2DK6l87iWTuVawqDc9gyupfO4lk7lWsKg3PYMrqXzuJZO5VrCoNT2eoNr6T2upVe5ljAotz2Da+k9rqVXuZYwKLc9g2vpPa6lV7mWMCi3PYNr6T2upVe5ljAotz2Da+k9rqVXuZYwKLc9g2vpPa6lV7mWMCi3PYNr6T2upVe5ljAotz2Da+k9rqVXuZYwKLc9g2vpPa6lV7mWMCi3PYNr6T2upVe5ljAotb3B4FoGj2sZVK4lDMptz+BaBo9rGVSuJQzKbc/gWgaPaxlUriUMym3P4FoGj2sZVK4lDMptz+BaBo9rGVSuJQzKbc/gWgaPaxlUriUMym3P4FoGj2sZVK4lDMptz+BaBo9rGVSuJQzKbc/gWgaPaxlUriUMym3P4FoGj2sZVK4lDEptbzS4ltHjWkaVawmDctszuJbR41pGlWsJg3LbM7iW0eNaRpVrCYNy2zO4ltHjWkaVawmDctszuJbR41pGlWsJg3LbM7iW0eNaRpVrCYNy2zO4ltHjWkaVawmDctszuJbR41pGlWsJg3LbM7iW0eNaRpVrCYNy2zO4ltHjWkaVawmDUtubDK5l8riWSeVawqDc9gyuZfK4lknlWsKg3PYMrmXyuJZJ5VrCoNz2DK5l8riWSeVawqDc9gyuZfK4lknlWsKg3PYMrmXyuJZJ5VrCoNz2DK5l8riWSeVawqDc9gyuZfK4lknlWsKg3PYMrmXyuJZJ5VrCoNz2DK5l8riWSeVawqDU9maDa5k9rmVWuZYwKLc9g2uZPa5lVrmWMCi3PYNrmT2uZVa5ljAotz2Da5k9rmVWuZYwKLc9g2uZPa5lVrmWMCi3PYNrmT2uZVa5ljAotz2Da5k9rmVWuZYwKLc9g2uZPa5l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",
  "MimeType": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
}
```

### DealerSupply/List

- **Data Type**: list
- **Item Count**: 50
- **Sample Data**:
```json
{
  "PartNumber": "841925RV",
  "DealerSKU": null,
  "Description": "Black Toner",
  "DescriptionLocalized": "Black Toner",
  "SupplyType": 3,
  "ColorType": 2,
  "MaintenanceKitType": null,
  "MaintenanceKitColor": null,
  "Duration": 12500,
  "IsInherited": true,
  "IsStandard": false,
  "Value": 0,
  "Id": "rzzZz2QesdGJacyHQ5YnBw2"
}
```

### DealerSupplyPriceListing/Get

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### DealerSupplyPriceListing/List

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### DealerSupplySet/Count

- **Data Type**: dict
- **Item Count**: 3
- **Sample Data**:
```json
{
  "IsValid": true,
  "Errors": [],
  "ReturnValue": "0"
}
```

### DealerSupplySet/Export

- **Data Type**: dict
- **Item Count**: 3
- **Sample Data**:
```json
{
  "FileName": "export.json",
  "Base64Content": "W10=",
  "MimeType": "application/unknown"
}
```

### DealerSupplySet/ExportExcel

- **Data Type**: dict
- **Item Count**: 3
- **Sample Data**:
```json
{
  "FileName": "export-supplyset.xlsx",
  "Base64Content": "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",
  "MimeType": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
}
```

### DealerSupplySet/List

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### DealerSupplySet/ListDealerSupplySetFromStandardModels

- **Data Type**: list
- **Item Count**: 132
- **Sample Data**:
```json
{
  "DealerSupplySetDescription": null,
  "PartNumbers": "[24B6040] Black Drum,[24B6186] Black Toner,[40X9138] Maintenance Kit",
  "GroupName": -2131927886,
  "Details": [
    {
      "Product": {
        "Model": "XM3150",
        "Brand": "LEXMARK",
        "Id": "8M-Gm0S9dvDqEXc7EMlhsg2"
      },
      "NrOfMangedDevices": 3
    }
  ]
}
```

### Device/Deleted/ListByDealer

- **Data Type**: list
- **Item Count**: 50
- **Sample Data**:
```json
{
  "AssetNumber": "FQ385",
  "Account": "cory.trifilo@systeloa.com",
  "Customer": {
    "Code": "S8COQ6NPQZ",
    "Description": "MOORE COUNTY",
    "CountryCode": "US",
    "CountryName": "United States",
    "CountryIsEu": false,
    "ExternalIdentifier": null,
    "Id": "USlIvWCpo-sF9xTjf2Fvog2"
  },
  "LastPingUtc": null,
  "DeletedOn": "2021-08-25T14:19:45Z",
  "SerialNumber": "CNC1N4602B",
  "MacAddress": "38-22-E2-8E-D6-81",
  "Firmware": null,
  "SystemName": null,
  "IpAddress": "10.1.102.176",
  "Product": {
    "Model": "HP LASERJET FLOW MFP E82560",
    "Brand": "HP",
    "Id": "IikK1tMEdybFUKuoZWylOQ2"
  },
  "Id": "xwjGf_RSY0ysuReN7Gzg7Q2"
}
```

### Explorer/Cluster/AutoClusters

- **Data Type**: list
- **Item Count**: 34
- **Sample Data**:
```json
{
  "Customer": null,
  "Description": "auto-1",
  "AutoFixDayLimit": 3,
  "ExplorerDatas": [
    {
      "CreatedAt": "2021-06-22T12:18:11Z",
      "Identifier": "0af64ee7-4fd1-4906-accc-7b0575d8faed",
      "IP": "10.0.127.100",
      "SystemName": "MOCO-JETADMIN",
      "MakeServiceUpdate": false,
      "MakeExplorerUpdate": false,
      "DealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
      "DealerCode": null,
      "DealerDescription": null,
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "CustomerCode": "S8COQ6NPQZ",
      "CustomerDescription": "MOORE COUNTY",
      "AutomaticUpdate": true,
      "BuildNumber": "3.9.4",
      "BuildDate": "2022-10-05T11:44:13Z",
      "IsEmbedded": false,
      "TableVersion": 13,
      "ServiceBuildNumber": "3.9.4.17854",
      "ServiceMajor": 3,
      "ConfiguratorBuildNumber": null,
      "PollingInterval": 20,
      "LastUpload": "2025-10-22T22:32:40.017Z",
      "Version": "3.9.4.13",
      "Platform": "Windows",
      "LastPing": "2025-10-23T14:11:30.87Z",
      "AgentIsRunning": false,
      "HasWarning": false,
      "PingIsOutOfDate": false,
      "DataIsOutOfDate": false,
      "NeverReceiveData": false,
      "NoValidConfiguration": false,
      "LastRun": "2023-01-09T23:18:00Z",
      "LastNetworkDiscovery": "2025-10-16T07:51:30.63Z",
      "TimeZone": "(UTC-05:00) Eastern Time (US & Canada)",
      "TimeZoneIana": "America/New_York",
      "ExplorerDataJamExplorerJamVersion": "3.9.4.27684 - 2022-10-17 12:03:21",
      "ExplorerDataJamVersion": "4.1.6712",
      "ExplorerDataJamConnectorStatus": 1,
      "ExplorerDataJamLastContactTimeUtc": "2025-10-23T14:12:00Z",
      "ExplorerDataJamRegistrationKey": "c66b1c22-57e9-4edb-8fc6-67c46194f35d",
      "ExplorerDataJamLastUploadUtc": "2025-10-22T22:38:00Z",
      "ExplorerDataJamCreatedAtUc": null,
      "ExplorerDataJamInstalledComputer": "MOCO-JETADMIN",
      "ExplorerDataJamWebProxyAddress": null,
      "ExplorerDataJamWebProxyPort": 0,
      "ExplorerDataJamConnectorId": 286148,
      "ExplorerCluster": null,
      "IsMasterInCluster": false,
      "ExplorerDataInfos": [
        {
          "Key": "CurrentDirectory",
          "Value": "C:\\Program Files (x86)\\MpsMonitor\\eXplorer3",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRn5yBzvQ26ur2QocEXCpOqo1"
        },
        {
          "Key": "CurrentTimeZone",
          "Value": "Eastern Standard Time",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRpTHBn4CfwrFvhpFeBiADsE1"
        },
        {
          "Key": "DotNetVersion",
          "Value": "4.0.30319.42000",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRm-8AFoChuZczz9foGNn_WA1"
        },
        {
          "Key": "HasProxy",
          "Value": "false",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRpUJC8GiHuXJQixD3QPs0GI1"
        },
        {
          "Key": "HasProxyAuth",
          "Value": "false",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRgGtGgVdHkSy0OegdzU4QJs1"
        },
        {
          "Key": "Is64BitOperatingSystem",
          "Value": "true",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRjKj-iXZO6QVVPRq9gwZCPg1"
        },
        {
          "Key": "Is64BitProcess",
          "Value": "true",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRmsZ_Jq1sa8CcoqQ4aBBcTg1"
        },
        {
          "Key": "IsPartOfDomain",
          "Value": "true",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRvy8yWS_XE9H91ki-KvWt5Y1"
        },
        {
          "Key": "IsServiceUser",
          "Value": "true",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRsrwLP6N2GOp3EaVVeZR15Q1"
        },
        {
          "Key": "MachineName",
          "Value": "MOCO-JETADMIN",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRlr8IfK_eyZDH3PoPPQvXrc1"
        },
        {
          "Key": "Manufacturer",
          "Value": "VMware, Inc.",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRuWSi4dbMbnl19cinets8Yc1"
        },
        {
          "Key": "Model",
          "Value": "VMware Virtual Platform",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRuKV8CY4JwVJbkPmXjkFY1o1"
        },
        {
          "Key": "NetworkInfo.IPAddresses.1",
          "Value": "ip=10.0.127.100;subnet=255.255.0.0|",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRozREwmRO2uJpnj3Vl-D9f01"
        },
        {
          "Key": "NetworkInfo.NetworkInterface.1",
          "Value": "vmxnet3 Ethernet Adapter",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRstGVatfkl4fdTy1tTyShkI1"
        },
        {
          "Key": "NetworkInfo.NetworkInterfaceType.1",
          "Value": "Ethernet",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRkXAet_iFy9x8QOvEQFI3YY1"
        },
        {
          "Key": "NumberOfCores",
          "Value": "2",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRrnmlQ3DE8BM01_UDk1IPJI1"
        },
        {
          "Key": "NumberOfLogicalProcessors",
          "Value": "2",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRpSgaQRoJtIOY3ikaIwM0W41"
        },
        {
          "Key": "NumberOfPhysicalProcessors",
          "Value": "2",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRiFp_uKLseNWWSYexjQGTWo1"
        },
        {
          "Key": "OperatingSystem",
          "Value": "Microsoft Windows Server 2019 Standard",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRhdb5Icrnc6KAIS0GjwnqN81"
        },
        {
          "Key": "OperatingSystemServicePack",
          "Value": "",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRsWkTlqNo69Bev3Y9Mc171s1"
        },
        {
          "Key": "OperatingSystemVersion",
          "Value": "10.0.17763",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRtgaCYRGWawi52_ARkdaZ_M1"
        },
        {
          "Key": "PhysicalMemory",
          "Value": "8589934592",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRkF8_L6AE_pwqlwg9w6QG001"
        },
        {
          "Key": "Processes",
          "Value": "HPWJAService,dllhost,splunk-winevtlog,vm3dservice,dllhost,cysandbox,WmiPrvSE,SRAgent,tlaworker,csrss,sppsvc,dwm,HP.Fms.Connector.Monitor.Service,wininit,TiWorker,WmiPrvSE,vm3dservice,smss,TrustedInstaller,HP.Dss.App.WinService,tlaworker,splunkd,conhost,WmiApSrv,cysandbox,services,Registry,msdtc,SRFeature,MpsMonitor.eXplorer.Service,sqlservr,WUDFHost,VGAuthService,LogonUI,sqlwriter,vmtoolsd,spoolsv,csrss,winlogon,conhost,cyserver,SRService,cortex-xdr-payload,fontdrvhost,snmp,fontdrvhost,VSSVC,SRManager,HP.Fms.Connector.Service,cysandbox,WmiPrvSE,SRVirtualDisplay,lsass,System,Idle",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRvrY7bJtkMXuBTCphCE_BUU1"
        },
        {
          "Key": "ProcessorInfo.ProcessorClockSpeed.1",
          "Value": "2893",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRj2b_P8oxH4Mgymd9ivB0hU1"
        },
        {
          "Key": "ProcessorInfo.ProcessorClockSpeed.2",
          "Value": "2893",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRjc2hikZWMFRpi_LTjTeXic1"
        },
        {
          "Key": "ProcessorInfo.ProcessorManufacturer.1",
          "Value": "GenuineIntel",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRiHIwdpu11Gk0pl68RUI2LM1"
        },
        {
          "Key": "ProcessorInfo.ProcessorManufacturer.2",
          "Value": "GenuineIntel",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRqELu5n7PEEGG5azq6EGm2E1"
        },
        {
          "Key": "ProcessorInfo.ProcessorName.1",
          "Value": "Intel(R) Xeon(R) Gold 6326 CPU @ 2.90GHz",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "C-W2B_iRElbJBOt9nDcu1hioLg9LADUbqC6a1n4WMxo1"
        },
        {
          "Key": "ProcessorInfo.ProcessorName.2",
          "Value": "Intel(R) Xeon(R) Gold 6326 CPU @ 2.90GHz",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRuNLMex4CKoKiDeyq-zDHPc1"
        },
        {
          "Key": "TotalFreeSpace",
          "Value": "32754630656",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRtYfuwhzhtONmUqaZpp8b9w1"
        },
        {
          "Key": "UserIsAdministrator",
          "Value": "true",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRmWBeah4Q3_b30PzwWQKkKQ1"
        },
        {
          "Key": "UserIsLocalSystem",
          "Value": "true",
          "LastUpdate": "2025-10-16T09:52:03.417Z",
          "Id": "n7DL4J5sVuwYlyeiAM-tRkpFPhOgHEhRXb80E_ZBEww1"
        }
      ],
      "Configurations": [
        {
          "Description": "DEFAULT",
          "ExplorerDataSystemName": "MOCO-JETADMIN",
          "IsValidConfiguration": true,
          "IsEnable": true,
          "UseAutoAssign": false,
          "ExplorerDataId": "AoibK16eMjhN9q0kkK-4Jw2",
          "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
          "Id": "y1Cxc2Yeld3p5vlQU0tsYA2"
        }
      ],
      "ClusteredSlaves": [],
      "IsSelected": false,
      "LogIsReady": true,
      "SendLog": false,
      "LogFile": "20240213T183814_0af64ee74fd14906accc7b0575d8faed.zip",
      "IsV4": false,
      "Id": "AoibK16eMjhN9q0kkK-4Jw2"
    }
  ],
  "Subnets": [
    "10.0.127"
  ],
  "Id": null
}
```

### Explorer/Cluster/List

- **Data Type**: list
- **Item Count**: 50
- **Sample Data**:
```json
{
  "Customer": {
    "Code": "XPY90A5X7E",
    "Description": "TEST",
    "CountryCode": "ES",
    "CountryName": "Spain",
    "CountryIsEu": true,
    "ExternalIdentifier": null,
    "Id": "xLU6dYTojIJx86T3v-cqqA2"
  },
  "Description": "cluster Oficinas 17/1/2019",
  "AutoFixDayLimit": 1,
  "ExplorerDatas": [
    {
      "CreatedAt": "2019-01-17T11:24:35Z",
      "Identifier": "bac7b563-f961-4dfa-9faa-53dbacc8eae8",
      "IP": "192.168.70.122;192.168.18.1;192.168.248.1",
      "SystemName": "DESKTOP-O84T8UM",
      "MakeServiceUpdate": false,
      "MakeExplorerUpdate": false,
      "DealerId": "4fUyik6lneHBr6gF7kbJvg2",
      "DealerCode": null,
      "DealerDescription": null,
      "CustomerId": "xLU6dYTojIJx86T3v-cqqA2",
      "CustomerCode": "XPY90A5X7E",
      "CustomerDescription": "TEST",
      "AutomaticUpdate": true,
      "BuildNumber": "3.9.4",
      "BuildDate": "2022-10-05T11:44:13Z",
      "IsEmbedded": false,
      "TableVersion": 5,
      "ServiceBuildNumber": "3.9.4.17854",
      "ServiceMajor": 3,
      "ConfiguratorBuildNumber": null,
      "PollingInterval": 20,
      "LastUpload": "2022-03-10T09:14:44.013Z",
      "Version": "3.9.4.5",
      "Platform": "Windows",
      "LastPing": "2024-08-07T08:21:07.023Z",
      "AgentIsRunning": false,
      "HasWarning": false,
      "PingIsOutOfDate": false,
      "DataIsOutOfDate": false,
      "NeverReceiveData": false,
      "NoValidConfiguration": false,
      "LastRun": "2022-03-10T09:14:44Z",
      "LastNetworkDiscovery": "2024-08-07T06:39:26.283Z",
      "TimeZone": "(UTC+01:00) Brussels, Copenhagen, Madrid, Paris",
      "TimeZoneIana": "Europe/Paris",
      "ExplorerDataJamExplorerJamVersion": "3.7.7508.25563 - 2020-07-22 14:22:12",
      "ExplorerDataJamVersion": "4.1.6086",
      "ExplorerDataJamConnectorStatus": 3,
      "ExplorerDataJamLastContactTimeUtc": "2020-12-12T20:11:00Z",
      "ExplorerDataJamRegistrationKey": "b01c9343-99ac-4e3e-839b-db2124d642f9",
      "ExplorerDataJamLastUploadUtc": "2020-11-16T13:35:00Z",
      "ExplorerDataJamCreatedAtUc": null,
      "ExplorerDataJamInstalledComputer": "DESKTOP-O84T8UM",
      "ExplorerDataJamWebProxyAddress": null,
      "ExplorerDataJamWebProxyPort": 0,
      "ExplorerDataJamConnectorId": 36101,
      "ExplorerCluster": null,
      "IsMasterInCluster": false,
      "ExplorerDataInfos": [
        {
          "Key": "CurrentDirectory",
          "Value": "C:\\Program Files (x86)\\ABAsset\\eXplorer3",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKCPzZnXMDZC44C0nKja0gVA1"
        },
        {
          "Key": "CurrentTimeZone",
          "Value": "Hora est\u00e1ndar romance",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKN0MHuObZplvnBwZlFGsPKE1"
        },
        {
          "Key": "DotNetVersion",
          "Value": "4.0.30319.42000",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKM-ZfNIBZze5-11nhCMqcbY1"
        },
        {
          "Key": "HasProxy",
          "Value": "false",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKAp5qSa_nWj483Rj32tLBx01"
        },
        {
          "Key": "HasProxyAuth",
          "Value": "false",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKJxl7Jv2ZYoIWEQEtFIkOLk1"
        },
        {
          "Key": "Is64BitOperatingSystem",
          "Value": "true",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKEl1-0FIxlzd561gm54hEtw1"
        },
        {
          "Key": "Is64BitProcess",
          "Value": "true",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKCxcqWsNwWdtVzqeLNzW9y41"
        },
        {
          "Key": "IsPartOfDomain",
          "Value": "false",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKD9_zz8MnSUHrnLuLjdwNh41"
        },
        {
          "Key": "IsServiceUser",
          "Value": "true",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKO_WrxyLw1Y0T6tUOs1QsPs1"
        },
        {
          "Key": "MachineName",
          "Value": "DESKTOP-O84T8UM",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKAOGFNF_FOV-lRzHYoJd0RA1"
        },
        {
          "Key": "Manufacturer",
          "Value": "HP",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKMk1b3hxTlzixTFjxYmOGrI1"
        },
        {
          "Key": "Model",
          "Value": "HP ProBook 430 G5",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKNyXI7NLn84d2DQSxswNudc1"
        },
        {
          "Key": "NetworkInfo.IPAddresses.1",
          "Value": "ip=192.168.70.122;subnet=255.255.255.0|",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0YpTH5On523rrh6YsW4OfEA1"
        },
        {
          "Key": "NetworkInfo.IPAddresses.2",
          "Value": "ip=169.254.112.161;subnet=255.255.0.0|",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0daPn_zlvpfgykgRwsX03oI1"
        },
        {
          "Key": "NetworkInfo.IPAddresses.3",
          "Value": "ip=169.254.217.246;subnet=255.255.0.0|",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0Spw7dvRm_BqymspT-ItAPc1"
        },
        {
          "Key": "NetworkInfo.IPAddresses.4",
          "Value": "ip=169.254.110.206;subnet=255.255.0.0|",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0fQiOroGKjhdva2-eoBBwR01"
        },
        {
          "Key": "NetworkInfo.IPAddresses.5",
          "Value": "ip=192.168.248.1;subnet=255.255.255.0|",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0Z3gVvrss7cv-B3loOsMDT81"
        },
        {
          "Key": "NetworkInfo.IPAddresses.6",
          "Value": "ip=192.168.18.1;subnet=255.255.255.0|",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0VinfYLkFFPw744R81uZ81s1"
        },
        {
          "Key": "NetworkInfo.IPAddresses.7",
          "Value": "ip=169.254.77.221;subnet=255.255.0.0|",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0ScR6JWQM1QilzJSzhIHQrE1"
        },
        {
          "Key": "NetworkInfo.IPAddresses.8",
          "Value": "ip=169.254.124.69;subnet=255.255.0.0|",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKInRpHYT1SZTW7TOm6_NBCo1"
        },
        {
          "Key": "NetworkInfo.NetworkInterface.1",
          "Value": "Realtek PCIe GBE Family Controller",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0V7R6eLxfa8U1I2sYsg9R-k1"
        },
        {
          "Key": "NetworkInfo.NetworkInterface.2",
          "Value": "Intel(R) Dual Band Wireless-AC 8265",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0YFtlrfBK257A4Hd5aTOM8g1"
        },
        {
          "Key": "NetworkInfo.NetworkInterface.3",
          "Value": "Microsoft Wi-Fi Direct Virtual Adapter",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0YeeKtUy6yLo6WAfPCm9mlg1"
        },
        {
          "Key": "NetworkInfo.NetworkInterface.4",
          "Value": "Microsoft Wi-Fi Direct Virtual Adapter #4",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0V1jjOw7PjD-PCA9FfpGsk41"
        },
        {
          "Key": "NetworkInfo.NetworkInterface.5",
          "Value": "VMware Virtual Ethernet Adapter for VMnet1",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0VESdINcne__5sHhkNnC1CM1"
        },
        {
          "Key": "NetworkInfo.NetworkInterface.6",
          "Value": "VMware Virtual Ethernet Adapter for VMnet8",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0V9SXf1iTJ72YPpjF3lHo6Y1"
        },
        {
          "Key": "NetworkInfo.NetworkInterface.7",
          "Value": "TAP-Windows Adapter V9",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0eu3e0Io-UJAe5g-vFs9_0g1"
        },
        {
          "Key": "NetworkInfo.NetworkInterface.8",
          "Value": "Bluetooth Device (Personal Area Network)",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKHAIK0mMS2eDNCZazGY1j7I1"
        },
        {
          "Key": "NetworkInfo.NetworkInterfaceType.1",
          "Value": "Ethernet",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0aC_AfYOwl-h321x0ydrneQ1"
        },
        {
          "Key": "NetworkInfo.NetworkInterfaceType.2",
          "Value": "Wireless80211",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0cxQQ4kdI4T4pTTx4L13EEE1"
        },
        {
          "Key": "NetworkInfo.NetworkInterfaceType.3",
          "Value": "Wireless80211",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0aEYGh5qycK6GlqEti4dEJk1"
        },
        {
          "Key": "NetworkInfo.NetworkInterfaceType.4",
          "Value": "Wireless80211",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0cQGIc6d0TyjsvX-r8Orfw01"
        },
        {
          "Key": "NetworkInfo.NetworkInterfaceType.5",
          "Value": "Ethernet",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0TrII3ZfsGi_M1NqKqcAQtg1"
        },
        {
          "Key": "NetworkInfo.NetworkInterfaceType.6",
          "Value": "Ethernet",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0dupQ9d6ePyxm-6SWTWpzqo1"
        },
        {
          "Key": "NetworkInfo.NetworkInterfaceType.7",
          "Value": "Ethernet",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0axn6hEMZAT_-aJZ_S6WlEc1"
        },
        {
          "Key": "NetworkInfo.NetworkInterfaceType.8",
          "Value": "Ethernet",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKAOckAU2_k8au_B1usTjrPQ1"
        },
        {
          "Key": "NumberOfCores",
          "Value": "4",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKKWyHmvmj7L0pGyFH1iCh-c1"
        },
        {
          "Key": "NumberOfLogicalProcessors",
          "Value": "8",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKA4LKv0wulItv7o6l8_1MG81"
        },
        {
          "Key": "NumberOfPhysicalProcessors",
          "Value": "1",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKK4LCVZyEvdV2bjKm7PxEKc1"
        },
        {
          "Key": "OperatingSystem",
          "Value": "Microsoft Windows 10 Pro",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKJ-ph8fu2TssjvGAzpFdlZE1"
        },
        {
          "Key": "OperatingSystemServicePack",
          "Value": "",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKO17JDQeFN1ytd4gwjaptBk1"
        },
        {
          "Key": "OperatingSystemVersion",
          "Value": "10.0.19045",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKAjUJjoqoPBUo697EID4FxQ1"
        },
        {
          "Key": "PhysicalMemory",
          "Value": "17179869184",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKGiqGlT8l9nAVUyhWpHx_0M1"
        },
        {
          "Key": "Processes",
          "Value": "SearchFilterHost,WmiPrvSE,DiagsCap,WmiPrvSE,Skype,AppHelperCap,backgroundTaskHost,SearchApp,csrss,MpsMonitor.eXplorer.Service,sppsvc,macompatsvc,SynTPEnh,fdlauncher,Adobe Desktop Service,UpdaterUI,SynTPEnhService,NetworkCap,mfetp,sqlwriter,SearchIndexer,HPJumpStartLaunch,IAStorIcon,Skype,OfficeClickToRun,RstMwService,hpqwmiex,WmiApSrv,WmiPrvSE,lsass,audiodg,WNetWatcher,DpAgent,unsecapp,RegSrvc,HPSmartDeviceAgentBase,DocShareService,SearchProtocolHost,jusched,BridgeCommunication,HPPrintScanDoctorService,sihost,dllhost,hpMAMSrv,mfewch,RuntimeBroker,WUDFHost,backgroundTaskHost,mfehcs,mfemactl,mfecanary,EvtEng,UserOOBEBroker,SystemSettings,ZeroConfigService,ctfmon,fpCSEvtSvc,jhi_service,wgsslvpnsrc,esif_uf,unsecapp,systray,SmartAudio3,conhost,DpCardEngine,HPCommRecovery,vmnat,mfeensppl,AdobeUpdateService,fontdrvhost,AggregatorHost,LanWlanWwanSwitchingServiceUWP,fontdrvhost,SecurityHealthService,node,AGMService,RuntimeBroker,RuntimeBroker,MoUsoCoreWorker,igfxCUIService,ONENOTEM,Skype,SynaMonApp,mfevtps,XtuService,vmware-usbarbitrator64,WUDFHost,RuntimeBroker,dasHost,vmnetdhcp,HPNotifications,RuntimeBroker,mctray,dasHost,WUDFHost,CxUtilSvc,Registry,dwm,mfeatp,LockApp,Flow,CCXProcess,ibtsiva,taskhostw,dllhost,taskhostw,TeamViewer_Service,PresentationFontCache,HP.Fms.Connector.Monitor.Service,Adobe CEF Helper,Skype,CoreSync,MicTray64,WmiPrvSE,RuntimeBroker,HPRadioMgr64,LMS,DpHostW,explorer,vmware-authd,Skype,StartMenuExperienceHost,valWBFPolicyService,services,smartscreen,WerFault,smss,winlogon,Creative Cloud,TextInputHost,HPSupportSolutionsFrameworkService,csrss,ShellExperienceHost,Adobe CEF Helper,mDNSResponder,wininit,armsvc,AdobeGCClient,conhost,conhost,SecurityHealthSystray,mcshield,igfxEM,masvc,mfeesp,SysInfoCap,IntelCpHDCPSvc,spoolsv,RuntimeBroker,conhost,ApplicationFrameHost,AdobeIPCBroker,HPAudioAnalytics,hpqWmiEx,HotKeyServiceUWP,DocShareLiberacion,PhoneExperienceHost,mfewc,Skype,sqlservr,macmnsvc,mfemms,sqlceip,HPJumpStartBridge,putty,DPAgent,SynTPHelper,IntelCpHeciSvc,IAStorDataMgrSvc,SgrmBroker,CxAudioSvc,fdhost,System,Memory Compression,Idle",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKPzJm-83KhLNfXCnoFkBju01"
        },
        {
          "Key": "ProcessorInfo.ProcessorClockSpeed.1",
          "Value": "1792",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0X-u4i6hecbEpTyfkBQX9L01"
        },
        {
          "Key": "ProcessorInfo.ProcessorManufacturer.1",
          "Value": "GenuineIntel",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0WwVHK3MPZHz2-mazvwDWHU1"
        },
        {
          "Key": "ProcessorInfo.ProcessorName.1",
          "Value": "Intel(R) Core(TM) i7-8550U CPU @ 1.80GHz",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "3Mld3Z7SBcOcrcpvxN7h0SYzp2wQ3nwg-4uKFI8HN-A1"
        },
        {
          "Key": "TotalFreeSpace",
          "Value": "143376453632",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKPJepY-QoCSVeBE0omF_dyM1"
        },
        {
          "Key": "UserIsAdministrator",
          "Value": "true",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKEuIo9JHjMuwwfZCwfr8G-M1"
        },
        {
          "Key": "UserIsLocalSystem",
          "Value": "true",
          "LastUpdate": "2024-08-07T08:40:33.113Z",
          "Id": "rwdOogTugeH0XQUGUL6MKP5HQ9FMveBWnB8T9rMkAQY1"
        }
      ],
      "Configurations": [],
      "ClusteredSlaves": [],
      "IsSelected": false,
      "LogIsReady": false,
      "SendLog": false,
      "LogFile": null,
      "IsV4": false,
      "Id": "SBUxLonylwkyYRbpZBTAxA2"
    },
    {
      "CreatedAt": "2019-04-30T06:49:16Z",
      "Identifier": "70ba3d93-f887-44c6-986f-23730d79192d",
      "IP": "192.168.70.7;192.168.56.1",
      "SystemName": "ADMINISTRACION2",
      "MakeServiceUpdate": false,
      "MakeExplorerUpdate": false,
      "DealerId": "4fUyik6lneHBr6gF7kbJvg2",
      "DealerCode": null,
      "DealerDescription": null,
      "CustomerId": "xLU6dYTojIJx86T3v-cqqA2",
      "CustomerCode": "XPY90A5X7E",
      "CustomerDescription": "TEST",
      "AutomaticUpdate": true,
      "BuildNumber": "3.7.12",
      "BuildDate": "2020-09-03T12:26:39Z",
      "IsEmbedded": false,
      "TableVersion": 1,
      "ServiceBuildNumber": "3.3.6702.19010",
      "ServiceMajor": 3,
      "ConfiguratorBuildNumber": "3.3.6584.28298",
      "PollingInterval": 20,
      "LastUpload": "2020-11-17T09:06:26.587Z",
      "Version": "3.7.12.1",
      "Platform": "Windows",
      "LastPing": "2020-11-26T17:36:31Z",
      "AgentIsRunning": null,
      "HasWarning": false,
      "PingIsOutOfDate": false,
      "DataIsOutOfDate": false,
      "NeverReceiveData": false,
      "NoValidConfiguration": false,
      "LastRun": "2020-11-17T09:06:30Z",
      "LastNetworkDiscovery": "2020-11-26T15:30:07Z",
      "TimeZone": "(UTC+01:00) Brussels, Copenhagen, Madrid, Paris",
      "TimeZoneIana": "Europe/Paris",
      "ExplorerDataJamExplorerJamVersion": "3.7.7508.25563 - 2020-07-22 14:22:12",
      "ExplorerDataJamVersion": "4.1.5300",
      "ExplorerDataJamConnectorStatus": 3,
      "ExplorerDataJamLastContactTimeUtc": "2020-11-26T17:38:00Z",
      "ExplorerDataJamRegistrationKey": "8ea5a2ea-702b-46f3-ae68-90eb49334cd6",
      "ExplorerDataJamLastUploadUtc": "2020-11-17T09:07:00Z",
      "ExplorerDataJamCreatedAtUc": null,
      "ExplorerDataJamInstalledComputer": "Administracion2",
      "ExplorerDataJamWebProxyAddress": null,
      "ExplorerDataJamWebProxyPort": 0,
      "ExplorerDataJamConnectorId": 46442,
      "ExplorerCluster": null,
      "IsMasterInCluster": false,
      "ExplorerDataInfos": [],
      "Configurations": [],
      "ClusteredSlaves": [],
      "IsSelected": false,
      "LogIsReady": false,
      "SendLog": false,
      "LogFile": null,
      "IsV4": false,
      "Id": "UkLZhqWAePu1kiW_DkLgRA2"
    }
  ],
  "Subnets": [],
  "Id": "p2Ma_cczIS9SfIOA3DC5MA2"
}
```

### Explorer/Configuration/List

- **Data Type**: list
- **Item Count**: 1
- **Sample Data**:
```json
{
  "ExplorerSchedules": [
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "Occurence": "Weekly",
      "StartAt": "2021-06-17T13:49:00Z",
      "Days": "1,2,3,4,5,6,7",
      "TimeZone": "(UTC-05:00) Eastern Time (US & Canada)",
      "LastRequest": "2025-10-23T00:00:00Z",
      "Id": "5K1G6df23wsCpKLrHCAidw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "Occurence": "Weekly",
      "StartAt": "2021-06-17T18:03:00Z",
      "Days": "1,2,3,4,5,6,7",
      "TimeZone": "(UTC-05:00) Eastern Time (US & Canada)",
      "LastRequest": "2025-10-22T00:00:00Z",
      "Id": "faRlcXxGtnlW2iQpXMJ8gw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "Occurence": "Weekly",
      "StartAt": "2021-06-17T21:04:00Z",
      "Days": "1,2,3,4,5,6,7",
      "TimeZone": "(UTC-05:00) Eastern Time (US & Canada)",
      "LastRequest": "2025-10-22T00:00:00Z",
      "Id": "5kgpmu6MhmRABxWOCHThZA2"
    }
  ],
  "ExplorerSubnets": [
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.0.127.1",
      "IpFrom": "10.0.127.1-254",
      "IpEnd": "10.0.127.254",
      "Id": "29qd5XCRJQH9LuxDp76whA2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.101.1",
      "IpFrom": "10.1.101.1-255",
      "IpEnd": "10.1.101.255",
      "Id": "OfSE6P34YawCGD6DGYZluw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.0.0.0",
      "IpFrom": "10.0.0.0-255",
      "IpEnd": "10.0.0.255",
      "Id": "oghFitacT2QfgORmNZzcdg2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.108.1",
      "IpFrom": "10.1.108.1-255",
      "IpEnd": "10.1.108.255",
      "Id": "RtVKSygaXuna2um9qdJjjA2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.133.1",
      "IpFrom": "10.1.133.1-255",
      "IpEnd": "10.1.133.255",
      "Id": "OlVBPvEV9jHNyEUkvz7lZw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.134.1",
      "IpFrom": "10.1.134.1-255",
      "IpEnd": "10.1.134.255",
      "Id": "S6d-z7ACjnzPcC_XFn93sA2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.102.1",
      "IpFrom": "10.1.102.1-255",
      "IpEnd": "10.1.102.255",
      "Id": "9B-1WvlhYiS_p3J9bTHoEw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.104.1",
      "IpFrom": "10.1.104.1-255",
      "IpEnd": "10.1.104.255",
      "Id": "JQW2jjtKs8l_GItxInMlzQ2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.129.1",
      "IpFrom": "10.1.129.1-255",
      "IpEnd": "10.1.129.255",
      "Id": "0FsPsea-tNBHNlghtR3GmQ2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.103.1",
      "IpFrom": "10.1.103.1-255",
      "IpEnd": "10.1.103.255",
      "Id": "FxEFP8FksLHPWrziREq16g2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.125.1",
      "IpFrom": "10.1.125.1-255",
      "IpEnd": "10.1.125.255",
      "Id": "MiDTuOF6RossndaPMw2Bmg2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.112.1",
      "IpFrom": "10.1.112.1-255",
      "IpEnd": "10.1.112.255",
      "Id": "f5naWvaZ4iBLG2LDqcptcA2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.127.1",
      "IpFrom": "10.1.127.1-255",
      "IpEnd": "10.1.127.255",
      "Id": "nvg6pnQXUrGI5iIIfoYzSg2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.135.1",
      "IpFrom": "10.1.135.1-255",
      "IpEnd": "10.1.135.255",
      "Id": "xQAfrp2r5JHZu_kpEv3Bfw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.128.1",
      "IpFrom": "10.1.128.1-255",
      "IpEnd": "10.1.128.255",
      "Id": "dD6qnLcwS4IQ9qk1Mev3lA2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.114.1",
      "IpFrom": "10.1.114.1-255",
      "IpEnd": "10.1.114.255",
      "Id": "MNQNE7oxl-vWYOhcsNy_uw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.120.1",
      "IpFrom": "10.1.120.1-255",
      "IpEnd": "10.1.120.255",
      "Id": "jn0KFTsxSS2PuTUK9smnDQ2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.138.1",
      "IpFrom": "10.1.138.1-255",
      "IpEnd": "10.1.138.255",
      "Id": "_LyAkbCrQND-Nj5cwOieZA2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.107.1",
      "IpFrom": "10.1.107.1-255",
      "IpEnd": "10.1.107.255",
      "Id": "S3vEXdqI1ks9CPeXUam_Hw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.115.1",
      "IpFrom": "10.1.115.1-255",
      "IpEnd": "10.1.115.255",
      "Id": "ghcVHOw9vIFJ3LWrMHkq6g2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.119.1",
      "IpFrom": "10.1.119.1-255",
      "IpEnd": "10.1.119.255",
      "Id": "dXC4NXDna7xctBYzSbeObw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.126.1",
      "IpFrom": "10.1.126.1-255",
      "IpEnd": "10.1.126.255",
      "Id": "VJxyAOo3Q-vDpdIY-jtJHQ2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.137.1",
      "IpFrom": "10.1.137.1-255",
      "IpEnd": "10.1.137.255",
      "Id": "fM2NVqP-gI5Y6KLFJMQlbA2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.0.2.1",
      "IpFrom": "10.0.2.1-255",
      "IpEnd": "10.0.2.255",
      "Id": "8ri2Dv-XRSg0RljofunVSQ2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.110.1",
      "IpFrom": "10.1.110.1-255",
      "IpEnd": "10.1.110.255",
      "Id": "bC-QYVvY0fHnRGrI6hpUSw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.118.1",
      "IpFrom": "10.1.118.1-255",
      "IpEnd": "10.1.118.255",
      "Id": "LuMyOn9fEHEY_vYZ_2oVGw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.122.1",
      "IpFrom": "10.1.122.1-255",
      "IpEnd": "10.1.122.255",
      "Id": "HQ51to9yJBu0lz3E92e6UQ2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.136.1",
      "IpFrom": "10.1.136.1-255",
      "IpEnd": "10.1.136.255",
      "Id": "cHoUjX1kTiwqakQ88fHSXw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "192.168.1.3",
      "IpFrom": "192.168.1.3-254",
      "IpEnd": "192.168.1.254",
      "Id": "rWWHRpBe3SMLMiilTyaXSg2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.128.153",
      "IpFrom": "10.1.128.153",
      "IpEnd": "",
      "Id": "RNLCfu3sJzwZxXU-BqrEsw2"
    },
    {
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "OfficeId": null,
      "OfficeCode": null,
      "OfficeDescription": null,
      "ExplorerConfigurationId": "y1Cxc2Yeld3p5vlQU0tsYA2",
      "SubnetMask": null,
      "PartialWalkOID": null,
      "IpStart": "10.1.102.64",
      "IpFrom": "10.1.102.64",
      "IpEnd": "",
      "Id": "w4xijwVvqydybFKWbO8Sfg2"
    }
  ],
  "ExplorerHostnames": [],
  "ExplorerWorkingDays": [
    {
      "DayOfWeek": 1,
      "IsDayEnabled": false,
      "Range1": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 2,
      "IsDayEnabled": false,
      "Range1": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 3,
      "IsDayEnabled": false,
      "Range1": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 4,
      "IsDayEnabled": false,
      "Range1": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 5,
      "IsDayEnabled": false,
      "Range1": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 6,
      "IsDayEnabled": false,
      "Range1": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    },
    {
      "DayOfWeek": 0,
      "IsDayEnabled": false,
      "Range1": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range2": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      },
      "Range3": {
        "Active": false,
        "Values": [
          8,
          20
        ]
      }
    }
  ],
  "IdTicket": null,
  "MaxProcess": 1,
  "MaxThread": 1,
  "MaxParallelOperations": 10,
  "DisablePing": false,
  "ActivateExclusions": true,
  "UseEmbeddedOIDMap": false,
  "MpsUrl": null,
  "DeviceDetectionOidArray": null,
  "ActivateOverrides": false,
  "DisableWalks": false,
  "ScanPc": false,
  "VersionTest": null,
  "UseHPSecureCounters": true,
  "Community": null,
  "ScanTimeout": null,
  "WalkTimeout": null,
  "GetTimeout": null,
  "PingTimeout": null,
  "SendEnvironmentInfo": false,
  "ExplorerJamcParameters": null,
  "WinceTimeoutSocket": 5000,
  "WinceDeepSleepDisable": false,
  "UseSNMPv2Version": false,
  "UseHpProxy": false,
  "MacOsUseOtherSNMP": false,
  "ForceEncoding": null,
  "UseKodakAlarisAgent": false,
  "ActivateZebraDetection": false,
  "DisableMessagePanelReadingsOutsideWorkingDays": false,
  "AllowUnicastAndBroadcast": false,
  "UseStandardWalk": false,
  "UseBulkWalkV3": true,
  "AlternativeSnmpPort": null,
  "AlternativeDiscoveryPorts": null,
  "ExplorerInterval": {
    "Discovery": null,
    "Meters": null,
    "Supplies": null,
    "Errors": null,
    "Attributes": null,
    "All": null
  },
  "DefaultExplorerInterval": {
    "Discovery": 360,
    "Meters": 240,
    "Supplies": 60,
    "Errors": 60,
    "Attributes": 240,
    "All": 60
  },
  "Description": "DEFAULT",
  "ExplorerDataSystemName": "MOCO-JETADMIN",
  "IsValidConfiguration": true,
  "IsEnable": true,
  "UseAutoAssign": false,
  "ExplorerDataId": "AoibK16eMjhN9q0kkK-4Jw2",
  "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
  "Id": "y1Cxc2Yeld3p5vlQU0tsYA2"
}
```

### Explorer/GetConnectorEndpoints

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### Explorer/GetConnectors

- **Data Type**: list
- **Item Count**: 50
- **Sample Data**:
```json
{
  "CreatedAt": "0001-01-01T00:00:00Z",
  "Identifier": "f1c75da3-ccf3-4111-907b-2b9d66603d56",
  "IP": "10.228.217.195;169.254.39.210",
  "SystemName": "TERRY",
  "MakeServiceUpdate": false,
  "MakeExplorerUpdate": false,
  "DealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
  "DealerCode": "NY06AGDWUQ",
  "DealerDescription": "SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE",
  "CustomerId": "IGqXz-HQDlikBkkKKpdJbw2",
  "CustomerCode": "KMZQLW08OV",
  "CustomerDescription": "102403 REED LALLIER CHEVROLET",
  "AutomaticUpdate": true,
  "BuildNumber": "4.3.4.0",
  "BuildDate": "2025-06-25T15:32:24Z",
  "IsEmbedded": false,
  "TableVersion": null,
  "ServiceBuildNumber": "4.3.4.0",
  "ServiceMajor": 4,
  "ConfiguratorBuildNumber": null,
  "PollingInterval": 5,
  "LastUpload": "2025-10-23T13:39:03Z",
  "Version": "4.3.4.0",
  "Platform": "Windows",
  "LastPing": "2025-10-23T14:18:15.527Z",
  "AgentIsRunning": null,
  "HasWarning": false,
  "PingIsOutOfDate": false,
  "DataIsOutOfDate": false,
  "NeverReceiveData": false,
  "NoValidConfiguration": false,
  "LastRun": null,
  "LastNetworkDiscovery": "2025-10-22T07:38:15.583Z",
  "TimeZone": "(UTC-05:00) Eastern Time (US & Canada)",
  "TimeZoneIana": "America/New_York",
  "ExplorerDataJamExplorerJamVersion": null,
  "ExplorerDataJamVersion": "4.1.6712",
  "ExplorerDataJamConnectorStatus": 1,
  "ExplorerDataJamLastContactTimeUtc": "2025-10-23T14:18:00Z",
  "ExplorerDataJamRegistrationKey": "bb09b6dc-a3a7-4c78-9703-ff59561af34e",
  "ExplorerDataJamLastUploadUtc": "2025-07-14T18:36:00Z",
  "ExplorerDataJamCreatedAtUc": "2025-07-14T18:35:38.407Z",
  "ExplorerDataJamInstalledComputer": null,
  "ExplorerDataJamWebProxyAddress": null,
  "ExplorerDataJamWebProxyPort": 0,
  "ExplorerDataJamConnectorId": 1482000,
  "ExplorerCluster": null,
  "IsMasterInCluster": false,
  "ExplorerDataInfos": [],
  "Configurations": [
    {
      "Description": "DEFAULT",
      "ExplorerDataSystemName": null,
      "IsValidConfiguration": true,
      "IsEnable": true,
      "UseAutoAssign": false,
      "ExplorerDataId": null,
      "CustomerId": "IGqXz-HQDlikBkkKKpdJbw2",
      "Id": "k49hV-P7CMnrCSmtiXeuRA2"
    }
  ],
  "ClusteredSlaves": [],
  "IsSelected": false,
  "LogIsReady": false,
  "SendLog": false,
  "LogFile": null,
  "IsV4": true,
  "Id": "ovb0HpH-pH6910MpA5MX2g2"
}
```

### Explorer/GetDcaReleaseNotes

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### Explorer/GetExplorerDatas

- **Data Type**: list
- **Item Count**: 50
- **Sample Data**:
```json
{
  "CreatedAt": "2021-06-22T12:18:11Z",
  "Identifier": "0af64ee7-4fd1-4906-accc-7b0575d8faed",
  "IP": "10.0.127.100",
  "SystemName": "MOCO-JETADMIN",
  "MakeServiceUpdate": false,
  "MakeExplorerUpdate": false,
  "DealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
  "DealerCode": null,
  "DealerDescription": null,
  "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
  "CustomerCode": "S8COQ6NPQZ",
  "CustomerDescription": "MOORE COUNTY",
  "AutomaticUpdate": true,
  "BuildNumber": "3.9.4",
  "BuildDate": "2022-10-05T11:44:13Z",
  "IsEmbedded": false,
  "TableVersion": 13,
  "ServiceBuildNumber": "3.9.4.17854",
  "ServiceMajor": 3,
  "ConfiguratorBuildNumber": null,
  "PollingInterval": 20,
  "LastUpload": "2025-10-22T22:32:40.017Z",
  "Version": "3.9.4.13",
  "Platform": "Windows",
  "LastPing": "2025-10-23T14:11:30.87Z",
  "AgentIsRunning": false,
  "HasWarning": false,
  "PingIsOutOfDate": false,
  "DataIsOutOfDate": false,
  "NeverReceiveData": false,
  "NoValidConfiguration": false,
  "LastRun": "2023-01-09T23:18:00Z",
  "LastNetworkDiscovery": "2025-10-16T07:51:30.63Z",
  "TimeZone": "(UTC-05:00) Eastern Time (US & Canada)",
  "TimeZoneIana": "America/New_York",
  "ExplorerDataJamExplorerJamVersion": "3.9.4.27684 - 2022-10-17 12:03:21",
  "ExplorerDataJamVersion": "4.1.6712",
  "ExplorerDataJamConnectorStatus": 1,
  "ExplorerDataJamLastContactTimeUtc": "2025-10-23T14:12:00Z",
  "ExplorerDataJamRegistrationKey": "c66b1c22-57e9-4edb-8fc6-67c46194f35d",
  "ExplorerDataJamLastUploadUtc": "2025-10-22T22:38:00Z",
  "ExplorerDataJamCreatedAtUc": null,
  "ExplorerDataJamInstalledComputer": "MOCO-JETADMIN",
  "ExplorerDataJamWebProxyAddress": null,
  "ExplorerDataJamWebProxyPort": 0,
  "ExplorerDataJamConnectorId": 286148,
  "ExplorerCluster": null,
  "IsMasterInCluster": false,
  "ExplorerDataInfos": [
    {
      "Key": "CurrentDirectory",
      "Value": "C:\\Program Files (x86)\\MpsMonitor\\eXplorer3",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRn5yBzvQ26ur2QocEXCpOqo1"
    },
    {
      "Key": "CurrentTimeZone",
      "Value": "Eastern Standard Time",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRpTHBn4CfwrFvhpFeBiADsE1"
    },
    {
      "Key": "DotNetVersion",
      "Value": "4.0.30319.42000",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRm-8AFoChuZczz9foGNn_WA1"
    },
    {
      "Key": "HasProxy",
      "Value": "false",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRpUJC8GiHuXJQixD3QPs0GI1"
    },
    {
      "Key": "HasProxyAuth",
      "Value": "false",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRgGtGgVdHkSy0OegdzU4QJs1"
    },
    {
      "Key": "Is64BitOperatingSystem",
      "Value": "true",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRjKj-iXZO6QVVPRq9gwZCPg1"
    },
    {
      "Key": "Is64BitProcess",
      "Value": "true",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRmsZ_Jq1sa8CcoqQ4aBBcTg1"
    },
    {
      "Key": "IsPartOfDomain",
      "Value": "true",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRvy8yWS_XE9H91ki-KvWt5Y1"
    },
    {
      "Key": "IsServiceUser",
      "Value": "true",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRsrwLP6N2GOp3EaVVeZR15Q1"
    },
    {
      "Key": "MachineName",
      "Value": "MOCO-JETADMIN",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRlr8IfK_eyZDH3PoPPQvXrc1"
    },
    {
      "Key": "Manufacturer",
      "Value": "VMware, Inc.",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRuWSi4dbMbnl19cinets8Yc1"
    },
    {
      "Key": "Model",
      "Value": "VMware Virtual Platform",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRuKV8CY4JwVJbkPmXjkFY1o1"
    },
    {
      "Key": "NetworkInfo.IPAddresses.1",
      "Value": "ip=10.0.127.100;subnet=255.255.0.0|",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRozREwmRO2uJpnj3Vl-D9f01"
    },
    {
      "Key": "NetworkInfo.NetworkInterface.1",
      "Value": "vmxnet3 Ethernet Adapter",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRstGVatfkl4fdTy1tTyShkI1"
    },
    {
      "Key": "NetworkInfo.NetworkInterfaceType.1",
      "Value": "Ethernet",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRkXAet_iFy9x8QOvEQFI3YY1"
    },
    {
      "Key": "NumberOfCores",
      "Value": "2",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRrnmlQ3DE8BM01_UDk1IPJI1"
    },
    {
      "Key": "NumberOfLogicalProcessors",
      "Value": "2",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRpSgaQRoJtIOY3ikaIwM0W41"
    },
    {
      "Key": "NumberOfPhysicalProcessors",
      "Value": "2",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRiFp_uKLseNWWSYexjQGTWo1"
    },
    {
      "Key": "OperatingSystem",
      "Value": "Microsoft Windows Server 2019 Standard",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRhdb5Icrnc6KAIS0GjwnqN81"
    },
    {
      "Key": "OperatingSystemServicePack",
      "Value": "",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRsWkTlqNo69Bev3Y9Mc171s1"
    },
    {
      "Key": "OperatingSystemVersion",
      "Value": "10.0.17763",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRtgaCYRGWawi52_ARkdaZ_M1"
    },
    {
      "Key": "PhysicalMemory",
      "Value": "8589934592",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRkF8_L6AE_pwqlwg9w6QG001"
    },
    {
      "Key": "Processes",
      "Value": "HPWJAService,dllhost,splunk-winevtlog,vm3dservice,dllhost,cysandbox,WmiPrvSE,SRAgent,tlaworker,csrss,sppsvc,dwm,HP.Fms.Connector.Monitor.Service,wininit,TiWorker,WmiPrvSE,vm3dservice,smss,TrustedInstaller,HP.Dss.App.WinService,tlaworker,splunkd,conhost,WmiApSrv,cysandbox,services,Registry,msdtc,SRFeature,MpsMonitor.eXplorer.Service,sqlservr,WUDFHost,VGAuthService,LogonUI,sqlwriter,vmtoolsd,spoolsv,csrss,winlogon,conhost,cyserver,SRService,cortex-xdr-payload,fontdrvhost,snmp,fontdrvhost,VSSVC,SRManager,HP.Fms.Connector.Service,cysandbox,WmiPrvSE,SRVirtualDisplay,lsass,System,Idle",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRvrY7bJtkMXuBTCphCE_BUU1"
    },
    {
      "Key": "ProcessorInfo.ProcessorClockSpeed.1",
      "Value": "2893",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRj2b_P8oxH4Mgymd9ivB0hU1"
    },
    {
      "Key": "ProcessorInfo.ProcessorClockSpeed.2",
      "Value": "2893",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRjc2hikZWMFRpi_LTjTeXic1"
    },
    {
      "Key": "ProcessorInfo.ProcessorManufacturer.1",
      "Value": "GenuineIntel",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRiHIwdpu11Gk0pl68RUI2LM1"
    },
    {
      "Key": "ProcessorInfo.ProcessorManufacturer.2",
      "Value": "GenuineIntel",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRqELu5n7PEEGG5azq6EGm2E1"
    },
    {
      "Key": "ProcessorInfo.ProcessorName.1",
      "Value": "Intel(R) Xeon(R) Gold 6326 CPU @ 2.90GHz",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "C-W2B_iRElbJBOt9nDcu1hioLg9LADUbqC6a1n4WMxo1"
    },
    {
      "Key": "ProcessorInfo.ProcessorName.2",
      "Value": "Intel(R) Xeon(R) Gold 6326 CPU @ 2.90GHz",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRuNLMex4CKoKiDeyq-zDHPc1"
    },
    {
      "Key": "TotalFreeSpace",
      "Value": "32754630656",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRtYfuwhzhtONmUqaZpp8b9w1"
    },
    {
      "Key": "UserIsAdministrator",
      "Value": "true",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRmWBeah4Q3_b30PzwWQKkKQ1"
    },
    {
      "Key": "UserIsLocalSystem",
      "Value": "true",
      "LastUpdate": "2025-10-16T09:52:03.417Z",
      "Id": "n7DL4J5sVuwYlyeiAM-tRkpFPhOgHEhRXb80E_ZBEww1"
    }
  ],
  "Configurations": [
    {
      "Description": "DEFAULT",
      "ExplorerDataSystemName": "MOCO-JETADMIN",
      "IsValidConfiguration": true,
      "IsEnable": true,
      "UseAutoAssign": false,
      "ExplorerDataId": "AoibK16eMjhN9q0kkK-4Jw2",
      "CustomerId": "USlIvWCpo-sF9xTjf2Fvog2",
      "Id": "y1Cxc2Yeld3p5vlQU0tsYA2"
    }
  ],
  "ClusteredSlaves": [],
  "IsSelected": false,
  "LogIsReady": true,
  "SendLog": false,
  "LogFile": "20240213T183814_0af64ee74fd14906accc7b0575d8faed.zip",
  "IsV4": false,
  "Id": "AoibK16eMjhN9q0kkK-4Jw2"
}
```

### Explorer/License/List

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### Explorer/V3/ReleaseNotes

- **Data Type**: str
- **Item Count**: None
- **Sample Data**:
```json
"eXplorer 3 Release Notes\r<br/>[3.9.4 16/09/2025]\r<br/>Release 13\r<br/>- Detailed counters detection correction for CANON<br/>- Serial Number, Detailed counters detection correction for CANON model PRO-6100<br/>- Standard Counters, Detailed counters detection correction for EPSON models EPSON ET-5855 SERIES, EPSON ET-16685 SERIES, EPSON ET-5185 SERIES, EPSON ET-2810 SERIES<br/>- Miantenance kit logic, Firmware, Standard Counters, Detailed counters detection correction for EPSON models EPSON L4260 SERIES, EPSON L6290 SERIES<br/>- Serial number, Miantenance kit logic, Firmware, Standard Counters, Detailed counters detection correction for EPSON models EPSON EM-C8101 SERIES, EPSON L4160 SERIES, EPSON L6570 SERIES<br/>- Serial number, Firmware, Detailed counters detection correction for EPSON model EPSON AM-M5500 SERIES<br/>- Miantenance kit logic, Firmware, Standard Counters, Detailed counters detection correction for EPSON models EPSON L4260 SERIES, EPSON L6290 SERIES<br/>- Serial Number detection correction for HP models HP OFFICEJET PRO 9720E SERIES, HP OFFICEJET PRO 8120E SERIES, HP LASERJET PRO MFP M26NW<br/>- Detailed counters detection for HP<br/>- Detailed counters detection correction for HP model HP COLOR LASERJET MFP M283FDW<br/>- Detailed counters detection correction for IBASE models OCE, PLOTWAVE3000 R2.1, OCE, PLOTWAVE5500*, OCE, PLOTWAVE7500*, OCE, PLOTWAVE3*<br/>- Standard counters detection correction for LEXMARK models CS963E, CS730DE, CS517DE, MC3224DWE<br/>- Detailed counters detection for TOSHIBA<br/>- Miantenance kit logic correction for XEROX model XEROX VERSALINK C625*\r<br/>\r<br/>[3.9.4 29/07/2025]\r<br/>Release 12\r<br/>- Detailed counters detection correction for KYOCERA models TASKALFA 308CI, TASKALFA 306CI, TASKALFA 307CI, 350CI<br/>- Standard counters detection correction for KYOCERA model TASKALFA 306CI<br/>- Detailed counters detection correction for LEXMARK\r<br/>\r<br/>[3.9.4 23/06/2025]\r<br/>Release 11\r<br/>- Firmware, Standard counters detection for EPSON models EPSON L3150 SERIES, EPSON L3550 SERIES, EPSON L8050 SERIES, EPSON L3260 SERIES<br/>- Firmware, Standard counters, detailed counters detection for EPSON models  EPSON L5290 SERIES, EPSON L565 SERIES<br/>- Serial Number, Firmware, Standard and Detailed counters detection for EPSON model EPSON L1455 SERIES<br/>- Firmware, Standard and Detailed counters detection for EPSON model EPSON EPSON L15180 SERIES<br/>- Detailed counters detection for EPSON model EPSON CW-C6500AE<br/>- Serial Number, Firmware, Maintenance kit logic, Standard and Detailed counters detection correction for EPSON model EPSON EP-C800 SERIES<br/>- Department detection for HP models HP COLOR LASERJET M452DN, HP LASERJET M402DN<br/>- System Name detection for KONICA model KONICA MINOLTA BIZHUB C360I<br/>- Toner, Firmware detection correction for KONICA model KONICA MINOLTA BIZHUB 4000I<br/>- Detailed counters detection correction for KYOCERA models TASKALFA 308CI, TASKALFA 306CI, TASKALFA 307CI, 350CI<br/>- Detailed counters detection correction for KYOCERA<br/>- Standard counters detection for LEXMARK models APEOSPORT PRINT C2410SD, CS632DWE,CX735ADSE,CX635ADWE, XC9525, SHARP MX-C358F, SHARP MX-C428P, SHARP MX-C428F, CX951SE, XC9535, CX962SE, CX961SE, CS531DW<br/>- Detailed counters detection correction for LEXMARK<br/>- Standard counters detection correction for RICOH models RICOH MP C2011, RICOH M C320FW<br/>- Detailed counters detection for CANON model MF750C SERIES<br/>- Detailed counters detection correction for FUJIFILM<br/>- Detailed counters detection correction for KATUN\r<br/>\r<br/>[3.9.4 13/02/2025]\r<br/>Release 10\r<br/>- Serial Number, Firmware, Model, Standard and Detailed counters detection correction for FUJIFILM ARIVIA*<br/>- Detailed counters detection correction for KYOCERA models 4008CI, KYOCERA TASKALFA 4054CI<br/>- Serial Number detection correction for HP model HP LASERJET PROFESSIONAL P1102W<br/>- Firmware detection correction for HP model HP LASER MFP 432FDN<br/>- Standard and Detailed counters detection correction for RICOH model RICOH M C320FW\r<br/>\r<br/>[3.9.4 06/02/2025]\r<br/>Release 9\r<br/>- Det. counters fix for BROTHER models HL-L9310CDW SERIES, MFC-L6900DW SERIES, MFC-L9570CDW SERIES<br/>- Det. counters fix for CANON models LBP252, MF650C SERIES<br/>- Det. counters fix for EPSON model EPSON SC-T5100M SERIES\t<br/>- Det. counters fix for FUJIFILM<br/>- Serial Number fix for HP series HP OFFICEJET PRO 91*, models HP ENVY 6100E SERIES, CP1025NW, LASERJET PRO M12W, OFFICEJET 8120E SERIES, 8139E SERIES, 9730E SERIES<br/>- Waste Toner fix for FUJIFILM models APEOS C2560, APEOS C5570, APEOS C3060<br/>- Waste Toner fix for OKI model ES9475 MFP<br/>- System name for KONICA models KONICA MINOLTA BIZHUB C368, KONICA MINOLTA BIZHUB C650I<br/>- System name, Det. counters fix for KONICA model KONICA MINOLTA BIZHUB 308E<br/>- Serial Number, Firmware, Maintenance Kit, Std. Counters, Det. counters fix for EPSON models EPSON AM-C550 SERIES, EPSON EM-C800 SERIES, EPSON SC-T5700D SERIES<br/>- Firmware, Maintenance Kit, Std. Counters, Det. counters fix for EPSON model EPSON L18050 SERIES<br/>- Firmware, Maintenance Kit, Std. Counters fix for EPSON model L6460 SERIES<br/>- Firmware, Std. Counters fix for EPSON model EPSON L3250 SERIES<br/>- Maintenance Kit fix for KONICA models ACCURIOPRESS C4080, ACCURIOPRESS C7100, ACCURIOPRINT C4065<br/>- Std. counters fix for LEXMARK models C3326DW, RICOH M C550SRF, XC8355, PANTUM CP2200DW, XC9255<br/>- Std. counters fix for OKI model ES5473 MFP<br/>- Std. counters, Det. counters fix for OKI models ES4192 MFP<br/>- Std. counters fix for EPSON model EPSON ET-16680 SERIES<br/>- Maintenance kit and photoconductors fix for LEXMARK model XC4140<br/>- Std. counters, Maintenance kit and photoconductors fix for LEXMARK model C4150<br/>- Std. counters, Toners level fix for PANTUM model CM2100ADW<br/>- Serial Number, Firmware, Model, Std. and Det. counters fix for KATUN<br/>- Det. counters for HP model HP LASERJET P3010 SERIES\r<br/>\r<br/>[3.9.4 05/11/2024]\r<br/>Release 8\r<br/>- Standard counters, Detailed counters detection correction for CANON model G6000 SERIES<br/>- Toner level correction detection correction for KONICA model KONICA MINOLTA ACCURIOPRINT C4065<br/>- Detailed counters detection correction for KYOCERA models  TASKALFA 3051CI, TASKALFA 3252CI, TASKALFA 4053CI, TASKALFA 306CI<br/>- Detailed counters detection correction for CANON model LBP223<br/>- Serial Number, Firmware, Maintenance Kit, Standard Counters, Detailed counters detection correction for EPSON model EPSON AM-C400 SERIES<br/>- Standard counters, Detailed counters detection correction for OKI models C844, C841<br/>- Toner level correction for EPSON\tmodel EPSON EPSON STYLUS PRO 3880<br/>- Department detection correction for HP model HP LASERJET 400 M401DN<br/>- Standard counters, Detailed counters detection correction for CANON model LBP673C<br/>- Standard counters, Detailed counters detection correction for EPSON model EPSON AL-C300DN<br/>- Standard counters, Detailed counters detection correction for CANON model LBP663C<br/>- Serial Number detection correction for HP series HP COLOR LASERJET PRO MFP 3*<br/>- Waste Toner detection correction for FUJIFILM model APEOS C3570<br/>- Waste Toner detection correction for FUJIFILM model AKLRMLS03<br/>- Toner detection correction for FUJIFILM model APEOSPRO C810<br/>- Detailed counters detection correction for RICOH models RICOH SP 3710SF, RICOH M 320F<br/>- Serial Number detection correction for HP series HP COLOR LASERJET PRO 3*<br/>- Standard counters detection correction for LEXMARK model XC8163, XC9645, XC9635, M C240FW, CS317DN, XC9655, MC3426ADW<br/>- Firmware, Standard Counters, Maintenance Kit detection correction for EPSON model EPSON L6490 SERIES<br/>- Firmware, Standard Counters, Maintenance Kit detection correction for EPSON model EPSON L6160 SERIES<br/>- Detailed counters detection correction for OKI\r<br/>\r<br/>[3.9.4 19/07/2024]\r<br/>Release 7\r<br/>- EPSON models cartridges' serial number detection<br/>- EPSON EPSON SC-T5100M SERIES Serial Number, Firmware, Toner detection correction<br/>- EPSON EPSON WF-M21000 SERIES Maintenance Kit logic detection correction<br/>- HP HP LASERJET PRO M501DN Department detection correction<br/>- LEXMARK C2335, CX532ADWE, P C200W, XC2335, XC4153, XC6153, C4352, CX942ADSE Standard counters detection correction<br/>- TOSHIBA E-STUDIO339CS, E-STUDIO409CP Standard counters detection correction<br/>- RISO COMCOLOR GD9630 Standard counters detection correction - Consumable levels detection correction<br/>- SHARP\tSHARP BP family, SHARP MX family Waste Toner Level correction<br/>- FUJI XEROX DOCUPRINT CP315/318 DW Standard counters detection correction<br/>- XEROX VERSALINK C400 DN PRINTER Maintenance kit detection correction\r<br/>\r<br/>[3.9.4 23/05/2024]\r<br/>Release 6\r<br/>- Standard counters and detailed counters detection correction for CANON model\tMF742C/744C<br/>- Serial Number detection correction for EPSON models EPSON AL-*,EPSON CW-C6500AE,EPSON EPL-*,EPSON M*<br/>- Maintenance box detection correction for EPSON ET-*<br/>- Serial Number and consumable detection correction for EPSON model\tEPSON SC-T7700D SERIES<br/>- Serial Number detection correction for HP\tfor HP LASERJET PROFESSIONAL P 1102W<br/>- KIP device detection<br/>- Firmware detection correction for KONICA models ACCURIO*, KONICA MINOLTA ACCURIO*<br/>- Consumable detection correction for KONICA models ACCURIOPRINT C3070L, GENERIC 66C-1<br/>- Detailed counters detection correction for KONICA\tmodel KONICA MINOLTA BIZHUB C227<br/>- Additional informations detection correction for LEXMARK model KONICA MINOLTA BIZHUB*<br/>- Detailed counters detection correction for LEXMARK model\tX792<br/>- Waste toner detection correction for SHARP models SHARP BP-50M55,SHARP BP-70M45,SHARP BP-C533WR,SHARP MX-C428F,SHARP MX-C428P,SHARP MX-C607P,SHARP MX-M3550,SHARP MX-M3571,SHARP MX-3051,SHARP MX-3551,SHARP MX-4051,SHARP MX-6051,SHARP MX-3561S,SHARP MX-3571S,SHARP MX-4061S,SHARP MX-4141N,SHARP BP-50C36,SHARP BP-50C55,SHARP BP-50C65,SHARP BP-55C26,SHARP BP-70C36,SHARP BP-70C55,SHARP BP-70C65,SHARP MX-C304WH<br/>- Consumables detection correction for SHARP models SHARP MX-C358F, SHARP MX-C428F, SHARP MX-C428P, SHARP MX-C607P<br/>- Photoconductors detection correction for XEROX model XEROX ALTALINK C8135 MULTIFUNCTION PRINTER<br/>- Zebra settings detection additions for ZEBRA *\r<br/>\r<br/>[3.9.4 07/02/2024]\r<br/>Release 5\r<br/>- Added detailed counters detection for EPSON EPSON WF-*, EPSON AM-C5000 SERIES, EPSON AM-C4000 SERIES<br/>- Serial numer detection correction for HP HP COLOR LASERJET PRO 4*, HP COLOR LASERJET PRO MFP 4*<br/>- Toner level correction for HP HP COLOR LASERJET PRO 4*, HP COLOR LASERJET PRO MFP 4*\r<br/>\r<br/>[3.9.4 14/09/2023]\r<br/>Release 4\r<br/>- System name detection correction for CANON models IR-ADV 4745, IR-ADV C350, IR-ADV C5850<br/>- Maintenance kit detection correction for EPSON models EPSON AM-C4000 SERIES, EPSON AM-C5000 SERIES, EPSON AM-C6000 SERIES<br/>- Standard counters detection correction for EPSON models EPSON ET-16600 SERIES, EPSON ET-4700 SERIES, EPSON ET-4700 SERIES, EPSON ET-4800 SERIES, EPSON ET-4800 SERIES<br/>- Detailed counters detection correction for FUJIFILM model *<br/>- Toner level correction for KONICA model ACCURIOPRESS C7100<br/>- Detecting Detail Counters Fax Usage Pages Sent, Fax Usage Pages Received. for LEXMARK  model *<br/>- Standard counters detection correction for LEXMARK  models C2535DW, CX522ADE, PANTUM CM2200FDW, XC9335, XC9445, XC9455<br/>- Consumable levels detection correction for OKI models PRO8432WT, PRO9542<br/>- Standard counters detection correction - Detailed counters detection correction for RICOH model RICOH M C251FW<br/>- System name detection correction for RICOH models RICOH MP C2003, RICOH MP C3004, RICOH MP C307<br/>- Detailed counters detection correction for RICOH model RICOH P 311<br/>- Standard counters detection correction - Detailed counters detection correction for RICOH model RICOH P C301W<br/>- System name detection correction for RICOH model RICOH SP C250DN<br/>- Serial number detection correction for ZEBRA all models<br/>- Detailed counters detection correction for BROTHER all models<br/>- Product number and specific model detection for HP all models<br/>- Product number, and serial numer detection correction for ZEBRA all models\r<br/>\r<br/>[3.9.4 07/07/2023]\r<br/>Release 3\r<br/>- Detailed counters detection correction\tfor\tCANON\t*<br/>- Detailed counters detection correction\tfor\tSAMSUNG\t*<br/>- Detailed counters detection correction\tfor\tFUJIFILM\t*<br/>- Standard counters detection correction\tfor\tKYOCERA\t2N5-45<br/>- Standard counters detection correction\tfor\tLEXMARK \tC4342<br/>- Standard counters detection correction\tfor\tOKI\tC831<br/>- Standard counters detection correction\tfor\tLEXMARK \tCS521DN<br/>- Standard counters detection correction\tfor\tLEXMARK \tCS923DE<br/>- Standard counters detection correction\tfor\tEPSON\tEPSON ET-3850 SERIES<br/>- Standard counters detection correction\tfor\tOKI\tES8483 MFP<br/>- Consumable levels detection correction\tfor\tOKI\tES9466 MFP<br/>- Toner level correction\tfor\tSAMSUNG\tML-3470 SERIES<br/>- Standard counters detection correction\tfor\tSAMSUNG\tML-371X SERIES<br/>- Standard counters detection correction\tfor\tRICOH\tRICOH P C311W<br/>- Standard counters detection correction\tfor\tTOSHIBA\tTOSHIBA E-STUDIO2822AM<br/>- Detailed counters detection correction\tfor\tSAMSUNG\tX7400LX\r<br/>\r<br/>[3.9.4 02/05/2023]\r<br/>Release 2\r<br/>- Standard counters detection correction for KYOCERA model TASKALFA PRO 15000C<br/>- Standard counters detection correction for KYOCERA model 6008CI<br/>- Maintenance kit detection correction for RICOH model RICOH MP 3055<br/>- Levels detection correction for RICOH PRO C9200<br/>- Standard counters detection correction for LEXMARK model XC4342<br/>- Standard counters detection correction for LEXMARK model XC4352<br/>- Standard counters detection correction for LEXMARK model XC9325<br/>- Waste toner level correction for SHARP model SHARP BP-50C45<br/>- Waste toner level correction for SHARP model SHARP BP-70C31<br/>- Waste toner level correction for SHARP model SHARP BP-70C45<br/>- Waste toner level correction for SHARP model SHARP BP-60C36<br/>- Waste toner level correction for SHARP model SHARP BP-50C31<br/>- Waste toner level correction for SHARP model SHARP MX-M654N<br/>- Standard counters detection correction for OKI model ES6450<br/>- Standard counters detection correction for OKI model C8800<br/>- Standard counters detection correction for EPSON model EPSON ET-5150 SERIES<br/>- Standard counters detection correction for EPSON model EPSON ET-4850 SERIES<br/>- Toner detection correction for EPSON model EPSON SC-T3200 SERIES<br/>- Standard counters detection,detailed counters detection, serial number and firmware detection for EPSON model EPSON AM-C5000 SERIES<br/>- Standard counters detection,detailed counters detection, serial number and firmware detection for EPSON model EPSON AM-C4000 SERIES<br/>- Standard counters detection,detailed counters detection, serial number and firmware detection for EPSON model EPSON AM-C6000 SERIES<br/>- Detailed counters detection correction for EPSON WF SERIES<br/>- Standard counters detection correction for LEXMARK model C2326\r<br/>\r<br/>[3.9.4 16/12/2022]\r<br/>Release 1\r<br/>- Detailed counter detection for OKI model C911<br/>- Toner detection correction for SHARP model SHARP MX-C507F<br/>- Standard counters detection correction, serial number and firmware detection for EPSON model EPSON L14150 SERIES\r<br/>\r<br/>[3.9.4 24/11/2022]\r<br/>- Setup and binary versions with new certificate signature.\r<br/>\r<br/>[3.9.3 13/10/2022]\r<br/>Release 4\r<br/>- Standard counters detection correction for KYOCERA model 4008CI<br/>- Standard counters detection correction for KYOCERA model 5008CI<br/>- Standard counters detection correction for KYOCERA model D-COLOR MF3555<br/>- Standard counters detection correction for KYOCERA model TASKALFA 7353CI<br/>- Serial number and Total Counter detection for HP model HP LASERJET PRO MFP M128FW<br/>- Toner detection correction for SHARP model SHARP MX-C507P<br/>- Waste toner level correction for SHARP model SHARP BP-50C26<br/>- Waste toner level correction for SHARP model SHARP BP-60C31<br/>- Waste toner level correction for SHARP model SHARP BP-60C45<br/>- Waste toner level correction for SHARP model SHARP MX-5071S<br/>- Standard counters detection correction for OKI model ES8441\r<br/>\r<br/>[3.9.3 10/08/2022]\r<br/>Release 3\r<br/>- Consumable levels detection correction for OKI model PRO1050.\r<br/>- Detailed counter correction for CANON model CANON MF420 SERIES.\r<br/>- Standard counters,Serial and Firmware detection correction for EPSON model EPSON L6580 SERIES.\r<br/>- Detailed counter correction for LEXMARK model XC4140.\r<br/>- Detailed counter correction for KYOCERA model P-C3565I MFP.\r<br/>- Serial number detection correction for CANON model TA-30.\r<br/>- Standard counters, consumable levels, serial number detection correction for CANON models CANON GX* and GX*.\r<br/>- Maintenance kit detection correction for SAMSUNG model X7400LX.\r<br/>- Serial number and Total Counter detection for HP models HP LASERJET PRO MFP M127FS and HP LASERJET PRO MFP M127FW.\r<br/>\r<br/>[3.9.3 08/06/2022]\r<br/>Release 2\r<br/>- Detailed counters detection correction for HP model HP COLOR LASERJET PRO MFP M479FDN.\r<br/>- Standard counters and serial number detection,toner level correction for CANON model CANON GX6000 SERIES 1.080\r<br/>- Standard counters detection correction for KYOCERA model TASKALFA 4054CI\r<br/>- Standard counters, Serial and Firmware detection for EPSON model EPSON L15160 SERIES\r<br/>- Standard counters detection correction for KYOCERA model ECOSYS M5526CDN\r<br/>- Serial Number, Firmware and all 12 consumables levels detection for EPSON model EPSON SC-P5000 SERIES\r<br/>- Standard counters detection correction for RICOH model RICOH M C250FWB\r<br/>- Detailed counter detection Low, High and Middle for TOSHIBA printer model TOSHIBA E-STUDIO2000AC\r<br/>- Waste toner level correction for SHARP model SHARP MX-M3050\r<br/>- Standard counters and serial number detection,toner level correction for CANON model GX7000 SERIES\r<br/>- Waste toner level correction for SHARP model SHARP MX-4071S\r<br/>- Waste toner level correction for SHARP model SHARP MX-C303WH\r<br/>- Standard counters detection correction for RICOH model RICOH M C2000\r<br/>- Standard counters detection correction for KYOCERA model TASKALFA 5054CI\r<br/>- Serial number detection correction for HP models HP DESIGNJET T520 36IN\r<br/>- Serial number detection correction for HP models OFFICEJET PRO 8600 N911G\r<br/>\r<br/>[3.9.3 06/04/2022]\r<br/>Release 1\r<br/>- Standard counters detection correction for RICOH model SAVIN SP C831DN\r<br/>- Standard counters detection correction for RICOH PRO C7110S E-43A\r<br/>- Standard counters detection,toner level correction for CANON model CANON GX7000 SERIES 1.030\r<br/>- Removal of incorrect A3 counter detection for KONICA model C MF310P-1\r<br/>- Serial Number, Firmware and all 12 consumables levels detection for EPSON model EPSON SC-P9500 SERIES\r<br/>- Standard counters detection correction for LEXMARK model XC2326\r<br/>- Standard counters detection correction for KYOCERA model CS 3253CI\r<br/>- Standard counters detection correction for OKI model ES6412\r<br/>- Standard counters detection correction for LEXMARK model CX825 MPS ELITE\r<br/>- Waste toner level correction for SHARP SHARP MX-3071S\r<br/>- Waste toner level correction for SHARP SHARP MX-3061S\r<br/>- Toner detection correction for SHARP SHARP MX-C357F\r<br/>- Photoconductors detection correction, Waste toner level correction and Toner detection correction for SHARP SHARP MX-C407F\r<br/>- Toner detection correction and Waste toner level correction for SHARP model SHARP MX-C407P\r<br/>- Waste toner level correction for SHARP model SHARP MX-M3570\r<br/>- Waste toner level correction for SHARP model SHARP MX-6071S\r<br/>- Standard counters detection correction for KYOCERA model ECOSYS P5021CDW\r<br/>- Standard counters detection correction for KYOCERA model CS 3552CI\r<br/>\r<br/>[3.9.3 09/02/2022]\r<br/>- New Setup release with security improvements.\r<br/>\r<br/>[3.9.2 04/01/2022]\r<br/>Release 4\r<br/>- Standard counters detection correction for KYOCERA model 3508CI\r<br/>- Standard counters detection correction for KYOCERA model D-COLOR P2230\r<br/>- Standard counters detection correction for KYOCERA model 8307CI\r<br/>- Standard counters detection correction for KYOCERA model D-COLOR MF2555\r<br/>- Detailed counters detection correction for KYOCERA model TASKALFA 2552CI\r<br/>- Standard counters detection correction for KYOCERA model TASKALFA 308CI\r<br/>- Serial Number e Firmware for all EPSON ET-*\r<br/>- Photoconductors levels detection correction for XEROX model XEROX ALTALINK C8130 MULTIFUNCTION PRINTER\r<br/>- Photoconductors levels detection correction for XEROX model XEROX ALTALINK C8145 MULTIFUNCTION PRINTER\r<br/>- Photoconductors levels detection correction for XEROX model XEROX ALTALINK C8170 MULTIFUNCTION PRINTER\r<br/>- Standard counters detection correction for OKI ES5162 MFP\r<br/>- Waste toner level detection correction for SHARP models SHARP MX-M3071 \r<br/>- Waste toner level detection correction for SHARP models SHARP MX-3561,SHARP MX-3571,SHARP MX-4061,SHARP MX-5051,SHARP MX-5071,SHARP MX-5140N,SHARP MX-6070V,SHARP MX-7090N \r<br/>- Standard counters detection correction for LEXMARK MC3426I\r<br/>\r<br/>[3.9.2 04/11/2021]\r<br/>Release 3\r<br/>- Detailed counters detection correction for KYOCERA models TASKALFA 306CI and TASKALFA 307CI.\r<br/>\r<br/>[3.9.2 14/10/2021]\r<br/>Release 2\r<br/>- Standard counters, serial number and firmware version detection correction for EPSON models EPSON L15150 SERIES, EPSON L5190 SERIES, EPSON L6170 SERIES, EPSON L6190 SERIES, EPSON L655 SERIES and EPSON ST-C8090 SERIES.\r<br/>- Standard counters and detailed counters detection correction for KYOCERA model 2508CI.\r<br/>- Standard counters detection correction for KYOCERA models CS 508CI, ECOSYS M8124CIDN, P-4035I MFP and TASKALFA 3554CI.\r<br/>- Detailed counters detection correction for KYOCERA models 5052CI, TASKALFA 2553CI and TASKALFA 308CI.\r<br/>- Standard counters detection correction for LEXMARK models C6160, MC3326I and XC9265.\r<br/>- Standard counters detection correction for OKI models C612, ES4132 and ES6405.\r<br/>- Standard counters detection correction for RICOH model RICOH PRO C7200S.\r<br/>- Waste toner detection correction for SHARP model SHARP MX-C507F.\r<br/>- Detailed counters detection correction for TOSHIBA models TOSHIBA E-STUDIO4508LP_LOOPS-LP45 and TOSHIBA E-STUDIO5008LP_LOOPS-LP50.\r<br/>- Photoconductors levels detection correction for XEROX model XEROX ALTALINK C8155 MULTIFUNCTION PRINTER.\r<br/>\r<br/>[3.9.2 06/08/2021]\r<br/>Release 1\r<br/>- Standard and detailed counters detection correction for CANON models CANON MF741C/743C and MF731C/733C.\r<br/>- Serial number detection correction for EPSON model EPSON SC-P5000 SERIES.\r<br/>- Detailed counters detection correction for KONICA models GENERIC 61C-1L, GENERIC 62C-1 and KONICA MINOLTA BIZHUB PRESS C6000.\r<br/>- Standard counters detection correction for KYOCERA models 7307CI, ECOSYS P8060CDN, TASKALFA 6052CI, TASKALFA 7052CI and TASKALFA 8052CI.\r<br/>- Standard counters detection correction for LEXMARK models CX923DE and XC6152.\r<br/>- Standard counters detection correction for OKI model ES9431.\r<br/>- Standard counters detection correction for RICOH model RICOH M C250FW.\r<br/>- Waste toner level detection correction for SHARP models SHARP MX-M6071 and MX-M264N.\r<br/>\r<br/>[3.9.2 19/07/2021]\r<br/>- Security improvements.\r<br/>- Detection of printers brand Fujifilm.\r<br/>- Detection of printers brand Pantum.\r<br/>\r<br/>[3.9.1 15/07/2021]\r<br/>Release 3\r<br/>- Standard counters detection correction for CANON models CANON IR-ADV C3730, CANON IR-ADV C475 III, CANON MF742C/744C.\r<br/>- Standard counters detection correction for EPSON model EPSON ET-5170 SERIES. \r<br/>- Standard counters detection correction for KYOCERA models TASKALFA 2554CI, TASKALFA 266CI, TASKALFA 356CI and D-COLOR MF3254. \r<br/>- Standard counters detection correction for LEXMARK model CS410DN.\r<br/>- Standard counters detection correction for LEXMARK model CX921DE. \r<br/>- Standard counters detection correction for LEXMARK model XC4143.\r<br/>- Maintenance kits detection correction for OKI model MC860. \r<br/>- Standard counters detection correction for RICOH models RICOH AFICIO MP C2050 and RICOH PRO C9210. \r<br/>- Standard counters detection correction for TOSHIBA model TOSHIBA E-STUDIO2802AF.\r<br/>- Detailed counters detection correction for LEXMARK models XM3150 and XM5163.\r<br/>\r<br/>[3.9.1 06/07/2021]\r<br/>Release 2\r<br/>- Maintenance kits detection correction for EPSON model EPSON WF-M20590 SERIES.\r<br/>- Detailed counters detection correction for HP model HP COLOR LASERJET 4700.\r<br/>- Detailed counters detection correction for HP model HP OFFICEJET PRO X451DW PRINTER.\r<br/>- Detailed counters detection correction for HP model HP LASERJET P2035N.\r<br/>- Detailed counters detection correction for HP model HP OFFICEJET PRO X451DW PRINTER.\r<br/>- Detailed counters detection correction for KYOCERA model 4007CI.\r<br/>- Maintenance kits detection correction for LEXMARK model MS823DN.\r<br/>- Detailed counters detection correction for LEXMARK model XM1145.\r<br/>- Photoconductor levels detection correction for SHARP model SHARP MX-C507F.\r<br/>- Detailed counters detection correction (added 461, 462, 463) for IBASE models.\r<br/>- Detailed counters detection correction (added 461, 462) for CANON models.\r<br/>- Toner levels and serial number detection correction for CANON model CANON MB5400 SERIES 1.130.\r<br/>\r<br/>[3.9.1 21/05/2021]\r<br/>Release 1\r<br/>- Detailed counters and serial number detection correction for PANTUM models.\r<br/>- Detailed counters detection correction for LEXMARK models X950 and X954.\r<br/>- Detailed counters detection correction for TOSHIBA models TEC B family.\r<br/>- Serial number and firmware version detection correction for EPSON model EPSON ET-5170 SERIES.\r<br/>- Waste toner detection correction for SHARP models SHARP BP-20C25, SHARP MX-C357F and SHARP MX-M6051.\r<br/>\r<br/>[3.9.1 21/05/2021]\r<br/>- Security improvements\r<br/>- ZEBRA devices detection\r<br/>- KODAK ALARIS scanners detection\r<br/>- PANTUM devices detection\r<br/>- LEXMARK printers standard A3 mono and color detection correction\r<br/>\r<br/>[3.9.0 05/05/2021]\r<br/>Release 1\r<br/>- Detailed counters detection correction for CANON model MF720C SERIES.\r<br/>- Standard counters, serial number and firmware version detection correction for EPSON model EPSON ET-5800 SERIES.\r<br/>- Serial number, firmware version, model and standard counters detection for FUJIFILM models.\r<br/>- Maintenance kit values detection correction for HP models.\r<br/>- Detection of developer units for HP model HP COLOR LASERJET MFP E77822.\r<br/>- Detailed counters detection correction for HP models HP LASERJET 500 COLOR MFP M575 and HP OFFICEJET PRO X576DW MFP.\r<br/>- Standard counters detection correction for LEXMARK model CS827DE.\r<br/>- Photoconductors detection correction for LEXMARK family models XC92*, CX92* and LEXMARK XC92*.\r<br/>- Detailed counters detection correction for LEXMARK model XC9245.\r<br/>- Firmware version detection correction for RISO model COMCOLOR FT5230.\r<br/>- Waste toner level detection correction for SHARP model SHARP MX-6071.\r<br/>\r<br/>[3.9.0 31/03/2021]\r<br/>- New setup process and new security measures.\r<br/>\r<br/>[3.8.0 26/03/2021]\r<br/>Release 4\r<br/>- Consumable levels detection correction for EPSON model EPSON WF-C21000 SERIES.\r<br/>- Serial number detection correction for HP models HP DESIGNJET T1600DR PRINTER (36\" SIZED) and HP DESIGNJET T1700DR (44\" SIZED).\r<br/>- Detailed counters detection correction for HP model HP LASERJET P4014.\r<br/>- Standard counter, serial number, black toner level and firmware version detection for PANTUM model P3300DW.\r<br/>- System name detection correction for RICOH models RICOH MP 3055, RICOH MP 4055, RICOH MP 5055, RICOH MP 6055, RICOH MP C3504EX and RICOH MP C6004EX.\r<br/>- Maintenance kit levels detection correction for SHARP model SHARP MX-2651.\r<br/>- Photoconductors levels detection correction for XEROX model XEROX ALTALINK C8030 MULTIFUNCTION PRINTER.\r<br/>- Maintenance kit levels detection correction for XEROX model XEROX VERSALINK C500.\r<br/>\r<br/>[3.8.0 02/03/2021]\r<br/>Release 3\r<br/>- Standard counter detection correction for KYOCERA model ECOSYS P7040CDN.\r<br/>- Standard counter detection correction for LEXMARK model CX625ADHE.\r<br/>- Standard counter detection correction for OKI models C911 and MC363.\r<br/>- Standard counter detection correction for RICOH model RICOH P C600.\r<br/>- Detailed counter detection correction for LEXMARK.\r<br/>- Detailed counter detection correction for LEXMARK model XC9225.\r<br/>\r<br/>[3.8.0 17/02/2021]\r<br/>Release 2\r<br/>- Detailed counter detection for LEXMARK models.\r<br/>- Detailed counter detection correction for HP models HP COLOR LASERJET 5550, HP LASERJET 2100 SERIES, HP LASERJET 2200, HP LASERJET 4050 SERIES, HP LASERJET 4100 SERIES, HP LASERJET 4200, HP LASERJET 4250, HP LASERJET 4300, HP LASERJET 4350, HP LASERJET 500 COLORMFP M570DW, HP LASERJET 5000 SERIES and HP LASERJET P4015.\r<br/>- Black toner level detection correction for KONICA models KONICA MINOLTA BIZHUB 5000I and KONICA MINOLTA BIZHUB 5020I.\r<br/>- Firmware version detection correction for KONICA model KONICA MINOLTA BIZHUB 5000I.\r<br/>- Detailed counter detection correction for KYOCERA model 302CI.\r<br/>- Maintenance kit levels detection correction for OKI model ES7480 MFP.\r<br/>- Waste toner level detection correction for SHARP models SHARP BP-30C25, SHARP MX-4071 and SHARP MX-M266NV.\r<br/>\r<br/>[3.8.0 13/01/2021]\r<br/>Release 1\r<br/>- Detailed counter detection correction for HP models HP COLOR LASERJET MFP E77822, HP LASERJET 1320 SERIES and HP LASERJET 600 M602. - Standard counter detection correction and serial number correction for HP printer model HP LASERJET PRO MFP M126NW. - Detailed counter detection for ZEBRA printers. - Standard counter detection correction for RISO printer model COMCOLOR BLACK FW1230. - Detailed counter detection correction for LEXMARK printer model XC9225. - Billing color counter detection for KONICA printers.\r<br/>\r<br/>[3.8.0 30/12/2020]\r<br/>- New Installation Folder Security Management - Zebra Printer Detection Update - Detection of printers brand SindoRicoh.\r<br/>\r<br/>[3.7.12 16/12/2020]\r<br/>Release 7\r<br/>- Detailed counter detection correction for HP COLOR LASERJET M553 printer.\r<br/>\r<br/>[3.7.12 16/12/2020]\r<br/>Release 6\r<br/>- Standard counter detection correction for EPSON printer model EPSON ET-5850 SERIES. - Maintenace level detection correction kit for HP printer model HP COLOR LASERJET FLOW MFP M880. - Business Color Pages detail counter added, Graphics Color Pages and Highlight Color Pages for LEXMARK XC4140 printer. - Waste toner level detection correction for SHARP MX-3050V, SHARP MX-3070N, SHARP MX-3070V, SHARP MX-3550V, SHARP MX-3560N, SHARP MX-3560V and SHARP MX-3570N printers.\r<br/>\r<br/>[3.7.12 09/12/2020]\r<br/>Release 5\r<br/>- HP proxy management update.\r<br/>\r<br/>[3.7.12 02/12/2020]\r<br/>Release 4\r<br/>- Waste toner level detection correction for SHARP printer models SHARP MX-5070N - SHARP MX-C300W and SHARP BP-20C20 - Counter detection correction for RICOH printer model PRO C7100X\r<br/>\r<br/>[3.7.12 27/11/2020]\r<br/>Release 3\r<br/>- Correction of serial number detection and firmware version for EPSON printer model EPSON ET-5850 SERIES. - Detection of other paper formats for HP printers. - Correction of maintenance detection kit for HP printer model HP COLOR LASERJET MFP E77830. - Correction of counter detection for HP LASERJET PRO MFP M128FN model HP printer. - Correction of counter detection for KYOCERA printer model P-C3062I MFP. - Correction of counter detection for LEXMARK printer model CX317DN. - Detail counter detection correction for LEXMARK XC4140 printer.\r<br/>\r<br/>[3.7.12 20/11/2020]\r<br/>Release 2\r<br/>- Standard counter detection correction for KYOCERA printer model 6006CI.\r<br/>\r<br/>[3.7.12 09/11/2020]\r<br/>Release 1\r<br/>- Standard counter detection correction for HP COLOR LASERJET PRO MFP M479* printers (SC to ST). - Detection firmware version for HP printer model HP COLOR LASERJET MFP M283FDW.\r<br/>\r<br/>[3.7.12 09/11/2020]\r<br/>- Fix message detection by panel.\r<br/>\r<br/>[3.7.11 30/10/2020]\r<br/>Release 9\r<br/>- Standard counter detection correction and detailed counter detection for CANON printer model MF742C/744C. - Standard counter detection correction for KYOCERA printer model 5006CI. - Detailed counter detection Low, High and Middle for TOSHIBA printer model TOSHIBA E-STUDIO3505AC. - Position detection for EPSON printer model EPSON WF-C579R SERIES.\r<br/>\r<br/>[3.7.11 26/10/2020]\r<br/>Release 8\r<br/>- Mono counter detection correction for HP COLOR LASERJET PRO M454* printers.\r<br/>\r<br/>[3.7.11 22/10/2020]\r<br/>Release 7\r<br/>- Added detailed counter Cycles hardware for HP models HP LASERJET 500 COLORMFP M570DW and HP COLOR LASERJET MFP M476DW. - Added detailed counters Ledger (11x17) Simplex/Duplex and Letter (8.5x11) Simplex/Duplex for HP printers. - Standard counter detection correction for HP COLOR LASERJET PRO M454* printers.\r<br/>\r<br/>[3.7.11 13/10/2020]\r<br/>Release 6\r<br/>- Counter detection correction, serial number and firmware version for EPSON printer model EPSON EC-C7000 SERIES. - Consumable level detection correction (double black toner and maintenance box) for EPSON printer model EPSON WF-C20750 SERIES. - Serial number detection correction for HP printer model HP LASERJET P2035N. - Detailed counter detection correction (added Low, Middle, High) for TOSHIBA printer model TOSHIBA E-STUDIO2550C. - Counter detection correction for XEROX printer model FUJI XEROX DOCUCENTRE SC2022. - Photoconductor level detection correction for XEROX printer model XEROX ALTALINK C8035*.\r<br/>\r<br/>[3.7.11 08/10/2020]\r<br/>Release 5\r<br/>- Counter detection correction for HP printer model HP LASERJET P3010 SERIES (mod SC).\r<br/>\r<br/>[3.7.11 01/10/2020]\r<br/>Release 4\r<br/>- Serial number detection correction for HP printer model HP DESIGNJET T525 24-IN PRINTER. - Firmware detection for KONICA printers models KONICA MINOLTA BIZHUB 550I, KONICA MINOLTA BIZHUB 750I and KONICA MINOLTA BIZHUB C750I. - Counter detection correction for KYOCERA printer model P-C3566I MFP. - Counter detection correction for LEXMARK printer model MS823DN. - Detailed counter detection correction for TOSHIBA printer model TOSHIBA E-STUDIO2010AC.\r<br/>\r<br/>[3.7.11 18/09/2020]\r<br/>Release 3\r<br/>\"- Waste toner detection correction for HP models HP COLOR LASERJET MFP E77822 and HP COLOR LASERJET MFP E77825. - Serial number detection for HP printer model HP DESIGNJET T2600DR POSTSCRIPT MFP (36\"\" SIZED). - Counter detection correction for HP printer model HP LASERJET P3010 SERIES. - Firmware detection for KONICA printer models GENERIC 28C-1, GENERIC 70C-10, KONICA MINOLTA BIZHUB 360I, KONICA MINOLTA BIZHUB C360I and KONICA MINOLTA BIZHUB C550I. - Counters detection correction for KYOCERA 502CI, D-COLOR MF2554 and P-C3566I MFP printers. - Counters detection correction for LEXMARK TOSHIBA E-STUDIO388CS printer.\"\r<br/>\r<br/>[3.7.11 02/09/2020]\r<br/>Release 2\r<br/>- Detailed counter implementation for BROTHER printers.\r<br/>\r<br/>[3.7.11 02/09/2020]\r<br/>Release 1\r<br/>- Added new detailed counters for HP printers. - Counter detection correction for HP printer model HP COLOR LASERJET PRO MFP M479FDN. - Counter detection correction for XEROX printer model NEC COLOR MULTIWRITER 600F.\r<br/>\r<br/>[3.7.11 01/09/2020]\r<br/>- Fix consumable level management for HP printers\r<br/>\r<br/>[3.7.9 06/08/2020]\r<br/>Release 11\r<br/>- Maintenance kit detection correction for EPSON printer model EPSON WF-C20600 SERIES. - Serial number detection correction for HP printer model HP LASERJET PROFESSIONAL M1212NF MFP. - Counter detection correction for RICOH printer models PRO C7100X E-43A and RICOH PRO C7100SX\r<br/>\r<br/>[3.7.9 04/08/2020]\r<br/>Release 10\r<br/>- Correction of detailed counter detection for TOSHIBA printer model TOSHIBA E-STUDIO3515AC.\r<br/>\r<br/>[3.7.9 03/08/2020]\r<br/>Release 9\r<br/>- Billings counter detection for KONICA printers. - Counter detection correction for KYOCERA printers models 5006CI and P-C3562DN. - Serial number detection correction for EPSON printer model EPSON SC-T7200D SERIES.\r<br/>\r<br/>[3.7.9 23/07/2020]\r<br/>Release 8\r<br/>- Counters correction for KYOCERA printer model TASKALFA 307CI. - Counter detection correction for TOSHIBA printers models TOSHIBA E-STUDIO2007 and TOSHIBA e-STUDIO4505AC. - Maintenance kit correction for HP printer HP COLOR LASERJET MFP E87640.\r<br/>\r<br/>[3.7.9 16/07/2020]\r<br/>Release 7\r<br/>- Correction of counter detection for CANON printer model MF633C/635C. - Correction for detection of firmware version, serial number and consumable levels for EPSON printer model EPSON SC-T5400 SERIES. - Correction for system name detection and firmware version for KONICA printer model KONICA MINOLTA BIZHUB C250I. - Detailed counter detection correction for KYOCERA 357CI printer. - Counter detection correction for KYOCERA printer model P-C3566I MFP. - Counter detection correction for LEXMARK printer model XC9225.\r<br/>\r<br/>[3.7.9 26/06/2020]\r<br/>Release 6\r<br/>- Consumable detection correction for LEXMARK CS725 printer.\r<br/>\r<br/>[3.7.9 25/06/2020]\r<br/>Release 5\r<br/>- Serial number detection correction for HP printer model HP LASERJET PRO MFP M128FN. - Counter detection correction for LEXMARK printer model CS727DE.\r<br/>\r<br/>[3.7.9 17/06/2020]\r<br/>Release 4\r<br/>- Correction for firmware, counter and serial number detection for EPSON printers models EPSON ET-16650 SERIES and EPSON ET-5880 SERIES. - Waste toner level detection correction for HP printer model HP LASERJET 4345 MFP. - Detailed counter detection correction for HP model HP LASERJET 500 COLOR MFP M575 printer. - Counter detection correction for LEXMARK models CS431DW and CX431ADW printer. - Detailed counter detection correction for SAMSUNG model X7400LX printer.\r<br/>\r<br/>[3.7.9 08/06/2020]\r<br/>Release 3\r<br/>- Detailed counter detection correction for HP printer model OFFICEJET PRO 8100 N811A.\r<br/>\r<br/>[3.7.9 03/06/2020]\r<br/>Release 2\r<br/>- Detailed counter detection correction for SAMSUNG printer model M4580FX. - Detailed counter detection for HP printer model OFFICEJET PRO 8100 N811A. - Waste toner level detection correction for SHARP printer model SHARP MX-M3070.\r<br/>\r<br/>[3.7.9 26/05/2020]\r<br/>Release 1\r<br/>\"- Serial number correction for HP models DESIGNJET 4020PS (42'' SIZED), HP DESIGNJET T1200 POSTSCRIPT (44'' SIZED), HP DESIGNJET T1300 POSTSCRIPT (44'' SIZED), HP DESIGNJET T1600DR POSTSCRIPT PRINTER (36\"\" SIZED), HP DESIGNJET T1700 POSTSCRIPT (44\"\" SIZED), HP DESIGNJET T1700DR POSTSCRIPT (44\"\" SIZED), HP DESIGNJET T7100 (42'' SIZED), HP DESIGNJET T7100PS (42'' SIZED), HP DESIGNJET T7200 (42'' SIZED), HP DESIGNJET T790PS 24IN (24'' SIZED) and OFFICEJET PRO 8100 N811A. - Waste toner level detection correction for SHARP MX-M4071 model printer. - Detailed meter detection correction for HP printers.\"\r<br/>\r<br/>[3.7.9 26/05/2020]\r<br/>- Improvement of HP counter management.\r<br/>\r<br/>[3.7.8 21/05/2020]\r<br/>Release 3\r<br/>- Detection serial number, model, system name and firmware version for ZEBRA printers.\r<br/>\r<br/>[3.7.8 18/05/2020]\r<br/>Release 2\r<br/>- Detection of detailed counters of the Simplex/Duplex A3/A4/A5 format for HP printers. - Detection of detailed counters for LEXMARK printer model MX611DHE. - Correction of counter detection for LEXMARK printer model XC9255.\r<br/>\r<br/>[3.7.8 08/05/2020]\r<br/>Release 1\r<br/>- Counter detection correction for LEXMARK printer model CS417DN. - Detailed counter detection for LEXMARK printer model MX522ADHE. - Maintenance level detection correction kit for OKI printer model ES8451 MFP. - System name detection correction for RICOH printers RICOH MP 4054, RICOH MP 5054 and RICOH MP C2503. - Detailed counter detection correction for SAMSUNG X4300LX printer.\r<br/>\r<br/>[3.7.8 04/05/2020]\r<br/>- Improved performance and stability\r<br/>\r<br/>[3.7.7 15/04/2020]\r<br/>- Improved reading for Message Management from Panel\r<br/>\r<br/>[3.7.6 01/04/2020]\r<br/>Release 6\r<br/>- Counter detection correction for HP printer model HP COLOR LASERJET PRO MFP M479DW. - A3 counter detection for KYOCERA printer model 3206CI. - Counter detection correction for LEXMARK printer model XC8155. - Counter detection correction for OKI printer model ES5432.\r<br/>\r<br/>[3.7.6 19/03/2020]\r<br/>Release 5\r<br/>- Counter detection correction for LEXMARK printer model C9235 - Counter detection correction for SAMSUNG printer model K7600LX - Waste toner level detection correction for SHARP printer model SHARP MX-M5071 - Serial number detection for HP printer model HP LASERJET PROFESSIONAL M1217NFW MFP\r<br/>\r<br/>[3.7.6 04/03/2020]\r<br/>Release 4\r<br/>- Maintenance kit detection correction for HP printers. - Toner level detection correction for XEROX printer model XEROX PHASER 8860.\r<br/>\r<br/>[3.7.6 28/02/2020]\r<br/>Release 3\r<br/>- Maintenance kit correction for EPSON Workforce series printers.\r<br/>\r<br/>[3.7.6 25/02/2020]\r<br/>Release 2\r<br/>- Counter detection correction for OKI printer model ES9466 MFP. - Counter detection correction for SHARP printer model MX-5141N.\r<br/>\r<br/>[3.7.6 19/02/2020]\r<br/>Release 1\r<br/> - Counter detection correction for HP printer model HP OFFICEJET PRO 251DW PRINTER.  - Counter detection correction for IBASE printers.  - Detailed counter detection correction for OKI printer model ES4192 MFP.  - Counter detection correction for RICOH printer model RICOH SP C840DN.  - Detailed counter detection correction for TOSHIBA printers.\r<br/>\r<br/>[3.7.6 14/02/2020]\r<br/>- A3 counter support with operator OR - TLS 1.1 and 1.2 - Department font management for HP printers - Detailed counter management with description detected via OID\r<br/>\r<br/>[3.7.5 13/02/2020]\r<br/>Release 6\r<br/>- A3 counter detection for BROTHER printer model BROTHER MFC-J6945DW - Counter detection correction for CANON printer model CANON WG7000 SERIES - Detailed counter detection correction for SAMSUNG printer model X4220RX\r<br/>\r<br/>[3.7.5 07/02/2020]\r<br/>Release 5\r<br/>- Maintenance kit detection correction for HP printer model HP COLOR LASERJET FLOW E87660 - Consumable level detection (as maintenance kit) for HP plotter model HP DESIGNJET T930 (36'' SIZED) - Counter detection correction for LEXMARK printer model CX622ADE - Counter detection correction for OKI printer model ES8473 MFP\r<br/>\r<br/>[3.7.5 29/01/2020]\r<br/>Release 4\r<br/>- Detailed counter detection correction for BROTHER printer models HL-L9310CDW SERIES, BROTHER MFC-L6900DW SERIES and BROTHER MFC-L9570CDW SERIES. - Serial number detection correction and firmware version for EPSON printer model EPSON SC-P7500 SERIES. - Department detection correction for HP printers. - Detailed counter detection for LEXMARK X466DE printer.\r<br/>\r<br/>[3.7.5 20/01/2020]\r<br/>Release 3\r<br/>- A3 counter detection for BROTHER printer model BROTHER MFC-J6930DW - Serial number and firmware version correction for EPSON printer model EPSON ST-M3000 SERIES - A3 counter detection for KYOCERA printer model TASKALFA 3212I - Counter detection correction for LEXMARK CS310DN printer - Detailed counter detection for LEXMARK X792, XM3150 and XM5163 printers - Counter detection correction for RICOH SP C260SFNW printer - Waste toner correction for SHARP MX-C300P printer\r<br/>\r<br/>[3.7.5 08/01/2020]\r<br/>Release 2\r<br/>- Correction of detailed counter detection for HP printers (A4 Duplex Mono/Color). - Firmware detection for KONICA printers models GENERIC 25C-0I and GENERIC 30C-0I. - Counter detection correction for SAMSUNG printer model K4250RX. - Waste toner detection correction for SHARP printer model MX-C304W. - Detailed counter detection correction for TOSHIBA printer model TOSHIBA E-STUDIO2050C.\r<br/>\r<br/>[3.7.5 20/12/2019]\r<br/>Release 1\r<br/>- Detailed counter detection correction for HP printer model HP COLOR LASERJET M553. - Counter detection correction for KYOCERA printer model TASKALFA 2550CI. - Counter detection correction for OKI printers models ES7411 and PRO9431.\r<br/>\r<br/>[3.7.5 19/12/2019]\r<br/>- Correction for serial reading detection toner for LEXMARK and SAMSUNG printers - Forceencoding parameter management for correct encoding setting - MAGNETA color toner detection management for HP printers\r<br/>\r<br/>[3.7.0 10/12/2019]\r<br/>Release 24\r<br/>- Counter detection correction for HP printer model HP PAGEWIDE MANAGED MFP P77740Z. - Firmware detection correction for KONICA printer model GENERIC C335-0I. - Counter detection correction for SAMSUNG printer model CLP-660 SERIES. - Waste Toner detection correction for SHARP printer model SHARP MX-3071.\r<br/>\r<br/>[3.7.0 28/11/2019]\r<br/>Release 23\r<br/>- Detail counter detection correction for CANON printers. - Position detection correction for HP printers models HP COLOR LASERJET FLOW E77830, HP COLOR LASERJET MFP E57540, HP COLOR LASERJET PRO MFP M479FDN, HP LASERJET FLOW MFP E52645 and HP PAGEWIDE COLOR FLOW E58650.\r<br/>\r<br/>[3.7.0 22/11/2019]\r<br/>Release 22\r<br/>- Counter detection correction for HP printer model HP PAGEWIDE MANAGED MFP P77740Z. - Counter detection correction for OKI printer model INTEC CS4000. - Serial Number detection correction and EPSON printer firmware version EPSON model ST-4000 SERIES. \r<br/>\r<br/>[3.7.0 14/11/2019]\r<br/>Release 21\r<br/>- Counter correction for CANON printer model CANON IR-ADV C3530 III 16.12. - Serial Number and System Name detection correction for CANON PRO-2100 and CANON PRO-6100S printers. - Serial Number detection correction for CANON PRO-4100 printer. - Serial Number detection correction for HP HP DESIGNJET Z6810 60IN printer (60'' SIZED). - Counter detection correction for HP printer model HP PAGEWIDE MANAGED MFP P77740Z. - System name detection correction for RICOH printer model RICOH IM 600SR\r<br/>\r<br/>[3.7.0 08/11/2019]\r<br/>Release 20\r<br/>- Counter detection correction for HP models HP OFFICEJET PRO 8720 and HP PAGEWIDE MANAGED MFP P77740Z. - Counter detection correction for OKI printer model ES8140. - Counter detection correction for SAMSUNG printers models K4250RX and M5370LX.\r<br/>\r<br/>[3.7.0 29/10/2019]\r<br/>Release 19\r<br/>- Counter detection correction for KYOCERA printers models 6007CI, 7006CI, TASKALFA 406CI and TASKALFA 6053CI.\r<br/>\r<br/>[3.7.0 21/10/2019]\r<br/>Release 18\r<br/>- Counter detection correction for KYOCERA models 4007CI and TASKALFA 351CI printers. - Photoconductor detection correction for EPSON printer model EPSON AL-M400 - Waste Toner detection correction for SHARP printer model SHARP MX-C303W\r<br/>\r<br/>[3.7.0 11/10/2019]\r<br/>Release 17\r<br/>- Toner level detection correction and firmware version for EPSON printer model EPSON SC-T5400M SERIES. - Counters detection correction for HP printer model HP COLOR LASERJET PRO MFP M479FDW. - System Name correction for KONICA printer model KONICA MINOLTA BIZHUB C458. - Photoconductor correction for LEXMARK printer model TOSHIBA e-STUDIO408S. - Counter detection correction for LEXMARK printer model XC2235. - Counter detection correction for OKI printer model E-STUDIO263CP. - Detail counter detection correction for TOSHIBA printer model E-STUDIO2508A.\r<br/>\r<br/>[3.7.0 01/10/2019]\r<br/>Release 16\r<br/>- Detail counter detection correction for BROTHER printer models BROTHER MFC-L6900DW SERIES and BROTHER MFC-L9570CDW SERIES. - Counter detection correction for HP printer models HP COLOR LASERJET PRO M454DW and HP COLOR LASERJET PRO MFP M479FDW. - System Name Detection Correction for RICOH printer model RICOH IM C300. - Counter detection correction for RICOH printers models RICOH PRO C651EX, RICOH SP C342DN and RICOH SP C360DNW. - Detail counter detection correction for TOSHIBA printers models TOSHIBA E-STUDIO2010AC, TOSHIBA E-STUDIO2050C, TOSHIBA E-STUDIO2518A, TOSHIBA E-STUDIO2555C, TOSHIBA E-STUDIO3515AC and TOSHIBA E-STUDIO4508A.\r<br/>\r<br/>[3.7.0 24/09/2019]\r<br/>Release 15\r<br/>- Serial Number detection correction, firmware version and counters for EPSON printer model EPSON ET-4760 SERIES. - Photoconductor detection correction for OKI printer model ES7480 MFP. - Counter detection correction for OKI printer model MC573.\r<br/>\r<br/>[3.7.0 05/09/2019]\r<br/>Release 14\r<br/>- System name detection correction for CANON printers CANON MF520 SERIES, CANON IR-ADV C3530 III 12.30, CANON MF745C/746C, CANON LBP710CX R1.44/PH and CANON LBP214. - Detailed counter detection correction for IBASE printer OCE model, VARIOPRINT 120. - Counter correction for CANON printer model CANON IR-ADV C5550 III 15.30. - Counter detection correction for LEXMARK printer model XC4240. - Waste Toner detection correction for SHARP printer model SHARP MX-M754N\r<br/>\r<br/>[3.7.0 01/08/2019]\r<br/>Release 13\r<br/>- Waste toner detection correction for LEXMARK printers models TOSHIBA E-STUDIO389CS and TOSHIBA E-STUDIO479CS. - Counter detection correction for KYOCERA printer model TASKALFA 4053CI.\r<br/>\r<br/>[3.7.0 26/07/2019]\r<br/>Release 12\r<br/>- Detailed counter detection correction for CANON printers. - Waste toner detection correction for CANON printer model CANON IR-ADV C256 III 11.20. - Counter detection correction for EPSON Workforce series printers.\r<br/>\r<br/>[3.7.0 16/07/2019]\r<br/>Release 11\r<br/>- Detailed counter detection correction for CANON printers. - Counter detection correction for KYOCERA printers models 2507CI, 3207CI, 5007CI, D-COLOR MF2553 and TASKALFA 2553CI. - Waste toner level detection correction for SHARP printers SHARP MX-M264N and SHARP MX-M316N. - Detailed counter detection correction for TOSHIBA printer e-STUDIO2505AC. \r<br/>\r<br/>[3.7.0 04/07/2019]\r<br/>Release 10\r<br/>- Detailed counter detection correction for CANON printers. - Counter detection correction and Serial Number correction for CANON printer model CANON G6000 SERIES 1.010. - Detailed counter detection correction for LEXMARK model X950 printer.\r<br/>\r<br/>[3.7.0 03/07/2019]\r<br/>Release 9\r<br/>- Counter detection correction for RICOH printer model RICOH SP C262DNW. - Counter detection correction for CANON printers models CANON IR-ADV C3530 III 12.30 and CANON IR-ADV C5550 III 12.30. - Waste toner level detection correction for SHARP printers models SHARP MX-4140N and SHARP MX-2314N. - Detailed counter detection correction for KYOCERA printer model TASKALFA 356CI. - Correction of detailed counter detection for TOSHIBA printer model TOSHIBA e-STUDIO4505AC. - Counter detection correction for SAMSUNG printer model M4370LX.\r<br/>\r<br/>[3.7.0 20/06/2019]\r<br/>Release 8\r<br/>- Counter detection correction for CANON printers models CANON IR-ADV C3525 III 12.30, CANON LBP664C, CANON MF745C/746C and CANON WG7000 SERIES.  - Counter detection correction, Serial Number and Firmware Version for EPSON printer model EPSON ET-2710 SERIES. \r<br/>\r<br/>[3.7.0 11/06/2019]\r<br/>Release 7\r<br/>- Firmware detection and counter detection correction for EPSON printer model EPSON ET-2750 SERIES.  - Detail counter detection correction for KYOCERA printers models 2507CI, 3207CI and 3505CI.  - Counter detection correction for LEXMARK printer model C2240.  - Counter detection correction for OKI printer model ES8431.  - Waste toner level detection correction for SHARP printer model MX-5070V. \r<br/>\r<br/>[3.7.0 30/05/2019]\r<br/>Release 6\r<br/>- System name detection for RICOH printer model RICOH IM 350.  - Detail counter detection for LEXMARK printer model XC9245.  - Waste toner level detection correction for SHARP printer model SHARP MX-M565N. \r<br/>\r<br/>[3.7.0 24/05/2019]\r<br/>Release 5\r<br/>- Counter detection correction for LEXMARK printer model TOSHIBA E-STUDIO338CS.  - Counter detection for OKI printer model PRO9431. \r<br/>\r<br/>[3.7.0 21/05/2019]\r<br/>Release 4\r<br/>- Counter detection correction for RICOH printer model RICOH AFICIO CL4000HDN.  - Waste toner level detection correction for SHARP printer model SHARP MX-M464N.  - Photoconductor level detection correction for XEROX printers models XEROX ALTALINK C8035 MULTIFUNCTION PRINTER, XEROX ALTALINK C8045 MULTIFUNCTION PRINTER and XEROX ALTALINK C8055 MULTIFUNCTION PRINTER. \r<br/>\r<br/>[3.7.0 09/05/2019]\r<br/>Release 3\r<br/>- Correction firmware version detection for HP printer model HP LASERJET 400 M401DN.  - Correction counter detection for KYOCERA printer model TASKALFA 3253CI.  - Detailed counter detection correction for KYOCERA printer model XC9235.  - Counter detection correction for OKI printer model C531.  - Detailed counter detection for OKI printer model ES9431.  - Included among the maintenance kits of grey toner for RISO printer model COMCOLOR GD7330. \r<br/>\r<br/>[3.7.0 30/04/2019]\r<br/>Release 2\r<br/>- Correction of detailed counter detection for KYOCERA printer model TASKALFA 307CI. \r<br/>\r<br/>[3.7.0 29/04/2019]\r<br/>Release 1\r<br/>- Correction of detailed counter detection for CANON printers (added counter 473 and 475).  - Correction of detailed counter detection for KYOCERA printer model TASKALFA 307CI.  - Counter detection correction for KYOCERA printers models D-COLORMF2552 PLUS and TASKALFA 5053CI. \r<br/>\r<br/>[3.7.0 19/04/2019]\r<br/>- Counters and consumable levels correction for BROTHER printers detected by embedded explorer. \r<br/>\r<br/>[3.6.0 19/04/2019]\r<br/>Release 2\r<br/>- Detailed counter detection correction for HP printer model HP LASERJET 500 COLOR MFP M575.  - Photoconductor level detection correction for LEXMARK printer model E352DN.  - Firmware version detection correction for OKI printer model B432. \r<br/>\r<br/>[3.6.0 05/04/2019]\r<br/>Release 1\r<br/>- Counter detection correction for LEXMARK printer model MC2425ADW.  - Detailed counter detection correction for TOSHIBA printer model TOSHIBA e-STUDIO4505AC.  - Counter detection correction for RISO printers. \r<br/>\r<br/>[3.6.0 02/04/2019]\r<br/>- General bug fix for embedded printer detection.  - Bug fix explorer windows.  - Correction for RISO printer detection. \r<br/>\r<br/>[3.5.0 26/03/2019]\r<br/>Release 3\r<br/>- Counter detection correction for LEXMARK printers TOSHIBA E-STUDIO305CS and TOSHIBA E-STUDIO385S.  - Counter detection correction for RICOH printers RICOH model SP C261SFNW.  - Photoconductor detection correction for XEROX printers XEROX WORKCENTRE 7525. \r<br/>\r<br/>[3.5.0 15/03/2019]\r<br/>Release 2\r<br/>- Counter detection correction for EPSON printers models EPSON ET-16500 SERIES, EPSON ET-3750 SERIES and EPSON WF-4740 SERIES.  - Photoconductor detection correction for XEROX printer model XEROX ALTALINK C8070 MULTIFUNCTION PRINTER.  - Waste toner level detection correction for SHARP MX-2651 and SHARP MX-3061 printers. \r<br/>\r<br/>[3.5.0 08/03/2019]\r<br/>Release 1\r<br/>- Detailed counter detection correction for LEXMARK printers models X860DE, X950, X954, XC9235.  - Detailed counter detection correction for KYOCERA printer model P-C2480I MFP.  - System name detection correction for RICOH printers RICOH IM C2000 and RICOH IM C4500. \r<br/>\r<br/>[3.5.0 08/03/2019]\r<br/>- Support Latin-1 encoding for the correct display of Korean characters for the description of maintenance kit, system name and alert.  - Detection of printers brand RISO. \r<br/>\r<br/>[3.4.2 13/02/2019]\r<br/>- Correction of toner level detection, firmware version and serial number for EPSON printers.   - Correction of counter detection for EPSON printer model EPSON WF-C8690 SERIES.   - Correction of counter detection for RISO printer model COMCOLOR 7150.   - Colour detection and maintenance kit \r<br/>\r<br/>[3.4.0 07/02/2019]\r<br/>Release 2\r<br/>- Correction for serial detection, firmware and consumables for EPSON printers models EPSON WF-C529R SERIES, EPSON WF-C8610 SERIES and EPSON WF-M5298 SERIES.  - Waste Toner correction for HP printer model HP COLOR LASERJET MFP M680.  - Location Correction for HP printer model HP PAGEWIDE MFP P57750.  - Firmware version detection correction for KONICA printer models KONICA MINOLTA BIZHUB C454E and KONICA MINOLTA BIZHUB C458.  - Counter detection correction for KYOCERA printer model 3505CI(2L6-30).  - Counter detection correction for LEXMARK printers models TOSHIBA E-STUDIO306CS and TOSHIBA E-STUDIO388CP.  - Counter detection correction for LEXMARK printer model XM1140.  - Counter detection correction for OKI printer model ES7480 MFP.  - Counter detection correction for RICOH printers RICOH MP C2003 and RICOH SP C261DNW.  - Waste Toner detection correction for SHARP printer SHARP MX-3060V.  - Waste Toner detection correction for XEROX printer model XEROX VERSALINK C405 DN MULTIFUNCTION PRINTER. \r<br/>\r<br/>[3.4.0 30/11/2018]\r<br/>\"- Improved management of scanning flows and printer detection.  - Improved detailed counter detection of the \"\"Scan Counts by Size\"\" type for HP printers. \"\r<br/>\r<br/>[3.3.7 07/12/2018]\r<br/>- Correction of serial detection, firmware and consumable version for EPSON printer model EPSON WF-C579R SERIES.  - Correction of counter detection for KYOCERA 300CI and P-C printersC2480I MFP.  - Correction of counter detection for LEXMARK printer model XC2132.  - Correction of counter detection for RICOH printer model RICOH PRO C7200. \r<br/>\r<br/>[3.3.6 20/11/2018]\r<br/>- Improved management of scanning flows and printer detection.  - Extension of the overrides for printer detection. \r<br/>\r<br/>[3.3.5 07/11/2018]\r<br/>\"- Detailed counter detection of the \"\"Scan Counts By Size\"\" type for HP printers.  - Improved scan flow management and printer detection. \"\r<br/>\r<br/>[3.3.4 06/11/2018]\r<br/>Release 4\r<br/>\"- Correction of serial detection, firmware and consumables for EPSON printers models EPSON WF-M5299 SERIES and EPSON WF-M5799 SERIES.  - Detailed counter detection of the \"\"Scan Counts By Destination\"\" type for HP printers. \"\r<br/>\r<br/>[3.3.4 18/10/2018]\r<br/>Release 3\r<br/>- Correction of detailed counter detection for CANON printers. \r<br/>\r<br/>[3.3.4 16/10/2018]\r<br/>Release 2\r<br/>- Correction of serial detection, firmware version, counters and consumables for EPSON printer model EPSON WF-C17590 SERIES.  - Correction of maintenance detection kit for OKI printer model MFX-C3400. \r<br/>\r<br/>[3.3.4 05/10/2018]\r<br/>Release 1\r<br/>- Correction of counter detection for CANON printer model CANON IPR C650-G200 1.0.  - Correction of detailed counter detection for CANON printer model CANON IPR C850-G200 1.0.  - A3 counter detection correction for EPSON printers models EPSON WF-8510 SERIES, EPSON WF-8590 SERIES and EPSON WF-C20590 SERIES.  - Detailed counter detection correction for KYOCERA printers models 300CI, 3206CI, P-C3065 MFP and 4006CI.  - Counter detection correction for OKI printer model ES8453 MFP.  - Counter detection correction for TOSHIBA printer model TOSHIBA E-STUDIO3508LP_LOOPS-LP35.  - Counter detection correction for LEXMARK printer model C950. \r<br/>\r<br/>[3.3.4 04/10/2018]\r<br/>- Correction of field detection POSITION. \r<br/>\r<br/>[3.3.3 04/10/2018]\r<br/>- RICOH printer toner level detection correction.  - POSITION field detection correction. \r<br/>\r<br/>[3.3.2 07/09/2018]\r<br/>Release 9\r<br/>- Detailed counter detection correction for CANON printers.  - Detailed counter detection correction for KYOCERA printers models 3005CI, 350CI, 355CI, 356CI, 5006CI and 6006CI.  - Counter detection correction for KYOCERA printer model TASKALFA 5002I. \r<br/>\r<br/>[3.3.2 03/08/2018]\r<br/>Release 8\r<br/>- Correction of detailed counter detection for KYOCERA printers models 2506CI and 261CI. \r<br/>\r<br/>[3.3.2 02/08/2018]\r<br/>Release 7\r<br/>- Counter detection correction for EPSON printer model EPSON WF-C5290 SERIES.  - Detailed counter detection correction for KYOCERA printers models 2500CI and 261CI.  - Counters correction for KYOCERA printer model TASKALFA 6052CI.  - Detailed counter detection correction for TOSHIBA printer model TOSHIBA E-STUDIO2000AC.  - Maintenance detection correction kit for XEROX printer model XEROX PHASER 6600DN. \r<br/>\r<br/>[3.3.2 23/07/2018]\r<br/>Release 6\r<br/>\"- Serial Number detection correction, firmware version and levels Toner for EPSON printer model EPSON WF-C5290 SERIES  - Waste Toner detection correction for HP models HP COLOR LASERJET MFP E77822, HP COLOR LASERJET MFP E77825, HP COLOR LASERJET MFP E77830, HP COLOR LASERJET MFP E87640, HP COLOR LASERJET MFP E87650, HP LASERJET MFP E72525,    HP LASERJET MFP E72530 and HP LASERJET MFP E72535  - Serial Number detection correction for HP printer model HP DESIGNJET Z6 24IN (24\"\" SIZED)  - Correction of retail counter detection for TOSHIBA printers models TOSHIBA e-STUDIO2505AC, TOSHIBA E-STUDIO256, TOSHIBA E-STUDIO3505AC, TOSHIBA E-STUDIO3508LP_LOOPS-LP35, TOSHIBA e-STUDIO4505AC and TOSHIBASTUDIO5506AC.  - Waste Toner and Maintenance Correction Kit for XEROX printer model XEROX VERSALINK C405  - Maintenance Correction Detection Kit and Photoconductors for XEROX printer model XEROX WORKCENTRE 4260. \"\r<br/>\r<br/>[3.3.2 18/07/2018]\r<br/>Release 5\r<br/>- Correction of detection of detailed counters for CANON printers models CANON IPR C650 38.02 and CANON IR-ADV C3520 32.10.  - Serial Number detection correction for EPSON printers EPSON models SC-T3200 SERIES and EPSON WP-4595 SERIES  - Firmware detection correction for EPSON printers EPSON models SC-T3200 SERIES and EPSON WF-R8590 SERIES.  - Waste Toner detection correction for HP printer model HP COLOR LASERJET MFP E87660.  - Serial Number detection correction for HP printer model HP DESIGNJET T520 24IN.  - Serial Number and Total Counter detection correction for HP printer model HP LASERJET PRO MFP M125NW.  - Counter detection correction for KYOCERA printers models 6006CI, 6056I and P-4035I MFP.  - Counter detection correction for LEXMARK printers models C4150, CX417DE and PANTUM CM7000 SERIES.  - Counter detection correction for OKI printer model ES9431.  - System name detection correction for RICOH printers models INFOTEC MP C2800, RICOH MP C3003 and RICOH SP 4510SF.  - Counter detection correction for RICOH printer model RICOH SP C262SFNW.  - Maintenance detection correction kit for XEROX printer model XEROX VERSALINK C400. \r<br/>\r<br/>[3.3.2 06/06/2018]\r<br/>Release 4\r<br/>- Waste Toner correction for HP printer model HP LASERJET 700 COLOR MFP M775. \r<br/>\r<br/>[3.3.2 05/06/2018]\r<br/>Release 3\r<br/>- Correction of toner level detection for BROTHER printer model BROTHER MFC-L6900DW SERIES.  - Implementation of detailed counters for CANON printer model CANON IPR C650 36.01.  - Maintenance kit level detection correction for HP, LEXMARK and PANASONIC printers.  - Implementation of detailed counters for HP printer model HP OFFICEJET PRO X451DW PRINTER.  - Location Correction for HP models HP PAGEWIDE MANAGED MFP P77740DN and HP PAGEWIDE MANAGED MFP P77740Z.  - Counter detection correction for KYOCERA printer model D-COLOR MF2553.  - Counter detection correction for LEXMARK printers models XC4150 and XC9245.  - Detailed counter detection correction for SAMSUNG printers.  - Counter detection correction for SAMSUNG printer model X7500LX  - Waste Toner correction for SHARP MX-M354N printer. \r<br/>\r<br/>[3.3.2 15/05/2018]\r<br/>Release 2\r<br/>- Counter detection correction for CANON printer model IR-ADV C356.  - System name detection correction for EPSON printer model AL-M300.  - Counter detection correction A3 for EPSON printer model EPSON WF-C869R SERIES.  - Consumable level detection correction for HP printer model HP COLOR LASERJET MFP E87660.  - Firmware version detection correction for KONICA printer model KONICA MINOLTA BIZHUB C659.  - Customized firmware version detection for SAMSUNG printers (detailed counter section).  - Consumable level correction for XEROX printer model XEROX VERSALINK C405 DN MULTIFUNCTION PRINTER. \r<br/>\r<br/>[3.3.2 24/04/2018]\r<br/>Release 1\r<br/>- Counter detection correction for SAMSUNG printers SAMSUNG X7600 SERIES, X4220RX, X4250LX and X4300LX.  - Counter detection correction for KYOCERA printer TASKALFA 6052CI. \r<br/>\r<br/>[3.3.2 24/04/2018]\r<br/>- Counter detection for SAMSUNG printers. \r<br/>\r<br/>[3.3.1 16/04/2018]\r<br/>Release 4\r<br/>- Correction of detection of detailed counters for CANON printers models CANON IPR C650-G100 1.21 and CANON IR-ADV C5535 32.10.  - Serial Number detection correction, firmware and toner levels for EPSON printers EPSON models ET-3750 SERIES and EPSON WF-4740 SERIES.  - Serial Number detection correction, firmware version, toner levels and counters for EPSON printers EPSON models WF-C5710 SERIES and EPSON WF-C5790 SERIES.  - Photoconductor level detection correction for XEROX printer model XEROX WORKCENTRE 7970. \r<br/>\r<br/>[3.3.1 19/03/2018]\r<br/>Release 3\r<br/>- Counter detection correction for OKI printer model ES9476 MFP.  - System Name detection correction for Ricoh printer models RICOH AFICIO MP 5002, RICOH MP C5503 and RICOH MP C6502.  - Counter detection correction for Samsung printer model X4250LX. \r<br/>\r<br/>[3.3.1 06/03/2018]\r<br/>Release 2\r<br/>- Maintenance Detection Correction Kit for HP printer model HP COLOR LASERJET MFP E77830.  - Counter detection correction for OKI printers models ES7170 MFP, ES9465 MFP and MC563.  - Counter detection correction and firmware version for Samsung printers models X4220RX, X4250LX and X7500LX. \r<br/>\r<br/>[3.3.1 20/02/2018]\r<br/>Release 1\r<br/>- Total and detailed counter detection correction for TOSHIBA printers models TOSHIBA E-STUDIO3508LP_LOOPS-LP35 and TOSHIBA E-STUDIO4508LP_LOOPS-LP45. \r<br/>\r<br/>[3.3.1 20/02/2018]\r<br/>- Blue toner detection as second black toner for TOSHIBA printers. \r<br/>\r<br/>[3.3.0 09/02/2018]\r<br/>Release 2\r<br/>- Waste toner detection for HP printer model HP COLOR LASERJET MFP E77830  - Modified detailed counter detection for HP printer models HP LASERJET 500 COLORMFP M570DW and HP OFFICEJET PRO X576DW MFP. \r<br/>\r<br/>[3.3.0 06/02/2018]\r<br/>Release 1\r<br/>- Counter detection correction for LEXMARK printers models XC2130, XC4140, XC8160, XM5163 and XC9235.  - Counter detection correction for OKI printer model C301. \r<br/>\r<br/>[3.3.0 29/01/2018]\r<br/>- Bug fixing and optimisation of data collection procedures. \r<br/>\r<br/>[3.2.7 26/01/2018]\r<br/>Release 4\r<br/>- Correction for serial number detection for CANON printer model CANON TR7500 SERIES 1.030.  - Correction for serial number detection, color counter and toner levels for EPSON printer model EPSON ET-4750 SERIES.  - Correction for serial number detection, color counter and firmware version for EPSON printer model EPSON WF-4720 SERIES.  - Total counter detection correction for HP printer model HP LASERJET 4200.  - Firmware version detection correction for HP printer model HP LASERJET P2055DN.  - Total counter detection correction for KYOCERA printer model D-COLOR MF2501.  - Color counter detection correction for LEXMARK printer model CX410E.  - Waste Toner correction for SHARP MX-6070N printer. \r<br/>\r<br/>[3.2.7 12/01/2018]\r<br/>Release 3\r<br/>- Color counter detection correction for CANON printer model LBP653C/654C.  - Color counter detection correction for CANON printer model IR-ADV C5255.  - Color counter detection correction for EPSON printer model EPSON WF-3720 SERIES.  - Detail counter detection correction for HP printers.  - Total counter detection correction for HP LASERJET 4200 printer.  - A3 counter detection correction for KYOCERA printers models D-COPY 4500MF PLUS and D-COPY 8000MF.  - Waste Toner level detection correction for SHARP MX-2630N and SHARP MX-4060N printers. \r<br/>\r<br/>[3.2.7 13/12/2017]\r<br/>Release 2\r<br/>- Color counter detection correction for CANON printer model LBP653C/654C  - Detailed counter detection correction for KONICA printers. \r<br/>\r<br/>[3.2.7 09/11/2017]\r<br/>Release 1\r<br/>- Color counter detection correction for CANON printers.  - POSITION and CONTACT field detection correction for HP printer model HP PAGEWIDE PRO 477DW MFP. \r<br/>\r<br/>[3.2.7 09/11/2017]\r<br/>- Counter detection correction for CANON printers. \r<br/>\r<br/>[3.2.6 24/10/2017]\r<br/>Release 3\r<br/>- Correction of serial detection and firmware version for EPSON printer model EPSON AL-M400.  - Correction of serial detection, firmware version, counters and toner levels and maintenance kit for EPSON printer model EPSON WF-C20590 SERIES.  - KONICA Detailed Counter Extension with Infinity entries 2/3/4/5 added. \r<br/>\r<br/>[3.2.6 23/10/2017]\r<br/>Release 2\r<br/>- Counter detection correction for KYOCERA printers models P-C3560DN and TASKALFA 6551CI.  - Counter detection correction for RICOH printers NRG MP C2500 and RICOH SP C340DN. \r<br/>\r<br/>[3.2.6 09/10/2017]\r<br/>Release 1\r<br/>- Counter detection correction for EPSON printer model EPSON WF-C869R SERIES.  - Detailed counter implementation for LEXMARK printer model MS811.  - Maintenance detection correction Kit for OKI printer model ES7120.  - Counter detection correction for RICOH printer RICOH model AFICIOSG7100DN.  - Counter detection correction for RICOH printer RICOH model SP C352DN. \r<br/>\r<br/>[3.2.6 26/09/2017]\r<br/>- Correction of printer exclusion parameter operation from scanning. \r<br/>\r<br/>[3.2.5 07/09/2017]\r<br/>- Serial number detection correction with dirty character management.  - Introduction of the Godaddy safety certificate. \r<br/>\r<br/>[3.2.4 04/09/2017]\r<br/>Release 3\r<br/>\"- Correction of counter detection for CANON printer model MF732C/734C/735C.  - Serial Number detection correction for EPSON printers models EPSON WF-C869R SERIES and EPSON WF-M5690 SERIES.  - Added \"\"Hardware cycles\"\" retail counter for HP vendor.  - A4 and A3 counter detection correction for KYOCERA printer model 6505CI.  - Counter correction for OKI printer model MC853. \"\r<br/>\r<br/>[3.2.4 27/07/2017]\r<br/>Release 2\r<br/>- Counter detection correction for CANON printers models LBP653C/654C, MF632C/634C and MF732C/734C/735C.  - A3 counter detection correction for LEXMARK printer model MX511DE.  - Waste Toner level detection correction for SHARP printer models MX-2310U, SHARP MX-2640N, SHARP MX-3050N, SHARP MX-3060N, SHARP MX-3140N, SHARP MX-5141N, SHARP MX-C250F, SHARP MX-C301W, SHARP MX-M1054 and SHARP MX-M264N.  - Counter detection correction for KYOCERA printer model ECOSYS P7035CDN.  - Counter detection correction for OKI printer model ES5463 MFP. \r<br/>\r<br/>[3.2.4 29/06/2017]\r<br/>Release 1\r<br/>- Serial Number detection correction and firmware version for EPSON printer model EPSON WF-M5190 SERIES. \r<br/>\r<br/>[3.2.4 19/06/2017]\r<br/>- Model detection correction for XEROX printers. \r<br/>\r<br/>[3.2.3 18/05/2017]\r<br/>Release 2\r<br/>- Maintenance level detection correction kit for OKI printer model MC861. \r<br/>\r<br/>[3.2.3 18/05/2017]\r<br/>Release 1\r<br/>- Counter detection correction for SHARP printers.  - Firmware detection for SHARP printers.  - Detailed counter detection correction for HP models HP LASERJET 600 M602 and HP LASERJET P3005. \r<br/>\r<br/>[3.2.3 09/05/2017]\r<br/>- Correction of detection of POSITION, CONTACT and SERIAL NUMBER fields for the importer. \r<br/>\r<br/>[3.2.2 09/05/2017]\r<br/>Release 4\r<br/>- Maintenance level detection correction kit for OKI printer model MC861.  - Counter detection correction for RICOH printer model RICOH MP C3003.  - Firmware version detection correction for SAMSUNG printer model K7500LX.  - Counter detection correction for SHARP printers. \r<br/>\r<br/>[3.2.2 31/03/2017]\r<br/>Release 3\r<br/>- Correction for serial number detection and firmware version for EPSON printer model EPSON ET-16500 SERIES.  - Correction for detailed counter detection for HP printer model HP COLOR LASERJET CM6030 MFP.  - Detailed counter detection correction for HP printer model HP LASERJET M9040 MFP.  - Counter detection correction for KYOCERA printer model TASKALFA 3252CI.  - Toner level correction for OKI printer model ES7120.  - Firmware level correction for XEROX printer model XEROX PHASER 6500N. \r<br/>\r<br/>[3.2.2 31/03/2017]\r<br/>Release 2\r<br/>- Color counter detection correction for SHARP printers.  - Detailed counter implementation for HP printers models HP COLOR LASERJET MFP M476DW and HP LASERJET M9040 MFP. \r<br/>\r<br/>[3.2.2 24/03/2017]\r<br/>Release 1\r<br/>- Counter detection correction for SHARP MX-* series printers. \r<br/>\r<br/>[3.2.2 23/03/2017]\r<br/>- Change in the detection of colour meters. \r<br/>\r<br/>[3.2.1 21/03/2017]\r<br/>Release 1\r<br/>- Correction of detailed counter detection for KYOCERA printer model TASKALFA 306CI.  - Counter detection correction for SHARP printer model SHARP MX-C300P.  - Detailed counter detection correction for TOSHIBA printer model TOSHIBA E-STUDIO2550C. \r<br/>\r<br/>[3.2.1 21/03/2017]\r<br/>- Support proxy parameters for the JAMC application. \r<br/>\r<br/>[3.2.0 02/03/2017]\r<br/>Release 9\r<br/>- Counter detection correction for SHARP printer model SHARP MX-C300W.  - Counter detection correction for OKI printer model MC861.  - Counter detection correction for LEXMARK printer model C4150.  - Counter detection correction for KYOCERA 5006CI printer. \r<br/>\r<br/>[3.2.0 02/03/2017]\r<br/>Release 8\r<br/>- Counter detection correction for SHARP MX-*.  series printers - Photoconductor detection correction for OKI printer model ES7170 MFP. \r<br/>\r<br/>[3.2.0 01/03/2017]\r<br/>Release 7\r<br/>- Counter detection correction for OKI printers model ES5473 MFP. \r<br/>\r<br/>[3.2.0 01/03/2017]\r<br/>Release 6\r<br/>- Counter detection correction for KYOCERA printers model TASKALFA 5052CI. \r<br/>\r<br/>[3.2.0 27/02/2017]\r<br/>Release 5\r<br/>- Counter detection correction for SHARP printers SHARP MX-3060N and SHARP MX-C311.  - Counter detection correction for KYOCERA printers TASKALFA 5052CI. \r<br/>\r<br/>[3.2.0 10/02/2017]\r<br/>Release 4\r<br/>- Maintenance kit correction for BROTHER printer model HL-S7000DN SERIES. \r<br/>\r<br/>[3.2.0 20/01/2017]\r<br/>Release 3\r<br/>- Correction of photoconductor level detection for OKI printer model ES7470 MFP.  - Correction of detail counter detection and removal of incorrect A3 counter detection for KONICA printer model KONICA MINOLTA BIZHUB C25. \r<br/>\r<br/>[3.2.0 18/01/2017]\r<br/>Release 2\r<br/>- Counter detection correction for LEXMARK printer model XC4140.  - Photoconductor level detection correction, firmware version and counters for OKI printer model ES9475 MFP. \r<br/>\r<br/>[3.2.0 09/01/2017]\r<br/>Release 1\r<br/>- Photoconductor level detection correction for OKI Model MC760, MC770 and MC780.  - Meter detection correction, firmware version and waste toner level for OKI printer model ES9455 MFP. \r<br/>\r<br/>[3.2.0 23/12/2016]\r<br/>- Correction of photoconductor levels and toner detection for TOSHIBA printers with OKI remaster. \r<br/>\r<br/>[3.1.34 23/12/2016]\r<br/>Release 5\r<br/>- Counter detection correction for CANON printer model MF620C SERIES.  - Maintenance kit and photoconductor level detection correction, firmware version and counters for OKI printer model ES7470 MFP.  - Introduction of HIGH and MIDDLE detail counter detection for all TOSHIBA printers. \r<br/>\r<br/>[3.1.34 16/12/2016]\r<br/>Release 4\r<br/>- Maintenance kit for OKI printers ES7170 MFP, ES7460 MFP, MB760, MB770, MC760, MC770, MC780 - Firmware correction for SAMSUNG printer M332X 382X 402X SERIES. \r<br/>\r<br/>[3.1.34 15/12/2016]\r<br/>Release 3\r<br/>- Counter detection correction, waste toner level, photoconductors and firmware version for OKI printers models ES7170 MFP, ES7460 MFP, MB760, MB770, MC760, MC770, MC780. \r<br/>\r<br/>[3.1.34 13/12/2016]\r<br/>Release 2\r<br/>- Firmware and A3 counter detection correction for OKI printer model ES7170 MFP. \r<br/>\r<br/>[3.1.34 13/12/2016]\r<br/>Release 1\r<br/>- Counter detection correction for CANON printer model MF8000C SERIES.  - Maintenance detection kit for OKI printer model ES7170 MFP. \r<br/>\r<br/>[3.1.34 13/12/2016]\r<br/>- Drum level detection, fuser and belt detection for TOSHIBA printers remastered OKI.  - Support for printer detection, via SNMP protocol, on port other than the default 161. \r<br/>\r<br/>[3.1.33 17/11/2016]\r<br/>Release 1\r<br/>- System name detection for RICOH printers RICOH MP 305+, RICOH MP 402SPF, RICOH MP 501, RICOH MP 601, RICOH SP 5300, RICOH SP 5310 and RICOH SP C342DN.  - Firmware version detection for SAMSUNG printer model SAMSUNG ML-451X 501X SERIES. \r<br/>\r<br/>[3.1.33 02/11/2016]\r<br/>- Detailed counter detection correction for HP printer model HP PAGEWIDE COLOR MFP E58650.  - Optimization of some software features. \r<br/>\r<br/>[3.1.32 27/10/2016]\r<br/>Release 4\r<br/>- Firmware version detection correction for KONICA printer model KONICA MINOLTA BIZHUB 958.  - Counter detection kit for KYOCERA printers models 2506CI, 4006CI, 6006CI, TASKALFA 2552CI and TASKALFA 4052CI. \r<br/>\r<br/>[3.1.32 14/10/2016]\r<br/>Release 3\r<br/>- Counter detection correction for SAMSUNG printer model ML-3470 SERIES. \r<br/>\r<br/>[3.1.32 14/10/2016]\r<br/>Release 2\r<br/>- Counter correction for CANON printer model IR-ADV 6055. \r<br/>\r<br/>[3.1.32 14/10/2016]\r<br/>Release 1\r<br/>- Counters correction for KYOCERA printer model 3206CI.  - Firmware version detection for HP printer model HP LASERJET 400 COLOR M451NW.  - Firmware and system name detection for KONICA printers models KONICA MINOLTA BIZHUB 958 and KONICA MINOLTA BIZHUB C658.  - Firmware version detection for SAMSUNG printer model X7600LX.  - Black toner level detection correction for SAMSUNG printer model ML-3470 SERIES.  - Counter detection correction for XEROX printer model FUJI XEROX DOCUPRINT CM315/318 Z. \r<br/>\r<br/>[3.1.32 28/09/2016]\r<br/>- Amendment to the exclusions procedure. \r<br/>\r<br/>[3.1.31 26/09/2016]\r<br/>- Changes to the exclusions procedure, with the insertion of MAC address and serial variables, in addition to the IP address. \r<br/>\r<br/>[3.1.30 09/09/2016]\r<br/>Release 1\r<br/>- Photoconductor level detection correction for XEROX printers models XEROX WORKCENTRE 6655 and XEROX WORKCENTRE 7556. \r<br/>\r<br/>[3.1.30 01/09/2016]\r<br/>- Improvement of SAMSUNG printer detection process.  - XEROX printer toner level detection correction.  - Firmware version detection for XEROX printers. \r<br/>\r<br/>[3.1.29 31/08/2016]\r<br/>Release 5\r<br/>- Counter detection correction for CANON printer model MF720C SERIES.  - Photoconductor level detection correction for XEROX printer model XEROX WORKCENTRE 7545. \r<br/>\r<br/>[3.1.29 05/08/2016]\r<br/>Release 4\r<br/>- Counter correction for CANON printer model IR-ADV C5255. \r<br/>\r<br/>[3.1.29 25/07/2016]\r<br/>Release 3\r<br/>\"- Counter detection correction for OKI printers models ES9431 and MC332.  - Counter detection correction for EPSON printers model EPSON ET-4550 SERIES.  - Detail counter detection called \"\"Inf. 1\"\" for KONICA printers. \"\r<br/>\r<br/>[3.1.29 12/07/2016]\r<br/>Release 2\r<br/>- Counter detection correction for KYOCERA printers ECOSYS M6035CIDN and ECOSYS M6526CDN.  - Photoconductor level detection correction for XEROX printer XEROX WORKCENTRE 7530. \r<br/>\r<br/>[3.1.29 07/07/2016]\r<br/>Release 1\r<br/>- Correction of toner and photoconductor level detection for PANASONIC printer model PANASONIC DP-MB311. \r<br/>\r<br/>[3.1.29 28/06/2016]\r<br/>\"- HP printer recognition with mac address starting with \"\"64-EB-8C\"\", \"\"AC-18-26\"\", \"\"A4-EE-57\"\", \"\"B0-E8-92\"\", \"\"58-20-B1\"\", \"\"B0-5A-DA\"\", \"\"84-34-97\"\", \"\"FC-3F-DB\"\", \"\"10-60-4B\"\", \"\"48-0F-CF\"\" and \"\"30-8D-99\"\".  - CANON printer recognition with mac address starting with \"\"D8-49-2F\"\", \"\"F4-81-39\"\", \"\"18-0C-AC\"\" and \"\"F8-0D-60\"\".  - Detailed counter detection correction for HP Futuresmart printers. \"\r<br/>\r<br/>[3.1.28 27/06/2016]\r<br/>Release 2\r<br/>- Counter detection correction for CANON printers models IR-ADV C250, IR-ADV C3325, MF8200C SERIES and CANON MF820. \r<br/>\r<br/>[3.1.28 20/06/2016]\r<br/>Release 1\r<br/>- Counter detection correction for LEXMARK printers models CX820, CX825 and CX860.  - Firmware detection for SAMSUNG printer model M4080FX. \r<br/>\r<br/>[3.1.28 17/06/2016]\r<br/>\"- Recognize new HP printers with mac addresses starting with \"\"DC-4A-3E\"\" and \"\"50-65-F3\"\".  - Change to the alternative method for detecting the printer\u00e2\u20ac\u2122s MAC Address. \"\r<br/>\r<br/>[3.1.27 14/06/2016]\r<br/>\"- Change to method for reading subnet mask value from printer MIB.  - Alternative method implementation for printer MAC Address detection.  - Recognize new HP printers with mac address starting with \"\"HP 94-57-A5\"\".  \"\r<br/>\r<br/>[3.1.26 14/06/2016]\r<br/>Release 3\r<br/>- A3 counter detection correction for LEXMARK printer model X748.  - Counter detection correction for CANON printers models CANON IR-ADV C250 31.03, CANON IR-ADV C3325 05.04 and IR-ADV C5255.  - Counter detection correction for EPSON printer model AL-ADVC500.  - Photoconductor level detection correction for XEROX printer model XEROX WORKCENTRE 7830.  - Detection of firmware versions for XEROX printer model XEROX WORKCENTRE 7845.  - Correction of detailed counter detection for KYOCERA printers. \r<br/>\r<br/>[3.1.26 08/06/2016]\r<br/>Release 2\r<br/>- Counter detection correction and firmware version for SAMSUNG printer model K7600LX.  - Counter detection correction for OKI printer model MC873.  - Counter detection correction and system name for RICOH printer model SP C435DN.  - Counter detection correction for LEXMARK printers models CS725, CX725 and CS820.  - Counter detection correction for CANON printer model IR-ADV 6055.  - Counter detection correction for HP printer model HP OFFICEJET PRO 8720.  - Counter detection correction for KYOCERA printer model ECOSYS M6035CIDN.  - Serial number detection correction for HP printer model HP COLOR LASERJET PRO MFP M177FW.  - Firmware version detection correction for XEROX COLOR MFP models H825CDW and DELL COLOR MFP S2825CDN.  - Meter and toner level detection for QISDA EVOJET OFFICE and C6010 PRINTER models. \r<br/>\r<br/>[3.1.26 27/05/2016]\r<br/>Release 1\r<br/>- Firmware version detection for EPSON printer model WF-6590 SERIES.  - Machine serial number detection correction and firmware version for EPSON printer model WF-8590 SERIES.  - Machine name detection correction for TOSHIBA printer model E-STUDIO407CS.  - Counter detection correction, machine serial number and firmware version for EPSON printer model WF-6090 SERIES.  - A3 counter detection elimination for LEXMARK printer model XM3150.  - Counter detection correction for SAMSUNG printer model K7600LX. \r<br/>\r<br/>[3.1.26 18/05/2016]\r<br/>- Elimination of A3 colour counter detection for monochrome printers.  - Toner level detection correction for some KYOCERA printer models.  - Toner level detection correction for some BROTHER printer models.  - Correction of printer code detection and alert messages. \r<br/>\r<br/>[3.1.25 18/05/2016]\r<br/>Release 2\r<br/>- Counter detection correction for KYOCERA printer model P-C3060DN.  - Firmware detection for KONICA printer model GENERIC 30C-9. \r<br/>\r<br/>[3.1.25 10/05/2016]\r<br/>Release 1\r<br/>- Counter detection correction for OKI printer model MC342.  - Error detection correction for A3 color counter for LEXMARK printer model XM1145.  - Black toner level detection correction for SAMSUNG printer model ML-4550 SERIES. \r<br/>\r<br/>[3.1.25 05/05/2016]\r<br/>- Correction of toner level detection with duplicate value when absent on SNMP protocol.  - Introduction method for data management in different formats. \r<br/>\r<br/>[3.1.24 04/05/2016]\r<br/>Release 1\r<br/>- Photoconductor level correction for XEROX printer model XEROX WORKCENTRE 7845.  - Counters correction for EPSON printer model EPSON WF-6590 SERIES. \r<br/>\r<br/>[3.1.24 12/04/2016]\r<br/>- Waste Toner Detection Correction for some HP models.  - MAC Address Detection Correction for CANON Canon MF720C Series printer. \r<br/>\r<br/>[3.1.23 11/04/2016]\r<br/>Release 4\r<br/>- Mono and color counter detection correction for OKI printer model C610.  - Waste toner box level detection correction for SHARP printer model SHARP MX-3114N. \r<br/>\r<br/>[3.1.23 07/04/2016]\r<br/>Release 3\r<br/>- Correction of mono counter detection, color and implementation of detail counter for CANON printer model MF720C CANON SERIES. \r<br/>\r<br/>[3.1.23 01/04/2016]\r<br/>Release 2\r<br/>- Color print detection correction for EPSON printer model EPSON WF-8590 SERIES.  - Meter detection correction for OKI printer model ES5430.  - Level detection correction maintenance kit for HP printer models HP COLOR LASERJET CP3525, HP COLOR LASERJET FLOW MFP M880, HP LASERJET 600 M602 and HP LASERJET 700 COLOR MFP M775. \r<br/>\r<br/>[3.1.23 17/03/2016]\r<br/>Release 1\r<br/>- Maintenance kit detection correction for EPSON Workforce series printers.  - English translation of the description of detailed counters for CANON, HP, EPSON, KONICA, KYOCERA, SAMSUNG printers. \r<br/>\r<br/>[3.1.23 17/03/2016]\r<br/>- Maintenance kit detection correction for EPSON Workforce series printers. \r<br/>\r<br/>[3.1.22 10/03/2016]\r<br/>Release 3\r<br/>- Mono and colour counter detection correction for RICOH printer model RICOH SP C250SF.  - Photoconductor, mono and colour counter detection for XEROX printer model XEROX WORKCENTRE PRO C2128.  - Detail counter detection correction for KYOCERA printers. \r<br/>\r<br/>[3.1.22 03/03/2016]\r<br/>Release 2\r<br/>- Implementation of detail counters for HP models LASERJET 500 COLORMFP M570DN and HP LASERJET 500 COLORMFP M570DW. \r<br/>\r<br/>[3.1.22 26/02/2016]\r<br/>Release 1\r<br/>- Firmware Engine detection correction for Kyocera printers.  - Firmware detection for Brother printers.  - Toner level detection correction for Samsung printer model CLP-770 SERIES. \r<br/>\r<br/>[3.1.22 26/02/2016]\r<br/>- Fix toner level detection, maintenance kit and photoconductors for Brother printers.  - Bug fix overrides management.  - Correction of xml file generation for detail meters. \r<br/>\r<br/>[3.1.21 17/02/2016]\r<br/>Release 1\r<br/>- Firmware version detection correction for KONICA printer model GENERIC 45C-6E.  - Maintenance kit level detection correction for XEROX printer model XEROX WORKCENTRE 7120. \r<br/>\r<br/>[3.1.21 03/02/2016]\r<br/>\"- Recognize new HP printers with mac addresses starting with \"\"A0-2B-B8\"\" and \"\"34-64-A9\"\".  - Elimination of detection of printers starting with mac address \"\"00-80-92\"\" so far associated with the OKI brand.  - Limitation of the description length on the occasion of multiple black toners. \"\r<br/>\r<br/>[3.1.20 02/02/2016]\r<br/>Release 4\r<br/>- Color printing counter correction for EPSON printers models EPSON WF-5690 SERIES and EPSON WP-4595 SERIES.  - Monochrome and color printing counter correction for RICOH printer model RICOH SP C440DN.  - Color printing counter correction for OKI model MC362. \r<br/>\r<br/>[3.1.20 25/01/2016]\r<br/>Release 3\r<br/>- Field detection correction serial number for EPSON printer model EPSON WF-5690 SERIES.  - Level detection correction maintenance kit for OKI printer model ES8451 MFP.  - Level detection correction for OKI printer model ES8473 MFP. \r<br/>\r<br/>[3.1.20 19/01/2016]\r<br/>Release 2\r<br/>- Support for firmware engine detection for Kyocera printers. \r<br/>\r<br/>[3.1.20 19/01/2016]\r<br/>Release 1\r<br/>- Support for firmware engine detection for Kyocera printers.  - Color counter detection correction for CANON printers. \r<br/>\r<br/>[3.1.20 19/01/2016]\r<br/>\"- Correction of consumable level detection and counters for HP printer models OFFICEJET PRO 8100 N811A and OFFICEJET PRO 8600 N911G.  - Correction of color counter detection for CANON printers.  - Black toner level correction for SAMSUNG printer model ML-4550ND.  - Correction detection field \"\"POSITION\"\" in the presence of the character \"\"|\"\" in its description.  - Recognize new HP printers with mac addresses starting with \"\"C4-34-6B\"\", \"\"D4-C9-EF\"\" and \"\"88-51-FB\"\".  \"\r<br/>\r<br/>[3.1.19 12/01/2016]\r<br/>Release 4\r<br/>\"- \"\"System Name\"\" and \"\"Firmware\"\" field detection correction for KONICA printers models KONICA MINOLTA BIZHUB 224E and KONICA MINOLTA BIZHUB C308.  - Photoconductor level detection correction for PANASONIC printer model PANASONIC DP-MB310. \"\r<br/>\r<br/>[3.1.19 30/12/2015]\r<br/>Release 3\r<br/>- Counters correction for KONICA printer model KONICA MINOLTA 350. \r<br/>\r<br/>[3.1.19 29/12/2015]\r<br/>Release 2\r<br/>- Correction of the detection of serial numbers and counters for XEROX printer model XEROX DOCUCOLOR 1632.  - Implementation of detailed counters for EPSON Workforce models. \r<br/>\r<br/>[3.1.19 21/12/2015]\r<br/>Release 1\r<br/>- A3 counter detection (both monochrome and color) for EPSON printer model WF-R8590.  - Detailed counter implementation for EPSON printer model WF-R8590. \r<br/>\r<br/>[3.1.19 21/12/2015]\r<br/>- A3 counter detection correction for EPSON printer model WF-R8590. \r<br/>\r<br/>[3.1.18 17/12/2015]\r<br/>Release 2\r<br/>- Correction of toner level detection for PANASONIC printer model PANASONIC DP-MB310. \r<br/>\r<br/>[3.1.18 15/12/2015]\r<br/>Release 1\r<br/>- Correction of toner detection for OKI printer model BROTHER MFC-8950DW. \r<br/>\r<br/>[3.1.18 10/12/2015]\r<br/>- Toner detection correction for KYOCERA colour printers.  - Tonert detection correction for CANON printer model IR-ADV C2020I. \r<br/>\r<br/>[3.1.17 09/12/2015]\r<br/>Release 2\r<br/>- Correction of toner detection for OKI printer model BROTHER MFC-8950DW. \r<br/>\r<br/>[3.1.17 01/12/2015]\r<br/>Release 1\r<br/>- Correction of b/w counters and color for CANON printer model CANON MF8500C SERIES.  - Correction of maintenance kit levels ADF Roller, ADF Retard Pad for SAMSUNG printer model M4580FX with firmware version equal to or greater than V11.01.08.04_05-16-2015.  - Correction of mono and colour print detection for KYOCERA printer model FS-C5250DN.  - Toner level correction for SAMSUNG printers, models:    SAMSUNG CLP-775 SERIES CLP-775 SERIES CLP-620 SERIES CLX-6220 SERIES CLX-6250 SERIES CLX-8385 SERIES CLX-8385X SERIES. \r<br/>\r<br/>[3.1.17 26/11/2015]\r<br/>- Bug fixing for counters related to some HP models. \r<br/>\r<br/>[3.1.16 23/11/2015]\r<br/>- Bug fixing and optimizations.  - Black toner level detection correction for KYOCERA monochromatic printers. \r<br/>\r<br/>[3.1.15 20/11/2015]\r<br/>Release 2\r<br/>- Incorrect maintenance kit detection on Brother printer model BROTHER MFC-J6520DW. \r<br/>\r<br/>[3.1.15 18/11/2015]\r<br/>Release 1\r<br/>- Correction of the color printing counter for KYOCERA printer model D-COLORMF2001 PLUS.  - Correction of mono and color counters for OKI printer model ES8453 MFP. \r<br/>\r<br/>[3.1.15 18/11/2015]\r<br/>- Bug fixing and code optimization for the detection of toners, photoconductors and maintenance kits. \r<br/>\r<br/>[3.1.14 10/11/2015]\r<br/>- Bug fixing and code optimization for the detection of toners, photoconductors and maintenance kits. \r<br/>\r<br/>[3.1.13 04/11/2015]\r<br/>\"- Unmanaged error correction on rare OID\u00e2\u20ac\u2122s reading on non-compliant SNMP protocol, discovery and get/walk for some printers.  - Bug correction for managing the \"\"community\"\" parameter when using the SNMPWALK command line instruction.  - Improved logging for certain web calls in case of error. \"\r<br/>\r<br/>[3.1.12 03/11/2015]\r<br/>Release 1\r<br/>\"- Waste toner detection logic correction on SHARP MX-M266N.  - Color consumable level correction on HP OFFICEJET PRO 8500 A909A.  - Black toner level correction on Samsung K4300LX, SCX-8030 and SCX-8040 models.  - Maintenance kit detection implementation \"\"Black Imaging Unit (Developer Unit)\"\" on SAMSUNG SCX-8123 8128 SERIES and SCX-8128NX. \"\r<br/>\r<br/>[3.1.12 21/10/2015]\r<br/>\"- Toshiba retail counter management in Low Color mode not active: Lines for Low Color counters do not appear between retail counters.  - RICOH AFICIOSG3100SNW and RICOH AFICIOSG3110SFNW, maintenance level correction kit \"\"Gel Recovered\"\".  - A3 counter management for HP Futuresmart printers. \"\r<br/>\r<br/>[3.1.11 13/10/2015]\r<br/>Release 4\r<br/>- Correction failed to detect black toner level on SAMSUNG M5370LX printer. \r<br/>\r<br/>[3.1.11 13/10/2015]\r<br/>Release 3\r<br/>- Implementation of Toshiba detail counters, and color counter correction.  - Correction of black toner level detection on SAMSUNG SCX-8128NX and SAMSUNG M43705370 SERIES printers. \r<br/>\r<br/>[3.1.11 06/10/2015]\r<br/>Release 2\r<br/>\"- Total counter correction for OKI ES4161 MFP.  - Added detail counter \"\"Prints Scans/Fax Color\"\", \"\"Prints Scans/Fax Mono\"\" and color counter correction for KONICA vendor.  - Toner and counter level correction for the RICOH AFICIOSG3100SNW.  - Set as maintenance kit the photoconductor present on the XEROX PHASER 6600N printer. \"\r<br/>\r<br/>[3.1.11 22/09/2015]\r<br/>Release 1\r<br/>\"- Remove log writing in the Windows event log.  - Recognize new HP printers with mac address starting with \"\"2C-76-8A\"\" and \"\"6C-C2-17\"\".  - Correction of mono and color counters for Canon with print counting modes as A4 equivalents.  - Implementation of dynamic pointing system towards the MPS portal.  - Implementation of detail counters for HP printers of the Future Smart type. \"\r<br/>\r<br/>[3.1.10 17/09/2015]\r<br/>Release 1\r<br/>- Set as maintenance kit the photoconductor on the XEROX PHASER 6600DN printer.  - Photoconductor detection correction on the following XEROX models:    XEROX PHASER 4600 XEROX PHASER 4622 XEROX WORKCENTRE 4250. \r<br/>\r<br/>[3.1.10 20/08/2015]\r<br/>- Fix compatibility problem with Windows 10. \r<br/>\r<br/>[3.1.9 05/08/2015]\r<br/>Release 9\r<br/>\"- \"\"Sheet Separator\"\" level correction for the LEXMARK X748.  - ADF Roller, ADF Retard Pad maintenance kit levels correction for the SAMSUNG X4300LX model with the latest firmware version.  - Correction on maintenance kit levels ADF Roller, ADF Retard Pad for SAMSUNG X4250LX model with latest firmware version.  - Total, Color and Serial Number counter correction for the EPSON WF-R5190 SERIES.  - Total, Color and Serial Number counter correction for the EPSON WF-2630 SERIES. \"\r<br/>\r<br/>[3.1.9 30/07/2015]\r<br/>Release 8\r<br/>- Implementation of the Fax counter in the retail counter and their use in the counter Total for the vendor KONICA.  - Custom implementation for the printer LASERJET P3010 SERIES manageable by overrides parameter. \r<br/>\r<br/>[3.1.9 20/07/2015]\r<br/>Release 7\r<br/>- Total and Color counter correction for RICOH SP C252DN.  - Total and Color counter correction for OKI MC562.  - Serial number correction for EPSON WP-M4595 SERIES.  - Total and Color counter correction for KYOCERA FS-C5350DN.  - Total and Color counter correction for OKI ES3452 MFP.  - Detail counter implementation for CANON vendor.   - Detail counter implementation for SHARP vendor.  - Waste Toner level correction for SHARP MX-3640. \r<br/>\r<br/>[3.1.9 10/07/2015]\r<br/>Release 6\r<br/>- Total and Color counter correction for the EPSON WF-5110 SERIES.  - Total, Color and Serial Number counter correction for the EPSON WF-8510 SERIES.   - Total and Mono counter correction for the KONICA MINOLTA Di3010.  - Waste Toner level correction for PANASONIC DP-C266.  - maintenance level correction kit for HP LASERJET M806.  - Elimination of A3 print detection for LEXMARK X548 and LEXMARK CX410DE.  - Implementation of detail counters divided by coverage levels on KYOCERA TASKALFA 3551CI/TASKALFA 4551CI/TASKALFA 2550CI. \r<br/>\r<br/>[3.1.9 16/06/2015]\r<br/>Release 5\r<br/>- Implementation A3 Mono and A3 Colour Counters for KYOCERA TASKALFA 2550CI.  - Total and Color Counter Correction LEXMARK model TOSHIBA E-STUDIO305CP.  - Waste Toner level correction for model SHARP MX-M564N. \r<br/>\r<br/>[3.1.9 29/05/2015]\r<br/>Release 4\r<br/>- Serial number correction for HP LASERJET PRO MFP M127FN.  - Implementation A3 Mono and A3 Counters Colour for KYOCERA TASKALFA 2551CI. \r<br/>\r<br/>[3.1.9 25/05/2015]\r<br/>Release 3\r<br/>- System name correction KONICA GENERIC 28C-1.  - Reverse logic implementation at the level of the maintenance kit ADF Roller, ADF Retard Pad for SAMSUNG X4300LX models.  - Reverse logic implementation at the level of the maintenance kit ADF Roller, ADF Retard Pad for SAMSUNG X4250LX models.  - Implementation of inverse logic to the levels of maintenance kit Belt Unit MURATA MACHINERY LTD for models OKI MFX-C3400.  - Total and mono counter implementation for models HP LASERJET PRO MFP M127FN. \r<br/>\r<br/>[3.1.9 28/04/2015]\r<br/>Release 2\r<br/>\"- Photoconductor detection correction XEROX WORKCENTRE 7220.  - Photoconductor detection correction XEROX WORKCENTRE 7835.  - Total and Color Counter Correction LEXMARK model TOSHIBA E-STUDIO305CS.  - Total and Color Counter Correction OKI ES5462 MFP.  - Retrieval of information on consumable descriptions on HP printers, activated by advanced parameter \"\"Generate XML data\"\".     To view the extracted data, use the link \"\"Information on consumables in the device\"\" which will appear on the home of the device. \"\r<br/>\r<br/>[3.1.9 14/04/2015]\r<br/>Release 1\r<br/>- Total and Color Counter Correction RICOH SP C252SF.  - Total and Color Counter Correction OKI C711.  - Total Counter Correction KONICA MINOLTA 222. \r<br/>\r<br/>[3.1.9 09/04/2015]\r<br/>- New implementation for overwriting brand names and models (overrides): possibility to use also the IP address, instead of the MAC address, to specify the devices on which to apply overwriting. \r<br/>\r<br/>[3.1.8 01/04/2015]\r<br/>Release 3\r<br/>- Total counter correction, A3 Mono and A3 Colour for LEXMARK X950.  - Total counter correction for LEXMARK X952, X954.  - Total counter correction and Colour for EPSON WF-5190 SERIES.  - Implementation of retail counters divided by coverage levels on KYOCERA 2500CI.  - Implementation of retail counters divided by coverage levels on KYOCERA 4505CI.  - Futuresmart counter detection implementation for HP Vendor. \r<br/>\r<br/>[3.1.8 13/03/2015]\r<br/>Release 2\r<br/>- Counter corrections of Futuresmart HP LASERJET 700 COLOR MFP M775 models. \r<br/>\r<br/>[3.1.8 12/03/2015]\r<br/>Release 1\r<br/>\"- Bug correction \"\"overrides\"\" mode (manual change of Make and Model for logical variation of the measurements).  - HP printers management of the OFFICEJET PRO X series: correct logic application even in the presence of the advanced parameter \"\"Enable algorithm designed for the detection of HP counters\"\" (not valid for the OFFICEJET PRO X series).  \"\r<br/>\r<br/>[3.1.7 11/03/2015]\r<br/>Release 6\r<br/>- Implementation of changes to the alert code PANASONIC DP-MB310.  - SAMSUNG M4580FX firmware correction.  - Correction of mono and color counters for HP OFFICEJET PRO X series models. \r<br/>\r<br/>[3.1.7 03/03/2015]\r<br/>Release 5\r<br/>- Mono and colour counter correction OKI ES8461 MFP.  - Colour counter correction KYOCERA TASKALFA 3551CI.  - Mono counter correction OKI ES4131. \r<br/>\r<br/>[3.1.7 18/02/2015]\r<br/>Release 4\r<br/>- EPSON serial number correction AL-C500.  - NEC serial number vendor correction.  - KONICA GENERIC 28C-6E firmware detection implementation.  - Waste toner logic correction OKI ES2632A4.  - Waste toner logic correction CANON LBP7750C. \r<br/>\r<br/>[3.1.7 03/02/2015]\r<br/>Release 3\r<br/>- Implementation fax counter HP LASERJET M5035 MFP.  - Correction of mono and colour counters NEC COMCOLOR 7050.  - Implementation of retail meters for KONICA vendors. \r<br/>\r<br/>[3.1.7 26/01/2015]\r<br/>Release 2\r<br/>- Correction of serial numbers and counters EPSON WF-R8590 SERIES.  - Correction of mono and color counters SAMSUNG CLX-6200 SERIES.  - Addition of scans in retail counters for KYOCERA models.  - Inverse logic implementation at maintenance kit levels OKI ES5461 MFP. \r<br/>\r<br/>[3.1.7 07/01/2015]\r<br/>Release 1\r<br/>- Counter corrections of Futuresmart HP COLOR LASERJET FLOW MFP M880 and HP LASERJET 600 M602 models. \r<br/>\r<br/>[3.1.7 07/01/2015]\r<br/>- Counter detection optimization for Futuresmart-type HP: For printers with older firmware the previous algorithm is applied.  - Consumable level calculation management when the current level is higher than the maximum level (SAMSUNG CLX-9201).  - Changes for handling errors when retrieving information from printers for troubleshooting purposes.  - Correction of counters RICOH AFICIO SP C830DN.  - Correction of counters RICOH SP C250DN. \r<br/>\r<br/>[3.1.6 11/12/2014]\r<br/>Release 1\r<br/>\"- Black toner level correction RICOH SP 204SFN DDST.  - Detail counter implementation SHARP MX-6500N.  - Reverse logic implementation at maintenance kit levels \"\"ADF Roller\"\" and \"\"ADF Retard Pad\"\" on SAMSUNG M4580FX.  - Black toner level correction on SAMSUNG M4580FX.  - Implementation counters detail divided by levels coverage on KYOCERA 3005CI. \"\r<br/>\r<br/>[3.1.6 11/12/2014]\r<br/>- Waste Toner level correction for all RICOH printers.  - Correction of data taken from SAMSUNG printers with new network cards (mac 30-CD-A7-XX-XX-XX).  \r<br/>\r<br/>[3.1.5 05/12/2014]\r<br/>Release 3\r<br/>- Color counter correction KYOCERA D-COLORMF2552 PLUS.  - Meter correction OKI MFX-C3400. \r<br/>\r<br/>[3.1.5 28/11/2014]\r<br/>Release 2\r<br/>- Black toner level correction on SAMSUNG M458X SERIES.  - OKI ES8451 MFP counter correction. \r<br/>\r<br/>[3.1.5 21/11/2014]\r<br/>Release 1\r<br/>- Counters correction KYOCERA CLP 3626_CLP 4626.  - Photoconductor level correction XEROX WORKCENTRE 7225.  - Correction counter mono CANON IR2020. \r<br/>\r<br/>[3.1.5 21/11/2014]\r<br/>- Optimization of printers detection via custom OID.  - Management of CANON detection IR2020.  - Management of XEROX detection J75. \r<br/>\r<br/>[3.1.4 06/11/2014]\r<br/>Release 3\r<br/>- Serial number correction EPSON WF-5620 SERIES.  - Serial number and counter correction EPSON WF-R5690 SERIES.  - Counter correction OKI ES5431.  - Waste toner level correction SHARP MX-2614N.  - LEXMARK C2132 counter correction. \r<br/>\r<br/>[3.1.4 28/10/2014]\r<br/>Release 2\r<br/>- EPSON WF-5620 SERIES counter correction.  - CANON MF6100 SERIES counter correction.  - LEXMARK CX310DN counter correction.  - PANASONIC DP-MB300 toner level correction.  - CANON LBP7660C counter correction.  - KYOCERA CLP 3550_CLP 4550 counter correction. \r<br/>\r<br/>[3.1.4 17/10/2014]\r<br/>Release 1\r<br/>- Counters correction KYOCERA 5505CI.  - Counters correction KYOCERA D-COLORMF2552. \r<br/>\r<br/>[3.1.4 25/09/2014]\r<br/>- Correction of counters RICOH AFICIO SP C242DN.  - Implementation tests for undetected printers. \r<br/>\r<br/>[3.1.3 03/09/2014]\r<br/>Release 2\r<br/>- Waste toner level correction on SAMSUNG CLX-6260 SERIES.  - Waste toner level correction on SHARP MX-M904.  - Waste toner level correction on SHARP MX-6500N.     - A3 counter false detection correction on LEXMARK MX611DE.  - Toner level correction on LEXMARK OPTRA T614. \r<br/>\r<br/>[3.1.3 30/07/2014]\r<br/>Release 1\r<br/>- Correction of counters PANASONIC DP-C266.  - Correction of counters and photoconductor level XEROX WORKCENTRE PRO 35.  - LEXMARK CX410DE counter correction.  - XEROX WORKCENTRE 7855 photoconductor level correction.  - Counters correction LEXMARK XC2130.  - Counters correction EPSON WF-3010 SERIES.  - Counters correction CANON IR1020.  - Counters correction CANON IR2016.  - Counters correction OKI MC860. \r<br/>\r<br/>[3.1.3 22/07/2014]\r<br/>- Implemented KYOCERA detection FS-1028MFP. \r<br/>\r<br/>[3.1.2 17/07/2014]\r<br/>Release 1\r<br/>- Counters correction OKI ES2632A4.  - Counters correction OKI ES8460 MFP.  - Counters correction KYOCERA TASKALFA 3550CI.  - Counters correction KYOCERA TASKALFA 5551CI.  - Counters correction BROTHER models in color.  - Counters correction CANON MF8200C SERIES. \r<br/>\r<br/>[3.1.2 08/07/2014]\r<br/>- Release of corrective tracks. \r<br/>\r<br/>[3.1.1 08/07/2014]\r<br/>Release 1\r<br/>- Counter correction RICOH AFICIO SP C240DN.  - Forced detection of EPSON printers with new network cards.  - Implementation of HP printer detection with new network cards (mac D8-9D-67-XX-XX-XX).  - OKI C710 toner level correction.  - CANON MF8500C SERIES counter correction. \r<br/>\r<br/>[3.1.0 27/06/2014]\r<br/>Release 2\r<br/>- SHARP MX-6500N counter correction and A3 counter implementation.  - SHARP MX-M904 counter correction.  - SAMSUNG SCX-6X22 SERIES toner level correction.  - Firmware detection implementation on TOSHIBA vendors. \r<br/>\r<br/>[3.1.0 17/06/2014]\r<br/>- Upgrade to version 3.1 with implementations for improved support management. \r<br/>\r<br/>[3.0.27 15/05/2014]\r<br/>\"- Detection correction CANON IR1022 and IR1024.  - Waste toner level correction on SAMSUNG CLX-4190 SERIES.  - System name detection correction on RICOH models: MP C4503, AFICIO MP C5502, AFICIO MP 9002.  - Mono print correction on OKI ES5461 MFP.  - KYOCERA counter correction FS-C8525MFP.  - A3 counter correction for SAMSUNG CLX-9201.  - OKI counter correction MC851.  - Level correction of the \"\"Separator roller and pick-up unit\"\" on LEXMARK X792.  - Correction of CANON MF8050 toner levels.  - Fixed bug for detecting detail counters. \"\r<br/>\r<br/>[3.0.26 23/04/2014]\r<br/>- Fixed bug on detection of detail counters in case of IP range scans.  - Fixed counters RICOH AFICIO CL7300.  - Fixed cyan and yellow level on SAMSUNG CLX-8385X SERIES.  - Fixed counters KYOCERA CDC 1935_DCC 2935 and KYOCERA FS-C8025MFP.  - CANON IR1024 detection correction.  - CANON MF8030 toner level correction.  - Waste toner detection logic correction on TOSHIBA E-STUDIO2505F. \r<br/>\r<br/>[3.0.25 14/03/2014]\r<br/>- Counter correction on SAMSUNG CLP-660.  - Toner level correction on XEROX WORKCENTRE 7232.  - Implementation management of the Xerographic-Module maintenance kit as a photoconductor on all XEROX models of the WORKCENTRE 56xx, WORKCENTRE 57xx and WORKCENTRE PRO 2xx series.  - Counter detection implementation for HP Futuresmart LASERJET M4555 MFP and LASERJET CM4540 MFP models.  - Photoconductor detection correction and maintenance kit for XEROX WORKCENTRE 56xx and XEROX WORKCENTRE 57xx series.  - Toner level correction on SAMSUNG CLX-6250 SERIES due to Samsung firmware bugs.  - Counters correction KYOCERA TASKALFA 2551CI. \r<br/>\r<br/>[3.0.24 06/02/2014]\r<br/>- Set as maintenance kit the only photoconductor present on the color printers of the XEROX WORKCENTRE 7132, 7232, 7242 series. \r<br/>\r<br/>[3.0.23 06/02/2014]\r<br/>- Implementation of forced counter detection mechanism on SAMSUNG printers,    to avoid firmware bugs. \r<br/>\r<br/>[3.0.22 04/02/2014]\r<br/>- Fix LEXMARK counters XC2132.  - Fix A3 counters on SAMSUNG SCX-8240.  - Fix bug detection counters KYOCERA 3005CI.  - Counter correction RICOH PRO 8100S.  - Detailed counter implementation for XEROX vendors.  - Maintenance kit correction logic on OKI ES6410. \r<br/>\r<br/>[3.0.21 15/01/2014]\r<br/>- Forced implementation on brand and model detection for rebranded printers.  - Toner detection correction on RICOH AFICIOSG3110SFNW.  - System Name detection correction on RICOH AFICIO MP C305.  - A3 counter correction on SAMSUNG CLX-9250 and SAMSUNG CLX-9350.  - KYOCERA TASKALFA 3051CI and KYOCERA 3005CI counter correction.  - Counters correction KYOCERA TASKALFA 4551CI.  - A3 counters implementation on SAMSUNG SCX-8030. \r<br/>\r<br/>[3.0.20 13/12/2013]\r<br/>- Photoconductor detection correction on XEROX WORKCENTRE 5665.  - Correction of counters RICOH AFICIOSG3110SFNW.  - Correction of counters on the following models KYOCERA: 206C, CDC 1965_DCC 2965, 3505CI, CDC 1945_DCC 2945,    CDC 1840_DCC 2840.  - Bug correction on counter detection KYOCERA D-COLOR MF2001. \r<br/>\r<br/>[3.0.19 27/11/2013]\r<br/>\"- Photoconductor detection correction on the following XEROX models:    XEROX WORKCENTRE 7755 XEROX WORKCENTRE 4150 XEROX WORKCENTRE 5755 XEROX WORKCENTRE 7655 XEROX WORKCENTRE 7132 (the only photoconductor present is managed as a maintenance kit)    XEROX WORKCENTRE PRO 255 XEROX WORKCENTRE PRO 245 - Photoconductor detection correction on XEROX detected as \"\"XEROX EFI FIERY CONTROLLER (WINDOWS\"\".  \"\r<br/>\r<br/>[3.0.18 11/11/2013]\r<br/>- Counters correction KYOCERA TASKALFA 400CI.  - Counters correction XEROX WORKCENTRE 7755.  - OKI counters correction C9650.  - Developer detection problem correction on SAMSUNG CLX-9201.  - A3 counters implementation for XEROX WORKCENTRE 7435, XEROX WORKCENTRE 7845 and XEROX WORKCENTRE 7328.  - Counters correction KYOCERA D-COLOR MF2501. \r<br/>\r<br/>[3.0.17 24/10/2013]\r<br/>- Toner tank level detection correction on XEROX vendor.  - Toner level detection correction on XEROX DOCUMENT CENTRE 440ST and XEROX DOCUMENT CENTRE 432ST.  - Correction of maintenace level detection kit on LEXMARK C782.  - Counter correction KYOCERA D-COLOR MF2001.  - Counter correction SAMSUNG ML-331X SERIES.  - Implementation of A3 counters on XEROX WORKCENTRE 7125. \r<br/>\r<br/>[3.0.16 11/10/2013]\r<br/>\"- Correct counter detection logic CANON IR C1021 after finding logical differences Canon on different firmware versions.  - Implemented SHARP model detection with mac address network card starting with \"\"78-1C-5A\"\".  - Implemented forced brand recognition for vendors SHARP, CANON, XEROX and BROTHER. \"\r<br/>\r<br/>[3.0.15 23/09/2013]\r<br/>- Corrected photoconductor level detection on LEXMARK E120N.  - Total and mono counter correction on CANON IR C1021.  - Firmware version detection correction on SAMSUNG CLX-9301 and CLX-9201.  - Mono counter correction for BROTHER HL-models5250DN SERIES, HL-5350DN SERIES and HL-5380DN.     Check the correct mono counter extraction for BROTHER HL-5170DN.  - Toner level correction BROTHER MFC-6490CW.  - Check the correct A3 counter management on CANON IR C3080.  - Implementation of A3 counters on TOSHIBA E-STUDIO4540C, XEROX WORKCENTRE 7120 and XEROX WORKCENTRE 7556. \r<br/>\r<br/>[3.0.14 16/09/2013]\r<br/>- Implementation of modifications for detection of the type of maintenance kit for diversified alert management.  - Bug correction on USB printer job detection for serial lack management. \r<br/>\r<br/>[3.0.13 16/08/2013]\r<br/>- Implementation of detailed counters for all KYOCERA printers, obtainable by activation of the appropriate advanced parameter.  - Counters correction models KYOCERA CDC 1520_DCC 2520, CLP 3416_CLP 4416  - Firmware version correction on SAMSUNG SCX-8240 and CLX-9352.  - Photoconductor logic removal and maintance kit for printer KONICA GENERIC 45C-5, previously inserted in version 2.2.41 of 13 December 2012. \r<br/>\r<br/>[3.0.12 19/07/2013]\r<br/>- Counters correction KYOCERA FS-C5020N.  - Change to setup: when running on a machine where the . Net Framework 4 Full can now download and install the framework from within the setup itself.  - Detailed counter implementation for all SAMSUNG and RICOH printers. These counters are not extracted by default but you must activate the appropriate advanced parameter on the installation configuration page. \r<br/>\r<br/>[3.0.11 12/07/2013]\r<br/>\"- Eliminated maintenace detection kit \"\"Ozone Filter\"\" on SAMSUNG SCX-8123 8128 SERIES due to failure to record correct data due to printer firmware problems.  - Fixed firmware version on SAMSUNG SCX-8230 and CLX-9252.  - Fixed OKI counters ES5461 MFP.  - Verified correct counter detection on LEXMARK C792. \"\r<br/>\r<br/>[3.0.10 10/07/2013]\r<br/>- Implementation of SNMP Protocol Detail Counter Forcing Update Mechanism for SAMSUNG SCX-5835_5935 SERIES and SAMSUNG SCX-6545 SERIES printers (valid for all Samsung printers if applicable).  - Counter correction XEROX DOCUCOLOR 250 WITH EFI FIERY CONTROLLER.  - Improved mono and color counter detection on all Xeroxes with TYAN (or other vendor other than XEROX).  - LEXMARK CX510DE counter correction. \r<br/>\r<br/>[3.0.9 28/06/2013]\r<br/>\"- Counter correction on LERXMARK CS510DE and LEXMARK C746.  - Logic correction for maintenace detection kit \"\"Ozone Filter\"\" on SAMSUNG SCX-8123 8128 SERIES.  - Counter correction RICOH AFICIO SP C821DN.  - Circumvented Samsung bug that prevented Mac Address detection on SAMSUNG SCX-4X24 SERIES.  - Correct level of \"\"Waste Toner Container\"\" consumable on XEROX WORKCENTRE 7125 V 5. 12.  0 MULTIFUNCTION SYSTEM. \"\r<br/>\r<br/>[3.0.8 20/06/2013]\r<br/>- Fix counters SAMSUNG CLX-3170 SERIES.  - Circumvented Samsung bug that prevented toner level detection on SAMSUNG SCX-8230.  - Circumvented Samsung bug that prevented Mac Address detection on SAMSUNG SCX-4X28 SERIES.  - Fix counters on RICOH AFICIO CL7100. \r<br/>\r<br/>[3.0.7 06/06/2013]\r<br/>- Counters correction KONICA MINOLTA 250.  - Implemented generation of XML files with retail counters for KONICA GENERIC 45C-5 printers,     SAMSUNG SCX-5835_5935 SERIES and SAMSUNG SCX-6545 SERIES.  - Toner level correction on XEROX PHASER 3250 and XEROX COLORQUBE 9203.  - Improved management for toner types on XEROX printers.  - Counters correction KYOCERA FS-C8020MFP.  - Photoconductor level correction on LEXMARK E450DN. \r<br/>\r<br/>[3.0.6 22/05/2013]\r<br/>\"- Circumvented Samsung bug that prevented toner level detection on SAMSUNG SCX-8240.  - Cyan, magenta and yellow toner level detection correction on HP OFFICEJET PRO 8000 A809.  - Mono and color counter correction on LEXMARK X748 and C748 models.  - Restored maintenace detection logic kit \"\"Ozone Filter\"\" on SAMSUNG SCX-8123 8128 SERIES,    previously modified in version 2.2.44.  - Implemented firmware reading and A3 counters on SAMSUNG SCX-8040. \"\r<br/>\r<br/>[3.0.5 30/04/2013]\r<br/>- Multiple black toner reading implementation on XEROX printers.  - Brand detection forcing for re-branded XEROX printers.  - System name correction on RICOH AFICIO SP 8300DN.  - Counter correction on RICOH AFICIO SP C240SF. \r<br/>\r<br/>[3.0.4 10/04/2013]\r<br/>- Implementation of sending walk files to new explorer version 3.  - Fixed bug with authenticated proxy in case of use of the logged user for authentication. \r<br/>\r<br/>[3.0.3 08/04/2013]\r<br/>- System name correction on RICOH models: AFICIO MP 4002, MP 2501.  - Counter correction on KONICA MINOLTA 282. \r<br/>\r<br/>[3.0.2 03/04/2013]\r<br/>- Test update explorer \r<br/>\r<br/>"
```

### Integrations/GetJoinedCustomers

- **Data Type**: list
- **Item Count**: 2
- **Sample Data**:
```json
{
  "Code": "joined",
  "Description": "22"
}
```

### Integrations/List

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### Product/Dealer/List

- **Data Type**: list
- **Item Count**: 50
- **Sample Data**:
```json
{
  "Logo": null,
  "Color": 3,
  "FormatType": 3,
  "HasGapInfo": false,
  "ModelClean": null,
  "Model": "HP COLOR LASERJET CP4520 SERIES",
  "Brand": "HP",
  "Id": "xoNXE5i-pYFyoinO7hid9g2"
}
```

### Product/Dealer/ListBrands

- **Data Type**: list
- **Item Count**: 5
- **Sample Data**:
```json
"HP"
```

### Product/Dealer/ListModels

- **Data Type**: list
- **Item Count**: 50
- **Sample Data**:
```json
"ACCURIOPRESS C3070"
```

### Product/GetBrands

- **Data Type**: list
- **Item Count**: 50
- **Sample Data**:
```json
"#VALUE!"
```

### Product/GetModels

- **Data Type**: list
- **Item Count**: 50
- **Sample Data**:
```json
"\u0000\u0000\u0000\u0000"
```

### Product/GetSnmpDiscoveryBrands

- **Data Type**: list
- **Item Count**: 31
- **Sample Data**:
```json
"BARIX"
```

### Role/List

- **Data Type**: list
- **Item Count**: 12
- **Sample Data**:
```json
{
  "Name": "Installer",
  "Description": "Installer",
  "Code": "IN",
  "Capabilities": [
    {
      "Name": "Devices_View",
      "CapabilityGroupName": "devices",
      "IsRolesForCustomer": true,
      "Id": "ad7C9x6m6NDyltAIMTotyg2"
    },
    {
      "Name": "Devices_View_UnManaged",
      "CapabilityGroupName": "devices",
      "IsRolesForCustomer": true,
      "Id": "evTKH6CKMJ6RXLiKzOYR2g2"
    },
    {
      "Name": "Connectors_View",
      "CapabilityGroupName": "explorer",
      "IsRolesForCustomer": true,
      "Id": "1CF888bW_yWkUxqW17GsGA2"
    },
    {
      "Name": "Connectors_CreateLicense",
      "CapabilityGroupName": "explorer",
      "IsRolesForCustomer": true,
      "Id": "K8m8PXO6Q960zVqQI25M0w2"
    },
    {
      "Name": "Connectors_Edit",
      "CapabilityGroupName": "explorer",
      "IsRolesForCustomer": true,
      "Id": "NQL8XFnF4XOKTPBbTsoHcg2"
    },
    {
      "Name": "Connectors_Delete",
      "CapabilityGroupName": "explorer",
      "IsRolesForCustomer": true,
      "Id": "rZ6oTRINEBjSzsOsr8aIqw2"
    },
    {
      "Name": "Connectors_SDS",
      "CapabilityGroupName": "explorer",
      "IsRolesForCustomer": true,
      "Id": "8mbg7ihlxdv17bN7vyo8IA2"
    },
    {
      "Name": "Customers_View",
      "CapabilityGroupName": "books",
      "IsRolesForCustomer": true,
      "Id": "LqD8QCRkSYl9TSUYSEAg7A2"
    },
    {
      "Name": "Customers_PrivacyData",
      "CapabilityGroupName": "books",
      "IsRolesForCustomer": true,
      "Id": "wPFaIokwpH0eJVkDAzYUSg2"
    },
    {
      "Name": "Connectors_Download",
      "CapabilityGroupName": "explorer",
      "IsRolesForCustomer": true,
      "Id": "nrEUQeQLNX-e-TrEHhvjiw2"
    },
    {
      "Name": "Devices_Export",
      "CapabilityGroupName": "devices",
      "IsRolesForCustomer": true,
      "Id": "3sFMDbL1W4ZZGuWZEarVRQ2"
    }
  ],
  "DealerCode": null,
  "IsCustomRole": false,
  "IsShared": false,
  "IsSharedByCurrentDealer": false,
  "Force2fa": false,
  "ForceSso": false,
  "MaxLoginFailedAttempts": 10,
  "DisableAfterInactivesDays": 180,
  "Id": "SnBx49i6Re2mpp7th_CgZw2"
}
```

### SdsAction/GetDeviceActions

- **Data Type**: list
- **Item Count**: 15
- **Sample Data**:
```json
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
  "HasGenuineHpCartridges": false,
  "Title": null,
  "Description": null,
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
  "OfficeId": "t7ocqp0rnEu39THPO8syCA2",
  "OfficeCode": "DEFAULT",
  "OfficeDescription": "DEFAULT",
  "Id": "afHJW0XD5DZDz9chXcpY5znmx5pwEnWGVne8QKhbV501"
}
```

### SdsConnector/GetConnector

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### SdsCustomer/GetAssessTemplates

- **Data Type**: list
- **Item Count**: 4
- **Sample Data**:
```json
{
  "CreatedAtUtc": "2021-08-20T20:02:00Z",
  "Name": "CNC1P1M076 - 20210820200214",
  "CustomerCode": null,
  "Values": [
    {
      "Label": "",
      "Name": "AdminPasswordEnabled",
      "Value": "False",
      "ValueType": "Checkbox",
      "ControlType": "Checkbox",
      "Constraints": null,
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Credentials",
      "SettingSubGroup": "Admin(EWS) Password",
      "Description": "Admin Password Enabled",
      "Order": 1100,
      "ExcludeFromApiCall": true,
      "LocalizedNotes": null
    },
    {
      "Label": "Admin (EWS) Password",
      "Name": "AdminPassword",
      "Value": null,
      "ValueType": "Password",
      "ControlType": "Password",
      "Constraints": null,
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Credentials",
      "SettingSubGroup": null,
      "Description": "AdminPassword",
      "Order": 1090,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "SNMP V1/V2",
      "Name": "SnmpRwAccessType",
      "Value": "read",
      "ValueType": "String",
      "ControlType": "Custom",
      "Constraints": {
        "PossibleValues": [
          "readwrite",
          "read"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Credentials",
      "SettingSubGroup": "SNMP v1/v2 Settings",
      "Description": "SNMP Rw Access Type",
      "Order": 1080,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": "disablingsnmprwaccesstypenotsupported"
    },
    {
      "Label": "SetCommunityName",
      "Name": "SetCommunityName",
      "Value": null,
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": null,
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Credentials",
      "SettingSubGroup": null,
      "Description": "Set Community Name",
      "Order": 1071,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "GetCommunityName",
      "Name": "GetCommunityName",
      "Value": null,
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": null,
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Credentials",
      "SettingSubGroup": null,
      "Description": "Get Community Name",
      "Order": 1070,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "",
      "Name": "GetCommunityNameEnabled",
      "Value": "True",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Credentials",
      "SettingSubGroup": null,
      "Description": "Get Community Name Enabled",
      "Order": 1010,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "SNMPV3",
      "Name": "SnmpV3Enabled",
      "Value": "False",
      "ValueType": "Custom",
      "ControlType": "Radio",
      "Constraints": null,
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Credentials",
      "SettingSubGroup": "SNMP v3 Settings",
      "Description": "SNMPV3 Enabled",
      "Order": 1000,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "SNMPV3Credentials",
      "Name": "SnmpV3Credentials",
      "Value": null,
      "ValueType": "Custom",
      "ControlType": "Custom",
      "Constraints": null,
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Credentials",
      "SettingSubGroup": null,
      "Description": "SNMPV3 Credentials",
      "Order": 990,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "FileSystemPassword",
      "Name": "FileSystemPassword",
      "Value": null,
      "ValueType": "Custom",
      "ControlType": "Password",
      "Constraints": null,
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": "File System Password",
      "Order": 970,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "PJLPassword",
      "Name": "PjlPassword",
      "Value": null,
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 0,
          "Max": 2147483647
        }
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Credentials",
      "SettingSubGroup": "Other Credentials",
      "Description": "PJL Password",
      "Order": 950,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "Check For Latest Firmware",
      "Name": "CheckForLatestFirmware",
      "Value": null,
      "ValueType": "Checkbox",
      "ControlType": "Checkbox",
      "Constraints": null,
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Firmware",
      "SettingSubGroup": null,
      "Description": "Check For Latest Firmware",
      "Order": 940,
      "ExcludeFromApiCall": true,
      "LocalizedNotes": null
    },
    {
      "Label": "FileSystemAccessPML",
      "Name": "FileSystemAccessPmlEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "File System Access Controls",
      "SettingSubGroup": null,
      "Description": "Printer Management Language",
      "Order": 930,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "FileSystemAccessNFS",
      "Name": "FileSystemAccessNfsEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "File System Access Controls",
      "SettingSubGroup": null,
      "Description": "Network File Systems",
      "Order": 920,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "FileSystemAccessPs",
      "Name": "FileSystemAccessPsEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "File System Access Controls",
      "SettingSubGroup": null,
      "Description": "Postscript",
      "Order": 910,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "FileSystemAccessPJL",
      "Name": "FileSystemAccessPjlEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "File System Access Controls",
      "SettingSubGroup": null,
      "Description": "Printer Job Language",
      "Order": 900,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "TelnetEnabled",
      "Name": "TelnetEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Network Services",
      "SettingSubGroup": null,
      "Description": "Telnet Enabled",
      "Order": 890,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "RFU",
      "Name": "RfuEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Network Services",
      "SettingSubGroup": null,
      "Description": "Remote Firmware Update",
      "Order": 880,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "FTPFirmwareUpdate",
      "Name": "FtpFirmwareUpdate",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Network Services",
      "SettingSubGroup": null,
      "Description": "FTP Firmware Update",
      "Order": 870,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "HTTPSRedirect",
      "Name": "HttpsRedirectEnabled",
      "Value": "True",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Network Services",
      "SettingSubGroup": null,
      "Description": "Require HTTPS Redirect",
      "Order": 860,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "FTP",
      "Name": "FtpEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Network Services",
      "SettingSubGroup": null,
      "Description": "File Transfer Protocol (FTP)",
      "Order": 850,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "Appletalk",
      "Name": "AppletalkEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Network Services",
      "SettingSubGroup": null,
      "Description": "AppleTalk",
      "Order": 840,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "IpxSpx",
      "Name": "IpxSpxEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "Radio",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": true,
      "SettingGroup": "Network Services",
      "SettingSubGroup": null,
      "Description": "Novell (IPX/SPX)",
      "Order": 830,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "AdminAccountLockout",
      "Name": "AdminAccountLockout",
      "Value": "True",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": "Admin Account Lockout",
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "AdminPasswordComplexityEnabled",
      "Name": "AdminPasswordComplexityEnabled",
      "Value": "True",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": "Admin Password Complexity Enabled",
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "AdminPasswordLockoutInterval",
      "Name": "AdminPasswordLockoutInterval",
      "Value": "10",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 5,
          "Max": 1800
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": "Admin Password Lockout Interval",
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "AdminPasswordMaxAttempts",
      "Name": "AdminPasswordMaxAttempts",
      "Value": "5",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 3,
          "Max": 30
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": "Admin Password Max Attempts",
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "AdminPasswordMinLength",
      "Name": "AdminPasswordMinLength",
      "Value": "8",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 0,
          "Max": 16
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": "Admin Password Min Length",
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "AdminPasswordResetLockoutCounterInterval",
      "Name": "AdminPasswordResetLockoutCounterInterval",
      "Value": "10",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 0,
          "Max": 1800
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "CartridgeLife",
      "Name": "CartridgeLife",
      "Value": "Standard",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "Standard",
          "Managed",
          "Maximum"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "ControlPanelDisplayText",
      "Name": "ControlPanelDisplayText",
      "Value": "Tray 4 empty: Plain, Letter",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": null,
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "ControlPanelTimeout",
      "Name": "ControlPanelTimeout",
      "Value": "60",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 10,
          "Max": 300
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "CorsEnabled",
      "Name": "CorsEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "DefaultMediaSize",
      "Name": "DefaultMediaSize",
      "Value": "Letter",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "Executive",
          "Letter",
          "Legal",
          "8.5x13",
          "Statement",
          "A6",
          "A5",
          "A4",
          "16K195x270",
          "B6(JIS)",
          "B5(JIS)",
          "Postcard(JIS)",
          "DPostcard(JIS)",
          "4x6",
          "5x8",
          "Envelope Monarch",
          "Envelope #10",
          "Envelope DL",
          "Envelope C5",
          "Envelope C6",
          "Envelope B5",
          "Custom",
          "Envelope #9"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "DefaultPrintCopy",
      "Name": "DefaultPrintCopy",
      "Value": "1",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 1,
          "Max": 9999
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "DeviceAnnouncementEnable",
      "Name": "DeviceAnnouncementEnable",
      "Value": "Enabled",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "Enabled",
          "Disabled"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "DeviceAnnouncementEnabledV2",
      "Name": "DeviceAnnouncementEnabledV2",
      "Value": "True",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "DeviceAnnouncementRequireMutualAuth",
      "Name": "DeviceAnnouncementRequireMutualAuth",
      "Value": "Disabled",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "Enabled",
          "Disabled"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "DeviceAnnouncementRequireMutualAuthEnabled",
      "Name": "DeviceAnnouncementRequireMutualAuthEnabled",
      "Value": "False",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "EWSEnabled",
      "Name": "EwsEnabled",
      "Value": "True",
      "ValueType": "Boolean",
      "ControlType": "Boolean",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "HttpIdleTimeout",
      "Name": "HttpIdleTimeout",
      "Value": "30",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 0,
          "Max": 60
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "InactivityTimeout",
      "Name": "InactivityTimeout",
      "Value": "60",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 10,
          "Max": 100
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "JobStorageLimit",
      "Name": "JobStorageLimit",
      "Value": "32",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 1,
          "Max": 100
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "LongLifeConsumablesLife",
      "Name": "LongLifeConsumablesLife",
      "Value": "Managed",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "Standard",
          "Managed",
          "Maximum"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "P9100Enabled",
      "Name": "P9100Enabled",
      "Value": "True",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "PowerSaveTimeout",
      "Name": "PowerSaveTimeout",
      "Value": "Long",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": null,
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "RequestedTrayMatch",
      "Name": "RequestedTrayMatch",
      "Value": "firstMatch",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "exactMatch",
          "firstMatch"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "RetrieveFromUsbEnabled",
      "Name": "RetrieveFromUsbEnabled",
      "Value": "True",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False",
          "NOT_SET"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "SNMPV3AccountLockout",
      "Name": "SnmpV3AccountLockout",
      "Value": "True",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "SNMPV3ComplexityEnabled",
      "Name": "SNMPV3ComplexityEnabled",
      "Value": "True",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "SNMPV3PasswordLockoutInterval",
      "Name": "SnmpV3PasswordLockoutInterval",
      "Value": "10",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 5,
          "Max": 1800
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "SNMPV3PasswordMaxAttempt",
      "Name": "SnmpV3PasswordMaxAttempt",
      "Value": "5",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 3,
          "Max": 30
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "SNMPV3PasswordMinLength",
      "Name": "SNMPV3PasswordMinLength",
      "Value": "8",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 8,
          "Max": 255
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "SNMPV3PasswordResetLockoutCounterInterval",
      "Name": "SnmpV3PasswordResetLockoutCounterInterval",
      "Value": "10",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 0,
          "Max": 1800
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "TcpIdleTimeout",
      "Name": "TcpIdleTimeout",
      "Value": "270",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 0,
          "Max": 3600
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "WebProxyServerPassword",
      "Name": "WebProxyServerPassword",
      "Value": "NOT_SET",
      "ValueType": "String",
      "ControlType": "Password",
      "Constraints": null,
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "WebProxyServerPort",
      "Name": "WebProxyServerPort",
      "Value": "80",
      "ValueType": "Integer",
      "ControlType": "Integer",
      "Constraints": {
        "PossibleValues": null,
        "Range": {
          "Min": 1,
          "Max": 65535
        }
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    },
    {
      "Label": "WsPrintEnabled",
      "Name": "WsPrintEnabled",
      "Value": "True",
      "ValueType": "String",
      "ControlType": "String",
      "Constraints": {
        "PossibleValues": [
          "True",
          "False"
        ],
        "Range": null
      },
      "IsEssentialSecurityPolicy": false,
      "SettingGroup": null,
      "SettingSubGroup": null,
      "Description": null,
      "Order": 0,
      "ExcludeFromApiCall": false,
      "LocalizedNotes": null
    }
  ],
  "Id": "HxErZwQTrfwRLvu5XEfWQw2"
}
```

### SdsDevice/GetDeviceOperation

- **Data Type**: dict
- **Item Count**: 4
- **Params Used**: `{"deviceId": "bdCjDNK4L1k8-c1hG1psNQ2"}`
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### SdsDevice/GetDevicesOperations

- **Data Type**: list
- **Item Count**: 34
- **Params Used**: `{"deviceId": "bdCjDNK4L1k8-c1hG1psNQ2"}`
- **Sample Data**:
```json
{
  "OperationId": 135004270,
  "CreatedTimeUtc": "2021-06-30T16:57:00Z",
  "LastUpdatedTimeUtc": "2021-06-30T16:58:00Z",
  "Operation": 2,
  "UserAccountId": 656,
  "Result": 4,
  "Details": "{\"TransducerMonitoring\":{\"Name\":\"TransducerMonitoring\",\"Value\":\"True\",\"Url\":\"https://jamanagement.api.hp.com/jetadv/Customers/430316/Devices/152322106/ConfigItems/TransducerMonitoring\",\"State\":{\"Result\":\"Success\",\"Reason\":\"Applied\"}}}",
  "OperationInfo": null,
  "IsPending": false,
  "IsSuccess": true,
  "IsError": false,
  "IsCredentialsOperation": false,
  "Id": "VCsnBIaOgcQTNbxRjXVY0eVgQA3pKgV5br8o8Eos-UY1"
}
```

### SdsEvent/GetDeviceEvent

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### StandardProduct/GetExcelReport

- **Data Type**: dict
- **Item Count**: 3
- **Sample Data**:
```json
{
  "FileName": "file.xlsx",
  "Base64Content": "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",
  "MimeType": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
}
```

### StandardProduct/GetStandardProductsSummary

- **Data Type**: dict
- **Item Count**: 7
- **Sample Data**:
```json
{
  "TotalStandardProductDevices": 2567,
  "TotalStandardProducts": 206,
  "TotalNonStandardProductDevices": 737,
  "TotalNonStandardProducts": 84,
  "TotalAssociations": 1,
  "TotalAssociationsWorking": 0,
  "TotalProjectVolumes": 0
}
```

### StandardProduct/ListOperations

- **Data Type**: list
- **Item Count**: 1
- **Sample Data**:
```json
{
  "OperationDateUtc": "2025-06-04T20:41:17.183Z",
  "AccountId": "OHmM5QcXuvJWlOHvok_x6w2",
  "AccountName": "jez.slade@systeloa.com",
  "DealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
  "DealerCode": "NY06AGDWUQ",
  "DealerDescription": "SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE",
  "TotalDevices": 30,
  "TotalDevicesInSuccess": 30,
  "TotalDevicesInError": 0,
  "TotalDevicesInRollback": 0,
  "Status": 3,
  "ProductModelToStandardProductModelMap": [
    {
      "Product": {
        "Model": "HP LASERJET 200 COLOR M251NW",
        "Brand": "HP",
        "Id": "yy4x1RLPNTrN2sEeEbSC7Q2"
      },
      "StandardProduct": {
        "Model": "LaserJet Pro 200 M251nw",
        "Brand": "HP",
        "Id": "54JgfwEI0qSvkyM6odjYBA2"
      },
      "NewProduct": {
        "Model": "LaserJet Pro 200 M251nw",
        "Brand": "HP",
        "Id": "ub8moVHGz2Q7zZx45MRUBA2"
      },
      "Rollback": false
    },
    {
      "Product": {
        "Model": "HP LASERJET 400 M401DNE",
        "Brand": "HP",
        "Id": "NJKLi3b4SInth13Adf-mEA2"
      },
      "StandardProduct": {
        "Model": "LaserJet Pro 400 M401dne",
        "Brand": "HP",
        "Id": "wzpcFlA0JOQJrhPF2eHozw2"
      },
      "NewProduct": {
        "Model": "LaserJet Pro 400 M401dne",
        "Brand": "HP",
        "Id": "k_heFajRe_3E9nLI6IgcYg2"
      },
      "Rollback": false
    },
    {
      "Product": {
        "Model": "HP DESIGNJET T2500 POSTSCRIPT (36'' SIZED)",
        "Brand": "HP",
        "Id": "MOpKXAC-XLtWXPDKvoPeEQ2"
      },
      "StandardProduct": {
        "Model": "DesignJet T2500PS",
        "Brand": "HP",
        "Id": "mlp51mg9Ib9QyiXHrPp09w2"
      },
      "NewProduct": {
        "Model": "DesignJet T2500PS",
        "Brand": "HP",
        "Id": "4KY5DC8LfdkpYmqmVATCKQ2"
      },
      "Rollback": false
    },
    {
      "Product": {
        "Model": "HP COLOR LASERJET M651",
        "Brand": "HP",
        "Id": "xKqtrFR4QHdIZrtVh0WEUg2"
      },
      "StandardProduct": {
        "Model": "Color LaserJet Enterprise M651dn",
        "Brand": "HP",
        "Id": "Ak6gGHRZRCwdC01_X0ICHQ2"
      },
      "NewProduct": {
        "Model": "Color LaserJet Enterprise M651dn",
        "Brand": "HP",
        "Id": "sTFdbOX_ni5tx_-FJg4RMg2"
      },
      "Rollback": false
    },
    {
      "Product": {
        "Model": "HP DESIGNJET T795 44IN (44'' SIZED)",
        "Brand": "HP",
        "Id": "XIgrQe4sa8250ad9y-JxIw2"
      },
      "StandardProduct": {
        "Model": "DesignJet T795",
        "Brand": "HP",
        "Id": "MPYgZtTnzs-E7_-r0T1GrQ2"
      },
      "NewProduct": {
        "Model": "DesignJet T795",
        "Brand": "HP",
        "Id": "E03r29tAOtw5oE1-Pa0Cww2"
      },
      "Rollback": false
    },
    {
      "Product": {
        "Model": "HP COLOR LASERJET M750",
        "Brand": "HP",
        "Id": "JginEybEVio53JO35SZBEQ2"
      },
      "StandardProduct": {
        "Model": "Color LaserJet Enterprise M750xh",
        "Brand": "HP",
        "Id": "TyH7SYuiB6z27D6m--jEhw2"
      },
      "NewProduct": {
        "Model": "Color LaserJet Enterprise M750xh",
        "Brand": "HP",
        "Id": "Y4GH34O_321rxjSa9nWXyQ2"
      },
      "Rollback": false
    }
  ],
  "Id": "X8gEDKW5ROjlyasuInu8CLjLYAIbPlCvvyWiuBc7H8w1"
}
```

### StandardProduct/ListStandardProducts

- **Data Type**: list
- **Item Count**: 50
- **Sample Data**:
```json
{
  "Logo": null,
  "Color": 2,
  "FormatType": 3,
  "HasGapInfo": true,
  "ModelClean": "DCP1000",
  "Model": "DCP-1000",
  "Brand": "BROTHER",
  "Id": "AqnBSrlgNWm_Ospj_B8ATg2"
}
```

### TradingPartner/Get

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### TradingPartner/List

- **Data Type**: list
- **Item Count**: 0
- **Sample Data**:
```json
[]
```

### WhiteLabel/GetWhiteLabelCustomizationByUrl

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### azuread/GetCustomerAzureSettings

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### azuread/GetDealerAzureSettings

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### okta/GetCustomerOktaSettings

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

### okta/GetDealerOktaSettings

- **Data Type**: dict
- **Item Count**: 4
- **Sample Data**:
```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

## Failed Endpoints

### Account/GetPsk2faData

- **Error**: E00000: An error has occurred during your request. 940571a6-b93d-4882-8dbc-507c3ba8266e

### Account/GetPsk2faDataForAccount

- **Error**: E00000: An error has occurred during your request. 13e5cfe2-8a80-4de2-8116-4f20e65c2de6

### Account/GetPsk2faDataForProfile

- **Error**: E00000: An error has occurred during your request. 0ba78550-d854-48f1-a795-265afc967566

### AlertLimit/Device/Get

- **Error**: Access denied

### AlertLimit2/Customer/GetProductList

- **Error**: E00000: An error has occurred during your request. 

### AlertLimit2/Dealer/GetProductList

- **Error**: E00000: An error has occurred during your request. 

### AlertLimit2/Device/GetDefault

- **Error**: Device not found

### AlertLimit2/GetAllLimits

- **Error**: E00000: An error has occurred during your request. 

### Analytics/GetReportFileResult

- **Error**: Missing required query parameter: idReport

### Analytics/GetReportResult

- **Error**: Missing required query parameter: idReport

### ApiClient/Account/Get

- **Error**: Account not found

### ApiClient/Account/List

- **Error**: Dealer not found

### ApiClient/Get

- **Error**: E00000: An error has occurred during your request. b45ad2d0-a5b2-4e67-a17c-287750a6e6e7

### Billing/GetInvoiceCategories

- **Error**: Request failed

### Communication/GetPortalReleaseNotes

- **Error**: E00000: An error has occurred during your request. 

### Counter/Device/Export

- **Error**: Device not found

### Counter/Device/List

- **Error**: Device not found

### Counter/ListMaintenanceKitCounters

- **Error**: Device not found

### CustomField/Get

- **Error**: CustomField not found

### CustomerNotification/Get

- **Error**: Customer not found

### CustomerNotification/GetNotificationPlaceholders

- **Error**: E00000: An error has occurred during your request. 

### CustomerNotification/GetSampleNotification

- **Error**: E00000: An error has occurred during your request. 4deece23-d39c-45fc-b761-b5c50813491f

### Dealer/CounterBlend/Get

- **Error**: E00000: An error has occurred during your request. aeff38a6-34fd-4077-a89d-b6a8147f6b53

### Dealer/CounterBlend/Search

- **Error**: Brand is required

### Dealer/CounterBlendToStandard/Get

- **Error**: DealerCounterBlendToStandardCounter not found

### Dealer/CounterBlendToStandard/GetByDevice

- **Error**: Device not found

### Dealer/DistributorSettings/Get

- **Error**: Access denied

### Dealer/ExportDealerTagsHierarchy

- **Error**: E00000: An error has occurred during your request. 2128aaac-807f-4e9b-bdf5-a62ba8d573db

### DealerNotification/Get

- **Error**: E00000: An error has occurred during your request. 08188abc-cbab-4f37-921a-c6c392ee928b

### DealerNotification/GetNotificationPlaceholders

- **Error**: E00000: An error has occurred during your request. 

### DealerSupply/Get

- **Error**: Code is required

### DealerSupplySet/AssociateByDealerSupplySetAndRelativeProducts

- **Error**: E00000: An error has occurred during your request. 

### DealerSupplySet/CountDealerSupplySetAndDevicesPotentialAssociations

- **Error**: E00000: An error has occurred during your request. 

### DealerSupplySet/Get

- **Error**: Id not found

### Device/Deleted/List

- **Error**: CustomerCode field is required

### Device/ExplorerDataAffinities/List

- **Error**: Device not found

### Device/GetDeviceAdditionalInfos

- **Error**: Device not found

### Device/GetDeviceGapInfos

- **Error**: Device not found

### Device/GetLfpCounters

- **Error**: E00000: An error has occurred during your request. 0aaf56a8-f7fd-4a57-8798-ec2fe0fb3dc8

### Device/GetSuppliesDetails

- **Error**: Device not found

### Device/GetSuppliesDetailsInfo

- **Error**: Device not found

### Device/GetSuppliesDetailsSummary

- **Error**: Device not found

### Device/GetZebraSuppliesDetailsSummary

- **Error**: Device not found

### Device/MaintenanceAlerts/List

- **Error**: IdInstalledProduct  field is required

### Explorer/Cluster/Get

- **Error**: Explorer Cluster not found

### Explorer/Configuration/Get

- **Error**: Missing required query parameter: configurationId

### Explorer/Configuration/GetTestTableVersions

- **Error**: E00000: An error has occurred during your request. 8117edcf-b456-4813-b55d-a7f1d6e25e44

### Explorer/DataPings

- **Error**: Invalid JSON response from API

### Explorer/DownloadLogs

- **Error**: ExplorerData non found

### Explorer/ExplorerDataCommand/List

- **Error**: ExplorerData not found

### Explorer/ExplorerDataInfo/List

- **Error**: ExplorerData not found

### Explorer/GetClusterCounters

- **Error**: Customer not found

### Explorer/GetDca4Otp

- **Error**: E00000: An error has occurred during your request. 2c9814cd-40a2-407a-aa0e-cce72a4d2a5c

### Explorer/GetDcaCurrentVersion

- **Error**: Invalid code specified

### Explorer/GetEndpointsLink

- **Error**: Missing required query parameter: platform

### Explorer/GetExplorerSetupLink

- **Error**: Access denied

### Explorer/GetJamcSetupLink

- **Error**: CustomerCode not found or SDS not Enabled

### Explorer/RequestSendLogs

- **Error**: ExplorerData non found

### Explorer/Staging/List

- **Error**: Access denied

### Integrations/Get

- **Error**: Integration not found

### Integrations/GetJoinedDevices

- **Error**: E00000: An error has occurred during your request. 

### Integrations/GetLogisticPlaceholders

- **Error**: E00000: An error has occurred during your request. 

### Integrations/GetNew

- **Error**: E00000: An error has occurred during your request. bea71187-0922-4b36-a939-20d0ce04f7a2

### Integrations/eautomate/GetEAutomateLog

- **Error**: E00000: An error has occurred during your request. 

### Integrations/eautomate/runjoin

- **Error**: E00000: An error has occurred during your request. 

### Office/OfficeFloor/GetPin

- **Error**: Device not found

### Office/OfficeFloor/List

- **Error**: Office not found

### Orders/GetOrderLineStatuses

- **Error**: Invalid JSON response from API

### Product/Customer/List

- **Error**: DealerCode field is required; CustomerCode field is required

### Project/GetContractFile

- **Error**: Project not found

### Project/GetDetail

- **Error**: Project not found

### Role/Get

- **Error**: E00000: An error has occurred during your request. e6f71d56-86f7-474a-9973-d64f8822b615

### Role/GetAllCapabilities

- **Error**: Missing required query parameter: isForAccount

### SdsAction/GetDeviceAction

- **Error**: Sds service request not found

### SdsAction/GetDeviceActionsDashboard

- **Error**: E00000: An error has occurred during your request. 

### SdsConnector/GetConnectors

- **Error**: DealerId is required

### SdsConnector/GetJamcConnectors

- **Error**: E00000: An error has occurred during your request. 

### SdsConnector/GetLogs

- **Error**: E00000: An error has occurred during your request. 

### SdsConnector/GetWppConnectors

- **Error**: E00000: An error has occurred during your request. 

### SdsCustomer/GetAssessTemplate

- **Error**: E00000: An error has occurred during your request. 556d006c-5fa9-4478-a41d-2dfd6a9421ad

### SdsCustomer/GetCredential

- **Error**: E00000: An error has occurred during your request. 09f24c9e-1969-414b-ab56-f9af80235f17

### SdsCustomer/GetCustomerOperation

- **Error**: CustomerId is required

### SdsCustomer/GetCustomerOperations

- **Error**: DeviceId is required

### SdsCustomer/GetNewAssessTemplate

- **Error**: E00000: An error has occurred during your request. 

### SdsDevice/GetAssessTemplate

- **Error**: E00000: An error has occurred during your request. 9eaa4830-5fc7-40f7-9802-b822df0a5cb4

### SdsDevice/GetConfigItems

- **Error**: E00000: An error has occurred during your request. 855598c6-88df-4c94-8586-277e1af65fba

### SdsDevice/GetCounters

- **Error**: Device not found

### SdsDevice/GetDeviceRemoteEws

- **Error**: E00000: An error has occurred during your request. dcd96639-a839-4cf2-9b6b-37cafbb25126

### SdsDevice/GetOnDeviceServices

- **Error**: E00000: An error has occurred during your request. cb6c0e8e-9cd2-4c46-8ff7-7c5768be0dfc

### SdsDevice/GetSupplyDetails

- **Error**: SupplyType is required

### SdsDevice/GetZendeskTicketInfo

- **Error**: E00000: An error has occurred during your request. ab8a172b-7744-4f10-9460-b141891601fa

### SdsEvent/GetDeviceEvents

- **Error**: E00000: An error has occurred during your request. 

### SdsScan/ScanDevice

- **Error**: Device not found

### SdsScan/ScanImmediate

- **Error**: Device not found

### StandardProduct/GetOperation

- **Error**: E00000: An error has occurred during your request. 17883791-8910-47aa-9a93-7ee1917784c7

### StandardProduct/GetProductsToAssociate

- **Error**: PageNumber must be greater than 0; PageRows must be greater than 0; SortColumn cannot be null or empty

### StandardProduct/ListDevicesInOperation

- **Error**: OperationId field is required

### SupplyAlert/GetAvailableMaintenanceKitColors

- **Error**: E00000: An error has occurred during your request. 

### SupplyAlert/GetAvailableMaintenanceKitTypes

- **Error**: E00000: An error has occurred during your request. 

### SupplyAlert/GetAvailableSuppliesForADevice

- **Error**: Missing required query parameter: maintenanceKitTypeId

### TraceVolume/Get

- **Error**: Trace Volume configuration not found

### TraceVolume/List

- **Error**: Device not found

### WhiteLabel/Get

- **Error**: E00000: An error has occurred during your request. 4b93ad49-2d69-4daf-9ad5-c10b5903b6b5

### WhiteLabel/GetWhitelabelPlaceholders

- **Error**: E00000: An error has occurred during your request. 

