using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Requests;
using Newtonsoft.Json;
using System;
using System.Text.RegularExpressions;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Input;

namespace MpsMonitor.Sdk.Simulator
{
    /// <summary>
    /// Interaction logic for HpSdsOperations.xaml
    /// </summary>
    public partial class HpSdsOperations : Window
    {
        internal string customerCode;
        public string responseMsg { get; private set; }
        IMpsMonitorAdapter _adapter = null;
        internal string deviceId;
        internal string dealerCode;
        internal bool restart;

        public HpSdsOperations(IMpsMonitorAdapter adapter)
        {
            _adapter = adapter;
            InitializeComponent();

            //for (int i = 0; i < 24; i++)
            //{
            //    cmbHours.Items.Add(i);
            //}

            //for (int i = 0; i < 60; i++)
            //{
            //    cmbMinutes.Items.Add(i);
            //}
            //cmbHours.SelectedIndex = 0;
            //cmbMinutes.SelectedIndex = 0;
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


        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnSave_Click(object sender, RoutedEventArgs e)
        {
            if (restart)
            {
                RestartDevice();
            }
            else {
                UpdateFirmware();
            }
          
        }

        private async  void UpdateFirmware()
        {
            var request = new SetDeviceUpdateFirmwareRequest();
            request.Id = deviceId;
            request.FwVersion = cmbVersion.SelectedValue.ToString();
            
            request.OperationFirmwareUpdateAtUtc = GetUtcDate(); 


            var result = await _adapter.SetDeviceUpdateFirmware(request);

            if (result.IsValid)
            {
                responseMsg = $"The update has been scheduled";
            }
            else
            {
                responseMsg = $"Error in  SetDeviceUpdateFirmware: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }


            SetInfoResults(request, "SdsDevice/SetDeviceUpdateFirmware", "POST", responseMsg);
        }

        /// <summary>
        /// 
        /// </summary>
        private async void RestartDevice()
        {
            var request = new SetDeviceRebootRequest();
            request.Id = deviceId;


            request.OperationRebootAtUtc = GetUtcDate();

            var result = await _adapter.SetDeviceReboot(request);

            if (result.IsValid)
            {
                responseMsg = $"The reboot has been scheduled";
            }
            else
            {
                responseMsg = $"Error in  SetDeviceReboot: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }


            SetInfoResults(request, "SdsDevice/SetDeviceReboot", "POST", responseMsg);
        }

        private DateTime? GetUtcDate()
        {
            //DateTime? schedule = pckScheduleOn.SelectedDate;
            DateTime? schedule = datetimeScheduleOn.Value;
            DateTime? utcDate = null;

            if (schedule.HasValue)
            {
                
                utcDate = DateTime.SpecifyKind(schedule.Value, DateTimeKind.Utc);
            }
           //lblDateUtc.Text = date.HasValue ? $"UTC: {date.Value.ToString()}" : string.Empty;
            return utcDate;
        }

        private void CmbHours_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            //var date = GetUtcDate();
            
        }

        private void CmbMinutes_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            //var date = GetUtcDate();
        }

        #endregion

        private void PckScheduleOn_SelectedDateChanged(object sender, SelectionChangedEventArgs e)
        {
            //var date = GetUtcDate();
        }

        private void DatetimeScheduleOn_ValueChanged(object sender, RoutedPropertyChangedEventArgs<object> e)
        {

        }
    }
}
