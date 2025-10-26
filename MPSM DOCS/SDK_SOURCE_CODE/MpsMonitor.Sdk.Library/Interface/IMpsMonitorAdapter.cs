using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Requests;
using MpsMonitor.Sdk.Models.Responses;
using System.Threading.Tasks;

namespace MpsMonitor.Sdk.Library.Interface
{
    /// <summary>
    /// 
    /// </summary>
    public interface IMpsMonitorAdapter
    {
        /// <summary>
        /// 
        /// </summary>
        /// <param name="endpoint"></param>
        /// <param name="clientId"></param>
        /// <param name="clientSecret"></param>
        /// <param name="username"></param>
        /// <param name="password"></param>
        /// <returns></returns>
        bool Authenticate(string endpoint, string clientId, string clientSecret, string username, string password);

        #region Dealer
        /// <summary>
        /// Gets the dealers.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        Task<PagedResultResponse<DealerListDto>> GetDealers(FilteredPagedRequest request);


        /// <summary>
        /// Gets the dealer.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        Task<SingleResultResponse<DealerDto>> GetDealer(GetByCodeRequest request);

        #endregion

        #region Customer
        /// <summary>
        /// Gets the customers.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        Task<PagedResultResponse<CustomerListDto>> GetCustomers(GetCustomersRequest request);

        /// <summary>
        /// Gets the customer.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        Task<SingleResultResponse<CustomerDto>> GetCustomerByCode(GetByCodeRequest request);
        

        /// <summary>
        /// Get the customer
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<SingleResultResponse<CustomerDto>> GetCustomerById(GetByIdRequest request);

        /// <summary>
        ///  Creates the customer.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<CreateCustomerResponse> CreateCustomer(CreateRequest<CreateCustomerDto> request);

        /// <summary>
        ///  Update the customer.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<SingleResultResponse<CustomerDto>> UpdateCustomer(UpdateRequest<UpdateCustomerDto> request);
       
        #endregion

        #region Device
        /// <summary>
        /// This operation gets lists of devices paged and filtered
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<PagedResultResponse<DeviceListDto>> GetDevices(GetDevicesRequest request);

        /// <summary>
        /// Returns a device by request parameters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<SingleResultResponse<DeviceDto>> GetDevice(GetByIdRequest request);

        /// <summary>
        /// update device
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<SingleResultResponse<DeviceDto>> UpdateDevice(UpdateDeviceRequest request);

        /// <summary>
        /// Retrieve all updated SDS device data. The operation will take about 20 minutes
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> ScanImmediate(GetByIdRequest request);

        /// <summary>
        /// Gets the device remote ews.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> GetDeviceRemoteEws(GetByIdRequest request);

        /// <summary>
        /// Sets the device update firmware.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> SetDeviceUpdateFirmware(SetDeviceUpdateFirmwareRequest request);

        /// <summary>
        /// Sets the device reboot.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> SetDeviceReboot(SetDeviceRebootRequest request);


        /// <summary>
        /// Returns a device by request parameters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<SingleResultResponse<DeviceSuppliesDetailsDto>> GetSuppliesDetails(GetByIdRequest request);

        /// <summary>
        /// Returns maintenance kit counters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<ListResultResponse<MaintenanceKitCounterDto>> ListMaintenanceKitCounters(GetByIdRequest request);
        #endregion

        #region Counters

        /// <summary>
        ///  Returns detailed counters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<ListResultResponse<CountersDetailedDeviceDto>> GetListDetailedCounters(GetCountersDetailedRequest request);


        /// <summary>
        /// Returns counters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<ListResultResponse<CountersDeviceDto>> GetCounters(GetCountersRequest request);
        
        /// <summary>
        /// Returns blended counters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<ListResultResponse<CountersBlendDeviceDto>> GetListBlendedCounters(GetCountersRequest request);

        #endregion

        #region Alerts

        /// <summary>
        /// Returns a list of opened alerts (not installed yet).
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        Task<PagedResultResponse<SupplyAlertListDto>> GetSupplyAlerts(GetSupplyAlertRequest request);

        /// <summary>
        /// Postpone an alert until percentage
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> SupplyAlertPostPone(UpdateRequest<PostponeAlertDto> request);

        /// <summary>
        /// Update massive supply alerts
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> UpdatesAlertsStatus(UpdateRequest<MassiveUpdateAlertDto> request);

        /// <summary>
        /// Gets a specific alert by its id
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        Task<SingleResultResponse<SupplyAlertDto>> GetSupplyAlert(GetByIdRequest request);

