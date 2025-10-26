using MpsMonitor.Sdk.Library.Interface;
using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Requests;
using Newtonsoft.Json;
using System.Windows;

namespace MpsMonitor.Sdk.Simulator
{
    /// <summary>
    /// Interaction logic for AddCustomer.xaml
    /// </summary>
    public partial class AddCustomer : Window
    {
        IMpsMonitorAdapter _adapter = null;
        public string responseMsg { get; private set; }
        internal string _dealerCode;
        public AddCustomer(IMpsMonitorAdapter adapter, string dealerCode)
        {
            _adapter = adapter;
            _dealerCode = dealerCode;
            InitializeComponent();
            LoadCountry();
        }

        private async void LoadCountry()
        {
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

        private async void BtnSave_Click(object sender, RoutedEventArgs e)
        {
            var request = new CreateRequest<CreateCustomerDto>();

            request.ObjectToCreate = new CreateCustomerDto();
            request.ObjectToCreate.DealerCode = _dealerCode;
            request.ObjectToCreate.CustomerDescription = txtCompanyName.Text;
            // If not specified use the dealer timezone
            request.ObjectToCreate.TimeZone = null;
            request.ObjectToCreate.CountryCode = ((CountryDto)cmbCountry.SelectedItem)?.Code;
            request.ObjectToCreate.Nominative = txtNominative.Text;
            request.ObjectToCreate.Telephone = txtPhone.Text;
            request.ObjectToCreate.MailAddress = txtMailAddress.Text;
            request.ObjectToCreate.ExternalIdentifier = txtExternalIdentifier.Text; // Or whatever you want max 50 characters
            request.ObjectToCreate.Note = txtNote.Text;
            // If you have entered the customer MailAddress you can send inviation email immediately
            request.ObjectToCreate.SendExplorerInstallationInvitation = chkSendExplorerInstallationInvitation.IsChecked.Value;

            // If it is an HP dealer and has enabled SDS feature, with that option can active customer to SDS immediately
            request.ObjectToCreate.EnableSDS = chkEnableHPSDS.IsChecked.Value;

            var result = await _adapter.CreateCustomer(request);

            if (result.IsValid)
            {
                responseMsg = $"Customer Details: \n {JsonConvert.SerializeObject(result, Formatting.Indented)}";
                //clearFileds();

            }
            else
            {
                responseMsg = $"Error in  CreateCustomer: \n {JsonConvert.SerializeObject(result.Errors, Formatting.Indented)}";
            }

            SetInfoResults(request, "Customer/CreateCustomer", "POST", responseMsg);
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
