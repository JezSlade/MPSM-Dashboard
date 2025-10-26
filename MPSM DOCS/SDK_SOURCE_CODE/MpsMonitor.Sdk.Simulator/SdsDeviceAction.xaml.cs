using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Enums;
using MpsMonitor.Sdk.Models.Requests;
using Newtonsoft.Json;
using System;
using System.ComponentModel;
using System.Linq;
using System.Windows;
using System.Windows.Controls;

namespace MpsMonitor.Sdk.Simulator
{
    /// <summary>
    /// Interaction logic for SdsDeviceAction.xaml
    /// </summary>
    public partial class SdsDeviceAction : Window
    {
        IMpsMonitorAdapter _adapter = null;
        public string responseMsg { get; private set; }
        private SdsDeviceActionDto _response;
        internal string deviceId;

        public SdsDeviceAction(IMpsMonitorAdapter adapter, string requestId)
        {
            _adapter = adapter;
            InitializeComponent();
            cmbState.ItemsSource = Enum.GetValues(typeof(SdsActionUpdateStateEnum)).Cast<SdsActionUpdateStateEnum>();

            loadServiceRequest(requestId);

        }

        private void loadServiceRequest(string requestId)
        {

            //==== Prepare Request
            var request = new GetByIdRequest();
            request.Id = requestId;

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.GetDeviceAction(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        _response = result.Result;
                        txtCode.Text = _response.Code;
                        txtActionType.Text = _response.ActionType;
                        txtCurrentState.Text = _response.CurrentState.ToString();
                        txtActionDateUtc.Text = _response.ActionDateUtc.ToString();
                        txtEventCodeContext.Text = _response.EventCodeContext;
                        txtFirmwareVersion.Text = _response.FirmwareVersion;
                        txtSeverity.Text = _response.Severity.ToString();

                        string lblDetail = "";
                        responseMsg = $"Request Details{lblDetail}: \n {JsonConvert.SerializeObject(_response, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetDeviceAction : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "SdsAction/GetDeviceAction", "GET", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();
        }

        private void CmbState_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {

        }

        private async void BtnSave_Click(object sender, RoutedEventArgs e)
        {
            var request = new ChangeDeviceActionStatusRequest();

            request.ActionJamId = _response.ActionJamId;
            request.Comments = txtDescription.Text;
            request.DeviceId = deviceId;

            request.State = (SdsActionUpdateStateEnum)cmbState.SelectedItem;
            request.Id = _response.Id;

            var result = await _adapter.ChangeDeviceActionStatus(request);

            if (result.IsValid)
            {
                responseMsg = $"ChangeDeviceActionStatus: \n {JsonConvert.SerializeObject(result.ReturnValue, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in  ChangeDeviceActionStatus: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "SdsAction/ChangeDeviceActionStatus", "POST", responseMsg);
        }

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

    }
}
