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
        #region SDS Service Request
        /// <summary>
        /// Returns list of dealer supplies
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<PagedResultResponse<SdsDeviceActionDto>> GetDeviceActions(GetDeviceActionsRequest request)
        {
            PagedResultResponse<SdsDeviceActionDto> result = new PagedResultResponse<SdsDeviceActionDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<PagedResultResponse<SdsDeviceActionDto>>("SdsAction/GetDeviceActions", request);

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
                _logger.LogError(ex, $"Errore nell GetDeviceActions");
            }
            return result;
        }

        /// <summary>
        /// Gets the device action.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<SingleResultResponse<SdsDeviceActionDto>> GetDeviceAction(GetByIdRequest request)
        {
            SingleResultResponse<SdsDeviceActionDto> result = new SingleResultResponse<SdsDeviceActionDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<SingleResultResponse<SdsDeviceActionDto>>("SdsAction/GetDeviceAction", request);

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
                _logger.LogError(ex, $"Errore nell GetDeviceAction");
            }
            return result;
        }

        /// <summary>
        /// Changes the device action status.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> ChangeDeviceActionStatus(ChangeDeviceActionStatusRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("SdsAction/ChangeDeviceActionStatus", request);
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
                _logger.LogError(ex, $"Errore nell ChangeDeviceActionStatus");
            }
            return result;
        }

        #endregion

    }
}
