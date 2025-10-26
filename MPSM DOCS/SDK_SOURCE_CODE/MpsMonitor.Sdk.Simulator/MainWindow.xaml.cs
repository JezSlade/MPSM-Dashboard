using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;
using MpsMonitor.Sdk.Library;
using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Enums;
using MpsMonitor.Sdk.Models.Requests;
using Newtonsoft.Json;
using Serilog;
using System;
using System.Collections;
using System.Collections.Generic;
using System.ComponentModel;
using System.Configuration;
using System.Linq;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Controls.Primitives;
using System.Windows.Data;
using System.Windows.Input;
using static MpsMonitor.Sdk.Models.Requests.PagedRequest;
using MessageBox = System.Windows.MessageBox;

namespace MpsMonitor.Sdk.Simulator
{
    /// <summary>
    /// Interaction logic for MainWindow.xaml
    /// </summary>
    public partial class MainWindow : Window
    {
        IMpsMonitorAdapter adapter = null;
        private bool _isAuth = false;
        private string _dealerCode;
        private string _customerCode;
        private string responseMsg;

        public MainWindow()
        {
            InitializeComponent();

            this.ClientId.Text = ConfigurationManager.AppSettings["ClientId"].ToString();
            this.ClientSecret.Text = ConfigurationManager.AppSettings["ClientSecret"].ToString();
            this.Username.Text = ConfigurationManager.AppSettings["Username"].ToString();
            this.Password.Text = ConfigurationManager.AppSettings["Password"].ToString();


            //==== comboBox degli endpoint
            var section = (Hashtable)ConfigurationManager.GetSection("EndPoints");
            Dictionary<string, string> endPoints = section.Cast<DictionaryEntry>().ToDictionary(d => (string)d.Key, d => (string)d.Value);
            cmbEndPoint.ItemsSource = endPoints;
            cmbEndPoint.DisplayMemberPath = "Key";
            cmbEndPoint.SelectedValuePath = "Value";


            //==== comboBox sortOrder delle griglie
            cmbDeviceSortOrder.ItemsSource =
                cmbAlertsSortOrder.ItemsSource =
                cmbDeliveriesSortOrder.ItemsSource =
                cmbSdsSrSortOrder.ItemsSource =
                cmbOfficesSortOrder.ItemsSource =
                cmbSuppliesSortOrder.ItemsSource =
                cmbConnectorsSortOrder.ItemsSource =
                cmbConnectorsConfigSortOrder.ItemsSource =
                    Enum.GetValues(typeof(SortDirectionEnum)).Cast<SortDirectionEnum>();

            cmbSuppliesPageNumber.SelectedIndex =
                cmbDeviceSortOrder.SelectedIndex =
                cmbAlertsSortOrder.SelectedIndex =
                cmbSdsSrSortOrder.SelectedIndex =
                cmbDeliveriesSortOrder.SelectedIndex =
                cmbOfficesSortOrder.SelectedIndex =
                cmbSuppliesSortOrder.SelectedIndex =
                cmbConnectorsSortOrder.SelectedIndex =
                cmbConnectorsConfigSortOrder.SelectedIndex =  0;


            var svcProvider = new ServiceCollection()
               .AddLogging(builder =>
               {
                   var logger = new LoggerConfiguration()
                   .ReadFrom.AppSettings()
                   //.MinimumLevel.Debug()
                   //.WriteTo.RollingFile(System.IO.Path.Combine(@"c:\\temp\\logs", "log-{Date}.txt"),shared:true, rollingInterval: RollingInterval.Day)
                   .CreateLogger();

                   builder.AddSerilog(logger, true);
               })
               .BuildServiceProvider();

            adapter = new MpsMonitorAdapter(svcProvider.GetRequiredService<ILogger<IMpsMonitorAdapter>>());
        }

        /// <summary>
        /// Authentication Button
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void AuthButton_Click(object sender, RoutedEventArgs e)
        {

            string endpoint = string.Empty;
            if (cmbEndPoint.SelectedItem != null)
            {
                endpoint = ((KeyValuePair<string, string>)cmbEndPoint.SelectedItem).Value;
            }
            string clientId = this.ClientId.Text;
            string clientSecret = this.ClientSecret.Text;
            string username = this.Username.Text;
            string password = this.Password.Text;

            if (string.IsNullOrWhiteSpace(endpoint) || string.IsNullOrWhiteSpace(clientId)
                || string.IsNullOrWhiteSpace(clientSecret) || string.IsNullOrWhiteSpace(username) || string.IsNullOrWhiteSpace(password))
            {
                ResponseBox.Text = $"Fill in the fields for authentication";
                return;
            }

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                _isAuth = adapter.Authenticate(endpoint, clientId, clientSecret, username, password);
                //use the Dispatcher to delegate the result back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (_isAuth)
                    {
                        TabDealer.IsEnabled = true;
                    }

                    ResponseBox.Text = $"User authenticate status: {_isAuth.ToString()}";
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();

        }

        private void CmbEndPoint_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            this.urlSelected.Text = ((KeyValuePair<string, string>)cmbEndPoint.SelectedItem).Value;
        }


        private void BtnReomveDevice_Click(object sender, RoutedEventArgs e)
        {
            ListViewDevices.SelectedItem = null;
        }

        private void BtnReomveCustomer_Click(object sender, RoutedEventArgs e)
        {
            cmbCustomers.ItemsSource = null;
        }

        private void BtnReomveDealer_Click(object sender, RoutedEventArgs e)
        {
            cmbDealers.ItemsSource = null;
            cmbCustomers.ItemsSource = null;
            ListViewDevices.ItemsSource = null;
            ListViewSupplies.ItemsSource = null;
            ResponseBox.Text = RequestBox.Text = string.Empty;
        }

        #region Dealers

        /// <summary>
        /// Get Dealers
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnGetDealers_Click(object sender, RoutedEventArgs e)
        {
            if (!_isAuth)
            {
                Xceed.Wpf.Toolkit.MessageBox.Show("You must be authenticated");
                return;
            }
            var request = new FilteredPagedRequest();


            var result = await adapter.GetDealers(request);
            if (result.IsValid)
            {
                cmbDealers.ItemsSource = result.Result;
                cmbDealers.DisplayMemberPath = "Description";
                cmbDealers.SelectedValuePath = "Code";
                cmbDealers.SelectedIndex = 0;
                responseMsg = $"Dealer List ({result.Result.Count()}): \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in GetDealers : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Dealer/GetDealers", "POST", responseMsg);
        }


