### A List of requests for future modules / widgets
## Customer's All Device's with Supply Alerts
'''{
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
}'''

## Dealer's All Device's with Supply Allerts
'''{
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
    "CustomerCode": "",
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
}'''

## /PanelMessageAlert/GetErrorCodes
Gets available panel message codes
'''{
  "FilterText": "string",
  "PageNumber": 0,
  "PageRows": 0,
  "SortColumn": "string",
  "SortOrder": "Asc"
}'''
