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
        #region Counter

        /// <summary>
        /// Returns counters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<ListResultResponse<CountersDeviceDto>> GetCounters(GetCountersRequest request)
        {
            ListResultResponse<CountersDeviceDto> result = new ListResultResponse<CountersDeviceDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<ListResultResponse<CountersDeviceDto>>("Counter/List", request);
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
                _logger.LogError(ex, $"Errore nell GetCustomerDeviceDetailedCounters");
            }
            return result;
        }

        /// <summary>
        ///  Returns detailed counters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<ListResultResponse<CountersDetailedDeviceDto>> GetListDetailedCounters(GetCountersDetailedRequest request)
        {
            ListResultResponse<CountersDetailedDeviceDto> result = new ListResultResponse<CountersDetailedDeviceDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<ListResultResponse<CountersDetailedDeviceDto>>("Counter/ListDetailed", request);

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
                _logger.LogError(ex, $"Errore nell GetCustomerDeviceDetailedCounters");
            }
            return result;
        }

        /// <summary>
        ///  Returns detailed counters
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<ListResultResponse<CountersBlendDeviceDto>> GetListBlendedCounters(GetCountersRequest request)
        {
            ListResultResponse<CountersBlendDeviceDto> result = new ListResultResponse<CountersBlendDeviceDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<ListResultResponse<CountersBlendDeviceDto>>("Counter/ListBlended", request);

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
                _logger.LogError(ex, $"Errore nell GetCustomerDeviceDetailedCounters");
            }
            return result;
        }

        #endregion
    }
}