        /// <summary>
        /// Combo Dealers SelectionChanged
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void CmbDealers_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if (cmbDealers.SelectedItem != null)
            {
                txtDealerId.Text = ((Models.Dto.EntityDto)cmbDealers.SelectedItem).Id;
                btnGetDealerDetails.IsEnabled = true;
                btnGetCustomers.IsEnabled = true;
                TabCustomer.IsEnabled = true;
                TabSupplies.IsEnabled = true;
                TabSDSServiceRequest.IsEnabled = true;
                lblDealer.Text = $"Dealer: {((DealerBaseDto)cmbDealers.SelectedItem).Description}";
                lblSuppliesSelectedDealer.Text = $"{((DealerBaseDto)cmbDealers.SelectedItem).Description}";
                lblSdsSrSelectedDealer.Text = $"{((DealerBaseDto)cmbDealers.SelectedItem).Description}";
                _dealerCode = cmbDealers.SelectedValue.ToString();
            }
            else
            {
                txtDealerId.Text = string.Empty;
                lblDealer.Text = $"Dealer:";
                lblSuppliesSelectedDealer.Text = string.Empty;
                lblSdsSrSelectedDealer.Text = string.Empty;
                btnGetDealerDetails.IsEnabled = false;
                btnGetCustomers.IsEnabled = false;
                TabCustomer.IsEnabled = false;
                TabSupplies.IsEnabled = false;
                TabSDSServiceRequest.IsEnabled = false;
                _dealerCode = string.Empty;
            }
        }


        /// <summary>
        /// Get Dealer Details
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnGetDealerDetails_Click(object sender, RoutedEventArgs e)
        {
            if (cmbDealers == null || cmbDealers.SelectedValue == null)
            {
                MessageBox.Show("Select a dealer");
                return;
            }

            var request = new GetByCodeRequest();
            request.Code = _dealerCode;

            var result = await adapter.GetDealer(request);

            if (result.IsValid)
            {
                responseMsg = $"Dealer Details: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in GetDealer : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Dealer/GetDealer", "POST", responseMsg);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnClearDealer_Click(object sender, RoutedEventArgs e)
        {
            btnReomveDealer.RaiseEvent(new RoutedEventArgs(ButtonBase.ClickEvent));

        }
        #endregion

        #region Customer
        /// <summary>
        /// Get Customers
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnGetCustomers_Click(object sender, RoutedEventArgs e)
        {
            if (string.IsNullOrWhiteSpace(_dealerCode))
            {
                MessageBox.Show("Select a dealer");
                return;
            }
            var request = new GetCustomersRequest();
            request.DealerCode = _dealerCode;
            request.PageRows = int.MaxValue;

            var result = await adapter.GetCustomers(request);
            if (result.IsValid)
            {
                cmbCustomers.ItemsSource = result.Result;
                cmbCustomers.DisplayMemberPath = "Description";
                cmbCustomers.SelectedValuePath = "Code";
                cmbCustomers.SelectedIndex = 0;
                responseMsg = $"Customer List ({result.Result.Count()}): \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in GetCustomers : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Customer/GetCustomers", "POST", responseMsg);
        }


        /// <summary>
        /// Get Customer Details
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnGetCustomerDetails_Click(object sender, RoutedEventArgs e)
        {
            if (cmbCustomers == null || cmbCustomers.SelectedValue == null)
            {
                MessageBox.Show("Select a customer");
                return;
            }

            var request = new GetByCodeRequest();
            request.Code = _customerCode;

            var result = await adapter.GetCustomerByCode(request);

            if (result.IsValid)
            {
                responseMsg = $"Customer Details: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in GetCustomer : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Customer/GetCustomerByCode", "POST", responseMsg);
        }


        /// <summary>
        /// Combo Customers SelectionChanged
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void CmbCustomers_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if (cmbCustomers.SelectedItem != null)
            {
                txtCustomerId.Text = ((Models.Dto.EntityDto)cmbCustomers.SelectedItem).Id;
                btnGetCustomerDetails.IsEnabled = true;
                btnCustomersMeters.IsEnabled = true;
                btnCustomersDeviceDetailedCounters.IsEnabled = true;
                btnCustomersDeviceBlendedCounters.IsEnabled = true;
                lblCustomer.Text = $"Customer: {((CustomerBaseDto)cmbCustomers.SelectedItem).Description}";
                TabOffices.IsEnabled = true;
                TabConnectorsConfig.IsEnabled = true;
                btnGetOffices.IsEnabled = true;
                _customerCode = cmbCustomers.SelectedValue.ToString();

            }
            else
            {
                txtCustomerId.Text = string.Empty;
                btnGetCustomerDetails.IsEnabled = false;
                btnCustomersMeters.IsEnabled = false;
                btnCustomersDeviceDetailedCounters.IsEnabled = false;
                btnCustomersDeviceBlendedCounters.IsEnabled = false;
                TabOffices.IsEnabled = false;
                TabConnectorsConfig.IsEnabled = false;
                btnGetOffices.IsEnabled = false;
                lblCustomer.Text = $"Customer:";
                _customerCode = string.Empty;
            }
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnAddCustomer_Click(object sender, RoutedEventArgs e)
        {
            AddCustomer addCustomerWin = new AddCustomer(adapter, _dealerCode);

            addCustomerWin.txtDealer.Text = ((DealerBaseDto)cmbDealers.SelectedItem)?.Description;
            addCustomerWin.ShowDialog();

            //== reload CmbCustomers
            btnGetCustomers.RaiseEvent(new RoutedEventArgs(ButtonBase.ClickEvent));
        }
        #endregion

        #region Device

        /// <summary>
        /// 
        /// </summary>
        /// <param name="customerCode">If empty, get dealer's devices</param>
        private async void GetDevices(string customerCode = "")
        {
            ListViewDevices.ItemsSource = null;
            bool showCustomerColumn = true;

            //==== Prepare Request
            var request = new GetDevicesRequest();
            request.FilterDealerId = txtDealerId.Text;
            //==== Pagination
            request.PageNumber = cmbDevicePageNumber.SelectedIndex + 1;
            request.PageRows = iudDevicePageRows.Value.Value;
            request.SortOrder = ((SortDirectionEnum)cmbDeviceSortOrder.SelectedItem);

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
           {

               if (!string.IsNullOrWhiteSpace(customerCode))
               {
                   request.FilterCustomerCodes = new[] { customerCode };
                   showCustomerColumn = false;
               }
               var result = adapter.GetDevices(request).Result;
                //use the Dispatcher to delegate the result back to the UI
                Dispatcher.Invoke((Action)(() =>
               {

                   if (result.IsValid)
                   {
                       cmbDevicePageNumber.IsEnabled = true;

                       lblDevicesTotalRows.Text = result.TotalRows.ToString();
                        //iudDevicePageNumber.Maximum = result.TotalRows / request.PageRows;
                        cmbDevicePageNumber.Items.Clear();
                       for (int i = 1; i <= result.TotalRows / request.PageRows + 1; i++)
                       {
                           cmbDevicePageNumber.Items.Add(i);
                       }
                       cmbDevicePageNumber.SelectedIndex = request.PageNumber - 1;
                       ListViewDevices.ItemsSource = result.Result;
                       var gridView = new GridView();
                       ListViewDevices.View = gridView;
                       List<GridViewColumn> gridinfo = Helper.GetGridDevicesColums();

                       if (showCustomerColumn)
                           gridinfo.Insert(2, new GridViewColumn() { Header = "Customer", DisplayMemberBinding = new Binding("CustomerDescription") });

                       CreateGridView(gridView, gridinfo);
                       responseMsg = $"Devices ({result.Result.Count()}): \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                   }
                   else
                   {
                       responseMsg = $"Error in GetDevices : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                   }

                   SetInfoResults(request, "Device/List", "POST", responseMsg);
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
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnGetDevices_Click(object sender, RoutedEventArgs e)
        {
            string customerCode = string.Empty;

            ListViewDevices.ItemsSource = null;
            string TabName = (MainTabControl.SelectedItem as TabItem).Header.ToString();

            switch (TabName)
            {
                case "Dealer":
                    lblDeviceGrid.Text = "Dealer's devices";
                    break;

                case "Customer":
                    lblDeviceGrid.Text = "Customer's devices";
                    if (string.IsNullOrWhiteSpace(_customerCode))
                    {
                        MessageBox.Show("Select a customer");
                        return;
                    }
                    else
                    {
                        customerCode = _customerCode;
                    }
                    break;

                case "Dispositivi":
                    if (cmbCustomers == null || cmbCustomers.SelectedValue == null)
                    {
                        lblDeviceGrid.Text = "Dealer's devices";
                    }
                    else
                    {
                        lblDeviceGrid.Text = "Customer's devices";
                        customerCode = _customerCode;
                    }
                    break;
            }

            GetDevices(customerCode);

        }

        /// <summary>
        /// Get Device
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnGetDevice_Click(object sender, RoutedEventArgs e)
        {
            //==== Prepare request
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            var request = new GetByIdRequest();
            request.Id = selectedDevice?.Id;

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = adapter.GetDevice(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        responseMsg = $"Device Detailed: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetDevice : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Device/Get", "POST", responseMsg);
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
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void ListViewDevices_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            if (selectedDevice == null)
            {
                TabDevices.IsEnabled = false;
                btnDeviceMeters.IsEnabled = false;
                btnDeviceDetailedCounters.IsEnabled = false;
                btnDeviceBlendedCounters.IsEnabled = false;
                spHpSds.Visibility = Visibility.Hidden;
                btnGetDevice.IsEnabled = false;
                btnSuppliesDetails.IsEnabled = false;
                btnListMaintenanceKitCounters.IsEnabled = false;
                lblDevice.Text = $"Device:";
            }
            else
            {
                btnDeviceMeters.IsEnabled = true;
                btnDeviceDetailedCounters.IsEnabled = true;
                btnDeviceBlendedCounters.IsEnabled = true;
                btnGetDevice.IsEnabled = true;
                btnSuppliesDetails.IsEnabled = true;
                btnListMaintenanceKitCounters.IsEnabled = true;

                //=== If device selected, also customer must be selected
                if (string.IsNullOrWhiteSpace(_customerCode))
                {
                    btnGetCustomers.RaiseEvent(new RoutedEventArgs(ButtonBase.ClickEvent));
                }
                cmbCustomers.SelectedItem = cmbCustomers.ItemsSource?.Cast<CustomerListDto>()?.First(i => i.Id == selectedDevice.CustomerId);

                if (selectedDevice.SdsDevice != null && selectedDevice.SdsDevice.JamDeviceId.HasValue)
                {
                    spHpSds.Visibility = Visibility.Visible;
                }
                else
                {
                    spHpSds.Visibility = Visibility.Hidden;
                }
            }
            string serialnumber = selectedDevice?.SerialNumber;
            lblDevice.Text = $"Device: {serialnumber}";
        }

        /// <summary>
        /// Dettaglio device
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void ListViewDevices_MouseDoubleClick(object sender, MouseButtonEventArgs e)
        {
            if (string.IsNullOrWhiteSpace(_dealerCode) || string.IsNullOrWhiteSpace(_customerCode))
            {
                //MessageBox.Show("Per maggiori informazioni sulle metriche Select a dealer e un customer.");
            }
            else
            {
                btnDeviceMeters.IsEnabled = true;
                btnDeviceDetailedCounters.IsEnabled = true;
                btnDeviceBlendedCounters.IsEnabled = true;
            }

            TabDevices.IsEnabled = true;
            MainTabControl.SelectedIndex = 3;
            btnGetDevice.IsEnabled = true;
            btnSuppliesDetails.IsEnabled = true;
            btnListMaintenanceKitCounters.IsEnabled = true;
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;


            var request = new GetByIdRequest();
            request.Id = selectedDevice?.Id;

            var result = await adapter.GetDevice(request);

            if (result.IsValid)
            {
                responseMsg = $"Device Detailed: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in GetDevice : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Device/Get", "POST", responseMsg);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnSdsRestart_Click(object sender, RoutedEventArgs e)
        {
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            HpSdsOperations hpOperationWin = new HpSdsOperations(adapter);
            hpOperationWin.Title = "Reboot Device";
            hpOperationWin.deviceId = selectedDevice?.Id;
            hpOperationWin.spVersionColumn.Visibility = Visibility.Hidden;
            hpOperationWin.lblRestart.Text = "Restart on";
            hpOperationWin.restart = true;

            hpOperationWin.ShowDialog();
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnSdsUpdateFirmware_Click(object sender, RoutedEventArgs e)
        {
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            HpSdsOperations hpOperationWin = new HpSdsOperations(adapter);
            hpOperationWin.Title = "Update Firmware";
            hpOperationWin.deviceId = selectedDevice?.Id;
            hpOperationWin.spVersionColumn.Visibility = Visibility.Visible;
            hpOperationWin.lblRestart.Text = "Update on";

            var request = new GetByIdRequest();
            request.Id = selectedDevice?.Id;

            var device = await adapter.GetDevice(request);
            if (device != null && device.Result.SdsDevice != null && !device.Result.SdsDevice.Firmwares.Any())
            {
                MessageBox.Show("There are no firmware to update ");
                return;
            }

            hpOperationWin.cmbVersion.ItemsSource = device?.Result?.SdsDevice.Firmwares;

            hpOperationWin.cmbVersion.SelectedValuePath = "BuildVersion";
            hpOperationWin.cmbVersion.SelectedIndex = 0;
            hpOperationWin.restart = false;
            hpOperationWin.ShowDialog();


        }

        /// <summary>
        /// ScanImmediate
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnSdsScan_Click(object sender, RoutedEventArgs e)
        {
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            if (selectedDevice != null)
            {
                var request = new GetByIdRequest();

                request.Id = selectedDevice?.Id;
                var result = await adapter.ScanImmediate(request);
                if (result.IsValid)
                {
                    responseMsg = $"Scan started correctly. The operation will take about 20 minutes ";
                }
                else
                {
                    responseMsg = $"Error in ScanImmediate : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                }
                SetInfoResults(request, "SdsScan/ScanImmediate", "GET", responseMsg);
            }
        }

        /// <summary>
        /// GetDeviceRemoteEws
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnSdsEnableEWS_Click(object sender, RoutedEventArgs e)
        {
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            if (selectedDevice != null)
            {
                var request = new GetByIdRequest();
                request.Id = selectedDevice?.Id;
                var result = await adapter.GetDeviceRemoteEws(request);
                if (result.IsValid)
                {
                    responseMsg = $"Details: \n {JsonConvert.SerializeObject(result.ReturnValue, Formatting.Indented)}";
                    System.Diagnostics.Process.Start(result.ReturnValue);
                }
                else
                {
                    responseMsg = $"Error in GetDeviceRemoteEws : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                }
                SetInfoResults(request, "SdsDevice/GetDeviceRemoteEws", "GET", responseMsg);
            }
        }

        /// <summary>
        /// Returns a device by request parameters
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnSuppliesDetails_Click(object sender, RoutedEventArgs e)
        {
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            var request = new GetByIdRequest();
            request.Id = selectedDevice?.Id;

            var result = await adapter.GetSuppliesDetails(request);

            if (result.IsValid)
            {
                responseMsg = $"Supplies Details: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in GetSuppliesDetails : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }
            ResponseBox.Text = string.Empty;
            SetInfoResults(request, "Device/GetSuppliesDetails", "GET", responseMsg);
        }

        /// <summary>
        /// Returns maintenance kit counters
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private async void BtnListMaintenanceKitCounters_Click(object sender, RoutedEventArgs e)
        {
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            var request = new GetByIdRequest();
            request.Id = selectedDevice?.Id;

            var result = await adapter.ListMaintenanceKitCounters(request);

            if (result.IsValid)
            {
                responseMsg = $"Maintenance Kit Counters ({result.Result?.Count()}): \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in ListMaintenanceKitCounters : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }
            ResponseBox.Text = string.Empty;
            SetInfoResults(request, "Counter/ListMaintenanceKitCounters", "GET", responseMsg);
        }

        #endregion

        #region Meter Collection

        /// <summary>
        /// Customers Meters
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnCustomersMeters_Click(object sender, RoutedEventArgs e)
        {
            if (string.IsNullOrWhiteSpace(_dealerCode) || string.IsNullOrWhiteSpace(_customerCode))
            {
                MessageBox.Show("Select a dealer and customer");
                return;
            }

            GetMeters(iudCounterslastdays.Value.Value, string.Empty);
        }

        /// <summary>
        /// DEvice Meters
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnDeviceMeters_Click(object sender, RoutedEventArgs e)
        {
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            string serialnumber = selectedDevice.SerialNumber;

            GetMeters(iudCountersDevicelastdays.Value.Value, serialnumber);

        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="lastdays"></param>
        /// <param name="serialnumber"></param>
        private async void GetMeters(int lastdays, string serialnumber)
        {
            var request = new GetCountersRequest();
            request.DealerCode = _dealerCode;
            request.CustomerCode = _customerCode;
            request.FromDate = DateTime.UtcNow.AddDays(lastdays * -1);
            request.ToDate = DateTime.UtcNow;
            request.SerialNumber = serialnumber;

            var result = await adapter.GetCounters(request);

            if (result.IsValid)
            {
                string lblDetail = string.IsNullOrEmpty(serialnumber) ? "Customer Meters" : "Device Meters";
                responseMsg = $"{lblDetail} ({result.Result.Count()}): \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in GetCounters : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Counter/List", "POST", responseMsg);
        }
        #endregion

        #region  Detailed Counters

        /// <summary>
        /// Customers Device Detailed Counters
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnCustomersDeviceDetailedCounters_Click(object sender, RoutedEventArgs e)
        {
            if (string.IsNullOrWhiteSpace(_dealerCode) || string.IsNullOrWhiteSpace(_customerCode))
            {
                MessageBox.Show("Select a dealer and a customer");
                return;
            }

            GetDetailedCounters();
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnDeviceDetailedCounters_Click(object sender, RoutedEventArgs e)
        {
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;

            string serialnumber = selectedDevice.SerialNumber;

            GetDetailedCounters(serialnumber);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="serialnumber"></param>
        private async void GetDetailedCounters(string serialnumber = "")
        {
            var request = new GetCountersDetailedRequest();
            request.DealerCode = _dealerCode;
            request.CustomerCode = _customerCode;
            request.SerialNumber = serialnumber;

            var result = await adapter.GetListDetailedCounters(request);

            if (result.IsValid)
            {
                string lblDetail = string.IsNullOrEmpty(serialnumber) ? "Customer Details Counter" : "Device Details Counter";
                responseMsg = $"{lblDetail} ({result.Result.Count()}): \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in GetListDetailedCounters: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Counter/ListDetailed", "POST", responseMsg);
        }


        #endregion

        #region  Blended Counters

        /// <summary>
        /// Customers Device Detailed Counters
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnCustomersDeviceBlendedCounters_Click(object sender, RoutedEventArgs e)
        {
            if (string.IsNullOrWhiteSpace(_dealerCode) || string.IsNullOrWhiteSpace(_customerCode))
            {
                MessageBox.Show("Select a dealer and a customer");
                return;
            }

            GetBlendedCounters(iudCounterslastdays.Value.Value);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnDeviceBlendedCounters_Click(object sender, RoutedEventArgs e)
        {
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;

            string serialnumber = selectedDevice.SerialNumber;

            GetBlendedCounters(iudCounterslastdays.Value.Value, serialnumber);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="serialnumber"></param>
        private async void GetBlendedCounters(int lastdays, string serialnumber = "")
        {
            var request = new GetCountersRequest();
            request.DealerCode = _dealerCode;
            request.CustomerCode = _customerCode;
            request.FromDate = DateTime.UtcNow.AddDays(lastdays * -1);
            request.ToDate = DateTime.UtcNow;
            request.SerialNumber = serialnumber;

            var result = await adapter.GetListBlendedCounters(request);

            if (result.IsValid)
            {
                string lblDetail = string.IsNullOrEmpty(serialnumber) ? "Customer Blended Counter" : "Device Blended Counter";
                responseMsg = $"{lblDetail} ({result.Result.Count()}): \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in GetListBlendedCounters: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Counter/ListBlended", "POST", responseMsg);
        }


        #endregion

        #region Alert
        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnGetOpenSupplyAlert_Click(object sender, RoutedEventArgs e)
        {
            GetSupplyAlert("Opend", SupplyAlertManageOptionEnum.NotManaged, SupplyAlertHiddenOptionEnum.NotHidden);
        }
        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnGetCancelledSupplyAlert_Click(object sender, RoutedEventArgs e)
        {
            GetSupplyAlert("Cancelled", cancellOption: SupplyAlertCancelOptionEnum.Canceled);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnGetAllSupplyAlert_Click(object sender, RoutedEventArgs e)
        {

            GetSupplyAlert("All");
        }

        private void BtnGetHideSupplyAlert_Click(object sender, RoutedEventArgs e)
        {
            GetSupplyAlert("Hide", hiddenOption: SupplyAlertHiddenOptionEnum.Hidden);
        }

        private void BtnGetInstalledSupplyAlert_Click(object sender, RoutedEventArgs e)
        {
            GetSupplyAlert("Installed", installationOption: SupplyAlertInstallationOptionEnum.Installed);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="state"></param>
        /// <param name="manageOption"></param>
        /// <param name="hiddenOption"></param>
        /// <param name="cancellOption"></param>
        /// <param name="installationOption"></param>
        private void GetSupplyAlert(string state, SupplyAlertManageOptionEnum? manageOption = null, SupplyAlertHiddenOptionEnum? hiddenOption = null, SupplyAlertCancelOptionEnum? cancellOption = null, SupplyAlertInstallationOptionEnum? installationOption = null)
        {
            string TabName = (MainTabControl.SelectedItem as TabItem).Header.ToString();
            string msgAlert = string.Empty;
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            string customerCode = _customerCode;

            //===== Prepare Grid
            List<GridViewColumn> gridinfo = Helper.GetGridAlertColums();

            switch (TabName)
            {
                case "Dealer":
                    if (string.IsNullOrWhiteSpace(_dealerCode))
                        msgAlert = "Select a dealer";
                    //filtro solo per dealer
                    selectedDevice = null;
                    customerCode = string.Empty;
                    lblAlertGrid.Text = $"Dealer's {state} Alerts";

                    break;

                case "Customer":
                    if (string.IsNullOrWhiteSpace(_customerCode))
                        msgAlert = "Select a customer";
                    //filtro solo per dealer e customer
                    selectedDevice = null;
                    gridinfo.RemoveAt(1);
                    lblAlertGrid.Text = $"Customer's {state} Alerts";

                    break;

                case "Dispositivi":
                    if (selectedDevice == null)
                        msgAlert = "Select a device";
                    gridinfo.RemoveAt(1); //customer l'indice da rimuove è sempre 1 perchè dopo la rimozione gli elementi scalano
                    gridinfo.RemoveAt(1); //serial number 
                    gridinfo.RemoveAt(1); //brand 
                    gridinfo.RemoveAt(1); //model
                    lblAlertGrid.Text = $"Device's {state} Alerts";

                    break;
            }

            if (!string.IsNullOrWhiteSpace(msgAlert))
            {
                MessageBox.Show(msgAlert);
                ResponseBox.Text = string.Empty;
                return;
            }

            //==== Prepare Request
            var request = new GetSupplyAlertRequest();
            request.SortColumn = "InitialDate";
            request.DealerCode = _dealerCode;
            request.CustomerCode = customerCode;
            request.DeviceId = selectedDevice?.Id;
            request.ManageOption = manageOption;
            request.HiddenOption = hiddenOption;
            request.CancelOption = cancellOption;
            request.InstallationOption = installationOption;

            //=== pagination
            request.PageNumber = cmbAlertsPageNumber.SelectedIndex + 1;
            request.PageRows = iudAlertsPageRows.Value.Value;
            request.SortOrder = ((SortDirectionEnum)cmbAlertsSortOrder.SelectedItem);


            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = adapter.GetSupplyAlerts(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        cmbAlertsPageNumber.IsEnabled = true;
                        lblAlertsTotalRows.Text = result.TotalRows.ToString();
                        cmbAlertsPageNumber.Items.Clear();

                        for (int i = 1; i <= result.TotalRows / request.PageRows + 1; i++)
                        {
                            cmbAlertsPageNumber.Items.Add(i);
                        }
                        cmbAlertsPageNumber.SelectedIndex = request.PageNumber - 1;

                        ListViewAlerts.ItemsSource = result.Result;
                        var gridView = new GridView();
                        ListViewAlerts.View = gridView;

                        CreateGridView(gridView, gridinfo);

                        string lblDetail = $"ManageOption={manageOption.ToString()} - HiddenOption={hiddenOption.ToString()}";
                        responseMsg = $"Alerts count({result.Result.Count()}) {lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetSupplyAlerts : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "SupplyAlert/List", "POST", responseMsg);
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
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void ListViewAlerts_MouseDoubleClick(object sender, MouseButtonEventArgs e)
        {
            SupplyAlertListDto selectedAlert = (SupplyAlertListDto)ListViewAlerts.SelectedItem;
            if (selectedAlert != null)
            {
                Alerts alertWin = new Alerts(adapter, selectedAlert.Id);
                alertWin.lblCustomer.Text = lblCustomer.Text;
                alertWin.lblDealer.Text = lblDealer.Text;
                alertWin.txtDealer.Text = alertWin.dealerCode = _dealerCode;
                alertWin.customerCode = _customerCode;

                //== miss in details
                alertWin.txtCustomer.Text = selectedAlert.CustomerDescription;
                alertWin.txtIpAddress.Text = selectedAlert.IpAddress;
                alertWin.txtDeliveredOn.Text = selectedAlert.ShippedSupplyCreation.HasValue ? selectedAlert.ShippedSupplyCreation.Value.Date.ToShortDateString() : string.Empty;
                alertWin.txtGenerationType.Text = selectedAlert.ShippedSupplyGenerationType.ToString();
                alertWin.txtSuggestedPartNumber.Text = selectedAlert.SuggestedPartNumber;
                alertWin._shippedSupplyId = selectedAlert.ShippedSupplyId;

                alertWin.ShowDialog();
            }
            else
            {
                MessageBox.Show("Select a alert.");
                ResponseBox.Text = string.Empty;
            }
        }
        #endregion

        #region Deliveries
        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnGetShippedSupplies_Click(object sender, RoutedEventArgs e)
        {
            List<GridViewColumn> gridinfo = Helper.GetGridShippedColumns();

            string TabName = (MainTabControl.SelectedItem as TabItem).Header.ToString();
            string msgAlert = string.Empty;
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            string customerCode = _customerCode;

            switch (TabName)
            {
                case "Dealer":
                    if (string.IsNullOrWhiteSpace(_dealerCode))
                        msgAlert = "Select a dealer";
                    //filtro solo per dealer
                    selectedDevice = null;
                    customerCode = string.Empty;


                    break;

                case "Customer":
                    if (string.IsNullOrWhiteSpace(_customerCode))
                        msgAlert = "Select a customer";
                    //filtro solo per dealer e customer
                    selectedDevice = null;
                    gridinfo.RemoveAt(1);
                    break;

                case "Dispositivi":
                    if (selectedDevice == null)
                        msgAlert = "Select a device";
                    gridinfo.RemoveAt(1); //customer l'indice da rimuove è sempre 1 perchè dopo la rimozione gli elementi scalano
                    gridinfo.RemoveAt(1); //serial number 
                    //lascio l'assernumber 
                    gridinfo.RemoveAt(2); //brand 
                    gridinfo.RemoveAt(2); //model
                    break;
            }
            if (!string.IsNullOrWhiteSpace(msgAlert))
            {
                MessageBox.Show(msgAlert);
                ResponseBox.Text = string.Empty;
                return;
            }

            //==== Prepare Request
            var request = new GetShippedSuppliesRequest();
            request.DealerCode = _dealerCode;
            request.CustomerCode = customerCode;

            //=== pagination
            request.PageNumber = cmbDeliveriesPageNumber.SelectedIndex + 1;
            request.PageRows = iudDeliveriesPageRows.Value.Value;
            request.SortOrder = ((SortDirectionEnum)cmbDeliveriesSortOrder.SelectedItem);

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = adapter.GetShippedSupply(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        cmbDeliveriesPageNumber.IsEnabled = true;
                        lblDeliveriesTotalRows.Text = result.TotalRows.ToString();
                        cmbDeliveriesPageNumber.Items.Clear();

                        for (int i = 1; i <= result.TotalRows / request.PageRows + 1; i++)
                        {
                            cmbDeliveriesPageNumber.Items.Add(i);
                        }
                        cmbDeliveriesPageNumber.SelectedIndex = request.PageNumber - 1;

                        ListViewDelivery.ItemsSource = result.Result;
                        var gridView = new GridView();
                        ListViewDelivery.View = gridView;

                        CreateGridView(gridView, gridinfo);
                        string lblDetail = "";
                        responseMsg = $"ShippedSupplies count({ result.Result.Count()}) {lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetShippedSupply : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "SupplyAlert/List", "POST", responseMsg);
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
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void ListViewDelivery_MouseDoubleClick(object sender, MouseButtonEventArgs e)
        {
            ShippedSupplyDto selectedSupplyAlert = (ShippedSupplyDto)ListViewDelivery.SelectedItem;
            if (selectedSupplyAlert != null)
            {
                Deliveries deliveryWin = new Deliveries(adapter);
                deliveryWin.lblCustomer.Text = lblCustomer.Text;
                deliveryWin.lblDealer.Text = lblDealer.Text;
                deliveryWin.txtDealer.Text = deliveryWin.dealerCode = _dealerCode;
                deliveryWin.supplyAlertId = selectedSupplyAlert.Id;
                deliveryWin.customerCode = _customerCode;

                deliveryWin.txtCustomer.Text = selectedSupplyAlert.CustomerDescription;
                deliveryWin.txtContact.Text = selectedSupplyAlert.Contact;
                deliveryWin.txtDepartment.Text = selectedSupplyAlert.Department;
                deliveryWin.txtOrderNumber.Text = selectedSupplyAlert.OrderNumber;
                deliveryWin.txtDocumentNumber.Text = selectedSupplyAlert.DocumentNumber;

                deliveryWin.txtCounter.Text = selectedSupplyAlert.Counter.ToString();
                deliveryWin.txtQty.Text = selectedSupplyAlert.Quantity.ToString();
                deliveryWin.CreationDate.SelectedDate = selectedSupplyAlert.Creation;

                deliveryWin.txtType.Text = selectedSupplyAlert.Generation.ToString();


                deliveryWin.ShowDialog();
            }
            else
            {
                MessageBox.Show("Seleziona un delivery.");
                ResponseBox.Text = string.Empty;
            }
        }
        #endregion

        #region Offices

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnGetOffices_Click(object sender, RoutedEventArgs e)
        {
            List<GridViewColumn> gridinfo = Helper.GetGridOfficesColums();

            //==== Prepare Request
            var request = new GetOfficesRequest();
            request.CustomerCode = _customerCode;

            //=== pagination
            request.PageNumber = cmbOfficesPageNumber.SelectedIndex + 1;
            request.PageRows = iudOfficesPageRows.Value.Value;
            request.SortOrder = ((SortDirectionEnum)cmbOfficesSortOrder.SelectedItem);

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = adapter.GetOffices(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        cmbOfficesPageNumber.IsEnabled = true;
                        lblOfficesTotalRows.Text = result.TotalRows.ToString();
                        cmbOfficesPageNumber.Items.Clear();

                        for (int i = 1; i <= result.TotalRows / request.PageRows + 1; i++)
                        {
                            cmbOfficesPageNumber.Items.Add(i);
                        }
                        cmbOfficesPageNumber.SelectedIndex = request.PageNumber - 1;
                        ListViewOffices.ItemsSource = result.Result;
                        var gridView = new GridView();
                        ListViewOffices.View = gridView;

                        CreateGridView(gridView, gridinfo);
                        string lblDetail = "";
                        responseMsg = $"Offices count({result.Result.Count()}) {lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetOffices : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Office/List", "POST", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();

        }

        private void ListViewOffices_MouseDoubleClick(object sender, MouseButtonEventArgs e)
        {
            OfficeListDto selectedOffice = (OfficeListDto)ListViewOffices.SelectedItem;
            if (selectedOffice != null)
            {
                Offices officeWin = new Offices(adapter, selectedOffice.Id);
                officeWin.lblCustomer.Text = lblCustomer.Text;
                officeWin.lblDealer.Text = lblDealer.Text;
                officeWin.customerCode = _customerCode;

                officeWin.ShowDialog();
            }
            else
            {
                MessageBox.Show("Seleziona un ufficio.");
                ResponseBox.Text = string.Empty;
            }
        }

        private void BtnAddOffices_Click(object sender, RoutedEventArgs e)
        {
            Offices officeWin = new Offices(adapter, string.Empty);
            officeWin.lblCustomer.Text = lblCustomer.Text;
            officeWin.lblDealer.Text = lblDealer.Text;
            officeWin.customerCode = _customerCode;
            officeWin.Code.IsEnabled = true;
            officeWin.ShowDialog();
        }
        #endregion


        #region Connectors

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnGetConnectors_Click(object sender, RoutedEventArgs e)
        {

            List<GridViewColumn> gridinfo = Helper.GetGridConnectorsColums();

            string TabName = (MainTabControl.SelectedItem as TabItem).Header.ToString();
            string msgAlert = string.Empty;
            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            string customerCode = _customerCode;

            switch (TabName)
            {
                case "Dealer":
                    if (string.IsNullOrWhiteSpace(_dealerCode))
                        msgAlert = "Select a dealer";
                    //filtro solo per dealer
                    selectedDevice = null;
                    customerCode = string.Empty;


                    break;

                case "Customer":
                    if (string.IsNullOrWhiteSpace(_customerCode))
                        msgAlert = "Select a customer";
                    //filtro solo per dealer e customer
                    selectedDevice = null;
                    gridinfo.RemoveAt(2); //remove customer code 
                    gridinfo.RemoveAt(2);//remove customer description
                    break;

                    //case "Dispositivi":
                    //    if (selectedDevice == null)
                    //        msgAlert = "Select a device";
                    //    gridinfo.RemoveAt(1); //customer l'indice da rimuove è sempre 1 perchè dopo la rimozione gli elementi scalano
                    //    gridinfo.RemoveAt(1); //serial number 
                    //    //lascio l'assernumber 
                    //    gridinfo.RemoveAt(2); //brand 
                    //    gridinfo.RemoveAt(2); //model
                    //    break;
            }
            if (!string.IsNullOrWhiteSpace(msgAlert))
            {
                MessageBox.Show(msgAlert);
                ResponseBox.Text = string.Empty;
                return;
            }




            //==== Prepare Request
            var request = new GetExplorerDatasRequest();
            request.FilterDealerId = txtDealerId.Text;
            request.FilterCustomerId= txtCustomerId.Text;
            //=== pagination
            request.PageNumber = cmbConnectorsPageNumber.SelectedIndex + 1;
            request.PageRows = iudConnectorsPageRows.Value.Value;
            request.SortOrder = ((SortDirectionEnum)cmbConnectorsSortOrder.SelectedItem);

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = adapter.GetConnectors(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        cmbConnectorsPageNumber.IsEnabled = true;
                        lblConnectorsTotalRows.Text = result.TotalRows.ToString();
                        cmbConnectorsPageNumber.Items.Clear();

                        for (int i = 1; i <= result.TotalRows / request.PageRows + 1; i++)
                        {
                            cmbConnectorsPageNumber.Items.Add(i);
                        }
                        cmbConnectorsPageNumber.SelectedIndex = request.PageNumber - 1;
                        ListViewConnectors.ItemsSource = result.Result;
                        var gridView = new GridView();
                        ListViewConnectors.View = gridView;

                        CreateGridView(gridView, gridinfo);
                        string lblDetail = "";
                        responseMsg = $"Connectors count({result.Result.Count()}) {lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetConnectors : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/GetConnectors", "GET", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();

        }

        private void ListViewConnectors_MouseDoubleClick(object sender, MouseButtonEventArgs e)
        {
          
        }


        private void BtnGetConnectorsConfig_Click(object sender, RoutedEventArgs e)
        {
            List<GridViewColumn> gridinfo = Helper.GetGridConfigurationsColums();

            //==== Prepare Request
            var request = new GetExplorerConfigurationsRequest();
            request.CustomerCode = _customerCode;

            //=== pagination
            request.PageNumber = cmbConnectorsConfigPageNumber.SelectedIndex + 1;
            request.PageRows = iudConnectorsConfigPageRows.Value.Value;
            request.SortOrder = ((SortDirectionEnum)cmbConnectorsConfigSortOrder.SelectedItem);

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = adapter.GetExplorerConfigurations(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        cmbConnectorsConfigPageNumber.IsEnabled = true;
                        lblConnectorsConfigTotalRows.Text = result.TotalRows.ToString();
                        cmbConnectorsConfigPageNumber.Items.Clear();

                        for (int i = 1; i <= result.TotalRows / request.PageRows + 1; i++)
                        {
                            cmbConnectorsConfigPageNumber.Items.Add(i);
                        }
                        cmbConnectorsConfigPageNumber.SelectedIndex = request.PageNumber - 1;
                        ListViewConnectorsConfig.ItemsSource = result.Result;
                        var gridView = new GridView();
                        ListViewConnectorsConfig.View = gridView;

                        CreateGridView(gridView, gridinfo);
                        string lblDetail = "";
                        responseMsg = $"ConnectorsConfig count({result.Result.Count()}) {lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetConnectorsConfig : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/Configuration/List", "GET", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();
        }

        private void ListViewConnectorsConfig_MouseDoubleClick(object sender, MouseButtonEventArgs e)
        {
            ExplorerConfigurationBaseDto selectedConfig = (ExplorerConfigurationBaseDto)ListViewConnectorsConfig.SelectedItem;
            if (selectedConfig != null)
            {
                ConnectorsConfig connectorConfigWin = new ConnectorsConfig(adapter, selectedConfig.Id, _dealerCode, _customerCode, txtDealerId.Text,txtCustomerId.Text);
                connectorConfigWin._customerCode = _customerCode;
                connectorConfigWin.txtExplorerConfigId.Text = selectedConfig.Id;
                connectorConfigWin.txtCustomerId.Text = txtCustomerId.Text;

                connectorConfigWin.ShowDialog();
                BtnGetConnectorsConfig_Click(sender, e);
            }
            else
            {
                MessageBox.Show("Select a configuration.");
                ResponseBox.Text = string.Empty;
            }
        }

        #endregion

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

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void MainTabControl_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            string TabName = (MainTabControl.SelectedItem as TabItem)?.Name.ToString();

            switch (TabName)
            {
                case null:
                    break;

                case "tabAuth":
                    spGridDevice.Visibility = Visibility.Collapsed;
                    selectedElements.IsEnabled = false;
                    break;
                case "TabSupplies":
                case "TabSDSServiceRequest":
                    spGridDevice.Visibility = Visibility.Collapsed;
                    selectedElements.IsEnabled = true;

                    break;

                case "TabCustomer":
                    spGridDevice.Visibility = Visibility.Visible;
                    selectedElements.IsEnabled = true;
                    TabOffices.IsEnabled = !string.IsNullOrEmpty(_customerCode);
                    TabConnectorsConfig.IsEnabled = !string.IsNullOrEmpty(_customerCode);
                    break;

                default:
                    spGridDevice.Visibility = Visibility.Visible;
                    selectedElements.IsEnabled = true;
                    TabOffices.IsEnabled = false;
                    TabConnectorsConfig.IsEnabled = false;
                    var selectedTab = (TabControllDetails.SelectedItem as TabItem)?.Name.ToString();
                    if (selectedTab == "TabOffices" || selectedTab == "TabConnectorsConfig") 
                        TabControllDetails.SelectedIndex = 0;
                    break;
            }
        }

        /// <summary>
        /// Generate Grid
        /// </summary>
        /// <param name="gridView"></param>
        /// <param name="gridInfo"></param>
        private void CreateGridView(GridView gridView, List<GridViewColumn> gridInfo)
        {
            foreach (var column in gridInfo)
            {
                gridView.Columns.Add(column);
            }
        }


        #endregion


        #region Supplies

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnGetSupplies_Click(object sender, RoutedEventArgs e)
        {
            if (string.IsNullOrWhiteSpace(_dealerCode))
            {
                MessageBox.Show("Select a dealer");
                return;
            }
            List<GridViewColumn> gridinfo = Helper.GetGridSuppliesColums();

            //==== Prepare Request
            var request = new GetDealerSuppliesRequest();
            request.Code = _dealerCode;

            //=== pagination
            request.PageNumber = cmbSuppliesPageNumber.SelectedIndex + 1;
            request.PageRows = iudSuppliesPageRows.Value.Value;
            request.SortOrder = ((SortDirectionEnum)cmbSuppliesSortOrder.SelectedItem);

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = adapter.GetDealerSupplies(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {

                        cmbSuppliesPageNumber.IsEnabled = true;
                        lblSuppliesTotalRows.Text = result.TotalRows.ToString();
                        cmbSuppliesPageNumber.Items.Clear();

                        for (int i = 1; i <= result.TotalRows / request.PageRows + 1; i++)
                        {
                            cmbSuppliesPageNumber.Items.Add(i);
                        }
                        cmbSuppliesPageNumber.SelectedIndex = request.PageNumber - 1;

                        ListViewSupplies.ItemsSource = result.Result;
                        var gridView = new GridView();
                        ListViewSupplies.View = gridView;

                        CreateGridView(gridView, gridinfo);
                        string lblDetail = "";
                        responseMsg = $"Supplies count({result.Result.Count()}) {lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetDealerSupplies : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "DealerSupply/List", "GET", responseMsg);
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
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void ListViewSupplies_MouseDoubleClick(object sender, MouseButtonEventArgs e)
        {
            DealerSupplyListDto selectedSupply = (DealerSupplyListDto)ListViewSupplies.SelectedItem;
            if (selectedSupply != null)
            {
                Supplies supplyWin = new Supplies(adapter);
                supplyWin.supplyId = selectedSupply.Id;
                supplyWin.lblDealer.Text = lblDealer.Text;
                supplyWin.txtDealer.Text = supplyWin.dealerCode = _dealerCode;
                supplyWin.txtDescription.Text = selectedSupply.Description;
                supplyWin.txtDuration.Text = selectedSupply.Duration.ToString();
                supplyWin.txtPartNumber.Text = selectedSupply.PartNumber;
                supplyWin.cmbColor.SelectedItem = selectedSupply.ColorType;
                supplyWin.cmbType.SelectedItem = selectedSupply.SupplyType;
                supplyWin.cmbMaintenanceKitColor.SelectedItem = selectedSupply.MaintenanceKitColor;
                supplyWin.cmbMaintenanceKitType.SelectedItem = selectedSupply.MaintenanceKitType;
                supplyWin.ShowDialog();
            }
            else
            {
                MessageBox.Show("Select a supply.");
                ResponseBox.Text = string.Empty;
            }
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void ListViewSupplies_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            DealerSupplyListDto selectedSupply = (DealerSupplyListDto)ListViewSupplies.SelectedItem;
            if (selectedSupply != null)
            {
                Supplies supplyWin = new Supplies(adapter);
                supplyWin.lblDealer.Text = lblDealer.Text;
                supplyWin.txtDealer.Text = supplyWin.dealerCode = _dealerCode;
                supplyWin.txtDescription.Text = "";
            }
        }


        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnAddSupply_Click(object sender, RoutedEventArgs e)
        {
            Supplies supplyWin = new Supplies(adapter);
            supplyWin.lblDealer.Text = lblDealer.Text;
            supplyWin.txtDealer.Text = supplyWin.dealerCode = _dealerCode;

            supplyWin.ShowDialog();
        }
        #endregion

        #region Sds Service Request
        private void ListViewSdsSr_MouseDoubleClick(object sender, MouseButtonEventArgs e)
        {
            SdsDeviceActionDto selected = (SdsDeviceActionDto)ListViewSdsSr.SelectedItem;
            if (selected != null)
            {
                SdsDeviceAction sdsWin = new SdsDeviceAction(adapter, selected.Id);
                sdsWin.txtSerialNumber.Text = selected.InstalledProductSerialNumber;
                sdsWin.deviceId = selected.DeviceId;
                sdsWin.ShowDialog();
            }
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void ListViewSdsSr_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {

        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void BtnGetDeviceActions_Click(object sender, RoutedEventArgs e)
        {
            if (string.IsNullOrWhiteSpace(_dealerCode))
            {
                MessageBox.Show("Select a dealer");
                return;
            }
            List<GridViewColumn> gridinfo = Helper.GetGridSdsServiceRequestColums();

            //==== Prepare Request
            var request = new GetDeviceActionsRequest();
            request.DealerCode = _dealerCode;
            request.CustomerCode = _customerCode;

            DeviceListDto selectedDevice = (DeviceListDto)ListViewDevices.SelectedItem;
            request.DeviceId = selectedDevice?.Id;

            //request.State = _customerCode;
            //request.Severity

            //=== pagination
            request.PageNumber = cmbSdsSrPageNumber.SelectedIndex + 1;
            request.PageRows = iudSdsSrPageRows.Value.Value;
            request.SortOrder = ((SortDirectionEnum)cmbSdsSrSortOrder.SelectedItem);

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = adapter.GetDeviceActions(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {

                        cmbSdsSrPageNumber.IsEnabled = true;
                        lblsdsSrTotalRows.Text = result.TotalRows.ToString();
                        cmbSdsSrPageNumber.Items.Clear();

                        for (int i = 1; i <= result.TotalRows / request.PageRows + 1; i++)
                        {
                            cmbSdsSrPageNumber.Items.Add(i);
                        }
                        cmbSdsSrPageNumber.SelectedIndex = request.PageNumber - 1;

                        ListViewSdsSr.ItemsSource = result.Result;
                        var gridView = new GridView();
                        ListViewSdsSr.View = gridView;

                        CreateGridView(gridView, gridinfo);
                        string lblDetail = "";
                        responseMsg = $"SDS Service Request count({result.Result.Count()}) {lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetDeviceActions : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "SdsAction/GetDeviceActions", "GET", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();
        }

        #endregion


    
    }
}
