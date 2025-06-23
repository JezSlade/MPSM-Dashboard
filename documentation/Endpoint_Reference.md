**Account Endpoints**
* **GET**
    * `/Account/GetProfile`: Gets profile of current authenticated user.
    * `/Account/GetPsk2faData`
    * `/Account/GetPsk2faDataForProfile` (Required: `profileId`)
    * `/Account/GetPsk2faDataForAccount` (Required: `accountId`)
    * `/Account/GetAccount` (Required: `id`)
    * `/Account/GetAccounts`
* **POST**
    * `/Account/RefreshAuthCookie`
    * `/Account/Logout`
    * `/Account/UpdateProfile`: Update profile of current authenticated user. (Required: `userProfile`)
    * `/Account/Enable2faForAccount` (Required: `accountId`, `totp`, `password`)
    * `/Account/Enable2faForProfile` (Required: `profileId`, `totp`, `password`)
    * `/Account/SendOtpEmailForAccount` (Required: `email`)
    * `/Account/SetPreferredDealer`: Gets profile of current authenticated account. (Required: `dealerId`)
    * `/Account/Create`: Create an account (Required: `model`)
    * `/Account/Update`: Update an account (Required: `model`)
    * `/Account/ChangePassword`: Changes the password of the connected account (Required: `model`)
    * `/Account/ResetPassword`: Resets the password. (Required: `model`)
    * `/Account/ResetPasswordVerifyToken`: Verify the Resets password auth token. (Required: `model`)
    * `/Account/ChangeLanguage` (Required: `language`)
* **DELETE**
    * `/Account/Delete`: This operation deletes an account (Required: `id`)
    * `/Account/Delete2fa`: This operation deletes the two factor authentication for a user (Required: `accountId`)
    * `/Account/DeleteProfile2fa`: This operation deletes the two factor authentication for a user (Required: `profileId`)

**AlertLimit2 Endpoints**
* **GET**
    * `/AlertLimit2/GetAllLimits`
    * `/AlertLimit2/Dealer/GetDefault` (Required: `dealerId`)
    * `/AlertLimit2/Dealer/GetProductList` (Required: `dealerId`)
    * `/AlertLimit2/Dealer/GetProduct` (Required: `dealerId`, `productId`)
    * `/AlertLimit2/Customer/GetDefault` (Required: `customerId`)
* **POST**
    * `/AlertLimit2/DisableAlertLimits` (Required: `disable`)
    * `/AlertLimit2/Dealer/CreateDefault` (Required: `dealerDefaultAlertLimit`)
    * `/AlertLimit2/Dealer/UpdateDefault` (Required: `dealerDefaultAlertLimit`)
    * `/AlertLimit2/Dealer/CreateProduct` (Required: `productAlertLimit`)
    * `/AlertLimit2/Dealer/UpdateProduct` (Required: `productAlertLimit`)
* **DELETE**
    * `/AlertLimit2/Dealer/DeleteProduct` (Required: `dealerId`, `productId`)

**AlertLimit Endpoints**
* **DELETE**
    * `/AlertLimit/Customer/Delete`: Delete Alert limits (Required: `id`)
    * `/AlertLimit/Customer/Product/Delete` (Required: `id`)
    * `/AlertLimit/Device/Delete` (Required: `id`)

**Analytics Endpoints**
* **GET**
    * `/Analytics/GetReportResult`: Get result from a saved report. (Required: `reportId`, `format`)
    * `/Analytics/GetReportResults` (Required: `filterId`)
    * `/Analytics/GetSavedReports`
    * `/Analytics/GetSavedReport` (Required: `reportId`)
    * `/Analytics/GetChartConfiguration` (Required: `id`)
    * `/Analytics/GetChartConfigurations`
    * `/Analytics/GetDashboards`
    * `/Analytics/GetDashboard` (Required: `dashboardId`)
