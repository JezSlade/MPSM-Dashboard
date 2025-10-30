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
        #region offices

        /// <summary>
        /// Gets the offices.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<PagedResultResponse<OfficeListDto>> GetOffices(GetOfficesRequest request)
        {
            PagedResultResponse<OfficeListDto> result = new PagedResultResponse<OfficeListDto>() ;
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<PagedResultResponse<OfficeListDto>>("Office/List", request);

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
                _logger.LogError(ex, $"Errore nell GetOffices");
            }
            return result;
        }

        /// <summary>
        /// Gets the office.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<SingleResultResponse<OfficeDto>> GetOffice(GetByIdRequest request)
        {
            SingleResultResponse<OfficeDto> result = new SingleResultResponse<OfficeDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<SingleResultResponse<OfficeDto>>("Office/Get", request);

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
                _logger.LogError(ex, $"Errore nell GetOffice");
            }
            return result;
        }

        /// <summary>
        /// Updates the office.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<BaseResponse> UpdateOffice(UpdateRequest<OfficeDto> request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("Office/Update", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {result.ReturnValue}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell UpdateOffice");
            }
            return result;
        }

        /// <summary>
        /// Creates the office.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<BaseResponse> CreateOffice(CreateRequest<OfficeDto> request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("Office/Create", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {result.ReturnValue}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell CreateOffice");
            }
            return result;
        }
        #endregion

    }
}
