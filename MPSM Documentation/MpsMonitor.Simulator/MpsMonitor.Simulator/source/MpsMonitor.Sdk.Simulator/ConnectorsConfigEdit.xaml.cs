using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Requests;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Data;
using System.Windows.Documents;
using System.Windows.Input;
using System.Windows.Media;
using System.Windows.Media.Imaging;
using System.Windows.Shapes;
using static MpsMonitor.Sdk.Models.Requests.PagedRequest;

namespace MpsMonitor.Sdk.Simulator
{
    /// <summary>
    /// Interaction logic for ConnectorsConfigEdit.xaml
    /// </summary>
    public partial class ConnectorsConfigEdit : Window
    {
        private IMpsMonitorAdapter _adapter = null;
        private bool _IsNetwork;
        private ExplorerScheduleDto _schedule;
        private ExplorerSubnetDto _subnet;

        public string _customerId { get; set; }
        public string _customerCode { get; set; }
        public string _explorerConfigurationId { get; set; }
        public string _dealerCode { get; set; }
        public string responseMsg { get; private set; }

        public ConnectorsConfigEdit(IMpsMonitorAdapter adapter, bool IsNetwork, string customerCode, ExplorerScheduleDto schedule = null, ExplorerSubnetDto subnet = null)
        {
            _adapter = adapter;
            _IsNetwork = IsNetwork;
            _schedule = schedule;
            _subnet = subnet;
            _customerCode = customerCode;
            InitializeComponent();
            spDataDetection.Visibility = _IsNetwork ? Visibility.Collapsed : Visibility.Visible;
            spNetwork.Visibility = _IsNetwork ? Visibility.Visible : Visibility.Collapsed;


            if (schedule != null)
            {
                iudHours.Value = schedule.StartAt.Hour;
                iudMinutes.Value = schedule.StartAt.Minute;
            }
            else
            {
                LoadOffices();
            }

        }