* **POST**
    * `/Analytics/UpdateChartConfiguration` (Required: `chartConfiguration`)
    * `/Analytics/AddDashboard` (Required: `dashboard`)
    * `/Analytics/UpdateDashboard` (Required: `dashboard`)
* **DELETE**
    * `/Analytics/DeleteDashboard` (Required: `dashboardId`)

**ApiClient Endpoints**
* **GET**
    * `/ApiClient/Get` (Required: `id`)
    * `/ApiClient/GetList`
* **POST**
    * `/ApiClient/Create` (Required: `model`)
    * `/ApiClient/Update` (Required: `model`)
* **DELETE**
    * `/ApiClient/Delete` (Required: `id`)

**Company Endpoints**
* **GET**
    * `/Company/Get` (Required: `id`)
* **POST**
    * `/Company/Create` (Required: `model`)
    * `/Company/Update` (Required: `model`)
* **DELETE**
    * `/Company/Delete` (Required: `id`)

**Contract Endpoints**
* **GET**
    * `/Contract/GetContracts`
    * `/Contract/GetContract` (Required: `id`)
    * `/Contract/GetContractOverview` (Required: `id`)
    * `/Contract/GetContractMeters` (Required: `contractId`)
    * `/Contract/GetContractPages` (Required: `contractId`)
    * `/Contract/GetContractConsumables` (Required: `contractId`)
    * `/Contract/GetContractReadings` (Required: `contractId`)
    * `/Contract/GetContractDetails` (Required: `id`)
    * `/Contract/GetContractDetailsForInstalledProduct` (Required: `id`)
    * `/Contract/GetContractRevenues` (Required: `contractId`)
* **POST**
    * `/Contract/CreateContract` (Required: `model`)
    * `/Contract/UpdateContract` (Required: `model`)
    * `/Contract/SetContractPages` (Required: `contractId`, `pages`)
    * `/Contract/SetContractConsumables` (Required: `contractId`, `consumables`)
    * `/Contract/SetContractReadings` (Required: `contractId`, `readings`)
    * `/Contract/ImportFromXml` (Required: `file`)
    * `/Contract/ExportToXml` (Required: `contractsIds`)
* **DELETE**
    * `/Contract/DeleteContract` (Required: `id`)

**CustomField Endpoints**
* **GET**
    * `/CustomField/GetCustomFields`
    * `/CustomField/GetCustomField` (Required: `id`)
* **POST**
    * `/CustomField/CreateCustomField` (Required: `model`)
    * `/CustomField/UpdateCustomField` (Required: `model`)
* **DELETE**
    * `/CustomField/DeleteCustomField` (Required: `id`)

**Customer Endpoints**
* **GET**
    * `/Customer/GetCustomers`
    * `/Customer/GetCustomer` (Required: `id`)
    * `/Customer/GetCustomerByCode`: Gets the customer. (Required: `code`)
* **POST**
    * `/Customer/CreateCustomer`: Creates the customer. (Required: `model`)
    * `/Customer/GetEmailExplorerInstallationToCustomer`: Returns the mail parts (subjet and body) of the email to be sent to the customer for eXplorer installation. (Required: `customerId`)
    * `/Customer/UpdateCustomer` (Required: `model`)
* **DELETE**
    * `/Customer/DeleteCustomer` (Required: `id`)

**CustomerDashboard Endpoints**
* **GET**
    * `/CustomerDashboard/Get`: Gets the customer's dashboard. (Required: `customerId`)
    * `/CustomerDashboard/Devices` (Required: `customerId`)

**Dashboard Endpoints**
* **GET**
    * `/Dashboard/Get`
    * `/Dashboard/GetCounters`
    * `/Dashboard/GetDeviceCounters`
    * `/Dashboard/GetSuppliesCounters`
    * `/Dashboard/GetDeviceActionsDashboard`
    * `/Dashboard/GetDeviceCounters2`

