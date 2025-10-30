using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Requests;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Windows;

namespace MpsMonitor.Sdk.Simulator
{
    /// <summary>
    /// Interaction logic for Offices.xaml
    /// </summary>
    public partial class Offices : Window
    {
        internal string customerCode;
        internal string _officeId;
        internal string responseMsg;


        IMpsMonitorAdapter _adapter = null;
        public Offices(IMpsMonitorAdapter adapter, string officeId)
        {
            _adapter = adapter;
            InitializeComponent();
            LoadCountry();
            _officeId = officeId;
            if (!string.IsNullOrWhiteSpace(officeId))
                LoadOffice(officeId);

        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="officeId"></param>
        private void LoadOffice(string officeId)
        {
            //==== Prepare Request
            var request = new GetByIdRequest();
            request.Id = officeId;

            //==== Create Task for invoke Action
            BackgroundWorker worker = new BackgroundWorker();
            worker.DoWork += (o, ea) =>
            {
                var result = _adapter.GetOffice(request).Result;
                //use the Dispatcher to delegate the result  back to the UI
                Dispatcher.Invoke((Action)(() =>
                {
                    if (result.IsValid)
                    {
                        BindObject(result.Result);

                        string lblDetail = "";
                        responseMsg = $"Office Details{lblDetail}: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
                    }
                    else
                    {
                        responseMsg = $"Error in GetOffice : \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
                    }

                    SetInfoResults(request, "Office/Get", "POST", responseMsg);
                }));
            };
            worker.RunWorkerCompleted += (o, ea) =>
            {
                _busyIndicator.IsBusy = false;
            };
            _busyIndicator.IsBusy = true;
            worker.RunWorkerAsync();
        }

        private void BindObject(OfficeDto office)
        {
            Code.Text = office.Code;
            Description.Text = office.Description;
            EnableAutoAssign.IsChecked = office.EnableAutoAssign;
            Address.Text = office.Address;
            City.Text = office.City;
            ZipCode.Text = office.ZipCode;
            if (office.IdCountry != null)
            {
                cmbCountry.SelectedItem = cmbCountry.ItemsSource.Cast<CountryDto>()?.First(i => i.Id == office.IdCountry);
            }
            Province.Text = office.Province;
            MailAddress.Text = office.MailAddress;
            Telephone.Text = office.Telephone;
            Fax.Text = office.Fax;
            Note.Text = office.Note;
        }

        private async void LoadCountry()
        {
            BaseRequest request = null;
            var result = await _adapter.GetCountries();

            if (result.IsValid)
            {
                cmbCountry.ItemsSource = result.Result;
                cmbCountry.DisplayMemberPath = "Name";
                cmbCountry.SelectedValuePath = "Id";
                cmbCountry.SelectedIndex = -1;
                responseMsg = $"GetCountries: \n {JsonConvert.SerializeObject(result.Result, Formatting.Indented)}";
            }
            else
            {
                responseMsg = $"Error in  GetCountries: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }
            //SetInfoResults(request, "Common/GetCountries", "GET", responseMsg);
        }

      
        private void BtnSave_Click(object sender, RoutedEventArgs e)
        {
            if (string.IsNullOrEmpty(_officeId))
            {
                CreateOffice();
            }
            else
            {
                UpdateOffice();
            }
        }


        private void BtnAddOffice_Click(object sender, RoutedEventArgs e)
        {
            Code.IsEnabled = true;
            //clear fields
            _officeId = string.Empty;
            Code.Text = string.Empty;
            Description.Text = string.Empty;
            EnableAutoAssign.IsChecked = false;
            Address.Text = string.Empty;
            City.Text = string.Empty;
            ZipCode.Text = string.Empty;
            cmbCountry.SelectedIndex = 0;
            Province.Text = string.Empty;
            MailAddress.Text = string.Empty;
            Telephone.Text = string.Empty;
            Fax.Text = string.Empty;
            Note.Text = string.Empty;

        }


        /// <summary>
        /// Creates the office.
        /// </summary>
        private async void CreateOffice()
        {
            var request = new CreateRequest<OfficeDto>();
            request.ObjectToCreate = ReadOffice();

            var result = await _adapter.CreateOffice(request);

            if (result.IsValid)
            {
                responseMsg = $"Office Details: \n {JsonConvert.SerializeObject(result.ReturnValue, Formatting.Indented)}";
                Code.IsEnabled = false;
            }
            else
            {
                responseMsg = $"Error in  CreateOffice: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Office/Create", "POST", responseMsg);
        }

        /// <summary>
        /// 
        /// </summary>
        private async void UpdateOffice()
        {
            string response = string.Empty;
            var request = new UpdateRequest<OfficeDto>();
            request.ObjectToUpdate = ReadOffice();

            var result = await _adapter.UpdateOffice(request);

            if (result.IsValid)
            {
                response = $"Office Details: \n {JsonConvert.SerializeObject(result.ReturnValue, Formatting.Indented)}";
                Code.IsEnabled = false;
            }
            else
            {
                response = $"Error in  UpdateOffice: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Office/Update", "POST", response);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="ObjectToUpsert"></param>
        private OfficeDto ReadOffice()
        {
            OfficeDto ObjectToUpsert = new OfficeDto();
            ObjectToUpsert.Id = _officeId;
            ObjectToUpsert.Customer = new CustomerBaseDto() { Code = customerCode };
            ObjectToUpsert.Code = Code.Text;
            ObjectToUpsert.Description = Description.Text;
            ObjectToUpsert.DestinationCode = null;
            ObjectToUpsert.Address = Address.Text;
            ObjectToUpsert.Sap = null;
            ObjectToUpsert.Telephone = Telephone.Text;
            ObjectToUpsert.Fax = Fax.Text;
            ObjectToUpsert.City = City.Text;
            ObjectToUpsert.ZipCode = ZipCode.Text;
            ObjectToUpsert.Province = Province.Text;
            ObjectToUpsert.IdCountry = ((EntityDto)cmbCountry.SelectedItem)?.Id;

            ObjectToUpsert.Lat = null;
            ObjectToUpsert.Lng = null;
            ObjectToUpsert.MailAddress = MailAddress.Text;
            ObjectToUpsert.DeliveryTonerMails = null;
            ObjectToUpsert.DestinationDescription = null;
            ObjectToUpsert.EnableAutoAssign = EnableAutoAssign.IsChecked.HasValue && EnableAutoAssign.IsChecked.Value;
            ObjectToUpsert.Note = Note.Text;
            ObjectToUpsert.AvailableDevices = 0;
            ObjectToUpsert.OfficeSubnets = new List<OfficeSubnetDto>();
            //ObjectToUpsert.OfficeSubnets.Add(new OfficeSubnetDto { IpRootFrom = "192.168.1.10", IpRootTo = "192.168.1.200" });
            //ObjectToUpsert.ExternalIdentifier = Guid.NewGuid().ToString(); // Or whatever you want max 50 characters
            return ObjectToUpsert;
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


        #endregion


    }
}
