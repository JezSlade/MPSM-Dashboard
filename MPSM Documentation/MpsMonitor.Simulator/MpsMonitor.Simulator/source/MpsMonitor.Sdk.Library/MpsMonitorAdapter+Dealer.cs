using Microsoft.Extensions.Logging;
using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Requests;
using MpsMonitor.Sdk.Models.Responses;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Text;
using System.Threading.Tasks;

namespace MpsMonitor.Sdk.Library
{
    public partial class MpsMonitorAdapter : IMpsMonitorAdapter
    {
        #region Dealers

        /// <summary>
        /// Gets the dealers.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<PagedResultResponse<DealerListDto>> GetDealers(FilteredPagedRequest request)
        {
            PagedResultResponse<DealerListDto> result = new PagedResultResponse<DealerListDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<PagedResultResponse<DealerListDto>>("Dealer/GetDealers", request);
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
                _logger.LogError(ex, $"Errore nella GetDealers");
            }
            return result;
        }

        /// <summary>
        /// Gets the dealer.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<SingleResultResponse<DealerDto>> GetDealer(GetByCodeRequest request)
        {
            SingleResultResponse<DealerDto> result = new SingleResultResponse<DealerDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }
                result = await _mpsMonitorClient.PostAsync<SingleResultResponse<DealerDto>>("Dealer/GetDealerByCode", request);
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
                _logger.LogError(ex, $"Errore nell GetDealers");
            }
            return result;
        }

        #endregion

    }
}