        /// <summary>
        /// Gets available supplies for a device
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<SingleResultResponse<AvailableSuppliesDto>> GetAvailableSuppliesForADevice(GetAvailableSuppliesForADeviceRequest request);

        #endregion

        #region Shipped

        /// <summary>
        /// Lists the specified request.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        Task<PagedResultResponse<ShippedSupplyDto>> GetShippedSupply(GetShippedSuppliesRequest request);

        /// <summary>
        /// Creates a shipped supply entry for a device (and alert).
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> CreateOnAlert(CreateShippingSupplyOnAlertRequest request);

        /// <summary>
        /// Updates the specified request.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> UpdateShippedSupply(UpdateRequest<UpdateShippedSupplyDto> request);

        #endregion

        #region Offices
        /// <summary>
        /// Gets the offices.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        Task<PagedResultResponse<OfficeListDto>> GetOffices(GetOfficesRequest request);

        /// <summary>
        /// Gets the office.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<SingleResultResponse<OfficeDto>> GetOffice(GetByIdRequest request);

        /// <summary>
        /// Updates the office.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> UpdateOffice(UpdateRequest<OfficeDto> request);

        /// <summary>
        /// Creates the office.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> CreateOffice(CreateRequest<OfficeDto> request);
        #endregion

        #region Common

        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        Task<ListResultResponse<CountryDto>> GetCountries();

        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        Task<ListResultResponse<EntityIdDescIntDto>> MaintenanceKitColors();

        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        Task<ListResultResponse<EntityIdDescIntDto>> MaintenanceKitTypes();
        #endregion

        #region Supplies
        /// <summary>
        /// Returns list of dealer supplies
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<PagedResultResponse<DealerSupplyListDto>> GetDealerSupplies(GetDealerSuppliesRequest request);

        /// <summary>
        /// Update the dealer supply.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<SingleResultResponse<DealerSupplyDto>> UpdateSupply(UpdateRequest<DealerSupplyDto> request);

        /// <summary>
        /// cREATE the dealer supply.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<SingleResultResponse<DealerSupplyDto>> CreateSupply(CreateRequest<DealerSupplyDto> request);

        #endregion

        #region SDS Service Request
        /// <summary>
        /// Gets the device actions.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<PagedResultResponse<SdsDeviceActionDto>> GetDeviceActions(GetDeviceActionsRequest request);

        /// <summary>
        /// Gets the device action.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<SingleResultResponse<SdsDeviceActionDto>> GetDeviceAction(GetByIdRequest request);

        /// <summary>
        /// Changes the device action status.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> ChangeDeviceActionStatus(ChangeDeviceActionStatusRequest request);
      

        #endregion


        #region Explorer
        /// <summary>
        /// Gets the offices.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        Task<PagedResultResponse<ExplorerDataDto>> GetConnectors(GetExplorerDatasRequest request);

        /// <summary>
        /// Returns eXplorer Configurations
        /// </summary>
        /// <returns></returns>
        Task<PagedResultResponse<ExplorerConfigurationBaseDto>> GetExplorerConfigurations(GetExplorerConfigurationsRequest request);

        /// <summary>
        /// Returns eXplorer Configurations
        /// </summary>
        /// <returns></returns>
        Task<SingleResultResponse<ExplorerConfigurationDto>> GetExplorerConfiguration(GetExplorerConfigurationRequest request);

        /// <summary>
        /// This operation gets explorer data from all dealer customer
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<PagedResultResponse<ExplorerDataDto>> GetExplorerDatas(GetExplorerDatasRequest request);

        /// <summary>
        /// Update Explorer Configuration
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> UpdateExplorerConfiguration(UpdateRequest<ExplorerConfigurationDto> request);


        /// <summary>
        /// Delete schedule on explorerconfiguration
        /// </summary>
        /// <returns></returns>
        Task<BaseResponse> DeleteSchedule(DeleteExplorerScheduleRequest request);

        /// <summary>
        /// Update schedule on explorerconfiguration
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> UpdateSchedule(UpdateExplorerScheduleRequest request);

        /// <summary>
        /// Create schedule on explorerconfiguration
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> CreateSchedule(CreateExplorerScheduleRequest request);

        /// <summary>
        /// Delete eXplorer subnet
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> DeleteExplorerSubnet(DeleteExplorerSubnetRequest request);

        /// <summary>
        /// Update eXplorer subnet
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> UpdateExplorerSubnet(UpdateExplorerSubnetRequest request);

        /// <summary>
        /// Create eXplorer subnet
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        Task<BaseResponse> CreateExplorerSubnet(CreateExplorerSubnetRequest request);

        #endregion
    }
}
