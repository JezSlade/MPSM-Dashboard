using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Requests;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Windows;
using System.Windows.Controls;
using System.Linq;
using static MpsMonitor.Sdk.Models.Requests.PagedRequest;

namespace MpsMonitor.Sdk.Simulator
{
    /// <summary>
    /// Interaction logic for Connectors.xaml
    /// </summary>
    public partial class ConnectorsConfig : Window
    {
        public string _customerCode { get; set; }
        public string _dealerCode { get; set; }
        public string _dealerId { get; set; }
        public string _customerId { get; set; }
        public string responseMsg { get; private set; }
        private ExplorerConfigurationDto configuration { get; set; }

        private IMpsMonitorAdapter _adapter = null;
        internal string _connectorConfigId;
        public ConnectorsConfig(IMpsMonitorAdapter adapter, string connectorConfigId, string dealerCode, string customerCode, string dealerId, string customerId)
        {
            _adapter = adapter;
            _dealerId = dealerId;
            _customerId = customerId;
            _connectorConfigId = connectorConfigId;
            _customerCode = customerCode;
            _dealerCode = dealerCode;
            InitializeComponent();

            GetExplorerDatas(connectorConfigId, customerCode);


        }

        private void GetExplorerDatas(string connectorConfigId, string customerCode)
        {
            //==== Prepare Request
            var request = new GetExplorerDatasRequest();
            request.FilterDealerId = _dealerId;
            request.FilterDealerCodes = _dealerCode;
            request.FilterCustomerCodes = _customerCode;
            request.FilterCustomerId = _customerId;
            //=== pagination
            request.PageNumber = 1;
            request.PageRows = int.MaxValue;
            request.SortOrder = SortDirectionEnum.Asc;
            request.SortColumn = "SystemName";

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.GetExplorerDatas(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {

                        cmbSystemName.ItemsSource = result.Result;
                        cmbSystemName.DisplayMemberPath = "SystemName";
                        cmbSystemName.SelectedValuePath = "Identifier";
                        cmbSystemName.SelectedIndex = -1;
                        string lblDetail = "";
                        responseMsg = $"Connector configurations Details{lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                        LoadConfiguration(connectorConfigId, customerCode);
                    }
                    else
                    {
                        responseMsg = $"Error in GetExplorerDatas : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/GetExplorerDatas", "GET", responseMsg);
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
        /// <param name="connectorConfigId"></param>
        private void LoadConfiguration(string connectorConfigId, string customerCode)
        {
            //==== Prepare Request
            var request = new GetExplorerConfigurationRequest();
            request.ConfigurationId = connectorConfigId;
            request.CustomerCode = customerCode;
            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.GetExplorerConfiguration(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        BindObject(result.Result);

                        string lblDetail = "";
                        responseMsg = $"Connector configurations Details{lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in Explorer_Configuration_Get : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/Configuration/Get", "GET", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();
        }


        private void BtnSave_Click(object sender, RoutedEventArgs e)
        {
            //==== Prepare Request
            var request = new UpdateRequest<ExplorerConfigurationDto>();
            configuration.Description = txtDesc.Text;
            configuration.Community = txtCommunity.Text;
            configuration.IsEnable = ckbEnable.IsChecked ?? ckbEnable.IsChecked.Value;
            configuration.ExplorerDataId = ((EntityDto)cmbSystemName.SelectedItem)?.Id; ;


            request.ObjectToUpdate = configuration;
            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.UpdateExplorerConfiguration(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {

                        string lblDetail = "";
                        responseMsg = $"UpdateExplorerConfiguration Details{lblDetail}: \n {JsonConvert.SerializeObject(result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in UpdateExplorerConfiguration : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/Configuration/Update", "PUT", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();

        }

        private void ListViewNetwork_MouseDoubleClick(object sender, System.Windows.Input.MouseButtonEventArgs e)
        {

        }

        private void ListViewDetection_MouseDoubleClick(object sender, System.Windows.Input.MouseButtonEventArgs e)
        {

        }


        #region Subnet
        private void NetworkDelete_Click(object sender, RoutedEventArgs e)
        {
            ExplorerSubnetDto subnet = (sender as Button).DataContext as ExplorerSubnetDto;
            MessageBoxResult result;

            result = MessageBox.Show("Sure you want to delete?", "Question", MessageBoxButton.YesNo, MessageBoxImage.Warning);

            if (result == MessageBoxResult.Yes)
            {
                DeleteSubnet(subnet);
            }
            else
            {

            }
        }

        private void NetworkEdit_Click(object sender, RoutedEventArgs e)
        {
            ExplorerSubnetDto subnet = (sender as Button).DataContext as ExplorerSubnetDto;
            ConnectorsConfigEdit configEditWin = new ConnectorsConfigEdit(_adapter, true, _customerCode, subnet: subnet)
            {
                _explorerConfigurationId = txtExplorerConfigId.Text,
                _customerId = txtCustomerId.Text,
                _customerCode = _customerCode

            };
            configEditWin.ShowDialog();
            LoadConfiguration(_connectorConfigId, _customerCode);
        }
        private void DeleteSubnet(ExplorerSubnetDto subnet)
        {
            //==== Prepare Request
            var request = new DeleteExplorerSubnetRequest
            {
                Id = subnet.Id,
                CustomerId = txtCustomerId.Text,
                ExplorerConfigurationId = txtExplorerConfigId.Text
            };


            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.DeleteExplorerSubnet(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        LoadConfiguration(_connectorConfigId, _customerCode);
                        string lblDetail = "";
                        responseMsg = $"DeleteSubnet Details{lblDetail}: \n {JsonConvert.SerializeObject(result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in DeleteSubnet : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/Subnet/Delete", "DELETE", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();
        }

        private void BtnAddNetwork_Click(object sender, RoutedEventArgs e)
        {
            ConnectorsConfigEdit configEditWin = new ConnectorsConfigEdit(_adapter, true, _customerCode)
            {
                _explorerConfigurationId = txtExplorerConfigId.Text,
                _customerId = txtCustomerId.Text,

            };
            configEditWin.ShowDialog();
            LoadConfiguration(_connectorConfigId, _customerCode);
        }

        #endregion

        #region Schedule
        private void DataDetectionDelete_Click(object sender, RoutedEventArgs e)
        {
            ExplorerScheduleDto schedule = (sender as Button).DataContext as ExplorerScheduleDto;
            MessageBoxResult result;

            result = MessageBox.Show("Sure you want to delete?", "Question", MessageBoxButton.YesNo, MessageBoxImage.Warning);

            if (result == MessageBoxResult.Yes)
            {
                DeleteSchedule(schedule);
            }
            else
            {

            }
        }
        private void DataDetectionEdit_Click(object sender, RoutedEventArgs e)
        {
            ExplorerScheduleDto schedule = (sender as Button).DataContext as ExplorerScheduleDto;
            ConnectorsConfigEdit configEditWin = new ConnectorsConfigEdit(_adapter, false, _customerCode, schedule)
            {
                _explorerConfigurationId = txtExplorerConfigId.Text,
                _customerId = txtCustomerId.Text
            };
            configEditWin.ShowDialog();
            LoadConfiguration(_connectorConfigId, _customerCode);

        }

        private void BtnAddSchedule_Click(object sender, RoutedEventArgs e)
        {
            ConnectorsConfigEdit configEditWin = new ConnectorsConfigEdit(_adapter, false, _customerCode)
            {
                _explorerConfigurationId = txtExplorerConfigId.Text,
                _customerId = txtCustomerId.Text
            };
            configEditWin.ShowDialog();
            LoadConfiguration(_connectorConfigId, _customerCode);

        }

        private void DeleteSchedule(ExplorerScheduleDto schedule)
        {
            //==== Prepare Request
            var request = new DeleteExplorerScheduleRequest
            {
                Id = schedule.Id,
                CustomerId = txtCustomerId.Text,
                ExplorerConfigurationId = txtExplorerConfigId.Text
            };


            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.DeleteSchedule(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        LoadConfiguration(_connectorConfigId, _customerCode);
                        string lblDetail = "";
                        responseMsg = $"DeleteSchedule Details{lblDetail}: \n {JsonConvert.SerializeObject(result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in DeleteSchedule : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/Schedule/Delete", "DELETE", responseMsg);
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

        #region Common Function

        private void BindObject(ExplorerConfigurationDto _config)
        {
            configuration = _config;
            if (_config != null)
            {
                List<GridViewColumn> gridinfoNetwork = Helper.GetGridNetworkColums(NetworkDelete_Click, NetworkEdit_Click);
                List<GridViewColumn> gridinfoData = Helper.GetGridDataDetectionColums(DataDetectionDelete_Click, DataDetectionEdit_Click);

                txtDesc.Text = _config.Description;
                txtCommunity.Text = _config.Community;

                cmbSystemName.SelectedItem = cmbSystemName.ItemsSource.Cast<EntityDto>()?.First(i => i.Id == _config.ExplorerDataId);

                //txtSystemName.Text = string.Concat(_config.ExplorerDataSystemName, " [", _config.ExplorerDataId, "]");
                ckbEnable.IsChecked = _config.IsEnable;
                
                ListViewNetwork.ItemsSource = _config.ExplorerSubnets;
                var gridView = new GridView();
                ListViewNetwork.View = gridView;
                CreateGridView(gridView, gridinfoNetwork);
                
                ListViewDetection.ItemsSource = _config.ExplorerSchedules;
                var gridViewDataDetection = new GridView();
                ListViewDetection.View = gridViewDataDetection;
                CreateGridView(gridViewDataDetection, gridinfoData);

            }
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



    }
}
