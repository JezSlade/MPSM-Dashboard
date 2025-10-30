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
        #region shippedsupply
        /// <summary>
        /// Lists the specified request.
        /// </summary>
        /// <param name="request">The request.</param>
        /// <returns></returns>
        public async Task<PagedResultResponse<ShippedSupplyDto>> GetShippedSupply(GetShippedSuppliesRequest request)
        {
            PagedResultResponse<ShippedSupplyDto> result = new PagedResultResponse<ShippedSupplyDto>();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<PagedResultResponse<ShippedSupplyDto>>("ShippedSupply/List", request);

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
                _logger.LogError(ex, $"Errore nell GetShippedSupply");
            }
            return result;
        }

        //private static void ShippedSupplyCreateInAdvance(SupplyAlertListDto supplyAlert)
        //{
        //    if (string.IsNullOrWhiteSpace(supplyAlert.SuggestedPartNumber))
        //    {
        //        Console.WriteLine($"WARNING: You can't automatically manage alert on SN: {supplyAlert.SerialNumber} because you don't have supplies associated or you have more than 1");
        //        return;
        //    }

        //    var request = new CreateShippingSupplyRequest();
        //    request.DeviceId = supplyAlert.DeviceId;
        //    request.Quantity = 1;

        //    request.Counter = 0; // The counter of the printer.
        //    request.Creation = DateTime.UtcNow;
        //    request.Supply = new DealerSupplyDto();
        //    request.Supply.PartNumber = "ABCD";
        //    request.ReplaceSupplyInCustomSet = false;

        //    // Shipment information
        //    request.DocumentNumber = null;
        //    request.OrderNumber = null;
        //    request.Department = null;
        //    request.Contact = null;

        //    // Shipment management actions
        //    request.SendCustomerNotificationEmail = false;
        //    request.ActivateLogisticNotification = true;


        //    var result = await _mpsMonitorClient.PostAsync<BaseResponse>("ShippedSupply/CreateInAdvance", request);
        //    if (result.IsValid)
        //    {
        //        Console.WriteLine($"ALERT ID: {result.ReturnValue}");
        //    }
        //    else
        //    {
        //        Console.WriteLine($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
        //    }
        //}

        //private static void ShippedSupplyCreateStock(SupplyAlertListDto supplyAlert)
        //{
        //    if (string.IsNullOrWhiteSpace(supplyAlert.SuggestedPartNumber))
        //    {
        //        Console.WriteLine($"WARNING: You can't automatically manage alert on SN: {supplyAlert.SerialNumber} because you don't have supplies associated or you have more than 1");
        //        return;
        //    }

        //    var request = new CreateShippingSupplyRequest();
        //    request.DeviceId = supplyAlert.DeviceId;
        //    request.Quantity = 1;

        //    request.Counter = 0; // The counter of the printer.
        //    request.Creation = DateTime.UtcNow;
        //    request.Supply = new DealerSupplyDto();
        //    request.Supply.PartNumber = "ABCD";
        //    request.ReplaceSupplyInCustomSet = false;

        //    // Shipment information
        //    request.DocumentNumber = null;
        //    request.OrderNumber = null;
        //    request.Department = null;
        //    request.Contact = null;

        //    // Shipment management actions
        //    request.SendCustomerNotificationEmail = false;
        //    request.ActivateLogisticNotification = true;

        //    var result = await _mpsMonitorClient.PostAsync<BaseResponse>("ShippedSupply/CreateStock", request);
        //    if (result.IsValid)
        //    {
        //        Console.WriteLine($"ALERT ID: {result.ReturnValue}");
        //    }
        //    else
        //    {
        //        Console.WriteLine($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
        //    }
        //}

        /// <summary>
        /// Creates a shipped supply entry for a device (and alert).
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> CreateOnAlert(CreateShippingSupplyOnAlertRequest request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("ShippedSupply/CreateOnAlert", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"ALERT ID: {result.ReturnValue}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }

            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell CreateOnAlert");
            }
            return result;
        }

        /// <summary>
        /// Updates the specified request.
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        public async Task<BaseResponse> UpdateShippedSupply(UpdateRequest<UpdateShippedSupplyDto> request)
        {
            BaseResponse result = new BaseResponse();
            try
            {
                if (!IsValidClient(result))
                {
                    return result;
                }

                result = await _mpsMonitorClient.PostAsync<BaseResponse>("ShippedSupply/Update", request);

                if (result.IsValid)
                {
                    _logger.LogDebug($"supplyAlert ID: {result.ReturnValue}");
                }
                else
                {
                    _logger.LogDebug($"ERROR: {JsonConvert.SerializeObject(result.Errors)}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Errore nell UpdateShippedSupply");
            }
            return result;
        }

        #endregion
    }
}