        private void LoadOffices()
        {
            //==== Prepare Request
            var request = new GetOfficesRequest
            {
                CustomerId = _customerId,
                CustomerCode = _customerCode,
                PageNumber = 1,
                PageRows = int.MaxValue,
                SortOrder = SortDirectionEnum.Asc,
                HasSubnets = true
            };
            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.GetOffices(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        cmbOffices.ItemsSource = result.Result;
                        //cmbOffices.DisplayMemberPath = "Description";
                        cmbOffices.SelectedValuePath = "Id";
                        cmbOffices.SelectedIndex = -1;
                        string lblDetail = "";

                        if (_subnet != null)
                        {
                            if (_subnet.OfficeId != null)
                            {
                                rbOffices.IsChecked = true;
                                rbNetwork.IsChecked = false;

                                cmbOffices.SelectedItem = cmbOffices.ItemsSource.Cast<OfficeListDto>()?.First(i => i.Id == _subnet.OfficeId);
                            }
                            else
                            {
                                txtSubnetFreeText.Text = GetIpFreeText(_subnet);

                                rbOffices.IsChecked = false;
                                rbNetwork.IsChecked = true;
                            }
                        }
                        else
                        {
                            rbOffices.IsChecked = false;
                            rbNetwork.IsChecked = true;
                        }


                        responseMsg = $"GetOffices Details{lblDetail}: \n {JsonConvert.SerializeObject(result, Formatting.Indented)}";
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

        #region subnet

        private void BtnSaveSubnet_Click(object sender, RoutedEventArgs e)
        {
            if (_subnet == null)
            {
                SaveNewSubnet();
            }
            else
            {
                UpdateSubnet(_subnet);
            }
        }

        private void UpdateSubnet(ExplorerSubnetDto subnet)
        {
            //==== Prepare Request
            var request = new UpdateExplorerSubnetRequest
            {
                Id = subnet.Id,
                CustomerId = _customerId,
                ExplorerConfigurationId = _explorerConfigurationId,
                IpFreeText = txtSubnetFreeText.Text
            };
            if (rbNetwork.IsChecked.HasValue && rbNetwork.IsChecked.Value)
            {
                request.IpFreeText = txtSubnetFreeText.Text;
                request.OfficeId = string.Empty;
            }
            else {
                request.OfficeId = ((OfficeListDto)cmbOffices.SelectedItem)?.Id;
                request.IpFreeText = string.Empty;
            }

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.UpdateExplorerSubnet(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        string lblDetail = "";
                        responseMsg = $"UpdateExplorerSubnet Details{lblDetail}: \n {JsonConvert.SerializeObject(result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in UpdateExplorerSubnet : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/Subnet/Udate", "PUT", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();
        }

        private void SaveNewSubnet()
        {
            //==== Prepare Request
            var request = new CreateExplorerSubnetRequest
            {
                CustomerId = _customerId,
                ExplorerConfigurationId = _explorerConfigurationId,
                IpFreeText = txtSubnetFreeText.Text
            };
            if (rbNetwork.IsChecked.HasValue && rbNetwork.IsChecked.Value)
            {
                request.IpFreeText = txtSubnetFreeText.Text;
                request.OfficeId = string.Empty;
            }
            else
            {
                request.OfficeId = ((OfficeListDto)cmbOffices.SelectedItem)?.Id;
                request.IpFreeText = string.Empty;
            }
            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.CreateExplorerSubnet(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        string lblDetail = "";
                        responseMsg = $"CreateExplorerSubnet Details{lblDetail}: \n {JsonConvert.SerializeObject(result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in CreateExplorerSubnet : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/Subnet/Create", "POST", responseMsg);
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
        #region schedule
        private void BtnSaveSchedule_Click(object sender, RoutedEventArgs e)
        {
            if (_schedule == null)
            {
                SaveNewSchedule();
            }
            else
            {
                UpdateSchedule(_schedule);
            }
        }

        private void SaveNewSchedule()
        {
            //==== Prepare Request
            var request = new CreateExplorerScheduleRequest
            {
                CustomerId = _customerId,
                ExplorerConfigurationId = _explorerConfigurationId,
                StartAtHours = string.Concat(iudHours.Value.ToString(), ":", iudMinutes.Value.ToString()),
                Days = "1,2,3,4,5,6,0",
            };

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.CreateSchedule(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        string lblDetail = "";
                        responseMsg = $"CreateSchedule Details{lblDetail}: \n {JsonConvert.SerializeObject(result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in CreateSchedule : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/Schedule/Delete", "POST", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();

        }

        private void UpdateSchedule(ExplorerScheduleDto schedule)
        {
            //==== Prepare Request
            var request = new UpdateExplorerScheduleRequest
            {
                Id = schedule.Id,
                Days = "1,2,3,4,5,6,0",
                StartAtHours = string.Concat(iudHours.Value.ToString(), ":", iudMinutes.Value.ToString()),
                CustomerId = _customerId,
                ExplorerConfigurationId = _explorerConfigurationId
            };


            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.UpdateSchedule(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        string lblDetail = "";
                        responseMsg = $"Update schedule Details{lblDetail}: \n {JsonConvert.SerializeObject(result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in Update schedule : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Explorer/Schedule/Update", "POST", responseMsg);
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

        private string GetIpFreeText(ExplorerSubnetDto explorerSubnet)
        {
            string IpFreeText;
            if (string.IsNullOrEmpty(explorerSubnet.IpEnd))
            {
                return explorerSubnet.IpStart;
            }

            var oneStart = explorerSubnet.IpStart.Split('.')[0];
            var twoStart = explorerSubnet.IpStart.Split('.')[1];
            var threeStart = explorerSubnet.IpStart.Split('.')[2];
            var fourStart = explorerSubnet.IpStart.Split('.')[3];

            var oneEnd = explorerSubnet.IpEnd.Split('.')[0];
            var twoEnd = explorerSubnet.IpEnd.Split('.')[1];
            var threeEnd = explorerSubnet.IpEnd.Split('.')[2];
            var fourEnd = explorerSubnet.IpEnd.Split('.')[3];

            IpFreeText = "";

            if (oneStart != oneEnd)
            {
                IpFreeText += oneStart + '-' + oneEnd;
            }
            else
            {
                IpFreeText += oneStart;
            }

            IpFreeText += ".";

            if (twoStart != twoEnd)
            {
                IpFreeText += twoStart + '-' + twoEnd;
            }
            else
            {
                IpFreeText += twoStart;
            }

            IpFreeText += ".";

            if (threeStart != threeEnd)
            {
                IpFreeText += threeStart + '-' + threeEnd;
            }
            else
            {
                IpFreeText += threeStart;
            }

            IpFreeText += ".";

            if (fourStart != fourEnd)
            {
                IpFreeText += fourStart + '-' + fourEnd;
            }
            else
            {
                IpFreeText += fourStart;
            }
            
            return IpFreeText;
        }

        private void cmbOffices_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            OfficeListDto officeSelected = (OfficeListDto)cmbOffices.SelectedItem;
            if (officeSelected != null)
            {
                List<GridViewColumn> gridinfo = Helper.GetGridOfficesSubnetColums();


                ListViewIpAddress.ItemsSource = officeSelected.OfficeSubnets;
                var gridView = new GridView();
                ListViewIpAddress.View = gridView;
                CreateGridView(gridView, gridinfo);
            }
            else
            {
                ListViewIpAddress.ItemsSource = null;
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

        private void RbNetwork_Checked(object sender, RoutedEventArgs e)
        {
            cmbOffices.SelectedIndex = -1;
            ListViewIpAddress.ItemsSource = null;
            txtSubnetFreeText.Visibility = Visibility.Visible;
            spOffice.Visibility = Visibility.Collapsed;
        }

        private void RbNetwork_Unchecked(object sender, RoutedEventArgs e)
        {
            txtSubnetFreeText.Visibility = Visibility.Collapsed;
            spOffice.Visibility = Visibility.Visible;
        }
    }



}