**DataStream Endpoints**
* **GET**
    * `/DataStream/Get` (Required: `id`)
    * `/DataStream/GetDataStreams`
    * `/DataStream/GetDeviceDataStreams` (Required: `deviceId`)
* **POST**
    * `/DataStream/Create` (Required: `model`)
    * `/DataStream/Update` (Required: `model`)
* **DELETE**
    * `/DataStream/Delete` (Required: `id`)

**Dealer Endpoints**
* **GET**
    * `/Dealer/GetDealers`
    * `/Dealer/GetDealer` (Required: `id`)
    * `/Dealer/GetDealerByCode`: Gets the dealer. (Required: `code`)
    * `/Dealer/GetDealerSettings` (Required: `dealerId`)
    * `/Dealer/GetDealerOnboarding` (Required: `dealerId`)
    * `/Dealer/GetExcelImport` (Required: `dealerId`, `importId`)
    * `/Dealer/GetExcelTemplate` (Required: `dealerId`)
    * `/Dealer/GetDealerAlerts` (Required: `dealerId`)
* **POST**
    * `/Dealer/CreateDealer`: Creates the dealer. (Required: `model`)
    * `/Dealer/UpdateDealer`: Updates the dealer. (Required: `model`)
    * `/Dealer/UpdateDealerSettings` (Required: `dealerSettings`)
    * `/Dealer/SetDealerOnboarding` (Required: `onboarding`)
    * `/Dealer/ExportDevices` (Required: `dealerId`, `filter`)
    * `/Dealer/ExportDevicesNew` (Required: `dealerId`, `filter`)
    * `/Dealer/ImportDevices` (Required: `dealerId`, `file`)
    * `/Dealer/SetExcelImport` (Required: `dealerId`, `importId`, `status`, `log`)
* **DELETE**
    * `/Dealer/DeleteDealer`: Deletes the dealer. (Required: `id`)

**DealerSupplySet Endpoints**
* **POST**
    * `/DealerSupplySet/ExportExcel`: Deletes the specified supply set. (Required: `id`)
    * `/DealerSupplySet/Export`: Deletes the specified supply set. (Required: `id`)
    * `/DealerSupplySet/Import`: Deletes the specified supply set. (Required: `id`, `file`)

**Device Endpoints**
* **GET**
    * `/Device/GetDevices`
    * `/Device/GetDevice` (Required: `id`)
    * `/Device/GetDeviceBySerialNumber`: Gets the device. (Required: `serialNumber`)
    * `/Device/GetDeviceDetails` (Required: `id`)
    * `/Device/GetDeviceOverview` (Required: `id`)
    * `/Device/GetDeviceCounters` (Required: `deviceId`)
    * `/Device/GetDeviceReadings` (Required: `deviceId`)
    * `/Device/GetDeviceConsumables` (Required: `deviceId`)
    * `/Device/GetDeviceAlerts` (Required: `deviceId`)
    * `/Device/GetDeviceEvents` (Required: `deviceId`)
    * `/Device/GetDevicePings` (Required: `deviceId`)
    * `/Device/GetDeviceSnmpErrors` (Required: `deviceId`)
    * `/Device/GetDeviceManagement` (Required: `deviceId`)
    * `/Device/GetDeviceExtendedProperty` (Required: `id`, `name`)
    * `/Device/GetDeviceAttributes` (Required: `deviceId`)
    * `/Device/GetAvailableSupplies` (Required: `deviceId`)
    * `/Device/GetDeviceFields` (Required: `deviceId`)
