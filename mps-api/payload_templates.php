<?php
/**
 * Payload Templates from API Discovery
 * 
 * Auto-generated from endpoint_reference.yaml
 * Contains 158 discovered working payload patterns
 * 
 * @version 1.0.0
 */

if (!defined('MPS_ENGINE_ACCESS')) {
    die('Direct access not permitted');
}

return [
    "AlertLimit/Customer/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "AlertLimit/Customer/Product/List" => [
        "query" => [
            "code" => null,
        ],
    ],
    "AlertLimit/Dealer/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "AlertLimit2/Customer/GetDefault" => [
        "query" => [
            "code" => null,
        ],
    ],
    "AlertLimit2/Customer/GetProduct" => [
        "query" => [
            "customerCode" => null,
            "id" => null,
        ],
    ],
    "AlertLimit2/Dealer/GetDefault" => [
        "query" => [
            "code" => null,
        ],
    ],
    "AlertLimit2/Dealer/GetProduct" => [
        "query" => [
            "dealerCode" => null,
            "id" => null,
        ],
    ],
    "ApiClient/Account/List" => [
        "query" => [
            "id" => null,
        ],
    ],
    "ApiClient/List" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Device/List" => [
        "query" => [
            "FilterDealerId" => null,
            "pageNumber" => null,
            "pageRows" => null,
        ],
    ],
    "Counter/Device/List" => [
        "query" => [
            "fromDate" => null,
            "toDate" => null,
            "id" => null,
        ],
    ],
    "Counter/ListMaintenanceKitCounters" => [
        "query" => [
            "id" => null,
        ],
    ],
    "CustomField/List" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Customer/Accessories/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Customer/AdvancedOptions/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Customer/AlertSettings/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Customer/CustomerServicesStatus/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Customer/EpsonSettings/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Customer/EpsonUSBCustomerId/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Customer/eXplorerSettings/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "CustomerDashboard" => [
        "query" => [
            "code" => null,
        ],
    ],
    "CustomerDashboard/Pages" => [
        "query" => [
            "code" => null,
        ],
    ],
    "CustomerNotification/Get" => [
        "query" => [
            "id" => null,
        ],
    ],
    "CustomerNotification/GetSampleNotification" => [
        "query" => [
            "id" => null,
        ],
    ],
    "CustomerNotification/List" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/AccountingSettings/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/AdvancedOptions/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/AlertLimitOptions/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/AlertSettings/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/CounterBlend/Get" => [
        "query" => [
            "dealerCode" => null,
            "id" => null,
        ],
    ],
    "Dealer/CounterBlend/List" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Dealer/CounterBlend/Search" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Dealer/CounterBlendToStandard/Get" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Dealer/CounterBlendToStandard/GetByDevice" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Dealer/CounterBlendToStandard/List" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Dealer/Customizations/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/DealerServicesStatus/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/DistributorSettings/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/ExportDealerTagsHierarchy" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/GetDealerHierarchy" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/GetDealerTagsHierarchy" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/Onboarding/Get" => [
        "query" => [
            "dealerCode" => null,
        ],
    ],
    "Dealer/RemoteOfflineCountersSettings/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Dealer/eXplorerSettings/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "DealerNotification/Get" => [
        "query" => [
            "id" => null,
        ],
    ],
    "DealerNotification/GetSampleNotification" => [
        "query" => [
            "id" => null,
        ],
    ],
    "DealerNotification/List" => [
        "query" => [
            "dealerCode" => null,
        ],
    ],
    "DealerNotification/Template/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
    "DealerProduct/Get" => [
        "query" => [
            "dealerCode" => null,
            "id" => null,
        ],
    ],
    "DealerProduct/List" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "DealerSupply/Count" => [
        "query" => [
            "code" => null,
        ],
    ],
    "DealerSupply/Export" => [
        "query" => [
            "code" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "DealerSupply/Get" => [
        "query" => [
            "dealerCode" => null,
            "id" => null,
        ],
    ],
    "DealerSupply/List" => [
        "query" => [
            "code" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "DealerSupplyPriceListing/Get" => [
        "query" => [
            "dealerCode" => null,
            "id" => null,
        ],
    ],
    "DealerSupplyPriceListing/List" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "DealerSupplySet/Count" => [
        "query" => [
            "code" => null,
        ],
    ],
    "DealerSupplySet/Export" => [
        "query" => [
            "code" => null,
        ],
    ],
    "DealerSupplySet/ExportExcel" => [
        "query" => [
            "code" => null,
        ],
    ],
    "DealerSupplySet/Get" => [
        "query" => [
            "id" => null,
        ],
    ],
    "DealerSupplySet/List" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "DealerSupplySet/ListDealerSupplySetFromStandardModels" => [
        "query" => [
            "dealerCode" => null,
        ],
    ],
    "Device/Deleted/List" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Device/Deleted/ListByDealer" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Device/ExplorerDataAffinities/List" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Device/MaintenanceAlerts/List" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Explorer/Cluster/List" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Explorer/Configuration/List" => [
        "query" => [
            "customerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Explorer/ExplorerDataCommand/List" => [
        "query" => [
            "customerCode" => null,
            "id" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Explorer/ExplorerDataInfo/List" => [
        "query" => [
            "customerCode" => null,
            "id" => null,
        ],
    ],
    "Explorer/License/List" => [
        "query" => [
            "customerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Explorer/Staging/List" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Integrations/List" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Office/OfficeFloor/List" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Product/Customer/List" => [
        "query" => [
            "code" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Product/Dealer/List" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Product/Dealer/ListBrands" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Product/Dealer/ListModels" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Role/GetAllCapabilities" => [
        "query" => [
            "isForAccount" => null,
            "code" => null,
        ],
    ],
    "Role/List" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "SdsCustomer/GetAssessTemplate" => [
        "query" => [
            "customerCode" => null,
            "id" => null,
        ],
    ],
    "SdsCustomer/GetAssessTemplates" => [
        "query" => [
            "code" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "SdsCustomer/GetCredential" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsCustomer/GetCustomerOperation" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsCustomer/GetCustomerOperations" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "StandardProduct/ListDevicesInOperation" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "StandardProduct/ListOperations" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "StandardProduct/ListStandardProducts" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "TraceVolume/List" => [
        "query" => [
            "id" => null,
        ],
    ],
    "TradingPartner/List" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "azuread/GetCustomerAzureSettings" => [
        "query" => [
            "code" => null,
        ],
    ],
    "azuread/GetDealerAzureSettings" => [
        "query" => [
            "code" => null,
        ],
    ],
    "okta/GetCustomerOktaSettings" => [
        "query" => [
            "code" => null,
        ],
    ],
    "okta/GetDealerOktaSettings" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Account/GetPsk2faData" => [
        "query" => [
            "platform" => "MpsMonitor",
        ],
    ],
    "AlertLimit/Device/Get" => [
        "query" => [
            "id" => null,
        ],
    ],
    "AlertLimit2/Device/GetDefault" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Analytics/GetReportFileResult" => [
        "query" => [
            "idReport" => null,
            "reportFormat" => null,
        ],
    ],
    "Analytics/GetReportResult" => [
        "query" => [
            "idReport" => null,
        ],
    ],
    "ApiClient/Account/Get" => [
        "query" => [
            "id" => null,
        ],
    ],
    "ApiClient/Get" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Counter/Device/Export" => [
        "query" => [
            "fromDate" => null,
            "toDate" => null,
            "id" => null,
        ],
    ],
    "CustomField/Get" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Device/GetDeviceAdditionalInfos" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Device/GetDeviceGapInfos" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Device/GetLfpCounters" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Device/GetSuppliesDetails" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Device/GetSuppliesDetailsInfo" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Device/GetSuppliesDetailsSummary" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Device/GetZebraSuppliesDetailsSummary" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Explorer/Cluster/AutoClusters" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Explorer/Cluster/Get" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Explorer/Configuration/Get" => [
        "query" => [
            "customerCode" => null,
            "configurationId" => null,
        ],
    ],
    "Explorer/Configuration/GetTestTableVersions" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Explorer/DataPings" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Explorer/DownloadLogs" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Explorer/GetClusterCounters" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Explorer/GetConnectorEndpoints" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Explorer/GetConnectors" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Explorer/GetDca4Otp" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Explorer/GetDcaCurrentVersion" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Explorer/GetDcaReleaseNotes" => [
        "query" => [
            "code" => null,
        ],
    ],
    "Explorer/GetEndpointsLink" => [
        "query" => [
            "dealerCode" => null,
            "platform" => null,
        ],
    ],
    "Explorer/GetExplorerDatas" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Explorer/GetExplorerSetupLink" => [
        "query" => [
            "customerCode" => null,
            "code" => null,
        ],
    ],
    "Explorer/GetJamcSetupLink" => [
        "query" => [
            "customerCode" => null,
            "code" => null,
        ],
    ],
    "Explorer/RequestSendLogs" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Integrations/Get" => [
        "query" => [
            "dealerCode" => null,
            "id" => null,
        ],
    ],
    "Integrations/GetJoinedCustomers" => [
        "query" => [
            "dealerCode" => null,
        ],
    ],
    "Office/OfficeFloor/GetPin" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Product/GetBrands" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Product/GetModels" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Product/GetSnmpDiscoveryBrands" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "Project/GetContractFile" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Project/GetDetail" => [
        "query" => [
            "id" => null,
        ],
    ],
    "Role/Get" => [
        "query" => [
            "dealerCode" => null,
            "id" => null,
        ],
    ],
    "SdsAction/GetDeviceAction" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsAction/GetDeviceActions" => [
        "query" => [
            "dealerCode" => null,
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "SdsConnector/GetConnector" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsConnector/GetConnectors" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "SdsDevice/GetAssessTemplate" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsDevice/GetConfigItems" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsDevice/GetCounters" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsDevice/GetDeviceOperation" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsDevice/GetDeviceRemoteEws" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsDevice/GetDevicesOperations" => [
        "query" => [
            "pageNumber" => null,
            "pageRows" => null,
            "sortColumn" => null,
            "sortOrder" => "Asc",
        ],
    ],
    "SdsDevice/GetOnDeviceServices" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsDevice/GetSupplyDetails" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsDevice/GetZendeskTicketInfo" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsEvent/GetDeviceEvent" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsScan/ScanDevice" => [
        "query" => [
            "id" => null,
        ],
    ],
    "SdsScan/ScanImmediate" => [
        "query" => [
            "id" => null,
        ],
    ],
    "StandardProduct/GetExcelReport" => [
        "query" => [
            "dealerCode" => null,
            "id" => null,
        ],
    ],
    "StandardProduct/GetOperation" => [
        "query" => [
            "dealerCode" => null,
            "id" => null,
        ],
    ],
    "StandardProduct/GetProductsToAssociate" => [
        "query" => [
            "dealerCode" => null,
        ],
    ],
    "StandardProduct/GetStandardProductsSummary" => [
        "query" => [
            "dealerCode" => null,
        ],
    ],
    "SupplyAlert/GetAvailableSuppliesForADevice" => [
        "query" => [
            "deviceId" => null,
            "supplyType" => "ManteinanceKit",
            "colorType" => "NotAvailable",
            "maintenanceKitTypeId" => null,
            "maintenanceKitColorId" => null,
            "warning" => null,
            "language" => "Italiano",
        ],
    ],
    "TraceVolume/Get" => [
        "query" => [
            "id" => null,
        ],
    ],
    "TradingPartner/Get" => [
        "query" => [
            "dealerCode" => null,
            "id" => null,
        ],
    ],
    "WhiteLabel/Get" => [
        "query" => [
            "code" => null,
        ],
    ],
];
