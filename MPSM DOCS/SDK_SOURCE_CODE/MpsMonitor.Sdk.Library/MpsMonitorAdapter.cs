using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Logging.Abstractions;
using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Responses;
using MpsRestClientStandard;
using MpsRestClientStandard.TokenStorage;
using Newtonsoft.Json;
using System;
using System.Threading.Tasks;

namespace MpsMonitor.Sdk.Library
{
    /// <summary>
    /// 
    /// </summary>
    public partial class MpsMonitorAdapter : IMpsMonitorAdapter
    {
        private ILogger _logger;
        private MpsClient _mpsMonitorClient = null;

        /// <summary>
        /// Costruttore
        /// </summary>
        /// <param name="logger"></param>
        public MpsMonitorAdapter(ILogger logger = null)
        {
            this._logger = logger ?? new NullLogger<IMpsMonitorAdapter>();
        }
      

        /// <summary>
        /// 
        /// </summary>
        /// <param name="endpoint"></param>
        /// <param name="clientId"></param>
        /// <param name="clientSecret"></param>
        /// <param name="username"></param>
        /// <param name="password"></param>
        /// <returns></returns>
        public bool Authenticate(string endpoint, string clientId, string clientSecret, string username, string password)
        {
            try
            {
                var authData = new AuthData(clientId, clientSecret);
                var tokenStorage = new InMemoryTokenStorage();

                _mpsMonitorClient = new MpsClient(endpoint, authData, tokenStorage);
                bool result = _mpsMonitorClient.Authenticate(username, password, AuthenticationScope.Account);

                _logger.LogDebug($"User authenticate status: {result} ");
                return result;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell'autenticazione");
            }
            return false;
        }


        /// <summary>
        /// Get Countries
        /// </summary>
        /// <returns></returns>
        public async Task<ListResultResponse<CountryDto>> GetCountries()
        {
            ListResultResponse<CountryDto> result = new ListResultResponse<CountryDto>();
            try
            {
               
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<ListResultResponse<CountryDto>>("Common/GetCountries",null);
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
                _logger.LogError(ex, $"Errore nell GetCountries");
            }
            return result;
        }

        /// <summary>
        /// check if client si connected
        /// </summary>
        /// <param name="result"></param>
        /// <returns></returns>
        public bool IsValidClient(BaseResponse result)
        {
            if (_mpsMonitorClient == null)
            {
                _logger.LogDebug($"MpsClient = null. First it is necessary  to authenticate ");
                result.IsValid = false;
                result.Errors.Add(new CodeDesc("MpsClient Error", "First it is necessary  to authenticate "));
                return result.IsValid;
            }
            return true;
        }


        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        public async Task<ListResultResponse<EntityIdDescIntDto>> MaintenanceKitColors()
        {
            ListResultResponse<EntityIdDescIntDto> result = new ListResultResponse<EntityIdDescIntDto>();
            try
            {

                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.GetAsync<ListResultResponse<EntityIdDescIntDto>>("Common/GetMaintenanceKitColors", null);
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
                _logger.LogError(ex, $"Errore nell GetMaintenanceKitColors");
            }
            return result;
        }

        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        public async Task<ListResultResponse<EntityIdDescIntDto>> MaintenanceKitTypes()
        {
            ListResultResponse<EntityIdDescIntDto> result = new ListResultResponse<EntityIdDescIntDto>();
            try
            {

                if (!IsValidClient(result))
                {
                    return result;
                }

                result =await  _mpsMonitorClient.GetAsync<ListResultResponse<EntityIdDescIntDto>>("Common/GetMaintenanceKitTypes", null);
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
                _logger.LogError(ex, $"Errore nell GetMaintenanceKitTypes");
            }
            return result;
        }

    }
}