* **POST**
    * `/Device/CreateDevice`: Creates the device. (Required: `model`)
    * `/Device/UpdateDevice`: Updates the device. (Required: `model`)
    * `/Device/UpdateDevices` (Required: `updatedDevices`)
    * `/Device/SetDeviceReadings` (Required: `deviceId`, `readings`)
    * `/Device/SetDeviceConsumables` (Required: `deviceId`, `consumables`)
    * `/Device/SetDeviceAlerts` (Required: `deviceId`, `alerts`)
    * `/Device/UpdateDeviceManagement` (Required: `deviceManagement`)
    * `/Device/SetDeviceExtendedProperty` (Required: `id`, `name`, `value`)
    * `/Device/SetDeviceSupply` (Required: `deviceId`, `supplyId`, `newSupplyId`, `isOriginal`)
    * `/Device/SetDeviceFields` (Required: `deviceId`, `fields`)
    * `/Device/ChangeOffice` (Required: `deviceId`, `officeId`)
    * `/Device/ChangeCustomer` (Required: `deviceId`, `customerId`)
    * `/Device/UpdateDevicesBySerialNumbers` (Required: `serialNumbers`, `updateModel`)
    * `/Device/UpdateDeviceRaw` (Required: `model`)
* **DELETE**
    * `/Device/DeleteDevice`: Deletes the device. (Required: `id`)
    * `/Device/DeleteDeviceExtendedProperty` (Required: `id`, `name`)

**Document Endpoints**
* **GET**
    * `/Document/GetDocuments`
    * `/Document/GetDocument` (Required: `id`)
* **POST**
    * `/Document/CreateDocument` (Required: `model`)
    * `/Document/UpdateDocument` (Required: `model`)
* **DELETE**
    * `/Document/DeleteDocument` (Required: `id`)

**Explorer Endpoints**
* **GET**
    * `/Explorer/GetConnectorEndpoints`
    * `/Explorer/GetExplorerData` (Required: `id`)
    * `/Explorer/GetDataCommands`
    * `/Explorer/GetSchedule` (Required: `id`)
    * `/Explorer/GetExplorerConfig` (Required: `id`)
    * `/Explorer/GetExplorerConfigs`
    * `/Explorer/DataPings`
    * `/Explorer/DataLogs`
    * `/Explorer/AgentVersions`: Get Agent Versions
    * `/Explorer/ServiceVersions`: Get Service Versions
* **POST**
    * `/Explorer/Subnet/Create`: Create eXplorer subnet (Required: `subnet`)
    * `/Explorer/Subnet/Update`: Update eXplorer subnet (Required: `subnet`)
    * `/Explorer/Hostname/Create`: Create eXplorer hostname (Required: `hostname`)
    * `/Explorer/Hostname/Update`: Update eXplorer subnet (Required: `hostname`)
    * `/Explorer/WorkingDays/Update`: Update configuration working days (Required: `settings`)
    * `/Explorer/Intervals/Update`: Updates the explorer interval. (Required: `settings`)
    * `/Explorer/SetDcaLogLevel`: This operation set the DCA log level (Required: `settings`)
    * `/Explorer/RequestSendLogs`: This operation gets explorer data and clusters (Required: `id`, `settings`)
    * `/Explorer/AbortRequestSendLogs`: Abort request send logs (Required: `id`)
    * `/Explorer/DownloadLogs`: This operation gets explorer data and clusters (Required: `id`, `logId`)
    * `/Explorer/UpdateSchedule` (Required: `schedule`)
    * `/Explorer/UpdateExplorerConfig` (Required: `explorerConfig`)
    * `/Explorer/CreateExplorerConfig` (Required: `explorerConfig`)
    * `/Explorer/UpdateDca4Client`: Explorer Update Agent (Required: `dcaUpdate`)
    * `/Explorer/TestDca4Client` (Required: `settings`)
* **DELETE**
    * `/Explorer/Subnet/Delete`: Delete eXplorer subnet (Required: `id`)
    * `/Explorer/Hostname/Delete`: Delete eXplorer hostname (Required: `id`)
    * `/Explorer/DeleteSchedule` (Required: `id`)
    * `/Explorer/DeleteExplorerConfig` (Required: `id`)

