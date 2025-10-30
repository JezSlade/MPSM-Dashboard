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
        #region Connectors

        /// <summary>
        /// Gets the offices.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<PagedResultResponse<ExplorerDataDto>> GetConnectors(GetExplorerDatasRequest request)
        {
            PagedResultResponse<ExplorerDataDto> result = new PagedResultResponse<ExplorerDataDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<PagedResultResponse<ExplorerDataDto>>("Explorer/GetConnectors", request);

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
                _logger.LogError(ex, $"Errore nell GetConnectors");
            }
            return result;
        }

        /// <summary>
        /// Returns eXplorer Configurations
        /// </summary>
        /// <returns></returns>
        public async Task<PagedResultResponse<ExplorerConfigurationBaseDto>> GetExplorerConfigurations(GetExplorerConfigurationsRequest request)
        {
            PagedResultResponse<ExplorerConfigurationBaseDto> result = new PagedResultResponse<ExplorerConfigurationBaseDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<PagedResultResponse<ExplorerConfigurationBaseDto>>("Explorer/Configuration/List", request);

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
                _logger.LogError(ex, $"Errore nell GetExplorerConfigurations");
            }
            return result;
        }




        /// <summary>
        /// Returns eXplorer Configurations
        /// </summary>
        /// <returns></returns>
        public async Task<SingleResultResponse<ExplorerConfigurationDto>> GetExplorerConfiguration(GetExplorerConfigurationRequest request)
        {
            SingleResultResponse<ExplorerConfigurationDto> result = new SingleResultResponse<ExplorerConfigurationDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<SingleResultResponse<ExplorerConfigurationDto>>("Explorer/Configuration/Get", request);

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
                _logger.LogError(ex, $"Errore nell GetExplorerConfiguration");
            }
            return result;
        }



        /// <summary>
        /// This operation gets explorer data from all dealer customer
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<PagedResultResponse<ExplorerDataDto>> GetExplorerDatas(GetExplorerDatasRequest request)
        {
            PagedResultResponse<ExplorerDataDto> result = new PagedResultResponse<ExplorerDataDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<PagedResultResponse<ExplorerDataDto>>("Explorer/GetExplorerDatas", request);

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
                _logger.LogError(ex, $"Errore nell GetExplorerDatas");
            }
            return result;
        }


        /// <summary>
        /// This operation gets explorer data from all dealer customer
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> UpdateExplorerConfiguration(UpdateRequest<ExplorerConfigurationDto> request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PutAsync<BaseResponse>("Explorer/Configuration/Update", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }

            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell UpdateExplorerConfiguration");
            }
            return result;
        }

        /// <summary>
        /// Delete schedule on explorerconfiguration
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> DeleteSchedule(DeleteExplorerScheduleRequest request) 
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.DeleteAsync<BaseResponse>("Explorer/Schedule/Delete", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }

            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nella DeleteSchedule");
            }
            return result;
        }


        /// <summary>
        /// Delete schedule on explorerconfiguration
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> UpdateSchedule(UpdateExplorerScheduleRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("Explorer/Schedule/Update", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }

            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nella UpdateSchedule");
            }
            return result;
        }

        /// <summary>
        /// Delete schedule on explorerconfiguration
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> CreateSchedule(CreateExplorerScheduleRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("Explorer/Schedule/Create", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }

            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nella CreateSchedule");
            }
            return result;
        }



        /// <summary>
        /// Delete schedule on explorerconfiguration
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> DeleteExplorerSubnet(DeleteExplorerSubnetRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.DeleteAsync<BaseResponse>("Explorer/Subnet/Delete", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }

            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nella DeleteExplorerSubnet");
            }
            return result;
        }

        /// <summary>
        /// Update eXplorer subnet
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> UpdateExplorerSubnet(UpdateExplorerSubnetRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PutAsync<BaseResponse>("Explorer/Subnet/Update", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }

            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nella UpdateExplorerSubnet");
            }
            return result;
        }

        /// <summary>
        /// Create eXplorer subnet
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> CreateExplorerSubnet(CreateExplorerSubnetRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("Explorer/Subnet/Create", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"DETAILED: {JsonConvert.SerializeObject(result)}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }

            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nella CreateExplorerSubnet");
            }
            return result;
        }
        #endregion

    }
}
