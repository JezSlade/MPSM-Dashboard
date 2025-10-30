using Microsoft.Extensions.Logging;
using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Requests;
using MpsMonitor.Sdk.Models.Responses;
using Newtonsoft.Json;
using System;
using System.Threading.Tasks;

namespace MpsMonitor.Sdk.Library
{
    public partial class MpsMonitorAdapter : IMpsMonitorAdapter
    {
        #region Devices
        /// <summary>
        /// This operation gets lists of devices paged and filtered
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<PagedResultResponse<DeviceListDto>> GetDevices(GetDevicesRequest request)
        {
            PagedResultResponse<DeviceListDto> result = new PagedResultResponse<DeviceListDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<PagedResultResponse<DeviceListDto>>("Device/List", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result.Result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell GetCustomers");
            }
            return result;
        }

        /// <summary>
        /// Returns a device by request parameters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<SingleResultResponse<DeviceDto>> GetDevice(GetByIdRequest request)
        {
            SingleResultResponse<DeviceDto> result = new SingleResultResponse<DeviceDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<SingleResultResponse<DeviceDto>>("Device/Get", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result.Result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell GetDevice");
            }
            return result;
        }

        /// <summary>
        ///  Retrieve all updated SDS device data. The operation will take about 20 minutes
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> ScanImmediate(GetByIdRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<SingleResultResponse<DeviceDto>>("SdsScan/ScanImmediate", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result.ReturnValue)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell ScanImmediate");
            }
            return result;
        }

        /// <summary>
        /// Gets the device remote ews.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> GetDeviceRemoteEws(GetByIdRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<SingleResultResponse<DeviceDto>>("SdsDevice/GetDeviceRemoteEws", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result.ReturnValue)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell GetDeviceRemoteEws");
            }
            return result;
        }

        /// <summary>
        ///  Sets the device update firmware.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> SetDeviceUpdateFirmware(SetDeviceUpdateFirmwareRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("SdsDevice/SetDeviceUpdateFirmware", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result.ReturnValue)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell SetDeviceUpdateFirmware");
            }
            return result;
        }

        /// <summary>
        ///   Sets the device reboot.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> SetDeviceReboot(SetDeviceRebootRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("SdsDevice/SetDeviceReboot", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result.ReturnValue)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell SetDeviceReboot");
            }
            return result;
        }

        /// <summary>
        /// Returns a device by request parameters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<SingleResultResponse<DeviceSuppliesDetailsDto>> GetSuppliesDetails(GetByIdRequest request)
        {
            SingleResultResponse<DeviceSuppliesDetailsDto> result = new SingleResultResponse<DeviceSuppliesDetailsDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<SingleResultResponse<DeviceSuppliesDetailsDto>>("Device/GetSuppliesDetails", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILS: {JsonConvert.SerializeObject(result.Result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell GetSuppliesDetails");
            }
            return result;
        }

        /// <summary>
        /// Returns maintenance kit counters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<ListResultResponse<MaintenanceKitCounterDto>> ListMaintenanceKitCounters(GetByIdRequest request)
        {
            ListResultResponse<MaintenanceKitCounterDto> result = new ListResultResponse<MaintenanceKitCounterDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<ListResultResponse<MaintenanceKitCounterDto>>("Counter/ListMaintenanceKitCounters", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result.Result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell ListMaintenanceKitCounters");
            }
            return result;
        }

        /// <summary>
        /// update device
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<SingleResultResponse<DeviceDto>> UpdateDevice(UpdateDeviceRequest request)
        {
            SingleResultResponse<DeviceDto> result = new SingleResultResponse<DeviceDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<SingleResultResponse<DeviceDto>>("Device/Update", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result.Result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nella Device/Update");
            }
            return result;
        }
        #endregion

    }
}
