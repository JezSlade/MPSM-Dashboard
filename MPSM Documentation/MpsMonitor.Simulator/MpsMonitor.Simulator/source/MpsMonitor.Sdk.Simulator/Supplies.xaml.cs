using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Enums;
using MpsMonitor.Sdk.Models.Requests;
using Newtonsoft.Json;
using System;
using System.Linq;
using System.Text.RegularExpressions;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Input;

namespace MpsMonitor.Sdk.Simulator
{
    /// <summary>
    /// Interaction logic for Supplies.xaml
    /// </summary>
    public partial class Supplies : Window
    {
        IMpsMonitorAdapter _adapter = null;
        public string responseMsg { get; private set; }
        internal string dealerCode;
        internal string supplyId;

        public Supplies(IMpsMonitorAdapter adapter)
        {
            _adapter = adapter;
            InitializeComponent();
            cmbType.ItemsSource = Enum.GetValues(typeof(SupplyTypeEnum)).Cast<SupplyTypeEnum>();
            cmbColor.ItemsSource = Enum.GetValues(typeof(ColorTypeEnum)).Cast<ColorTypeEnum>();

            loadMaintenanceKitTypes();
            loadMaintenanceKitColors();
        }

        private async void loadMaintenanceKitColors()
        {
            var result = await _adapter.MaintenanceKitColors();

            if (result.IsValid)
            {
                cmbMaintenanceKitColor.ItemsSource = result.Result;
                cmbMaintenanceKitColor.DisplayMemberPath = "Description";
                cmbMaintenanceKitColor.SelectedValuePath = "Id";
                cmbMaintenanceKitColor.SelectedIndex = 0;
                responseMsg = $"MaintenanceKitColors: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in  MaintenanceKitColors: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }
            //SetInfoResults(request, "Common/GetMaintenanceKitColors", "GET", responseMsg);
        }

        private async void loadMaintenanceKitTypes()
        {
            var result = await _adapter.MaintenanceKitTypes();

            if (result.IsValid)
            {
                cmbMaintenanceKitType.ItemsSource = result.Result;
                cmbMaintenanceKitType.DisplayMemberPath = "Description";
                cmbMaintenanceKitType.SelectedValuePath = "Id";
                cmbMaintenanceKitType.SelectedIndex = 0;
                responseMsg = $"MaintenanceKitTypes: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in  loadMaintenanceKitTypes: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }
            //SetInfoResults(request, "Common/GetMaintenanceKitTypes", "GET", responseMsg);
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
        private void BtnSave_Click(object sender, RoutedEventArgs e)
        {
            if (string.IsNullOrEmpty(supplyId))
            {
                CreateSupply();
            }
            else
            {
                UpdateSupply();
            }
        }

        /// <summary>
        /// 
        /// </summary>
        private async void UpdateSupply()
        {
            var request = new UpdateRequest<DealerSupplyDto>();
            request.ObjectToUpdate = ReadSupply();

            var result = await _adapter.UpdateSupply(request);

            if (result.IsValid)
            {
                responseMsg = $"Supply Details: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in  UpdateSupply: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "DealerSupply/Update", "PUT", responseMsg);
        }

        /// <summary>
        /// 
        /// </summary>
        private async void CreateSupply()
        {
            var request = new CreateRequest<DealerSupplyDto>();
            request.ObjectToCreate = ReadSupply();

            var result = await _adapter.CreateSupply(request);

            if (result.IsValid)
            {
                responseMsg = $"Supply Details: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in  CreateSupply: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "DealerSupply/Create", "POST", responseMsg);
        }

        private DealerSupplyDto ReadSupply()
        {
            var objUpdInsert = new DealerSupplyDto();
            objUpdInsert.Id = supplyId;
            objUpdInsert.DealerCode = dealerCode;
            objUpdInsert.PartNumber = txtPartNumber.Text;
            objUpdInsert.Description = txtDescription.Text; // "ABC description";
            objUpdInsert.SupplyType = (SupplyTypeEnum)cmbType.SelectedItem; // SupplyTypeEnum.Toner;
            objUpdInsert.ColorType = (ColorTypeEnum)cmbColor.SelectedItem;//   ColorTypeEnum.Black;
            objUpdInsert.MaintenanceKitColor = (EntityIdDescIntDto)cmbMaintenanceKitColor.SelectedItem;
            objUpdInsert.MaintenanceKitType = (EntityIdDescIntDto)cmbMaintenanceKitType.SelectedItem;


            objUpdInsert.Duration = string.IsNullOrWhiteSpace(txtDuration.Text) ? 0 : int.Parse(txtDuration.Text);//; 15000;
            return objUpdInsert;
        }

        private void CmbType_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if (cmbType.SelectedItem != null)
            {
                switch ((SupplyTypeEnum)cmbType.SelectedItem)
                {
                    case SupplyTypeEnum.MaintenanceKit:
                        cmbColor.IsEnabled = false;
                        cmbColor.SelectedItem = null;
                        cmbMaintenanceKitColor.IsEnabled = true;
                        cmbMaintenanceKitType.IsEnabled = true;
                        break;

                    default:
                        cmbColor.IsEnabled = true;
                        cmbMaintenanceKitColor.SelectedItem = null;
                        cmbMaintenanceKitColor.IsEnabled = false;
                        cmbMaintenanceKitType.SelectedItem = null;
                        cmbMaintenanceKitType.IsEnabled = false;
                        break;
                }
            }
            else
            {
                cmbColor.IsEnabled = true;
                cmbMaintenanceKitColor.IsEnabled = false;
                cmbMaintenanceKitType.IsEnabled = false;
            }
        }
    }
}
