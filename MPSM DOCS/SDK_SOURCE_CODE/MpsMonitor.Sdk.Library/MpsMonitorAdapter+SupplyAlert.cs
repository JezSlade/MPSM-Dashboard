using Microsoft.Extensions.Logging;
using MpsMonitor.Sdk.Library.Interface;
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
        #region Supply Alerts

        /// <summary>
        /// Returns a list of opened alerts (not installed yet).
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<PagedResultResponse<SupplyAlertListDto>> GetSupplyAlerts(GetSupplyAlertRequest request)
        {
            PagedResultResponse<SupplyAlertListDto> result = new PagedResultResponse<SupplyAlertListDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<PagedResultResponse<SupplyAlertListDto>>("SupplyAlert/List", request);

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
                _logger.LogError(ex, $"Errore nell GetSupplyAlerts");
            }
            return result;
        }


        /// <summary>
        /// Postpone an alert until percentage
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> SupplyAlertPostPone(UpdateRequest<PostponeAlertDto> request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = _mpsMonitorClient.Put<BaseResponse>("SupplyAlert/PostponeAlert", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"Alert postponed: {JsonConvert.SerializeObject(result.ReturnValue)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell SupplyAlertPostPone");
            }
            return result;
        }


        /// <summary>
        /// Update massive supply alerts
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> UpdatesAlertsStatus(UpdateRequest<MassiveUpdateAlertDto> request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("SupplyAlert/MassiveUpdate", request);
                if (result.IsValid)
                {
                    _logger.LogDebug($"Alert Updated {JsonConvert.SerializeObject(result.ReturnValue)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell UpdatesAlertsStatus");
            }
            return result;
        }

        /// <summary>
        /// Gets a specific alert by its id
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<SingleResultResponse<SupplyAlertDto>> GetSupplyAlert(GetByIdRequest request)
        {
            SingleResultResponse<SupplyAlertDto> result = new SingleResultResponse<SupplyAlertDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<SingleResultResponse<SupplyAlertDto>>("SupplyAlert/Get", request);

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
                _logger.LogError(ex, $"Errore nell GetSupplyAlert");
            }
            return result;
        }


        /// <summary>
        /// Gets a specific alert by its id
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<SingleResultResponse<AvailableSuppliesDto>> GetAvailableSuppliesForADevice(GetAvailableSuppliesForADeviceRequest request)
        {
            SingleResultResponse<AvailableSuppliesDto> result = new SingleResultResponse<AvailableSuppliesDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<SingleResultResponse<AvailableSuppliesDto>>("SupplyAlert/GetAvailableSuppliesForADevice", request);

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
                _logger.LogError(ex, $"Errore nell GetAvailableSuppliesForADevice");
            }
            return result;
        }




        #endregion
    }
}
