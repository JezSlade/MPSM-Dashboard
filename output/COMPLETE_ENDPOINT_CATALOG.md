# Complete MPSM API GET Endpoint Catalog

**Generated**: 2025-10-23 11:05:16

**Total Endpoints Tested**: 188
**Successful**: 87 (46.3%)
**Failed**: 101 (53.7%)

---

## Table of Contents

- [Successful Endpoints](#successful-endpoints)
- [Failed Endpoints](#failed-endpoints)
- [Dataset Structures](#dataset-structures)

---

## Successful Endpoints

### List Responses (41 endpoints)

| Endpoint | Items | Structure |
|----------|-------|------|-----|
| DealerSupplySet/ListDealerSupplySetFromStandardModels | 132 | Array |
| DealerSupply/List | 50 | Array |
| Device/Deleted/ListByDealer | 50 | Array |
| Explorer/Cluster/List | 50 | Array |
| Explorer/GetConnectors | 50 | Array |
| Explorer/GetExplorerDatas | 50 | Array |
| Product/Dealer/List | 50 | Array |
| Product/Dealer/ListModels | 50 | Array |
| Product/GetBrands | 50 | Array |
| Product/GetModels | 50 | Array |
| StandardProduct/ListStandardProducts | 50 | Array |
| Explorer/Cluster/AutoClusters | 34 | Array |
| Product/GetSnmpDiscoveryBrands | 31 | Array |
| AlertLimit2/Customer/GetDefault | 18 | Array |
| SdsAction/GetDeviceActions | 15 | Array |
| DealerNotification/GetNotificationPlaceholders | 13 | Array |
| Role/List | 12 | Array |
| AlertLimit2/Dealer/GetDefault | 9 | Array |
| Product/Dealer/ListBrands | 5 | Array |
| Dealer/CounterBlend/List | 4 | Array |
| DealerNotification/List | 4 | Array |
| SdsCustomer/GetAssessTemplates | 4 | Array |
| Product/Customer/List | 3 | Array |
| Integrations/GetJoinedCustomers | 2 | Array |
| ApiClient/List | 1 | Array |
| CustomerNotification/List | 1 | Array |
| Explorer/Configuration/List | 1 | Array |
| StandardProduct/ListOperations | 1 | Array |
| AlertLimit/Customer/Product/List | 0 | Array |
| AlertLimit2/Customer/GetProduct | 0 | Array |
| AlertLimit2/Dealer/GetProduct | 0 | Array |
| CustomField/List | 0 | Array |
| Dealer/CounterBlendToStandard/List | 0 | Array |
| DealerProduct/List | 0 | Array |
| DealerSupplyPriceListing/List | 0 | Array |
| DealerSupplySet/List | 0 | Array |
| Device/Deleted/List | 0 | Array |
| Explorer/GetConnectorEndpoints | 0 | Array |
| Explorer/License/List | 0 | Array |
| Integrations/List | 0 | Array |
| TradingPartner/List | 0 | Array |

### Object Responses (44 endpoints)

| Endpoint | Fields |
|----------|--------|
| Account/GetProfile | 27 |
| AlertLimit/Customer/Get | 12 |
| AlertLimit/Dealer/Get | 13 |
| Customer/Accessories/Get | 6 |
| Customer/AdvancedOptions/Get | 5 |
| Customer/AlertSettings/Get | 6 |
| Customer/CustomerServicesStatus/Get | 9 |
| Customer/EpsonSettings/Get | 5 |
| Customer/eXplorerSettings/Get | 2 |
| CustomerDashboard | 4 |
| CustomerDashboard/Pages | 5 |
| Dealer/AccountingSettings/Get | 9 |
| Dealer/AdvancedOptions/Get | 13 |
| Dealer/AlertLimitOptions/Get | 12 |
| Dealer/AlertSettings/Get | 6 |
| Dealer/Customizations/Get | 5 |
| Dealer/DealerServicesStatus/Get | 6 |
| Dealer/GetDealerHierarchy | 7 |
| Dealer/GetDealerTagsHierarchy | 4 |
| Dealer/Onboarding/Get | 2 |
| Dealer/RemoteOfflineCountersSettings/Get | 15 |
| Dealer/eXplorerSettings/Get | 13 |
| DealerNotification/GetSampleNotification | 4 |
| DealerNotification/Template/Get | 2 |
| DealerProduct/Get | 4 |
| DealerSupply/Count | 3 |
| DealerSupply/Export | 3 |
| DealerSupplyPriceListing/Get | 4 |
| DealerSupplySet/Count | 3 |
| DealerSupplySet/Export | 3 |
| DealerSupplySet/ExportExcel | 3 |
| Explorer/GetDcaReleaseNotes | 4 |
| Explorer/GetEndpointsLink | 3 |
| SdsConnector/GetConnector | 4 |
| SdsDevice/GetDevicesOperations | 5 |
| SdsEvent/GetDeviceEvent | 4 |
| StandardProduct/GetExcelReport | 3 |
| StandardProduct/GetStandardProductsSummary | 7 |
| TradingPartner/Get | 4 |
| WhiteLabel/GetWhiteLabelCustomizationByUrl | 4 |
| azuread/GetCustomerAzureSettings | 4 |
| azuread/GetDealerAzureSettings | 4 |
| okta/GetCustomerOktaSettings | 4 |
| okta/GetDealerOktaSettings | 4 |

### String Responses (2 endpoints)

- **Customer/EpsonUSBCustomerId/Get**: Returns string data
- **Explorer/V3/ReleaseNotes**: Returns string data

---

## Dataset Structures

Detailed structure and example values for each successful endpoint.

### Account/GetProfile

**Type**: `dict`

**Count**: 27 items

**Structure**:

```json
{
  "Nominative": "string (example: \"Jez Slade\")",
  "CreatedAt": "string (example: \"21/05/2024 20:44:41\")",
  "CreatedAtDate": "string (example: \"2024-05-21T20:44:41Z\")",
  "Email": "string (example: \"jez.slade@systeloa.com\")",
  "Token": "string (example: \"afb663c2-890d-4d89-97fc-57501bb3b06e\")",
  "Role": "string (example: \"DPU\")",
  "Capabilities": "array of integer (example: 115) (84 items)",
  "Language": "string (example: \"English\")",
  "ShortLanguage": "string (example: \"en-US\")",
  "IsActive": "boolean (example: True)",
  "Force2fa": "boolean (example: False)",
  "ForceSso": "boolean (example: False)",
  "Use2fa": "boolean (example: False)",
  "LastLoginAt": "string (example: \"2025-10-23T14:30:12.323Z\")",
  "IsDeleted": "boolean (example: False)",
  "ExcludeFromWarningNotifications": "boolean (example: True)",
  "EnabledNewDevicesNotification": "boolean (example: False)",
  "PreferredDealer": "null",
  "DefaultDealer": "null",
  "DefaultCustomer": "null",
  "Customers": "null",
  "Dealers": "null",
  "Tags": "null",
  "ReportingReports": "null",
  "EnablePasswordExpiration": "boolean (example: False)",
  "Name": "string (example: \"dashboard\")",
  "Id": "string (example: \"oE9k5vV9W-ccTsuPpMjyfQ2\")"
}
```

**Sample Data**:

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
  "LastLoginAt": "2025-10-23T14:30:12.323Z",
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

---

### AlertLimit/Customer/Get

**Type**: `dict`

**Count**: 12 items

**Structure**:

```json
{
  "Id": "null",
  "Product": "null",
  "BlackTonerLimit": "integer (example: 20)",
  "YellowTonerLimit": "integer (example: 15)",
  "CyanTonerLimit": "integer (example: 15)",
  "MagentaTonerLimit": "integer (example: 15)",
  "BlackPhotoLimit": "integer (example: 0)",
  "YellowPhotoLimit": "integer (example: 0)",
  "CyanPhotoLimit": "integer (example: 0)",
  "MagentaPhotoLimit": "integer (example: 0)",
  "MaintenanceKitLimit": "integer (example: 0)",
  "MaintenanceKits": "array of {'MaintenanceKitTypeId': 'integer (example: 4)', 'MaintenanceKitColorId': 'integer (example: 2)', 'Limit': 'integer (example: 2)'} (9 items)"
}
```

**Sample Data**:

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

---

### AlertLimit/Customer/Product/List

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### AlertLimit/Dealer/Get

**Type**: `dict`

**Count**: 13 items

**Structure**:

```json
{
  "DealerCode": "string (example: \"NY06AGDWUQ\")",
  "OverwriteExistingCustomersAlertLimit": "boolean (example: False)",
  "BlackTonerLimit": "integer (example: 10)",
  "YellowTonerLimit": "integer (example: 10)",
  "CyanTonerLimit": "integer (example: 10)",
  "MagentaTonerLimit": "integer (example: 10)",
  "BlackPhotoLimit": "integer (example: 10)",
  "YellowPhotoLimit": "integer (example: 10)",
  "CyanPhotoLimit": "integer (example: 10)",
  "MagentaPhotoLimit": "integer (example: 10)",
  "MaintenanceKitLimit": "integer (example: 10)",
  "MaintenanceKits": "array (empty)",
  "Id": "null"
}
```

**Sample Data**:

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

---

### AlertLimit2/Customer/GetDefault

**Type**: `list`

**Count**: 18 items

**Structure**:

```json
"array of {'IdCustomer': 'string (example: \"USlIvWCpo-sF9xTjf2Fvog2\")', 'AlertLimit': {'SupplyType': 'integer (example: 1)', 'ColorType': 'null', 'MaintenanceKitTypeId': 'null', 'MaintenanceKitColorId': 'null', 'AlertLimitType': 'integer (example: 1)', 'CreationAlertLimit': 'null', 'CreationAlertLimitStatus': 'integer (example: 3)', 'PostAlertNotification': 'null', 'PostAlertNotificationStatus': 'integer (example: 2)', 'LastUpdateUTC': 'string (example: \"2024-05-06T19:20:39Z\")', 'CreationAlertLimitProximity': 'null', 'CreationAlertLimitProximityStatus': 'integer (example: 2)'}} (18 items)"
```

**Sample Data**:

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

---

### AlertLimit2/Customer/GetProduct

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### AlertLimit2/Dealer/GetDefault

**Type**: `list`

**Count**: 9 items

**Structure**:

```json
"array of {'IdDealer': 'string (example: \"SZ13qRwU5GtFLj0i_CbEgQ2\")', 'AlertLimit': {'SupplyType': 'integer (example: 1)', 'ColorType': 'null', 'MaintenanceKitTypeId': 'null', 'MaintenanceKitColorId': 'null', 'AlertLimitType': 'integer (example: 1)', 'CreationAlertLimit': 'integer (example: 10)', 'CreationAlertLimitStatus': 'integer (example: 1)', 'PostAlertNotification': 'null', 'PostAlertNotificationStatus': 'integer (example: 2)', 'LastUpdateUTC': 'string (example: \"2022-03-11T14:50:05Z\")', 'CreationAlertLimitProximity': 'null', 'CreationAlertLimitProximityStatus': 'integer (example: 2)'}} (9 items)"
```

**Sample Data**:

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

---

### AlertLimit2/Dealer/GetProduct

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### ApiClient/List

**Type**: `list`

**Count**: 1 items

**Structure**:

```json
"array of {'Id': 'string (example: \"Ga-Mt3FvZ7F5Qwd1Nr_3Qg2\")', 'Name': 'string (example: \"dashboard\")', 'AppId': 'string (example: \"9AT9j4UoU2BgLEqmiYCz\")', 'AppSecret': 'string (example: \"ghWlqYK7lejAihT7I+9qEBMWrAFMg4IBH/anLEfV290=\")', 'ApplicationType': 'integer (example: 1)', 'IsActive': 'boolean (example: True)', 'RefreshTokenLifeTime': 'integer (example: 120)', 'AllowedOrigin': 'null', 'DeveloperEmail': 'string (example: \"jez.slade@systeloa.com\")', 'DealerCode': 'string (example: \"NY06AGDWUQ\")'} (1 items)"
```

**Sample Data**:

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

---

### CustomField/List

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### Customer/Accessories/Get

**Type**: `dict`

**Count**: 6 items

**Structure**:

```json
{
  "Exclusions": "string (example: \";CNC1N4602B;38-22-E2-8E-D6-81\r\n;CN96IDK0QR;80-E...\")",
  "Overrides": "null",
  "OutOfDateLimitDevices": "integer (example: 2)",
  "OutOfDateLimitConnectors": "integer (example: 2)",
  "IdOrderReplacementLogic": "null",
  "ExportPreferences": {
    "CsvDelimiter": "null",
    "CsvQualifier": "null",
    "BracketsInColumnName": "boolean (example: True)",
    "CsvDateTimeFormat": "null"
  }
}
```

**Sample Data**:

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

---

### Customer/AdvancedOptions/Get

**Type**: `dict`

**Count**: 5 items

**Structure**:

```json
{
  "ExportPreferences": {
    "CsvDelimiter": "null",
    "CsvQualifier": "null",
    "BracketsInColumnName": "boolean (example: True)",
    "CsvDateTimeFormat": "null"
  },
  "OutOfDateLimitDevices": "integer (example: 2)",
  "OutOfDateLimitConnectors": "integer (example: 2)",
  "Exclusions": "string (example: \";CNC1N4602B;38-22-E2-8E-D6-81\r\n;CN96IDK0QR;80-E...\")",
  "Overrides": "null"
}
```

**Sample Data**:

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

---

### Customer/AlertSettings/Get

**Type**: `dict`

**Count**: 6 items

**Structure**:

```json
{
  "IsTonerEnabled": "boolean (example: True)",
  "IsPhotoEnabled": "boolean (example: True)",
  "IsMaintKitEnabled": "boolean (example: True)",
  "IsWasteTonerBoxEnabled": "boolean (example: True)",
  "IsTransferKitEnabled": "boolean (example: True)",
  "CustomerCode": "string (example: \"S8COQ6NPQZ\")"
}
```

**Sample Data**:

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

---

### Customer/CustomerServicesStatus/Get

**Type**: `dict`

**Count**: 9 items

**Structure**:

```json
{
  "PrintreleafActive": "boolean (example: False)",
  "CanActivatePrintreleaf": "boolean (example: False)",
  "HpSdsActive": "boolean (example: True)",
  "CanActivateHpSds": "boolean (example: True)",
  "CanActivatePaperCut": "boolean (example: True)",
  "PaperCutActive": "boolean (example: False)",
  "RemoteWsActive": "boolean (example: True)",
  "CanActivateSharpFSS": "boolean (example: False)",
  "SharpFSSActive": "boolean (example: False)"
}
```

**Sample Data**:

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

---

### Customer/EpsonSettings/Get

**Type**: `dict`

**Count**: 5 items

**Structure**:

```json
{
  "EpsonERSCustomerId": "null",
  "EpsonUSBCustomerGuid": "null",
  "EpsonUSBCustomerDateFormat": "string (example: \"d/M/yyyy H:m\")",
  "EpsonUSBAvailableDateFormats": "array of {'Code': 'string (example: \"d/M/yyyy H:m\")', 'Description': 'string (example: \"d/M/yyyy H:m\")'} (4 items)",
  "CustomerCode": "string (example: \"S8COQ6NPQZ\")"
}
```

**Sample Data**:

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

---

### Customer/EpsonUSBCustomerId/Get

**Type**: `str`

**Structure**:

```json
"string (example: \"639b8c16-bf5a-4744-9c55-0d9370044a11\")"
```

**Sample Data**:

```json
"639b8c16-bf5a-4744-9c55-0d9370044a11"
```

---

### Customer/eXplorerSettings/Get

**Type**: `dict`

**Count**: 2 items

**Structure**:

```json
{
  "Dca4Stack": "integer (example: 0)",
  "CustomerCode": "string (example: \"S8COQ6NPQZ\")"
}
```

**Sample Data**:

```json
{
  "Dca4Stack": 0,
  "CustomerCode": "S8COQ6NPQZ"
}
```

---

### CustomerDashboard

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "SdsDashboard": {
    "TotalCustomers": "integer (example: 1)",
    "TotalDevices": "integer (example: 104)",
    "TotalConnectors": "integer (example: 1)",
    "CommonActionsToComplete": "integer (example: 3)",
    "PredictiveActionsToComplete": "integer (example: 0)",
    "NonCommunicatingDevices": "integer (example: 9)",
    "NonCommunicatingConnectors": "integer (example: 0)",
    "NonGenuineHPDevices": "integer (example: 0)",
    "EarlyReplacementDevices": "integer (example: 22)",
    "DevicesWithErrors": "integer (example: 0)",
    "DevicesWithWarnings": "integer (example: 0)",
    "ConnectorsWithErrors": "integer (example: 0)",
    "ConnectorsWithWarnings": "integer (example: 0)",
    "CustomerIsEnable": "boolean (example: True)"
  },
  "MpsDashboardCustomer": {
    "EnabledDevicesByContract": "integer (example: 0)",
    "TotalConnectors": "integer (example: 1)",
    "TotalManagedDevices": "integer (example: 104)",
    "HasSentExplorerEmail": "boolean (example: True)",
    "EmailExplorerInstallationSentAt": "string (example: \"2021-06-17T21:55:30Z\")",
    "ContactedDevices": "array of {'Key': '...', 'Value': '...'} (3 items)",
    "ToBeUpdated": "array of {'Type': '...', 'Id': '...', 'Count': '...'} (2 items)",
    "Books": "array of {'Key': '...', 'Value': '...'} (2 items)",
    "SupplyAlerts": "array of {'Key': '...', 'Value': '...'} (5 items)",
    "Warnings": "array (empty)",
    "SuppliesDelivery": "array (empty)"
  },
  "MustShowWelcome": "boolean (example: False)",
  "ExplorerSetupLink": "null"
}
```

**Sample Data**:

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

---

### CustomerDashboard/Pages

**Type**: `dict`

**Count**: 5 items

**Structure**:

```json
{
  "MonthlyMonoManaged": "integer (example: 294072)",
  "MonthlyMonoUnManaged": "integer (example: 3555)",
  "MonthlyColorManaged": "integer (example: 66123)",
  "MonthlyColorUnManaged": "integer (example: 0)",
  "AnomalousCounters": "integer (example: 0)"
}
```

**Sample Data**:

```json
{
  "MonthlyMonoManaged": 294072,
  "MonthlyMonoUnManaged": 3555,
  "MonthlyColorManaged": 66123,
  "MonthlyColorUnManaged": 0,
  "AnomalousCounters": 0
}
```

---

### CustomerNotification/List

**Type**: `list`

**Count**: 1 items

**Structure**:

```json
"array of {'IsActive': 'boolean (example: True)', 'ActivateOnCustomer': 'boolean (example: False)', 'LastNotificationSentAtUtc': 'null', 'NextNotificationSentAtUtc': 'null', 'NotificationType': 'integer (example: 2)', 'NotificationMode': 'integer (example: 0)', 'OwnerCode': 'null', 'Language': 'integer (example: 1)', 'DealerTemplateId': 'null', 'DealerTemplateName': 'null', 'Name': 'string (example: \"Moore County _Supply Level Alert\")', 'Id': 'string (example: \"UE70zGWBAvUoPyNwwN0NSw2\")'} (1 items)"
```

**Sample Data**:

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

---

### Dealer/AccountingSettings/Get

**Type**: `dict`

**Count**: 9 items

**Structure**:

```json
{
  "SDICode": "null",
  "SDIPEC": "null",
  "Abi": "null",
  "Cab": "null",
  "Vat": "string (example: \"MISSING\")",
  "IBAN": "null",
  "PaymentTermOverride": "null",
  "CountryCode": "null",
  "DealerCode": "string (example: \"NY06AGDWUQ\")"
}
```

**Sample Data**:

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

---

### Dealer/AdvancedOptions/Get

**Type**: `dict`

**Count**: 13 items

**Structure**:

```json
{
  "ShowRepeteadAlertManagement": "boolean (example: False)",
  "EnableRepeteadAlertManagementOnNewDevices": "boolean (example: False)",
  "DisableAlertGeneratorOnDeviceCreation": "boolean (example: False)",
  "DisableImporterMacAddressComparison": "boolean (example: False)",
  "notInheritSupplies": "boolean (example: False)",
  "notInheritProductReplacement": "boolean (example: False)",
  "AvailablePreferences": "array of string (example: \"SupplyList\") (45 items)",
  "SelectedPreferences": "array of string (example: \"SupplyList\") (9 items)",
  "ExportPreferences": {
    "CsvDelimiter": "null",
    "CsvQualifier": "null",
    "BracketsInColumnName": "boolean (example: True)",
    "CsvDateTimeFormat": "null"
  },
  "OutOfDateLimitDevices": "integer (example: 2)",
  "OutOfDateLimitConnectors": "integer (example: 2)",
  "Settings": {
    "DashboardPreferencesTask": "integer (example: 63)",
    "CustomerDashboardPreferencesTask": "integer (example: 7)",
    "DealerCreateNewCustomerSettings": {
      "ActivateHPSds": "boolean (example: False)",
      "InstallationProposalDefaultSelection": "boolean (example: False)",
      "InstallationProposalDefaultTemplate": "null",
      "ReadingProblemsDefaultSelection": "boolean (example: False)",
      "ReadingProblemsDefaultTemplate": "null",
      "ReadingProblemsExplorerDefaultSelection": "boolean (example: False)",
      "ReadingProblemsExplorerDefaultTemplate": "null",
      "DeliveryDefaultSelection": "boolean (example: False)",
      "DeliveryDefaultTemplate": "null"
    }
  },
  "DealerCode": "string (example: \"NY06AGDWUQ\")"
}
```

**Sample Data**:

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
  "OutOfDateLimitConnector
... (truncated)
```

---

### Dealer/AlertLimitOptions/Get

**Type**: `dict`

**Count**: 12 items

**Structure**:

```json
{
  "AlertLimitLevelEnabled": "boolean (example: True)",
  "AlertLimitResidualPagesEnabled": "boolean (example: False)",
  "AlertLimitResidualDaysEnabled": "boolean (example: False)",
  "PostAlertLevelEnabled": "boolean (example: False)",
  "PostAlertResidualPagesEnabled": "boolean (example: False)",
  "PostAlertResidualDaysEnabled": "boolean (example: False)",
  "FallbackLevelCreationAlertLimit": "integer (example: 10)",
  "EnableBatchAlertMode": "boolean (example: False)",
  "BatchAlertProximityType": "integer (example: 0)",
  "BatchAlertScheduledTimes": "null",
  "BatchAlertScheduledHistories": "null",
  "DealerCode": "string (example: \"NY06AGDWUQ\")"
}
```

**Sample Data**:

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

---

### Dealer/AlertSettings/Get

**Type**: `dict`

**Count**: 6 items

**Structure**:

```json
{
  "IsTonerEnabled": "boolean (example: True)",
  "IsPhotoEnabled": "boolean (example: True)",
  "IsMaintKitEnabled": "boolean (example: True)",
  "IsWasteTonerBoxEnabled": "boolean (example: True)",
  "IsTransferKitEnabled": "boolean (example: True)",
  "DealerCode": "string (example: \"NY06AGDWUQ\")"
}
```

**Sample Data**:

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

---

### Dealer/CounterBlend/List

**Type**: `list`

**Count**: 4 items

**Structure**:

```json
"array of {'Id': 'string (example: \"fJCJ0sBgLgdUd3PHDMK0iw2\")', 'Name': 'string (example: \"A4 Equivalent Mono Impressions\")', 'Sequence': 'integer (example: 3)', 'Brands': \"array of {'Brand': '...', 'Descriptions': '...', 'Details': '...'} (1 items)\", 'Source': 'integer (example: 1)', 'DealerCode': 'string (example: \"NY06AGDWUQ\")'} (4 items)"
```

**Sample Data**:

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

---

### Dealer/CounterBlendToStandard/List

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### Dealer/Customizations/Get

**Type**: `dict`

**Count**: 5 items

**Structure**:

```json
{
  "AlternativeAssetDescription": "null",
  "LogoUrl": "string (example: \"/media/Dealers/b97c1e73-b9ce-47c9-a077-680b8a2f...\")",
  "DeleteLogo": "boolean (example: False)",
  "LogoFile": {
    "FileName": "string (example: \"b97c1e73-b9ce-47c9-a077-680b8a2fd5e5.png\")",
    "Base64Content": "string (example: \"iVBORw0KGgoAAAANSUhEUgAAASwAAABACAYAAACgPErgAAA...\")",
    "MimeType": "string (example: \"image/png\")"
  },
  "DealerCode": "string (example: \"NY06AGDWUQ\")"
}
```

**Sample Data**:

```json
{
  "AlternativeAssetDescription": null,
  "LogoUrl": "/media/Dealers/b97c1e73-b9ce-47c9-a077-680b8a2fd5e5.png",
  "DeleteLogo": false,
  "LogoFile": {
    "FileName": "b97c1e73-b9ce-47c9-a077-680b8a2fd5e5.png",
    "Base64Content": "iVBORw0KGgoAAAANSUhEUgAAASwAAABACAYAAACgPErgAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBNYWNpbnRvc2giIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MkFDQjkzNjE2NTNGMTFFNUI3QTg5NEIwOEIzRTEyMzYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MkFDQjkzNjI2NTNGMTFFNUI3QTg5NEIwOEIzRTEyMzYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoyQUNCOTM1RjY1M0YxMUU1QjdBODk0QjA4QjNFMTIzNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoyQUNCOTM2MDY1M0YxMUU1QjdBODk0QjA4QjNFMTIzNiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PhRsFrAAADb1SURBVHja7H0JnBxF2f7b3XPtzOydPZPNfUJCohDuCMh9KSiXeCHgxekHiHjx51BQERU/RfFAhE9EIqBIiCQIBBIgGMlByLU5dnNudrP37tw9/a+35+lMb2/P7MzuzG4kW1C/zcz0UV1d9dTzPvXWW1LrTdcsIKLxIrtE1iifyalIWmcwJG9pXdRVGu9afK0qF0olC/yeyhmapkW0PN9eJEnkdSL/J91BikOmVW810r3femGw9/CJXIi/Gr7rFjkgcq/IcTr8koL68ONvHPXSiXoJ5Kj9FYl8v8gni/x1kZfk+bn4Pt9B2e8W+W0aTXlLDpG/KvLHRfbmt7nKRNEYqU2dXVJPZH7cq3VF4xFHQG2/3uXwXepy+EnT1OF45h8PBFhOAVg/vPuf2XbGiSIfI/KHRJ4tci3q1AAs7pB7RV4v8iqR14q81dJJRSXRWQA7PmaH6bdikU/CNdUsyxZCRzqQQXs4TuRxIkcyuDaXt13k10WO2VWlyJNFPlbko0WeKXKNyB4TYDGQ70K9vCPyeyI3Wq7Dx5+N+61EPaZK/B6uEdkt8hdFLjCVNdeJB5/LUDZCu3
... (truncated)
```

---

### Dealer/DealerServicesStatus/Get

**Type**: `dict`

**Count**: 6 items

**Structure**:

```json
{
  "PrintreleafActive": "boolean (example: False)",
  "CanActivatePrintreleaf": "boolean (example: True)",
  "ZzTonerActive": "boolean (example: False)",
  "CanActivateZzToner": "boolean (example: False)",
  "HpSdsActive": "boolean (example: True)",
  "CanActivateHpSds": "boolean (example: False)"
}
```

**Sample Data**:

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

---

### Dealer/GetDealerHierarchy

**Type**: `dict`

**Count**: 7 items

**Structure**:

```json
{
  "Children": "array (empty)",
  "Parents": "array (empty)",
  "LogoUrl": "string (example: \"/media/Dealers/b97c1e73-b9ce-47c9-a077-680b8a2f...\")",
  "AlternativeDealer": {
    "Code": "string (example: \"NHEZFOY36U\")",
    "Description": "string (example: \"SYSTEL BUSINESS EQUIPMENT\")",
    "Id": "string (example: \"bbFPVg7nGD6OhzSh3ACqjA2\")"
  },
  "Code": "string (example: \"NY06AGDWUQ\")",
  "Description": "string (example: \"SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE\")",
  "Id": "string (example: \"SZ13qRwU5GtFLj0i_CbEgQ2\")"
}
```

**Sample Data**:

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

---

### Dealer/GetDealerTagsHierarchy

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### Dealer/Onboarding/Get

**Type**: `dict`

**Count**: 2 items

**Structure**:

```json
{
  "DealerCode": "string (example: \"NY06AGDWUQ\")",
  "Steps": "array of {'Title': 'string (example: \"Dealer’s general information\")', 'Groups': 'array of ... (1 items)'} (10 items)"
}
```

**Sample Data**:

```json
{
  "DealerCode": "NY06AGDWUQ",
  "Steps": [
    {
      "Title": "Dealer’s general information",
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
                  "Key"
... (truncated)
```

---

### Dealer/RemoteOfflineCountersSettings/Get

**Type**: `dict`

**Count**: 15 items

**Structure**:

```json
{
  "IsActive": "boolean (example: False)",
  "IsTest": "boolean (example: False)",
  "DeviceWithNoCommunicationDays": "integer (example: 0)",
  "SendReminderDays": "null",
  "AutoCalculateCounters": "boolean (example: False)",
  "AutoCalculateMonthDay": "integer (example: 0)",
  "EmailTemplate": "null",
  "AccountName": "null",
  "EmailSubject": "null",
  "EmailBcc": "null",
  "EmailTemplateConfirm": "null",
  "EmailSubjectConfirm": "null",
  "EmailConfirmIsActive": "boolean (example: False)",
  "OnlyOfflineDevices": "boolean (example: False)",
  "DealerCode": "string (example: \"NY06AGDWUQ\")"
}
```

**Sample Data**:

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

---

### Dealer/eXplorerSettings/Get

**Type**: `dict`

**Count**: 13 items

**Structure**:

```json
{
  "ScansNumberMorning": "integer (example: 1)",
  "ScansNumberAfternoon": "integer (example: 1)",
  "ScansNumberEvening": "integer (example: 1)",
  "AutoEnableDefaultConfiguration": "boolean (example: True)",
  "AutomaticUpdate": "boolean (example: True)",
  "UseDca4AsDefault": "boolean (example: True)",
  "Dca4Stack": "integer (example: 0)",
  "ExplorerInterval": {
    "Discovery": "integer (example: 360)",
    "Meters": "integer (example: 240)",
    "Supplies": "integer (example: 60)",
    "Errors": "integer (example: 60)",
    "Attributes": "integer (example: 240)",
    "All": "integer (example: 60)"
  },
  "DefaultExplorerInterval": {
    "Discovery": "integer (example: 360)",
    "Meters": "integer (example: 240)",
    "Supplies": "integer (example: 60)",
    "Errors": "integer (example: 60)",
    "Attributes": "integer (example: 240)",
    "All": "integer (example: 60)"
  },
  "ExplorerWorkingDays": "array of {'DayOfWeek': 'integer (example: 1)', 'IsDayEnabled': 'boolean (example: True)', 'Range1': {'Active': '...', 'Values': '...'}, 'Range2': {'Active': '...', 'Values': '...'}, 'Range3': {'Active': '...', 'Values': '...'}} (7 items)",
  "AvailableSNMPDiscoveryBrands": "array of {'Key': 'string (example: \"19\")', 'Value': 'string (example: \"BARIX\")'} (33 items)",
  "PreferredSNMPDiscoveryBrands": "array (empty)",
  "DealerCode": "string (example: \"NY06AGDWUQ\")"
}
```

**Sample Data**:

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
       
... (truncated)
```

---

### DealerNotification/GetNotificationPlaceholders

**Type**: `list`

**Count**: 13 items

**Structure**:

```json
"array of {'Name': 'string (example: \"$Customer_Description$\")', 'Description': 'string (example: \"np_customer_description\")', 'GroupName': 'string (example: \"np_gr_customers\")', 'SampleValue': 'string (example: \"Customer_Description_SampleValue\")'} (13 items)"
```

**Sample Data**:

```json
{
  "Name": "$Customer_Description$",
  "Description": "np_customer_description",
  "GroupName": "np_gr_customers",
  "SampleValue": "Customer_Description_SampleValue"
}
```

---

### DealerNotification/GetSampleNotification

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### DealerNotification/List

**Type**: `list`

**Count**: 4 items

**Structure**:

```json
"array of {'IsActive': 'boolean (example: False)', 'ActivateOnCustomer': 'boolean (example: False)', 'LastNotificationSentAtUtc': 'null', 'NextNotificationSentAtUtc': 'null', 'NotificationType': 'integer (example: 7)', 'NotificationMode': 'integer (example: 0)', 'OwnerCode': 'null', 'Language': 'integer (example: 1)', 'DealerTemplateId': 'null', 'DealerTemplateName': 'null', 'Name': 'string (example: \"MPS Monitor Installation invitation\")', 'Id': 'string (example: \"SjTqVDMBfTcCAZHFUsSKeQ2\")'} (4 items)"
```

**Sample Data**:

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

---

### DealerNotification/Template/Get

**Type**: `dict`

**Count**: 2 items

**Structure**:

```json
{
  "EmailTemplateBase": "string (example: \"$BODY$\")",
  "DealerCode": "string (example: \"NY06AGDWUQ\")"
}
```

**Sample Data**:

```json
{
  "EmailTemplateBase": "$BODY$",
  "DealerCode": "NY06AGDWUQ"
}
```

---

### DealerProduct/Get

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### DealerProduct/List

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### DealerSupply/Count

**Type**: `dict`

**Count**: 3 items

**Structure**:

```json
{
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "string (example: \"1593\")"
}
```

**Sample Data**:

```json
{
  "IsValid": true,
  "Errors": [],
  "ReturnValue": "1593"
}
```

---

### DealerSupply/Export

**Type**: `dict`

**Count**: 3 items

**Structure**:

```json
{
  "FileName": "string (example: \"file.xlsx\")",
  "Base64Content": "string (example: \"UEsDBBQACAAIAEyEV1vzd1bCJwEAAM4EAAATAAAAW0NvbnR...\")",
  "MimeType": "string (example: \"application/vnd.openxmlformats-officedocument.s...\")"
}
```

**Sample Data**:

```json
{
  "FileName": "file.xlsx",
  "Base64Content": "UEsDBBQACAAIAEyEV1vzd1bCJwEAAM4EAAATAAAAW0NvbnRlbnRfVHlwZXNdLnhtbM2U3UoDMRCFX2XJrTRpq4hId3vhz6UK1gcYk9nd0PyRSWv79mazIlIq6EWhV5PkZM75GEIWy5011RYjae9qNuNTVqGTXmnX1ext9Ti5YRUlcAqMd1izPRJbNovVPiBVuddRzfqUwq0QJHu0QNwHdFlpfbSQ8jZ2IoBcQ4diPp1eC+ldQpcmafBgzeIeW9iYVN2N54N1zSAEoyWkjCWyGasedlkcKYe9+EPf1qkDmMkXCI9oyh3qdaCLw4Cs0pDwnAcTtcJ/Rfi21RKVlxubWziFiKCoR0zW8FK5Be3G0BeI6QlsdhU7Iz58XL97v+ZFOwnAEFHWv+UXkUQpsxOCUNobpGMUo3IuM5ifC8jluYBcnfJV9BBRvaaY/5/jj+PnhW8QUf6j5hNQSwcI83dWwicBAADOBAAAUEsDBBQACAAIAEyEV1uY2uuLrgAAACcBAAALAAAAX3JlbHMvLnJlbHONz8EOgjAMBuBXWXqXgQdjDIOLMeFq8AHmVgYB1mWbCm/vjmI8eGz69/vTsl7miT3Rh4GsgCLLgaFVpAdrBNzay+4ILERptZzIooAVA9RVecVJxnQS+sEFlgwbBPQxuhPnQfU4y5CRQ5s2HflZxjR6w51UozTI93l+4P7TgK3JGi3AN7oA1q4O/7Gp6waFZ1KPGW38UfGVSLL0BqOAZeIv8uOdaMwSCrwq+ebB6g1QSwcImNrri64AAAAnAQAAUEsDBBQACAAIAEyEV1sag/5NCAEAAPoBAAAPAAAAeGwvd29ya2Jvb2sueG1sjZDBTsQgEIZfhXB3qburMU3bPayXxjV6MN6RTluywBCgrj6bBx/JV5C2acTsQU/8w/zfzwxfH5/F7k0r8grOSzQlvVxllIAR2EjTlXQI7cUN3VXFCd3xBfFIotv43JW0D8HmjHnRg+Z+hRZM7LXoNA+xdB3DtpUCblEMGkxg6yy7Zg4UD/El30vr6Zz2nyxvHfDG9wBBqzlKc2loVYxTPUs4+Z8hx5KwqmBJb0KXkxiuoaQH6MA0lEx3dRO3p8TlMgpXN1Gz3/ZaW3Qhsa8T++bMfh/nC2C4EXAnw9O7BZ+wm4Td/sHuUaFL4W0CX00wW/YTXIlHR9pBqX2UD+aAfN5sdC0/VH0DUEsHCBqD/k0IAQAA+gEAAFBLAwQUAAgACABMhFdbLphGLewAAADbAwAAGgAAAHhsL19yZWxzL3dvcmtib29rLnhtbC5yZWxzvZPNasMwDIBfxei+OEm7MkbdXsag1617AGMrcWhiG0v7ydvPbKykUMIOJScjCX/6ENJ2/zX04gMTdcErqIoSBHoTbOdbBW/H57sHEMTaW90HjwpGJNjvti/Ya85fyHWRRGZ4UuCY46OUZBwOmooQ0edKE9KgOYeplVGbk25R1mW5kWnKgEumOFgF6WArEMcx4n/YoWk6g0/BvA/o+UoL+RnSiRwiZ6hOLbKCc4rkz1MVmQryukx9Sxnisc+zPJv8xnPtVwvPop6TWS8ss5qTuV9YZj0ns7npljid0L5yyvc4XZZp+k9GXhzl7htQSwcILphGLewAAADbAwAAUEsDBBQACAAIAEyEV1v6M5cUhQYAAPAcAAAYAAAAeGwvd29ya3NoZWV0cy9zaGVldDEueG1stVnrkto2GH0V15Ofa2xJvjJABuwl2SabJk0mnf70GrF41raoLfYynTxZf/SR+gqV5MuCLBrwNswCsnx0vvNJRxIr//PX35PXj3mm3eOySkkx1cHI0jVcJGSVFrdTfUfXhq9rFY2LVZyRAk/1J1zpr2eTB1LeVRuMqcbaF9VU31C6HZtmlWxwHlcjssUFu7MmZR5TdlnemtW2xPFKNMozE1qWa+ZxWug1w7g8hYOs12mCI5LsclzQmqT
... (truncated)
```

---

### DealerSupply/List

**Type**: `list`

**Count**: 50 items

**Structure**:

```json
"array of {'PartNumber': 'string (example: \"841925RV\")', 'DealerSKU': 'null', 'Description': 'string (example: \"Black Toner\")', 'DescriptionLocalized': 'string (example: \"Black Toner\")', 'SupplyType': 'integer (example: 3)', 'ColorType': 'integer (example: 2)', 'MaintenanceKitType': 'null', 'MaintenanceKitColor': 'null', 'Duration': 'integer (example: 12500)', 'IsInherited': 'boolean (example: True)', 'IsStandard': 'boolean (example: False)', 'Value': 'integer (example: 0)', 'Id': 'string (example: \"rzzZz2QesdGJacyHQ5YnBw2\")'} (50 items)"
```

**Sample Data**:

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

---

### DealerSupplyPriceListing/Get

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### DealerSupplyPriceListing/List

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### DealerSupplySet/Count

**Type**: `dict`

**Count**: 3 items

**Structure**:

```json
{
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "string (example: \"0\")"
}
```

**Sample Data**:

```json
{
  "IsValid": true,
  "Errors": [],
  "ReturnValue": "0"
}
```

---

### DealerSupplySet/Export

**Type**: `dict`

**Count**: 3 items

**Structure**:

```json
{
  "FileName": "string (example: \"export.json\")",
  "Base64Content": "string (example: \"W10=\")",
  "MimeType": "string (example: \"application/unknown\")"
}
```

**Sample Data**:

```json
{
  "FileName": "export.json",
  "Base64Content": "W10=",
  "MimeType": "application/unknown"
}
```

---

### DealerSupplySet/ExportExcel

**Type**: `dict`

**Count**: 3 items

**Structure**:

```json
{
  "FileName": "string (example: \"export-supplyset.xlsx\")",
  "Base64Content": "string (example: \"UEsDBBQACAAIAF2EV1vkSK2vGAEAADMDAAATAAAAW0NvbnR...\")",
  "MimeType": "string (example: \"application/vnd.openxmlformats-officedocument.s...\")"
}
```

**Sample Data**:

```json
{
  "FileName": "export-supplyset.xlsx",
  "Base64Content": "UEsDBBQACAAIAF2EV1vkSK2vGAEAADMDAAATAAAAW0NvbnRlbnRfVHlwZXNdLnhtbLWSz0oDMRDGX2XJVZq0HkSk2x6qHlWwPsCYzHZD84/MtLZvbzYrIqWCHnqaJN/M9/0IM18evGv2mMnG0IqZnIoGg47Ghk0r3taPk1vREEMw4GLAVhyRxHIxXx8TUlNmA7WiZ053SpHu0QPJmDAUpYvZA5dr3qgEegsbVNfT6Y3SMTAGnvDgIRbze+xg57hZje+DdSsgJWc1cMFSxUw0D4cijpTDXf1hbh/MCczkC0RmdLWHepvo6jSgqDQkPJePydbgvyJi11mNJuqdLyOSUkYw1COyd7JW6cGGMfQFMj+BL67q4NRHzNv3GLeyahcBGCLq+bf8KpKqZXZBEOKjQzpHMSqXjO4ho3nlXJb8PMHPhm8QVZd+8QlQSwcI5EitrxgBAAAzAwAAUEsDBBQACAAIAF2EV1uY2uuLrgAAACcBAAALAAAAX3JlbHMvLnJlbHONz8EOgjAMBuBXWXqXgQdjDIOLMeFq8AHmVgYB1mWbCm/vjmI8eGz69/vTsl7miT3Rh4GsgCLLgaFVpAdrBNzay+4ILERptZzIooAVA9RVecVJxnQS+sEFlgwbBPQxuhPnQfU4y5CRQ5s2HflZxjR6w51UozTI93l+4P7TgK3JGi3AN7oA1q4O/7Gp6waFZ1KPGW38UfGVSLL0BqOAZeIv8uOdaMwSCrwq+ebB6g1QSwcImNrri64AAAAnAQAAUEsDBBQACAAIAF2EV1v2ZXQl3AAAAFYBAAAPAAAAeGwvd29ya2Jvb2sueG1sjVAxbsMwDPyKwL2R2yEIDNsZ2iVAgRQI0F2VqViIJAqU3CRvy9An9QuVYxjt2IlHHu+O4Pftq9levBOfyMlSaOFxVYHAoKm34djCmM3DBrZdcyY+fRCdRNkOqeYWhpxjLWXSA3qVVhQxFM4Qe5VLy0dJxliNL6RHjyHLp6paS0ancklKg40JZrf/eKXIqPo0IGbvZiuvbICuma56t3hOv0dOrZBdI/9wd+lSRVAeWziMMbrrocxB3Oe7vnwABNe2AN71BU82i1Yrp99YmNG55wL34ZXUrJi2lvTuB1BLBwj2ZXQl3AAAAFYBAABQSwMEFAAIAAgAXYRXW4FikqLWAAAANAIAABoAAAB4bC9fcmVscy93b3JrYm9vay54bWwucmVsc62Rz2rDMAyHX8XovjjpYIxRt5cx6LV/HkDYShya2MbS2uXtazZWUihjh56EZPT9Pqzl+msc1Iky9zEYaKoaFAUbXR86A4f9x9MrKBYMDocYyMBEDOvVcksDSllh3ydWhRHYgBdJb1qz9TQiVzFRKC9tzCNKaXOnE9ojdqQXdf2i85wBt0y1cQbyxjWg9lOi/7Bj2/aW3qP9HCnInQh9jvnInkgKFHNHYuA6Yv1dmqpQQd+XWTxShmUayl9eTX76v+KfHxrvMZPbSS6HnlvMx78y+ubaqwtQSwcIgWKSotYAAAA0AgAAUEsDBBQACAAIAF2EV1sO/KHAFgEAAAkCAAAYAAAAeGwvd29ya3NoZWV0cy9zaGVldDEueG1sjZHBToQwEIZfpendLazRGFLYrJKN3owH77UM0Cx0SFvAd/PgI/kKDqCrGy57m+l8+ef/p18fn3L33jZsAOcN2pTHm4gzsBoLY6uU96G8uuO7TI7ojr4GCIxw6xOX8jqELhHC6xpa5TfYgaVZia5VgVpXCSxLoyFH3bdgg9hG0a1w0KhAq3xtOs8XtUu0fOdAFbOFtlmkWmUsz2RhSH1yzxyUKd/HSR5zJjI5w68GRv+vZlOSN8Tj1DwVKY9mVqzgw7z82bECStU34QXHRzBVHehIN3/6uQoqkw5HRieJyY6eij058LM24Z5ehyySYqA9+oe4XxPxOfGwJrbnRL4mrk+
... (truncated)
```

---

### DealerSupplySet/List

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### DealerSupplySet/ListDealerSupplySetFromStandardModels

**Type**: `list`

**Count**: 132 items

**Structure**:

```json
"array of {'DealerSupplySetDescription': 'null', 'PartNumbers': 'string (example: \"[24B6040] Black Drum,[24B6186] Black Toner,[40X...\")', 'GroupName': 'integer (example: -2131927886)', 'Details': \"array of {'Product': '...', 'NrOfMangedDevices': '...'} (1 items)\"} (132 items)"
```

**Sample Data**:

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

---

### Device/Deleted/List

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### Device/Deleted/ListByDealer

**Type**: `list`

**Count**: 50 items

**Structure**:

```json
"array of {'AssetNumber': 'string (example: \"FQ385\")', 'Account': 'string (example: \"cory.trifilo@systeloa.com\")', 'Customer': {'Code': 'string (example: \"S8COQ6NPQZ\")', 'Description': 'string (example: \"MOORE COUNTY\")', 'CountryCode': 'string (example: \"US\")', 'CountryName': 'string (example: \"United States\")', 'CountryIsEu': 'boolean (example: False)', 'ExternalIdentifier': 'null', 'Id': 'string (example: \"USlIvWCpo-sF9xTjf2Fvog2\")'}, 'LastPingUtc': 'null', 'DeletedOn': 'string (example: \"2021-08-25T14:19:45Z\")', 'SerialNumber': 'string (example: \"CNC1N4602B\")', 'MacAddress': 'string (example: \"38-22-E2-8E-D6-81\")', 'Firmware': 'null', 'SystemName': 'null', 'IpAddress': 'string (example: \"10.1.102.176\")', 'Product': {'Model': 'string (example: \"HP LASERJET FLOW MFP E82560\")', 'Brand': 'string (example: \"HP\")', 'Id': 'string (example: \"IikK1tMEdybFUKuoZWylOQ2\")'}, 'Id': 'string (example: \"xwjGf_RSY0ysuReN7Gzg7Q2\")'} (50 items)"
```

**Sample Data**:

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

---

### Explorer/Cluster/AutoClusters

**Type**: `list`

**Count**: 34 items

**Structure**:

```json
"array of {'Customer': 'null', 'Description': 'string (example: \"auto-1\")', 'AutoFixDayLimit': 'integer (example: 3)', 'ExplorerDatas': \"array of {'CreatedAt': '...', 'Identifier': '...', 'IP': '...', 'SystemName': '...', 'MakeServiceUpdate': '...', 'MakeExplorerUpdate': '...', 'DealerId': '...', 'DealerCode': '...', 'DealerDescription': '...', 'CustomerId': '...', 'CustomerCode': '...', 'CustomerDescription': '...', 'AutomaticUpdate': '...', 'BuildNumber': '...', 'BuildDate': '...', 'IsEmbedded': '...', 'TableVersion': '...', 'ServiceBuildNumber': '...', 'ServiceMajor': '...', 'ConfiguratorBuildNumber': '...', 'PollingInterval': '...', 'LastUpload': '...', 'Version': '...', 'Platform': '...', 'LastPing': '...', 'AgentIsRunning': '...', 'HasWarning': '...', 'PingIsOutOfDate': '...', 'DataIsOutOfDate': '...', 'NeverReceiveData': '...', 'NoValidConfiguration': '...', 'LastRun': '...', 'LastNetworkDiscovery': '...', 'TimeZone': '...', 'TimeZoneIana': '...', 'ExplorerDataJamExplorerJamVersion': '...', 'ExplorerDataJamVersion': '...', 'ExplorerDataJamConnectorStatus': '...', 'ExplorerDataJamLastContactTimeUtc': '...', 'ExplorerDataJamRegistrationKey': '...', 'ExplorerDataJamLastUploadUtc': '...', 'ExplorerDataJamCreatedAtUc': '...', 'ExplorerDataJamInstalledComputer': '...', 'ExplorerDataJamWebProxyAddress': '...', 'ExplorerDataJamWebProxyPort': '...', 'ExplorerDataJamConnectorId': '...', 'ExplorerCluster': '...', 'IsMasterInCluster': '...', 'ExplorerDataInfos': '...', 'Configurations': '...', 'ClusteredSlaves': '...', 'IsSelected': '...', 'LogIsReady': '...', 'SendLog': '...', 'LogFile': '...', 'IsV4': '...', 'Id': '...'} (1 items)\", 'Subnets': 'array of string (example: \"10.0.127\") (1 items)', 'Id': 'null'} (34 items)"
```

**Sample Data**:

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
      "LastPing": "2025-10-23T14:31:31.62Z",
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
      "ExplorerDataJamLastContactTimeUtc": "2025-10-23T14:32:00Z",
      "ExplorerDataJamRegistrationKey": "c66b1c22-57e9-4edb-8fc6-67c46194f35d",
      "ExplorerDataJamLastUploadUtc": "2025-10-22T22:38:00Z",
      "ExplorerDataJamCreatedAtUc": null,
      "ExplorerDataJamInstalledComputer": "MOCO-JETADMIN",
      "ExplorerDataJamWebProxyAddress": null,
      "ExplorerDataJamWebProxyPort": 0,
      "ExplorerDataJamConnectorI
... (truncated)
```

---

### Explorer/Cluster/List

**Type**: `list`

**Count**: 50 items

**Structure**:

```json
"array of {'Customer': {'Code': 'string (example: \"XPY90A5X7E\")', 'Description': 'string (example: \"TEST\")', 'CountryCode': 'string (example: \"ES\")', 'CountryName': 'string (example: \"Spain\")', 'CountryIsEu': 'boolean (example: True)', 'ExternalIdentifier': 'null', 'Id': 'string (example: \"xLU6dYTojIJx86T3v-cqqA2\")'}, 'Description': 'string (example: \"cluster Oficinas 17/1/2019\")', 'AutoFixDayLimit': 'integer (example: 1)', 'ExplorerDatas': \"array of {'CreatedAt': '...', 'Identifier': '...', 'IP': '...', 'SystemName': '...', 'MakeServiceUpdate': '...', 'MakeExplorerUpdate': '...', 'DealerId': '...', 'DealerCode': '...', 'DealerDescription': '...', 'CustomerId': '...', 'CustomerCode': '...', 'CustomerDescription': '...', 'AutomaticUpdate': '...', 'BuildNumber': '...', 'BuildDate': '...', 'IsEmbedded': '...', 'TableVersion': '...', 'ServiceBuildNumber': '...', 'ServiceMajor': '...', 'ConfiguratorBuildNumber': '...', 'PollingInterval': '...', 'LastUpload': '...', 'Version': '...', 'Platform': '...', 'LastPing': '...', 'AgentIsRunning': '...', 'HasWarning': '...', 'PingIsOutOfDate': '...', 'DataIsOutOfDate': '...', 'NeverReceiveData': '...', 'NoValidConfiguration': '...', 'LastRun': '...', 'LastNetworkDiscovery': '...', 'TimeZone': '...', 'TimeZoneIana': '...', 'ExplorerDataJamExplorerJamVersion': '...', 'ExplorerDataJamVersion': '...', 'ExplorerDataJamConnectorStatus': '...', 'ExplorerDataJamLastContactTimeUtc': '...', 'ExplorerDataJamRegistrationKey': '...', 'ExplorerDataJamLastUploadUtc': '...', 'ExplorerDataJamCreatedAtUc': '...', 'ExplorerDataJamInstalledComputer': '...', 'ExplorerDataJamWebProxyAddress': '...', 'ExplorerDataJamWebProxyPort': '...', 'ExplorerDataJamConnectorId': '...', 'ExplorerCluster': '...', 'IsMasterInCluster': '...', 'ExplorerDataInfos': '...', 'Configurations': '...', 'ClusteredSlaves': '...', 'IsSelected': '...', 'LogIsReady': '...', 'SendLog': '...', 'LogFile': '...', 'IsV4': '...', 'Id': '...'} (2 items)\", 'Subnets': 'array (empty)', 'Id': 'string (example: \"p2Ma_cczIS9SfIOA3DC5MA2\")'} (50 items)"
```

**Sample Data**:

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
      "ExplorerDataJamLastUplo
... (truncated)
```

---

### Explorer/Configuration/List

**Type**: `list`

**Count**: 1 items

**Structure**:

```json
"array of {'ExplorerSchedules': \"array of {'CustomerId': '...', 'ExplorerConfigurationId': '...', 'Occurence': '...', 'StartAt': '...', 'Days': '...', 'TimeZone': '...', 'LastRequest': '...', 'Id': '...'} (3 items)\", 'ExplorerSubnets': \"array of {'CustomerId': '...', 'OfficeId': '...', 'OfficeCode': '...', 'OfficeDescription': '...', 'ExplorerConfigurationId': '...', 'SubnetMask': '...', 'PartialWalkOID': '...', 'IpStart': '...', 'IpFrom': '...', 'IpEnd': '...', 'Id': '...'} (31 items)\", 'ExplorerHostnames': 'array (empty)', 'ExplorerWorkingDays': \"array of {'DayOfWeek': '...', 'IsDayEnabled': '...', 'Range1': '...', 'Range2': '...', 'Range3': '...'} (7 items)\", 'IdTicket': 'null', 'MaxProcess': 'integer (example: 1)', 'MaxThread': 'integer (example: 1)', 'MaxParallelOperations': 'integer (example: 10)', 'DisablePing': 'boolean (example: False)', 'ActivateExclusions': 'boolean (example: True)', 'UseEmbeddedOIDMap': 'boolean (example: False)', 'MpsUrl': 'null', 'DeviceDetectionOidArray': 'null', 'ActivateOverrides': 'boolean (example: False)', 'DisableWalks': 'boolean (example: False)', 'ScanPc': 'boolean (example: False)', 'VersionTest': 'null', 'UseHPSecureCounters': 'boolean (example: True)', 'Community': 'null', 'ScanTimeout': 'null', 'WalkTimeout': 'null', 'GetTimeout': 'null', 'PingTimeout': 'null', 'SendEnvironmentInfo': 'boolean (example: False)', 'ExplorerJamcParameters': 'null', 'WinceTimeoutSocket': 'integer (example: 5000)', 'WinceDeepSleepDisable': 'boolean (example: False)', 'UseSNMPv2Version': 'boolean (example: False)', 'UseHpProxy': 'boolean (example: False)', 'MacOsUseOtherSNMP': 'boolean (example: False)', 'ForceEncoding': 'null', 'UseKodakAlarisAgent': 'boolean (example: False)', 'ActivateZebraDetection': 'boolean (example: False)', 'DisableMessagePanelReadingsOutsideWorkingDays': 'boolean (example: False)', 'AllowUnicastAndBroadcast': 'boolean (example: False)', 'UseStandardWalk': 'boolean (example: False)', 'UseBulkWalkV3': 'boolean (example: True)', 'AlternativeSnmpPort': 'null', 'AlternativeDiscoveryPorts': 'null', 'ExplorerInterval': {'Discovery': 'null', 'Meters': 'null', 'Supplies': 'null', 'Errors': 'null', 'Attributes': 'null', 'All': 'null'}, 'DefaultExplorerInterval': {'Discovery': 'integer (example: 360)', 'Meters': 'integer (example: 240)', 'Supplies': 'integer (example: 60)', 'Errors': 'integer (example: 60)', 'Attributes': 'integer (example: 240)', 'All': 'integer (example: 60)'}, 'Description': 'string (example: \"DEFAULT\")', 'ExplorerDataSystemName': 'string (example: \"MOCO-JETADMIN\")', 'IsValidConfiguration': 'boolean (example: True)', 'IsEnable': 'boolean (example: True)', 'UseAutoAssign': 'boolean (example: False)', 'ExplorerDataId': 'string (example: \"AoibK16eMjhN9q0kkK-4Jw2\")', 'CustomerId': 'string (example: \"USlIvWCpo-sF9xTjf2Fvog2\")', 'Id': 'string (example: \"y1Cxc2Yeld3p5vlQU0tsYA2\")'} (1 items)"
```

**Sample Data**:

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
      "OfficeId
... (truncated)
```

---

### Explorer/GetConnectorEndpoints

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### Explorer/GetConnectors

**Type**: `list`

**Count**: 50 items

**Structure**:

```json
"array of {'CreatedAt': 'string (example: \"0001-01-01T00:00:00Z\")', 'Identifier': 'string (example: \"f1c75da3-ccf3-4111-907b-2b9d66603d56\")', 'IP': 'string (example: \"10.228.217.195;169.254.39.210\")', 'SystemName': 'string (example: \"TERRY\")', 'MakeServiceUpdate': 'boolean (example: False)', 'MakeExplorerUpdate': 'boolean (example: False)', 'DealerId': 'string (example: \"SZ13qRwU5GtFLj0i_CbEgQ2\")', 'DealerCode': 'string (example: \"NY06AGDWUQ\")', 'DealerDescription': 'string (example: \"SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE\")', 'CustomerId': 'string (example: \"IGqXz-HQDlikBkkKKpdJbw2\")', 'CustomerCode': 'string (example: \"KMZQLW08OV\")', 'CustomerDescription': 'string (example: \"102403 REED LALLIER CHEVROLET\")', 'AutomaticUpdate': 'boolean (example: True)', 'BuildNumber': 'string (example: \"4.3.4.0\")', 'BuildDate': 'string (example: \"2025-06-25T15:32:24Z\")', 'IsEmbedded': 'boolean (example: False)', 'TableVersion': 'null', 'ServiceBuildNumber': 'string (example: \"4.3.4.0\")', 'ServiceMajor': 'integer (example: 4)', 'ConfiguratorBuildNumber': 'null', 'PollingInterval': 'integer (example: 5)', 'LastUpload': 'string (example: \"2025-10-23T13:39:03Z\")', 'Version': 'string (example: \"4.3.4.0\")', 'Platform': 'string (example: \"Windows\")', 'LastPing': 'string (example: \"2025-10-23T14:33:15.57Z\")', 'AgentIsRunning': 'null', 'HasWarning': 'boolean (example: False)', 'PingIsOutOfDate': 'boolean (example: False)', 'DataIsOutOfDate': 'boolean (example: False)', 'NeverReceiveData': 'boolean (example: False)', 'NoValidConfiguration': 'boolean (example: False)', 'LastRun': 'null', 'LastNetworkDiscovery': 'string (example: \"2025-10-22T07:38:15.583Z\")', 'TimeZone': 'string (example: \"(UTC-05:00) Eastern Time (US & Canada)\")', 'TimeZoneIana': 'string (example: \"America/New_York\")', 'ExplorerDataJamExplorerJamVersion': 'null', 'ExplorerDataJamVersion': 'string (example: \"4.1.6712\")', 'ExplorerDataJamConnectorStatus': 'integer (example: 1)', 'ExplorerDataJamLastContactTimeUtc': 'string (example: \"2025-10-23T14:33:00Z\")', 'ExplorerDataJamRegistrationKey': 'string (example: \"bb09b6dc-a3a7-4c78-9703-ff59561af34e\")', 'ExplorerDataJamLastUploadUtc': 'string (example: \"2025-07-14T18:36:00Z\")', 'ExplorerDataJamCreatedAtUc': 'string (example: \"2025-07-14T18:35:38.407Z\")', 'ExplorerDataJamInstalledComputer': 'null', 'ExplorerDataJamWebProxyAddress': 'null', 'ExplorerDataJamWebProxyPort': 'integer (example: 0)', 'ExplorerDataJamConnectorId': 'integer (example: 1482000)', 'ExplorerCluster': 'null', 'IsMasterInCluster': 'boolean (example: False)', 'ExplorerDataInfos': 'array (empty)', 'Configurations': \"array of {'Description': '...', 'ExplorerDataSystemName': '...', 'IsValidConfiguration': '...', 'IsEnable': '...', 'UseAutoAssign': '...', 'ExplorerDataId': '...', 'CustomerId': '...', 'Id': '...'} (1 items)\", 'ClusteredSlaves': 'array (empty)', 'IsSelected': 'boolean (example: False)', 'LogIsReady': 'boolean (example: False)', 'SendLog': 'boolean (example: False)', 'LogFile': 'null', 'IsV4': 'boolean (example: True)', 'Id': 'string (example: \"ovb0HpH-pH6910MpA5MX2g2\")'} (50 items)"
```

**Sample Data**:

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
  "LastPing": "2025-10-23T14:33:15.57Z",
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
  "ExplorerDataJamLastContactTimeUtc": "2025-10-23T14:33:00Z",
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
... (truncated)
```

---

### Explorer/GetDcaReleaseNotes

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### Explorer/GetEndpointsLink

**Type**: `dict`

**Count**: 3 items

**Structure**:

```json
{
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "string (example: \"https://monitoring.mpsmonitor.com/Endpoint?plat...\")"
}
```

**Sample Data**:

```json
{
  "IsValid": true,
  "Errors": [],
  "ReturnValue": "https://monitoring.mpsmonitor.com/Endpoint?platform=windows&dealerid=SZ13qRwU5GtFLj0i_CbEgQ2"
}
```

---

### Explorer/GetExplorerDatas

**Type**: `list`

**Count**: 50 items

**Structure**:

```json
"array of {'CreatedAt': 'string (example: \"2021-06-22T12:18:11Z\")', 'Identifier': 'string (example: \"0af64ee7-4fd1-4906-accc-7b0575d8faed\")', 'IP': 'string (example: \"10.0.127.100\")', 'SystemName': 'string (example: \"MOCO-JETADMIN\")', 'MakeServiceUpdate': 'boolean (example: False)', 'MakeExplorerUpdate': 'boolean (example: False)', 'DealerId': 'string (example: \"SZ13qRwU5GtFLj0i_CbEgQ2\")', 'DealerCode': 'null', 'DealerDescription': 'null', 'CustomerId': 'string (example: \"USlIvWCpo-sF9xTjf2Fvog2\")', 'CustomerCode': 'string (example: \"S8COQ6NPQZ\")', 'CustomerDescription': 'string (example: \"MOORE COUNTY\")', 'AutomaticUpdate': 'boolean (example: True)', 'BuildNumber': 'string (example: \"3.9.4\")', 'BuildDate': 'string (example: \"2022-10-05T11:44:13Z\")', 'IsEmbedded': 'boolean (example: False)', 'TableVersion': 'integer (example: 13)', 'ServiceBuildNumber': 'string (example: \"3.9.4.17854\")', 'ServiceMajor': 'integer (example: 3)', 'ConfiguratorBuildNumber': 'null', 'PollingInterval': 'integer (example: 20)', 'LastUpload': 'string (example: \"2025-10-22T22:32:40.017Z\")', 'Version': 'string (example: \"3.9.4.13\")', 'Platform': 'string (example: \"Windows\")', 'LastPing': 'string (example: \"2025-10-23T14:31:31.62Z\")', 'AgentIsRunning': 'boolean (example: False)', 'HasWarning': 'boolean (example: False)', 'PingIsOutOfDate': 'boolean (example: False)', 'DataIsOutOfDate': 'boolean (example: False)', 'NeverReceiveData': 'boolean (example: False)', 'NoValidConfiguration': 'boolean (example: False)', 'LastRun': 'string (example: \"2023-01-09T23:18:00Z\")', 'LastNetworkDiscovery': 'string (example: \"2025-10-16T07:51:30.63Z\")', 'TimeZone': 'string (example: \"(UTC-05:00) Eastern Time (US & Canada)\")', 'TimeZoneIana': 'string (example: \"America/New_York\")', 'ExplorerDataJamExplorerJamVersion': 'string (example: \"3.9.4.27684 - 2022-10-17 12:03:21\")', 'ExplorerDataJamVersion': 'string (example: \"4.1.6712\")', 'ExplorerDataJamConnectorStatus': 'integer (example: 1)', 'ExplorerDataJamLastContactTimeUtc': 'string (example: \"2025-10-23T14:32:00Z\")', 'ExplorerDataJamRegistrationKey': 'string (example: \"c66b1c22-57e9-4edb-8fc6-67c46194f35d\")', 'ExplorerDataJamLastUploadUtc': 'string (example: \"2025-10-22T22:38:00Z\")', 'ExplorerDataJamCreatedAtUc': 'null', 'ExplorerDataJamInstalledComputer': 'string (example: \"MOCO-JETADMIN\")', 'ExplorerDataJamWebProxyAddress': 'null', 'ExplorerDataJamWebProxyPort': 'integer (example: 0)', 'ExplorerDataJamConnectorId': 'integer (example: 286148)', 'ExplorerCluster': 'null', 'IsMasterInCluster': 'boolean (example: False)', 'ExplorerDataInfos': \"array of {'Key': '...', 'Value': '...', 'LastUpdate': '...', 'Id': '...'} (32 items)\", 'Configurations': \"array of {'Description': '...', 'ExplorerDataSystemName': '...', 'IsValidConfiguration': '...', 'IsEnable': '...', 'UseAutoAssign': '...', 'ExplorerDataId': '...', 'CustomerId': '...', 'Id': '...'} (1 items)\", 'ClusteredSlaves': 'array (empty)', 'IsSelected': 'boolean (example: False)', 'LogIsReady': 'boolean (example: True)', 'SendLog': 'boolean (example: False)', 'LogFile': 'string (example: \"20240213T183814_0af64ee74fd14906accc7b0575d8fae...\")', 'IsV4': 'boolean (example: False)', 'Id': 'string (example: \"AoibK16eMjhN9q0kkK-4Jw2\")'} (50 items)"
```

**Sample Data**:

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
  "LastPing": "2025-10-23T14:31:31.62Z",
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
  "ExplorerDataJamLastContactTimeUtc": "2025-10-23T14:32:00Z",
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
      "Id": "n7DL4J5sVuwYlyeiAM-tRn5
... (truncated)
```

---

### Explorer/License/List

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### Explorer/V3/ReleaseNotes

**Type**: `str`

**Structure**:

```json
"string (example: \"eXplorer 3 Release Notes\r<br/>[3.9.4 16/09/2025...\")"
```

**Sample Data**:

```json
"eXplorer 3 Release Notes\r<br/>[3.9.4 16/09/2025]\r<br/>Release 13\r<br/>- Detailed counters detection correction for CANON<br/>- Serial Number, Detailed counters detection correction for CANON model PRO-6100<br/>- Standard Counters, Detailed counters detection correction for EPSON models EPSON ET-5855 SERIES, EPSON ET-16685 SERIES, EPSON ET-5185 SERIES, EPSON ET-2810 SERIES<br/>- Miantenance kit logic, Firmware, Standard Counters, Detailed counters detection correction for EPSON models EPSON L4260 SERIES, EPSON L6290 SERIES<br/>- Serial number, Miantenance kit logic, Firmware, Standard Counters, Detailed counters detection correction for EPSON models EPSON EM-C8101 SERIES, EPSON L4160 SERIES, EPSON L6570 SERIES<br/>- Serial number, Firmware, Detailed counters detection correction for EPSON model EPSON AM-M5500 SERIES<br/>- Miantenance kit logic, Firmware, Standard Counters, Detailed counters detection correction for EPSON models EPSON L4260 SERIES, EPSON L6290 SERIES<br/>- Serial Number detection correction for HP models HP OFFICEJET PRO 9720E SERIES, HP OFFICEJET PRO 8120E SERIES, HP LASERJET PRO MFP M26NW<br/>- Detailed counters detection for HP<br/>- Detailed counters detection correction for HP model HP COLOR LASERJET MFP M283FDW<br/>- Detailed counters detection correction for IBASE models OCE, PLOTWAVE3000 R2.1, OCE, PLOTWAVE5500*, OCE, PLOTWAVE7500*, OCE, PLOTWAVE3*<br/>- Standard counters detection correction for LEXMARK models CS963E, CS730DE, CS517DE, MC3224DWE<br/>- Detailed counters detection for TOSHIBA<br/>- Miantenance kit logic correction for XEROX model XEROX VERSALINK C625*\r<br/>\r<br/>[3.9.4 29/07/2025]\r<br/>Release 12\r<br/>- Detailed counters detection correction for KYOCERA models TASKALFA 308CI, TASKALFA 306CI, TASKALFA 307CI, 350CI<br/>- Standard counters detection correction for KYOCERA model TASKALFA 306CI<br/>- Detailed counters detection correction for LEXMARK\r<br/>\r<br/>[3.9.4 23/06/2025]\r<br/>Release 11\r<br/>- Firmware, Standard
... (truncated)
```

---

### Integrations/GetJoinedCustomers

**Type**: `list`

**Count**: 2 items

**Structure**:

```json
"array of {'Code': 'string (example: \"joined\")', 'Description': 'string (example: \"22\")'} (2 items)"
```

**Sample Data**:

```json
{
  "Code": "joined",
  "Description": "22"
}
```

---

### Integrations/List

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### Product/Customer/List

**Type**: `list`

**Count**: 3 items

**Structure**:

```json
"array of {'Logo': 'null', 'Color': 'integer (example: 3)', 'FormatType': 'integer (example: 2)', 'HasGapInfo': 'boolean (example: True)', 'ModelClean': 'null', 'Model': 'string (example: \"RICOH MP C3004\")', 'Brand': 'string (example: \"RICOH\")', 'Id': 'string (example: \"dN9CS6Qw_zx5e3ihkUjq-Q2\")'} (3 items)"
```

**Sample Data**:

```json
{
  "Logo": null,
  "Color": 3,
  "FormatType": 2,
  "HasGapInfo": true,
  "ModelClean": null,
  "Model": "RICOH MP C3004",
  "Brand": "RICOH",
  "Id": "dN9CS6Qw_zx5e3ihkUjq-Q2"
}
```

---

### Product/Dealer/List

**Type**: `list`

**Count**: 50 items

**Structure**:

```json
"array of {'Logo': 'null', 'Color': 'integer (example: 3)', 'FormatType': 'integer (example: 3)', 'HasGapInfo': 'boolean (example: False)', 'ModelClean': 'null', 'Model': 'string (example: \"HP COLOR LASERJET CP4520 SERIES\")', 'Brand': 'string (example: \"HP\")', 'Id': 'string (example: \"xoNXE5i-pYFyoinO7hid9g2\")'} (50 items)"
```

**Sample Data**:

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

---

### Product/Dealer/ListBrands

**Type**: `list`

**Count**: 5 items

**Structure**:

```json
"array of string (example: \"HP\") (5 items)"
```

**Sample Data**:

```json
"HP"
```

---

### Product/Dealer/ListModels

**Type**: `list`

**Count**: 50 items

**Structure**:

```json
"array of string (example: \"ACCURIOPRESS C3070\") (50 items)"
```

**Sample Data**:

```json
"ACCURIOPRESS C3070"
```

---

### Product/GetBrands

**Type**: `list`

**Count**: 50 items

**Structure**:

```json
"array of string (example: \"#VALUE!\") (50 items)"
```

**Sample Data**:

```json
"#VALUE!"
```

---

### Product/GetModels

**Type**: `list`

**Count**: 50 items

**Structure**:

```json
"array of string (example: \"\u0000\u0000\u0000\u0000\") (50 items)"
```

**Sample Data**:

```json
"\u0000\u0000\u0000\u0000"
```

---

### Product/GetSnmpDiscoveryBrands

**Type**: `list`

**Count**: 31 items

**Structure**:

```json
"array of string (example: \"BARIX\") (31 items)"
```

**Sample Data**:

```json
"BARIX"
```

---

### Role/List

**Type**: `list`

**Count**: 12 items

**Structure**:

```json
"array of {'Name': 'string (example: \"Installer\")', 'Description': 'string (example: \"Installer\")', 'Code': 'string (example: \"IN\")', 'Capabilities': \"array of {'Name': '...', 'CapabilityGroupName': '...', 'IsRolesForCustomer': '...', 'Id': '...'} (11 items)\", 'DealerCode': 'null', 'IsCustomRole': 'boolean (example: False)', 'IsShared': 'boolean (example: False)', 'IsSharedByCurrentDealer': 'boolean (example: False)', 'Force2fa': 'boolean (example: False)', 'ForceSso': 'boolean (example: False)', 'MaxLoginFailedAttempts': 'integer (example: 10)', 'DisableAfterInactivesDays': 'integer (example: 180)', 'Id': 'string (example: \"SnBx49i6Re2mpp7th_CgZw2\")'} (12 items)"
```

**Sample Data**:

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
  "ForceSso": fals
... (truncated)
```

---

### SdsAction/GetDeviceActions

**Type**: `list`

**Count**: 15 items

**Structure**:

```json
"array of {'CustomerId': 'string (example: \"USlIvWCpo-sF9xTjf2Fvog2\")', 'CustomerCode': 'string (example: \"S8COQ6NPQZ\")', 'CustomerDescription': 'string (example: \"MOORE COUNTY\")', 'DealerId': 'string (example: \"SZ13qRwU5GtFLj0i_CbEgQ2\")', 'DealerCode': 'string (example: \"NY06AGDWUQ\")', 'DealerDescription': 'string (example: \"SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE\")', 'InstalledProductSerialNumber': 'string (example: \"CNB3P1C8GW\")', 'Brand': 'string (example: \"HP\")', 'Model': 'string (example: \"HP COLOR LASERJET FLOW E78330\")', 'ActionJamId': 'string (example: \"fbfeb5b7-5409-4927-b826-36444487fbf9\")', 'DeviceId': 'string (example: \"bdCjDNK4L1k8-c1hG1psNQ2\")', 'Code': 'string (example: \"TriageFuser\")', 'EventCodeContext': 'string (example: \"50.*\")', 'ActionDateUtc': 'string (example: \"2024-05-06T12:14:00Z\")', 'Severity': 'integer (example: 2)', 'CurrentState': 'integer (example: 1)', 'StatusReports': 'null', 'HasGenuineHpCartridges': 'boolean (example: False)', 'Title': 'null', 'Description': 'null', 'MeanTimeToRepair': 'null', 'ServiceLevel': 'null', 'Tools': 'string (example: \"null\")', 'Parts': 'string (example: \"null\")', 'Link': 'null', 'TotalImpressions': 'integer (example: 109549)', 'FirmwareVersion': 'string (example: \"2502507_000153\")', 'ActionType': 'string (example: \"ExpertRules\")', 'PredictiveData': 'null', 'CustomerReportedProblemData': 'null', 'OfficeId': 'string (example: \"t7ocqp0rnEu39THPO8syCA2\")', 'OfficeCode': 'string (example: \"DEFAULT\")', 'OfficeDescription': 'string (example: \"DEFAULT\")', 'Id': 'string (example: \"afHJW0XD5DZDz9chXcpY5znmx5pwEnWGVne8QKhbV501\")'} (15 items)"
```

**Sample Data**:

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

---

### SdsConnector/GetConnector

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### SdsCustomer/GetAssessTemplates

**Type**: `list`

**Count**: 4 items

**Structure**:

```json
"array of {'CreatedAtUtc': 'string (example: \"2021-08-20T20:02:00Z\")', 'Name': 'string (example: \"CNC1P1M076 - 20210820200214\")', 'CustomerCode': 'null', 'Values': \"array of {'Label': '...', 'Name': '...', 'Value': '...', 'ValueType': '...', 'ControlType': '...', 'Constraints': '...', 'IsEssentialSecurityPolicy': '...', 'SettingGroup': '...', 'SettingSubGroup': '...', 'Description': '...', 'Order': '...', 'ExcludeFromApiCall': '...', 'LocalizedNotes': '...'} (57 items)\", 'Id': 'string (example: \"HxErZwQTrfwRLvu5XEfWQw2\")'} (4 items)"
```

**Sample Data**:

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
... (truncated)
```

---

### SdsDevice/GetDevicesOperations

**Type**: `dict`

**Count**: 5 items

**Structure**:

```json
{
  "TotalRows": "integer (example: 0)",
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "TotalRows": 0,
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### SdsEvent/GetDeviceEvent

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### StandardProduct/GetExcelReport

**Type**: `dict`

**Count**: 3 items

**Structure**:

```json
{
  "FileName": "string (example: \"file.xlsx\")",
  "Base64Content": "string (example: \"UEsDBBQACAAIACiFV1uyyeObJQEAALQDAAATAAAAW0NvbnR...\")",
  "MimeType": "string (example: \"application/vnd.openxmlformats-officedocument.s...\")"
}
```

**Sample Data**:

```json
{
  "FileName": "file.xlsx",
  "Base64Content": "UEsDBBQACAAIACiFV1uyyeObJQEAALQDAAATAAAAW0NvbnRlbnRfVHlwZXNdLnhtbLWTTU/DMAyG/0qVK1qzcUAIrduBjyMgMX6Al7httHwp9sb270lThNA0JDj05Div7edNJC/XR2erAyYywTdiUc9FhV4FbXzXiPfN0+xWVMTgNdjgsREnJLFeLTeniFTlXk+N6JnjnZSkenRAdYjos9KG5IBzmjoZQe2gQ3k9n99IFTyj5xkPM8Rq+YAt7C1X9+P9MLoREKM1CjjbknmYqB6PWRxdDrn8Q9/B6zMzsy8jdUJbaqg3ka7OAVmlgfCSPyYZjf9ChLY1CnVQe5dbaooJQVOPyM7WJdYOjB+hr5D4GVyeKo9WfoS024awq4s2iYEBUc6/8YtIsoTFhEaITxbpkotRmRDNsLV4iVwEGsOkb+8hoX7jlLfs8hf8LPg2IsvWrT4BUEsHCLLJ45slAQAAtAMAAFBLAwQUAAgACAAohVdbmNrri64AAAAnAQAACwAAAF9yZWxzLy5yZWxzjc/BDoIwDAbgV1l6l4EHYwyDizHhavAB5lYGAdZlmwpv745iPHhs+vf707Je5ok90YeBrIAiy4GhVaQHawTc2svuCCxEabWcyKKAFQPUVXnFScZ0EvrBBZYMGwT0MboT50H1OMuQkUObNh35WcY0esOdVKM0yPd5fuD+04CtyRotwDe6ANauDv+xqesGhWdSjxlt/FHxlUiy9AajgGXiL/LjnWjMEgq8KvnmweoNUEsHCJja64uuAAAAJwEAAFBLAwQUAAgACAAohVdbfWYQMNcAAABVAQAADwAAAHhsL3dvcmtib29rLnhtbI2QsU7EMAyGXyXyzqUwnFDV9gZYTkIHE3tInWt0SVw5Kce7MfBIvAJuqwpGJn+W/f+/5e/Pr+bwEYN6R86eUgu3uwoUJku9T+cWpuJu7uHQNVfiyxvRRcl2yjW3MJQy1lpnO2A0eUcjJpk54miKtHzW5Jy3+Eh2ipiKvquqvWYMpkhSHvyYYXX7j1ceGU2fB8QSw2oVjU/QNfNVrx6v+ffIuVW6a/Sf2SLdqkomYgsLnwRBLXjs5QGguPYCfOyFZ5dNak2wL6zcFMKD4HN6IrMq5q0tvPsBUEsHCH1mEDDXAAAAVQEAAFBLAwQUAAgACAAohVdbgWKSotYAAAA0AgAAGgAAAHhsL19yZWxzL3dvcmtib29rLnhtbC5yZWxzrZHPasMwDIdfxei+OOlgjFG3lzHotX8eQNhKHJrYxtLa5e1rNlZSKGOHnoRk9P0+rOX6axzUiTL3MRhoqhoUBRtdHzoDh/3H0ysoFgwOhxjIwEQM69VySwNKWWHfJ1aFEdiAF0lvWrP1NCJXMVEoL23MI0ppc6cT2iN2pBd1/aLznAG3TLVxBvLGNaD2U6L/sGPb9pbeo/0cKcidCH2O+cieSAoUc0di4Dpi/V2aqlBB35dZPFKGZRrKX15Nfvq/4p8fGu8xk9tJLoeeW8zHvzL65tqrC1BLBwiBYpKi1gAAADQCAABQSwMEFAAIAAgAKIVXW2FDohI1AgAAVgYAABgAAAB4bC93b3Jrc2hlZXRzL3NoZWV0MS54bWyNlctymzAUQH9Fo33RA6GHBzuT1nWTZpPpol0TIxsmgDySbKff1kU/qb9QgYkNQ6fDBoR1dHR1ryT/+fU7vXurK3DS1pWmWUISYQh0szV52eyX8Oh3HyS8W6VnY19dobUHAW/cwi5h4f1hgZDbFrrOXGQOugl9O2PrzIdPu0dmtyu3em22x1o3HlGMObK6ynyYyhXlwcGLbY7LHazO8i6Eurqo6qxs4CrNy2BvowdW75bwniyeCARolXbw91Kf3aAN2pW8GPPafjzmS4g7Fk3gTTf5swW53mXHyn8z5wdd7gsfkpR0Y7amct0T1GWbOgjq7K17n8vcF6HFI65kojgO4Qr
... (truncated)
```

---

### StandardProduct/GetStandardProductsSummary

**Type**: `dict`

**Count**: 7 items

**Structure**:

```json
{
  "TotalStandardProductDevices": "integer (example: 2567)",
  "TotalStandardProducts": "integer (example: 206)",
  "TotalNonStandardProductDevices": "integer (example: 737)",
  "TotalNonStandardProducts": "integer (example: 84)",
  "TotalAssociations": "integer (example: 1)",
  "TotalAssociationsWorking": "integer (example: 0)",
  "TotalProjectVolumes": "integer (example: 0)"
}
```

**Sample Data**:

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

---

### StandardProduct/ListOperations

**Type**: `list`

**Count**: 1 items

**Structure**:

```json
"array of {'OperationDateUtc': 'string (example: \"2025-06-04T20:41:17.183Z\")', 'AccountId': 'string (example: \"OHmM5QcXuvJWlOHvok_x6w2\")', 'AccountName': 'string (example: \"jez.slade@systeloa.com\")', 'DealerId': 'string (example: \"SZ13qRwU5GtFLj0i_CbEgQ2\")', 'DealerCode': 'string (example: \"NY06AGDWUQ\")', 'DealerDescription': 'string (example: \"SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE\")', 'TotalDevices': 'integer (example: 30)', 'TotalDevicesInSuccess': 'integer (example: 30)', 'TotalDevicesInError': 'integer (example: 0)', 'TotalDevicesInRollback': 'integer (example: 0)', 'Status': 'integer (example: 3)', 'ProductModelToStandardProductModelMap': \"array of {'Product': '...', 'StandardProduct': '...', 'NewProduct': '...', 'Rollback': '...'} (6 items)\", 'Id': 'string (example: \"X8gEDKW5ROjlyasuInu8CLjLYAIbPlCvvyWiuBc7H8w1\")'} (1 items)"
```

**Sample Data**:

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
        "Mode
... (truncated)
```

---

### StandardProduct/ListStandardProducts

**Type**: `list`

**Count**: 50 items

**Structure**:

```json
"array of {'Logo': 'null', 'Color': 'integer (example: 2)', 'FormatType': 'integer (example: 3)', 'HasGapInfo': 'boolean (example: True)', 'ModelClean': 'string (example: \"DCP1000\")', 'Model': 'string (example: \"DCP-1000\")', 'Brand': 'string (example: \"BROTHER\")', 'Id': 'string (example: \"AqnBSrlgNWm_Ospj_B8ATg2\")'} (50 items)"
```

**Sample Data**:

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

---

### TradingPartner/Get

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### TradingPartner/List

**Type**: `list`

**Count**: 0 items

**Structure**:

```json
"array (empty)"
```

---

### WhiteLabel/GetWhiteLabelCustomizationByUrl

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### azuread/GetCustomerAzureSettings

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### azuread/GetDealerAzureSettings

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### okta/GetCustomerOktaSettings

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

### okta/GetDealerOktaSettings

**Type**: `dict`

**Count**: 4 items

**Structure**:

```json
{
  "Result": "null",
  "IsValid": "boolean (example: True)",
  "Errors": "array (empty)",
  "ReturnValue": "null"
}
```

**Sample Data**:

```json
{
  "Result": null,
  "IsValid": true,
  "Errors": [],
  "ReturnValue": null
}
```

---

## Failed Endpoints

The following 101 endpoints returned errors:

### Error Summary

- **E00000: An error has occurred during your request. **: 20 endpoints
- **Device not found**: 17 endpoints
- **Access denied**: 4 endpoints
- **Missing required query parameter: idReport**: 2 endpoints
- **Customer not found**: 2 endpoints
- **Invalid JSON response from API**: 2 endpoints
- **ExplorerData non found**: 2 endpoints
- **ExplorerData not found**: 2 endpoints
- **Project not found**: 2 endpoints
- **DeviceId is required**: 2 endpoints
- **E00000: An error has occurred during your request. 572f6b07-6c91-4ab8-9247-58179639612a**: 1 endpoints
- **E00000: An error has occurred during your request. 4f4c60b6-a71b-49bb-9367-bb31fec265fc**: 1 endpoints
- **E00000: An error has occurred during your request. 2aba93f6-a2d9-4fce-9847-420c6670ff22**: 1 endpoints
- **Account not found**: 1 endpoints
- **Dealer not found**: 1 endpoints
- **E00000: An error has occurred during your request. 3646b738-28bc-45e5-8e90-8804e35b1528**: 1 endpoints
- **Request failed**: 1 endpoints
- **CustomField not found**: 1 endpoints
- **E00000: An error has occurred during your request. b4c2b7f2-ed3a-4b85-a57c-90281a32cc59**: 1 endpoints
- **E00000: An error has occurred during your request. d8c1b95f-1c33-47fa-a35b-0912b219ec33**: 1 endpoints
- **Brand is required**: 1 endpoints
- **DealerCounterBlendToStandardCounter not found**: 1 endpoints
- **E00000: An error has occurred during your request. 8ac3859b-d7f8-4a7f-b1ec-2f399e2cc882**: 1 endpoints
- **E00000: An error has occurred during your request. 7f41e10c-dd04-4773-841b-9dfe090a8d44**: 1 endpoints
- **Code is required**: 1 endpoints
- **Id not found**: 1 endpoints
- **E00000: An error has occurred during your request. 336f4dc7-e703-40a8-81bc-b5d907811534**: 1 endpoints
- **IdInstalledProduct  field is required**: 1 endpoints
- **Explorer Cluster not found**: 1 endpoints
- **Missing required query parameter: configurationId**: 1 endpoints
- **E00000: An error has occurred during your request. 222f097d-ed04-4a9f-b4f4-0796f89cd9b0**: 1 endpoints
- **E00000: An error has occurred during your request. 3aee43ce-c4b9-45ee-9c18-311fe50158a0**: 1 endpoints
- **Invalid code specified**: 1 endpoints
- **CustomerCode not found or SDS not Enabled**: 1 endpoints
- **Integration not found**: 1 endpoints
- **E00000: An error has occurred during your request. 42f0041b-7c66-454b-92be-f73d59b9f7be**: 1 endpoints
- **Office not found**: 1 endpoints
- **E00000: An error has occurred during your request. 82aad02f-14ba-44d5-a7e0-a00f239e7a5a**: 1 endpoints
- **Missing required query parameter: isForAccount**: 1 endpoints
- **Sds service request not found**: 1 endpoints
- **DealerId is required**: 1 endpoints
- **E00000: An error has occurred during your request. 68a6c460-6d90-49b3-b803-46cd8ef7a731**: 1 endpoints
- **E00000: An error has occurred during your request. 19afb922-583f-47c3-9424-0ed28acd0ccc**: 1 endpoints
- **CustomerId is required**: 1 endpoints
- **E00000: An error has occurred during your request. 5cd22dfc-d9de-4657-87c4-ea47feea04c5**: 1 endpoints
- **E00000: An error has occurred during your request. 01bcd6f2-e7fa-4e3f-801d-da46167eac22**: 1 endpoints
- **E00000: An error has occurred during your request. f49c62c7-27a0-496d-940e-1e433959ccf2**: 1 endpoints
- **E00000: An error has occurred during your request. 6d8880f4-fb51-48ee-b6d1-681c1ec5f3f8**: 1 endpoints
- **SupplyType is required**: 1 endpoints
- **E00000: An error has occurred during your request. 2ec304eb-0c3f-4a2d-9ba7-f41aa8f1f889**: 1 endpoints
- **E00000: An error has occurred during your request. 8e63e19a-d971-41da-a55e-0b3856ab83dc**: 1 endpoints
- **PageNumber must be greater than 0; PageRows must be greater than 0; SortColumn cannot be null or empty**: 1 endpoints
- **OperationId field is required**: 1 endpoints
- **Missing required query parameter: maintenanceKitTypeId**: 1 endpoints
- **Trace Volume configuration not found**: 1 endpoints
- **E00000: An error has occurred during your request. d6da72b3-a5ba-42c8-a372-0565dcc494f3**: 1 endpoints

### Failed Endpoint List

| Endpoint | Error |
|----------|-------|
| Account/GetPsk2faData | E00000: An error has occurred during your request. 572f6b... |
| Account/GetPsk2faDataForAccount | E00000: An error has occurred during your request. 4f4c60... |
| Account/GetPsk2faDataForProfile | E00000: An error has occurred during your request. 2aba93... |
| AlertLimit/Device/Get | Access denied |
| AlertLimit2/Customer/GetProductList | E00000: An error has occurred during your request.  |
| AlertLimit2/Dealer/GetProductList | E00000: An error has occurred during your request.  |
| AlertLimit2/Device/GetDefault | Device not found |
| AlertLimit2/GetAllLimits | E00000: An error has occurred during your request.  |
| Analytics/GetReportFileResult | Missing required query parameter: idReport |
| Analytics/GetReportResult | Missing required query parameter: idReport |
| ApiClient/Account/Get | Account not found |
| ApiClient/Account/List | Dealer not found |
| ApiClient/Get | E00000: An error has occurred during your request. 3646b7... |
| Billing/GetInvoiceCategories | Request failed |
| Communication/GetPortalReleaseNotes | E00000: An error has occurred during your request.  |
| Counter/Device/Export | Device not found |
| Counter/Device/List | Device not found |
| Counter/ListMaintenanceKitCounters | Device not found |
| CustomField/Get | CustomField not found |
| CustomerNotification/Get | Customer not found |
| CustomerNotification/GetNotificationPlaceholders | E00000: An error has occurred during your request.  |
| CustomerNotification/GetSampleNotification | E00000: An error has occurred during your request. b4c2b7... |
| Dealer/CounterBlend/Get | E00000: An error has occurred during your request. d8c1b9... |
| Dealer/CounterBlend/Search | Brand is required |
| Dealer/CounterBlendToStandard/Get | DealerCounterBlendToStandardCounter not found |
| Dealer/CounterBlendToStandard/GetByDevice | Device not found |
| Dealer/DistributorSettings/Get | Access denied |
| Dealer/ExportDealerTagsHierarchy | E00000: An error has occurred during your request. 8ac385... |
| DealerNotification/Get | E00000: An error has occurred during your request. 7f41e1... |
| DealerSupply/Get | Code is required |
| DealerSupplySet/AssociateByDealerSupplySetAndRelativeProducts | E00000: An error has occurred during your request.  |
| DealerSupplySet/CountDealerSupplySetAndDevicesPotentialAssociations | E00000: An error has occurred during your request.  |
| DealerSupplySet/Get | Id not found |
| Device/ExplorerDataAffinities/List | Device not found |
| Device/GetDeviceAdditionalInfos | Device not found |
| Device/GetDeviceGapInfos | Device not found |
| Device/GetLfpCounters | E00000: An error has occurred during your request. 336f4d... |
| Device/GetSuppliesDetails | Device not found |
| Device/GetSuppliesDetailsInfo | Device not found |
| Device/GetSuppliesDetailsSummary | Device not found |
| Device/GetZebraSuppliesDetailsSummary | Device not found |
| Device/MaintenanceAlerts/List | IdInstalledProduct  field is required |
| Explorer/Cluster/Get | Explorer Cluster not found |
| Explorer/Configuration/Get | Missing required query parameter: configurationId |
| Explorer/Configuration/GetTestTableVersions | E00000: An error has occurred during your request. 222f09... |
| Explorer/DataPings | Invalid JSON response from API |
| Explorer/DownloadLogs | ExplorerData non found |
| Explorer/ExplorerDataCommand/List | ExplorerData not found |
| Explorer/ExplorerDataInfo/List | ExplorerData not found |
| Explorer/GetClusterCounters | Customer not found |
| Explorer/GetDca4Otp | E00000: An error has occurred during your request. 3aee43... |
| Explorer/GetDcaCurrentVersion | Invalid code specified |
| Explorer/GetExplorerSetupLink | Access denied |
| Explorer/GetJamcSetupLink | CustomerCode not found or SDS not Enabled |
| Explorer/RequestSendLogs | ExplorerData non found |
| Explorer/Staging/List | Access denied |
| Integrations/Get | Integration not found |
| Integrations/GetJoinedDevices | E00000: An error has occurred during your request.  |
| Integrations/GetLogisticPlaceholders | E00000: An error has occurred during your request.  |
| Integrations/GetNew | E00000: An error has occurred during your request. 42f004... |
| Integrations/eautomate/GetEAutomateLog | E00000: An error has occurred during your request.  |
| Integrations/eautomate/runjoin | E00000: An error has occurred during your request.  |
| Office/OfficeFloor/GetPin | Device not found |
| Office/OfficeFloor/List | Office not found |
| Orders/GetOrderLineStatuses | Invalid JSON response from API |
| Project/GetContractFile | Project not found |
| Project/GetDetail | Project not found |
| Role/Get | E00000: An error has occurred during your request. 82aad0... |
| Role/GetAllCapabilities | Missing required query parameter: isForAccount |
| SdsAction/GetDeviceAction | Sds service request not found |
| SdsAction/GetDeviceActionsDashboard | E00000: An error has occurred during your request.  |
| SdsConnector/GetConnectors | DealerId is required |
| SdsConnector/GetJamcConnectors | E00000: An error has occurred during your request.  |
| SdsConnector/GetLogs | E00000: An error has occurred during your request.  |
| SdsConnector/GetWppConnectors | E00000: An error has occurred during your request.  |
| SdsCustomer/GetAssessTemplate | E00000: An error has occurred during your request. 68a6c4... |
| SdsCustomer/GetCredential | E00000: An error has occurred during your request. 19afb9... |
| SdsCustomer/GetCustomerOperation | CustomerId is required |
| SdsCustomer/GetCustomerOperations | DeviceId is required |
| SdsCustomer/GetNewAssessTemplate | E00000: An error has occurred during your request.  |
| SdsDevice/GetAssessTemplate | E00000: An error has occurred during your request. 5cd22d... |
| SdsDevice/GetConfigItems | E00000: An error has occurred during your request. 01bcd6... |
| SdsDevice/GetCounters | Device not found |
| SdsDevice/GetDeviceOperation | DeviceId is required |
| SdsDevice/GetDeviceRemoteEws | E00000: An error has occurred during your request. f49c62... |
| SdsDevice/GetOnDeviceServices | E00000: An error has occurred during your request. 6d8880... |
| SdsDevice/GetSupplyDetails | SupplyType is required |
| SdsDevice/GetZendeskTicketInfo | E00000: An error has occurred during your request. 2ec304... |
| SdsEvent/GetDeviceEvents | E00000: An error has occurred during your request.  |
| SdsScan/ScanDevice | Device not found |
| SdsScan/ScanImmediate | Device not found |
| StandardProduct/GetOperation | E00000: An error has occurred during your request. 8e63e1... |
| StandardProduct/GetProductsToAssociate | PageNumber must be greater than 0; PageRows must be great... |
| StandardProduct/ListDevicesInOperation | OperationId field is required |
| SupplyAlert/GetAvailableMaintenanceKitColors | E00000: An error has occurred during your request.  |
| SupplyAlert/GetAvailableMaintenanceKitTypes | E00000: An error has occurred during your request.  |
| SupplyAlert/GetAvailableSuppliesForADevice | Missing required query parameter: maintenanceKitTypeId |
| TraceVolume/Get | Trace Volume configuration not found |
| TraceVolume/List | Device not found |
| WhiteLabel/Get | E00000: An error has occurred during your request. d6da72... |
| WhiteLabel/GetWhitelabelPlaceholders | E00000: An error has occurred during your request.  |
