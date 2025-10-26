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
        #region Supplies
        /// <summary>
        /// Returns list of dealer supplies
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<PagedResultResponse<DealerSupplyListDto>> GetDealerSupplies(GetDealerSuppliesRequest request)
        {
            PagedResultResponse<DealerSupplyListDto> result = new PagedResultResponse<DealerSupplyListDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<PagedResultResponse<DealerSupplyListDto>>("DealerSupply/List", request);

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
                _logger.LogError(ex, $"Errore nell GetDealerSupplies");
            }
            return result;
        }


        /// <summary>
        /// Update the dealer supply.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<SingleResultResponse<DealerSupplyDto>> UpdateSupply(UpdateRequest<DealerSupplyDto> request)
        {
            SingleResultResponse<DealerSupplyDto> result = new SingleResultResponse<DealerSupplyDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = _mpsMonitorClient.Put<SingleResultResponse<DealerSupplyDto>>("DealerSupply/Update", request);

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
                _logger.LogError(ex, $"Errore nell UpdateSupply");
            }
            return result;
        }

        /// <summary>
        /// cREATE the dealer supply.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<SingleResultResponse<DealerSupplyDto>> CreateSupply(CreateRequest<DealerSupplyDto> request)
        {
            SingleResultResponse<DealerSupplyDto> result = new SingleResultResponse<DealerSupplyDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<SingleResultResponse<DealerSupplyDto>>("DealerSupply/Create", request);

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
                _logger.LogError(ex, $"Errore nell CreateSupply");
            }
            return result;
        }
        #endregion

    }
}
