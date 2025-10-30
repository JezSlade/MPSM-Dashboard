using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Requests;
using Newtonsoft.Json;
using System;
using System.Text.RegularExpressions;
using System.Windows;
using System.Windows.Input;

namespace MpsMonitor.Sdk.Simulator
{
    /// <summary>
    /// Interaction logic for Deliveries.xaml
    /// </summary>
    public partial class Deliveries : Window
    {
        internal string customerCode;
        public string responseMsg { get; private set; }
        IMpsMonitorAdapter _adapter = null;
        internal string supplyAlertId;
        internal string dealerCode;

        public Deliveries(IMpsMonitorAdapter adapter)
        {
            _adapter = adapter;
            InitializeComponent();

            CreationDate.SelectedDate = DateTime.Now;

        }



        #region Common Function
        /// <summary>
        /// 
        /// </summary>
        /// <param name="request"></param>
        /// <param name="url"></param>
        /// <param name="method"></param>
        private void SetInfoResults<T>(T request, string url, string method, string response)
        {
            ResponseBox.Text = response;
            RequestDto<T> requestType = new RequestDto<T>();
            requestType.Url = url;
            requestType.Request = request;
            requestType.Method = method;
            RequestBox.Text = $"{JsonConvert.SerializeObject(requestType, Formatting.Indented)}";
        }

        private void NumberValidationTextBox(object sender, TextCompositionEventArgs e)
        {
            Regex regex = new Regex("[^0-9]+");
            e.Handled = regex.IsMatch(e.Text);
        }

        #endregion



        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnSave_Click(object sender, RoutedEventArgs e)
        {

            var dto = new UpdateShippedSupplyDto();
            dto.SupplyAlertId = supplyAlertId;

            dto.Creation = CreationDate.SelectedDate.Value;
            dto.DocumentNumber = txtDocumentNumber.Text;
            dto.OrderNumber = txtOrderNumber.Text;
            dto.Department = txtDepartment.Text;
            dto.Contact = txtContact.Text;

            // Shipment management actions
            dto.SendMail = chkSendCustomerNotificationEmail.IsChecked.Value;
            dto.SendLogisticMail = chkActivateLogisticNotification.IsChecked.Value;

            var request = new UpdateRequest<UpdateShippedSupplyDto>();
            request.ObjectToUpdate = dto;

            var result = await _adapter.UpdateShippedSupply(request);

            if (result.IsValid)
            {
                responseMsg = $"Alert Id: \n {result.ReturnValue}";
            }
            else
            {
                responseMsg = $"Error in  UpdateShippedSupply: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "ShippedSupply/Update", "POST", responseMsg);
        }
    }
}