**Ews Endpoints**
* **GET**
    * `/Ews/Get` (Required: `deviceId`)
    * `/Ews/GetAvailableSetting` (Required: `deviceId`, `settingName`)
    * `/Ews/GetSetting` (Required: `deviceId`, `settingName`)
    * `/Ews/GetZebraSettings` (Required: `deviceId`)
* **POST**
    * `/Ews/SetSetting` (Required: `deviceId`, `setting`)
    * `/Ews/SetZebraSettings` (Required: `deviceId`, `settings`)

**File Endpoints**
* **POST**
    * `/File/UploadFile`: Uploads file to temporary folder. (Required: `file`)
* **GET**
    * `/File/DownloadFile` (Required: `id`)
* **DELETE**
    * `/File/DeleteFile` (Required: `id`)

**Firmware Endpoints**
* **GET**
    * `/Firmware/GetDeviceFirmwares` (Required: `deviceId`)
    * `/Firmware/GetFirmwareList`
    * `/Firmware/GetFirmware` (Required: `id`)
    * `/Firmware/GetDeviceFirmwareUpgradeStatus` (Required: `deviceId`)
    * `/Firmware/GetDeviceFirmwareUpgradeStatusHistory` (Required: `deviceId`)
* **POST**
    * `/Firmware/SetFirmware` (Required: `model`)
* **DELETE**
    * `/Firmware/DeleteFirmware` (Required: `id`)

**InstalledProduct Endpoints**
* **GET**
    * `/InstalledProduct/GetInstalledProduct` (Required: `id`)
    * `/InstalledProduct/GetInstalledProducts`
    * `/InstalledProduct/GetAvailableSupplies` (Required: `installedProductId`)
    * `/InstalledProduct/GetSupplyAlerts` (Required: `installedProductId`)
    * `/InstalledProduct/MaintenanceAlerts`: Returns a list of maintenance alerts. (Required: `installedProductId`)
    * `/InstalledProduct/GetAvailableSupplyForOrder` (Required: `installedProductId`, `supplyId`)
    * `/InstalledProduct/GetInstalledProductMeters` (Required: `installedProductId`)
* **POST**
    * `/InstalledProduct/CreateInstalledProduct` (Required: `model`)
    * `/InstalledProduct/UpdateInstalledProduct` (Required: `model`)
    * `/InstalledProduct/SetProductSupply` (Required: `installedProductId`, `supplyId`, `newSupplyId`, `isOriginal`)
    * `/InstalledProduct/SetSupplyAlert` (Required: `installedProductId`, `supplyId`, `warningLevel`, `criticalLevel`)
    * `/InstalledProduct/SetSupplyOrder` (Required: `model`)
    * `/InstalledProduct/SetSupplyOrderV2` (Required: `model`)
    * `/InstalledProduct/SetInstalledProductMeters` (Required: `installedProductId`, `meters`)
* **DELETE**
    * `/InstalledProduct/DeleteInstalledProduct` (Required: `id`)

**ManagedDevice Endpoints**
* **GET**
    * `/ManagedDevice/GetManagedDevice` (Required: `id`)
* **POST**
    * `/ManagedDevice/SetManagedDevice` (Required: `managedDevice`)

**Office Endpoints**
* **GET**
    * `/Office/GetOffices`
    * `/Office/GetOffice` (Required: `id`)
* **POST**
    * `/Office/CreateOffice` (Required: `model`)
    * `/Office/UpdateOffice` (Required: `model`)
* **DELETE**
    * `/Office/DeleteOffice` (Required: `id`)

**Order Endpoints**
* **GET**
    * `/Order/GetOrders`
    * `/Order/GetOrder` (Required: `id`)
    * `/Order/GetOrderLines` (Required: `orderId`)
* **POST**
    * `/Order/CreateOrder` (Required: `model`)
    * `/Order/UpdateOrder` (Required: `model`)
* **DELETE**
    * `/Order/DeleteOrder` (Required: `id`)

