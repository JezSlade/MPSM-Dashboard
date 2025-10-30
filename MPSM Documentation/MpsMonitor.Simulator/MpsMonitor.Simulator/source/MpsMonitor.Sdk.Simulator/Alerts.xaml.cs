using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Enums;
using MpsMonitor.Sdk.Models.Requests;
using Newtonsoft.Json;
using System;
using System.ComponentModel;
using System.Text.RegularExpressions;
using System.Windows;
using System.Windows.Input;

namespace MpsMonitor.Sdk.Simulator
{
    /// <summary>
    /// Interaction logic for Alerts.xaml
    /// </summary>
    public partial class Alerts : Window
    {
        internal string customerCode;
        public string responseMsg { get; private set; }

        private IMpsMonitorAdapter _adapter = null;
        internal string alertId;
        internal string dealerCode;
        internal string _shippedSupplyId;
        internal string deviceId;
        private SupplyAlertDto _supplyAlert;

        public Alerts(IMpsMonitorAdapter adapter, string supplyAlertId)
        {
            _adapter = adapter;

            InitializeComponent();

            CreationDate.SelectedDate = DateTime.Now;

            loadSupplyAlert(supplyAlertId);

        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="supplyAlertId"></param>
        private void loadSupplyAlert(string supplyAlertId)
        {
            //==== Prepare Request
            var request = new GetByIdRequest();
            request.Id = supplyAlertId;

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.GetSupplyAlert(request).Result;
                _supplyAlert = result.Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        BindObject(result.Result);

                        string lblDetail = "";
                        responseMsg = $"Supply Alert Details{lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetSupplyAlert : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "SupplyAlert/GetSupplyAlert", "POST", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();
        }

        private void BindObject(SupplyAlertDto _supplyAlert)
        {
            if (_supplyAlert != null)
            {
                alertId = _supplyAlert.Id;
                deviceId = _supplyAlert.InstalledProductId; //== miss
                // txtCustomer.Text = _supplyAlert.CustomerDescription; //== miss
                txtSerialNumber.Text = _supplyAlert.SerialNumber;
                txtAssertNumber.Text = _supplyAlert.AssetNumber;
                //txtIpAddress.Text = _supplyAlert.IpAddress;//== miss
                txtBrand.Text = _supplyAlert.Product.Brand;
                txtModel.Text = _supplyAlert.Product.Model;
                txtSupplyType.Text = _supplyAlert.SupplyType.ToString();
                txtColorType.Text = _supplyAlert.ColorType.ToString();
                txtWarning.Text = _supplyAlert.Warning;
                txtOffice.Text = _supplyAlert.Office.Description;
                txtProject.Text = _supplyAlert.Project.Description;
                txtInitialResidual.Text = _supplyAlert.InitialResidualPercentage.ToString();
                txtActualResidual.Text = _supplyAlert.ActualResidualPercentage.ToString();
                txtActualDate.Text = _supplyAlert.ActualDate.HasValue ? _supplyAlert.ActualDate.Value.Date.ToShortDateString() : string.Empty;

                txtInstalledDate.Text = _supplyAlert.TonerInstalled.HasValue ? _supplyAlert.TonerInstalled.Value.Date.ToShortDateString() : string.Empty;
                txtCancelledOn.Text = _supplyAlert.CanceledOn.HasValue ? _supplyAlert.CanceledOn.Value.Date.ToShortDateString() : string.Empty;
                //                txtDeliveredOn.Text = _supplyAlert.ShippedSupplyCreation.HasValue ? _supplyAlert.ShippedSupplyCreation.Value.Date.ToShortDateString() : string.Empty;//== miss
                chkHidden.IsChecked = _supplyAlert.IsHidden;
                //txtGenerationType.Text = _supplyAlert.ShippedSupplyGenerationType.ToString();//== miss
                txtExhaustedExpiration.Text = _supplyAlert.ExhaustedExpiration.HasValue ? _supplyAlert.ExhaustedExpiration.Value.Date.ToShortDateString() : string.Empty;
                //txtSuggestedPartNumber.Text = _supplyAlert.SuggestedPartNumber;//== miss

                if (_supplyAlert.TonerInstalled.HasValue || _supplyAlert.CanceledOn.HasValue)
                {
                    spOperations.Visibility = Visibility.Collapsed;
                    return;
                }

                if (!_supplyAlert.CanceledOn.HasValue && _supplyAlert.IsHidden.HasValue && _supplyAlert.IsHidden.Value)
                {
                    TabShipping.IsEnabled = false;
                    TabOptions.IsEnabled = true;
                    btnCancelAlert.IsEnabled = btnHideAlert.IsEnabled = btnPostPone.IsEnabled = false;
                    btnShowAlert.IsEnabled = true;
                    return;
                }
                if (!string.IsNullOrWhiteSpace(_shippedSupplyId) && _supplyAlert.IsShipped)//== miss
                {
                    TabShipping.IsEnabled = false;
                    TabControllOperations.SelectedIndex = 1;
                }
                btnShowAlert.IsEnabled = false;

            }
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


        private async void BtnCreateShippingOnAlert_Click(object sender, RoutedEventArgs e)
        {
            if (string.IsNullOrWhiteSpace(txtSuggestedPartNumber.Text))
            {
                MessageBox.Show($"WARNING: You can't automatically manage alert on SN: {txtSuggestedPartNumber.Text} because you don't have supplies associated or you have more than 1");
                return;
            }

            if (string.IsNullOrWhiteSpace(txtPartNumber.Text))
            {
                MessageBox.Show($"Error: Supply Part Number is required");
                return;
            }
            //==== Prepare Request
            var request = new CreateShippingSupplyOnAlertRequest();
            request.SupplyAlertId = alertId;
            request.DeviceId = deviceId;
            request.Quantity = string.IsNullOrWhiteSpace(txtQty.Text) ? 1 : int.Parse(txtQty.Text);

            request.Counter = 0; // The counter of the printer.
            //request.Creation = DateTime.UtcNow;
            request.Creation = CreationDate.SelectedDate.Value;
            request.Supply = new DealerSupplyDto();
            request.Supply.PartNumber = txtPartNumber.Text; // "ABCD";
            request.ReplaceSupplyInCustomSet = false;

            // Shipment information
            request.DocumentNumber = string.IsNullOrWhiteSpace(txtDocumentNumber.Text) ? txtDocumentNumber.Text : null;
            request.OrderNumber = string.IsNullOrWhiteSpace(txtOrderNumber.Text) ? txtOrderNumber.Text : null;
            request.Department = string.IsNullOrWhiteSpace(txtDepartment.Text) ? txtDepartment.Text : null;
            request.Contact = string.IsNullOrWhiteSpace(txtContact.Text) ? txtContact.Text : null;

            // Shipment management actions
            request.SendCustomerNotificationEmail = chkSendCustomerNotificationEmail.IsChecked.Value;
            request.ActivateLogisticNotification = chkActivateLogisticNotification.IsChecked.Value;


            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.CreateOnAlert(request).Result;

                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        responseMsg = $"Alert Id: \n {result.ReturnValue}";
                    }
                    else
                    {
                        responseMsg = $"Error in  CreateOnAlert: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "ShippedSupply/CreateOnAlert", "POST", responseMsg);

                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();
        }



        /// <summary>
        /// Cancel Alert
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnCancelAlert_Click(object sender, RoutedEventArgs e)
        {
            var installRequest = new UpdateRequest<MassiveUpdateAlertDto>
            {
                ObjectToUpdate = new MassiveUpdateAlertDto
                {
                    Id = new[] { alertId },
                    DealerCode = dealerCode,
                    Cancel = true
                }
            };
            UpdatesAlertsStatus(installRequest);
        }

        /// <summary>
        /// Hide Alert
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnHideAlert_Click(object sender, RoutedEventArgs e)
        {
            var installRequest = new UpdateRequest<MassiveUpdateAlertDto>
            {
                ObjectToUpdate = new MassiveUpdateAlertDto
                {
                    Id = new[] { alertId },
                    DealerCode = dealerCode,
                    Hidden = true
                }
            };
            UpdatesAlertsStatus(installRequest);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnShowAlert_Click(object sender, RoutedEventArgs e)
        {
            var installRequest = new UpdateRequest<MassiveUpdateAlertDto>
            {
                ObjectToUpdate = new MassiveUpdateAlertDto
                {
                    Id = new[] { alertId },
                    DealerCode = dealerCode,
                    Hidden = false
                }
            };
            UpdatesAlertsStatus(installRequest);
        }

        /// <summary>
        /// Update Alert Status
        /// </summary>
        /// <param name="installRequest"></param>
        private async void UpdatesAlertsStatus(UpdateRequest<MassiveUpdateAlertDto> installRequest)
        {
            var result = await _adapter.UpdatesAlertsStatus(installRequest);
            if (result.IsValid)
            {
                responseMsg = $"Alert updated: \n {JsonConvert.SerializeObject(result.ReturnValue, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in  UpdatesAlertsStatus: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }
            SetInfoResults(installRequest, "SupplyAlert/MassiveUpdate", "POST", responseMsg);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnPostPone_Click(object sender, RoutedEventArgs e)
        {

            var installRequest = new UpdateRequest<PostponeAlertDto>
            {
                ObjectToUpdate = new PostponeAlertDto
                {
                    Id = alertId,
                    Percentage = string.IsNullOrWhiteSpace(txtPercentage.Text) ? 25 : (int)(txtPercentage.Value.Value * 10)
                }
            };
            var result = await _adapter.SupplyAlertPostPone(installRequest);
            if (result.IsValid)
            {
                responseMsg = $"Alert Post Poned: \n {JsonConvert.SerializeObject(result.ReturnValue, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in  PostponeAlert: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }
            SetInfoResults(installRequest, "SupplyAlert/PostponeAlert", "PUT", responseMsg);
        }

        /// <summary>
        /// Gets available supplies for a device
        /// </summary>
        /// <param name="request"></param>
        /// <returns></returns>
        private void BtnSuggestedAlert_Click(object sender, RoutedEventArgs e)
        {

            //==== Prepare Request
            var request = new GetAvailableSuppliesForADeviceRequest
            {
                DeviceId = deviceId,
                SupplyType = _supplyAlert.SupplyType,
                ColorType = _supplyAlert.ColorType,
                MaintenanceKitTypeId = _supplyAlert.MaintenanceKitType?.Id,
                MaintenanceKitColorId = _supplyAlert.MaintenanceKitColor?.Id,
                Warning = _supplyAlert.Warning,
                ShowOnlyCurrentSuppliesInUse = true
            };


            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.GetAvailableSuppliesForADevice(request).Result;

                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                        {
                            if (result.IsValid)
                            {
                                responseMsg = $"Suggested Part Numbers: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                            }
                            else
                            {
                                responseMsg = $"Error in GetAvailableSuppliesForADevice : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                            }

                            SetInfoResults(request, "SupplyAlert/GetAvailableSuppliesForADevice", "GET", responseMsg);
                        }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();
        }
    }
}
