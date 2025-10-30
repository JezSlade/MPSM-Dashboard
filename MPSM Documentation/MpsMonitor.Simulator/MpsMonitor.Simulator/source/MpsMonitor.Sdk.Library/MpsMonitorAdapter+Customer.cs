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
        #region Customer

        /// <summary>
        /// Gets the customers.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<PagedResultResponse<CustomerListDto>> GetCustomers(GetCustomersRequest request)
        {
            PagedResultResponse<CustomerListDto> result = new PagedResultResponse<CustomerListDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<PagedResultResponse<CustomerListDto>>("Customer/GetCustomers", request);
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
        /// Gets the customer.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<SingleResultResponse<CustomerDto>> GetCustomerByCode(GetByCodeRequest request)
        {
            SingleResultResponse<CustomerDto> result = new SingleResultResponse<CustomerDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }
                result = await  _mpsMonitorClient.PostAsync<SingleResultResponse<CustomerDto>>("Customer/GetCustomerByCode", request);
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
                _logger.LogError(ex, $"Errore nell GetCustomer");
            }
            return result;
        }

        /// <summary>
        /// Gets the customer.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<SingleResultResponse<CustomerDto>> GetCustomerById(GetByIdRequest request)
        {
            SingleResultResponse<CustomerDto> result = new SingleResultResponse<CustomerDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }
                result = await _mpsMonitorClient.PostAsync<SingleResultResponse<CustomerDto>>("Customer/GetCustomer", request);
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
                _logger.LogError(ex, $"Errore nell GetCustomer");
            }
            return result;
        }

        /// <summary>
        ///  Creates the customer.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<CreateCustomerResponse> CreateCustomer(CreateRequest<CreateCustomerDto> request)
        {
            CreateCustomerResponse result = new CreateCustomerResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<CreateCustomerResponse>("Customer/CreateCustomer", request);
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
                _logger.LogError(ex, $"Errore nell CreateCustomer");
            }
            return result;
        }

        /// <summary>
        ///  Update the customer.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<SingleResultResponse<CustomerDto>> UpdateCustomer(UpdateRequest<UpdateCustomerDto> request)
        {
            SingleResultResponse<CustomerDto> result = new SingleResultResponse<CustomerDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<SingleResultResponse<CustomerDto>>("Customer/UpdateCustomer", request);
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
                _logger.LogError(ex, $"Errore nell UpdateCustomer");
            }
            return result;
        }

        #endregion
    }
}