**Product Endpoints**
* **GET**
    * `/Product/GetProducts`
    * `/Product/GetBrands`: Gets the brands related to all dealers
    * `/Product/GetSnmpDiscoveryBrands`: Gets the Snmp discovery brands
    * `/Product/GetModels`: Gets the models related to all dealers
    * `/Product/GetProduct` (Required: `id`)
* **POST**
    * `/Product/CreateProduct` (Required: `model`)
    * `/Product/UpdateProduct` (Required: `model`)
* **DELETE**
    * `/Product/DeleteProduct` (Required: `id`)

**Report Endpoints**
* **GET**
    * `/Report/GetReport` (Required: `id`)
    * `/Report/GetReports`
    * `/Report/GetReportFilters` (Required: `reportId`)
    * `/Report/GetReportAvailableFilterValues` (Required: `reportId`, `filterId`)
    * `/Report/GetReportAvailableGroups` (Required: `reportId`)
    * `/Report/GetReportAvailableColumns` (Required: `reportId`)
    * `/Report/GetReportAvailableCharts` (Required: `reportId`)
* **POST**
    * `/Report/ExecuteReport` (Required: `reportId`, `filter`)
    * `/Report/SaveReport` (Required: `report`)
    * `/Report/SendReportByMail` (Required: `reportId`, `sendReportByMail`)
* **DELETE**
    * `/Report/DeleteReport` (Required: `id`)

**SdsDevice Endpoints**
* **POST**
    * `/SdsDevice/CopyPolicy`: Copy device config items to a new policy entity (Required: `model`)
    * `/SdsDevice/AssessAndRemediate`: Run an Assess and Remediate operation (Required: `assessAndRemediate`)
    * `/SdsDevice/ApplyPolicy` (Required: `applyPolicy`)
    * `/SdsDevice/SetPolicy` (Required: `policy`)
    * `/SdsDevice/RegisterPrinter` (Required: `registerPrinter`)
    * `/SdsDevice/SetDeviceCertificate` (Required: `deviceId`, `model`)
    * `/SdsDevice/SetConfiguration` (Required: `deviceId`, `model`)
* **GET**
    * `/SdsDevice/GetAvailablePolicies`
    * `/SdsDevice/GetPolicy` (Required: `id`)
    * `/SdsDevice/GetAvailablePrinters`
    * `/SdsDevice/GetDeviceCertificate` (Required: `deviceId`)
    * `/SdsDevice/GetConfiguration` (Required: `deviceId`)
* **DELETE**
    * `/SdsDevice/DeletePolicy` (Required: `id`)
    * `/SdsDevice/UnregisterPrinter` (Required: `id`)

**Signature Endpoints**
* **GET**
    * `/Signature/GetSignerAuthToken` (Required: `ticketId`)
* **POST**
    * `/Signature/FirstSigner` (Required: `ticketId`, `firstSigner`)

**Support Endpoints**
* **GET**
    * `/Support/GetSupportEmails`
* **POST**
    * `/Support/UpdateSupportEmails` (Required: `emails`)

**Task Endpoints**
* **GET**
    * `/Task/GetTasks`
    * `/Task/GetTask` (Required: `id`)
* **POST**
    * `/Task/CreateTask` (Required: `model`)
    * `/Task/UpdateTask` (Required: `model`)
* **DELETE**
    * `/Task/DeleteTask` (Required: `id`)

**Ticket Endpoints**
* **GET**
    * `/Ticket/GetTickets`
    * `/Ticket/GetTicket`: Gets the ticket. (Required: `id`)
    * `/Ticket/GetTicketByNumber` (Required: `ticketNumber`)
* **POST**
    * `/Ticket/CreateTicket` (Required: `model`)
    * `/Ticket/UpdateTicket` (Required: `model`)
* **DELETE**
    * `/Ticket/DeleteTicket` (Required: `id`)