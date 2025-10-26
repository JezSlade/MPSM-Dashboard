using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    /// Represent a device
    /// </summary>
    [DataContract]
    public class DeviceListDto : DeviceBaseDto
    {
        #region Parents
        /// <summary>
        /// Gets or sets the dealer identifier.
        /// </summary>
        /// <value>
        /// The dealer identifier.
        /// </value>
        [DataMember]
        public string DealerId { get; set; }

        /// <summary>
        /// Gets or sets the dealer code.
        /// </summary>
        /// <value>
        /// The dealer code.
        /// </value>
        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>
        /// Gets or sets the dealer description.
        /// </summary>
        /// <value>
        /// The dealer description.
        /// </value>
        [DataMember]
        public string DealerDescription { get; set; }

        /// <summary>
        /// Gets the customer id.
        /// </summary>
        /// <value>
        /// The customer id
        /// </value>
        [DataMember]
        public string CustomerId { get; set; }

        /// <summary>
        /// Gets or sets the customer code.
        /// </summary>
        /// <value>
        /// The customer code.
        /// </value>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Gets or sets the customer description.
        /// </summary>
        /// <value>
        /// The customer description.
        /// </value>
        [DataMember]

        public string CustomerDescription { get; set; }

        /// <summary>
        /// Gets or sets the office identifier.
        /// </summary>
        /// <value>
        /// The office identifier.
        /// </value>
        [DataMember]
        public string OfficeId { get; set; }

        /// <summary>
        /// Gets or sets the office code.
        /// </summary>
        /// <value>
        /// The office code.
        /// </value>
        [DataMember]
        public string OfficeCode { get; set; }


        /// <summary>
        /// Gets or sets the office description.
        /// </summary>
        /// <value>
        /// The office description.
        /// </value>
        [DataMember]

        public string OfficeDescription { get; set; }

        /// <summary>
        /// Gets or sets the office identifier.
        /// </summary>
        /// <value>
        /// The office identifier.
        /// </value>
        [DataMember]
        public string ProjectId { get; set; }

        /// <summary>
        /// Gets or sets the project description.
        /// </summary>
        /// <value>
        /// The project description.
        /// </value>
        [DataMember]
        public string ProjectDescription { get; set; }

        #endregion

        /// <summary>
        /// Gets or sets the contact.
        /// </summary>
        /// <value>
        /// The contact.
        /// </value>
        [DataMember]
        public string Contact { get; set; }

        /// <summary>
        /// Gets or sets the department.
        /// </summary>
        /// <value>
        /// The department.
        /// </value>
        [DataMember]
        public string Department { get; set; }

        /// <summary>
        /// Gets or sets the IsInsideProject
        /// </summary>
        /// <value>
        /// The IsInsideProject.
        /// </value>
        [DataMember]
        public bool IsInsideProject { get; set; }

        /// <summary>
        /// Gets or sets the is alert generator.
        /// </summary>
        /// <value>
        /// The is alert generator.
        /// </value>
        [DataMember]
        public bool IsAlertGenerator { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is manage supplies.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is manage supplies; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsManageSupplies { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is classified.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is classified; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsClassified { get; set; }

        /// <summary>
        /// Gets or sets the last update.
        /// </summary>
        /// <value>
        /// The last update.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime LastUpdate { get; set; }

        /// <summary>
        /// Gets or sets the install.
        /// </summary>
        /// <value>
        /// The install.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime Install { get; set; }

        /// <summary>
        /// Gets or sets the black toner.
        /// </summary>
        /// <value>
        /// The black toner.
        /// </value>
        [DataMember]
        public byte? BlackToner { get; set; }

        /// <summary>
        /// Gets or sets the black toner1.
        /// </summary>
        /// <value>
        /// The black toner1.
        /// </value>
        [DataMember]
        public byte? BlackToner1 { get; set; }

        /// <summary>
        /// Gets or sets the black toner2.
        /// </summary>
        /// <value>
        /// The black toner2.
        /// </value>
        [DataMember]
        public byte? BlackToner2 { get; set; }

        /// <summary>
        /// Gets or sets the black toner3.
        /// </summary>
        /// <value>
        /// The black toner3.
        /// </value>
        [DataMember]
        public byte? BlackToner3 { get; set; }

        /// <summary>
        /// Gets or sets the black photo.
        /// </summary>
        /// <value>
        /// The black photo.
        /// </value>
        [DataMember]
        public byte? BlackPhoto { get; set; }

        /// <summary>
        /// Gets or sets the cyan toner.
        /// </summary>
        /// <value>
        /// The cyan toner.
        /// </value>
        [DataMember]
        public byte? CyanToner { get; set; }

        /// <summary>
        /// Gets or sets the cyan photo.
        /// </summary>
        /// <value>
        /// The cyan photo.
        /// </value>
        [DataMember]
        public byte? CyanPhoto { get; set; }

        /// <summary>
        /// Gets or sets the magenta toner.
        /// </summary>
        /// <value>
        /// The magenta toner.
        /// </value>
        [DataMember]
        public byte? MagentaToner { get; set; }

        /// <summary>
        /// Gets or sets the magenta photo.
        /// </summary>
        /// <value>
        /// The magenta photo.
        /// </value>
        [DataMember]
        public byte? MagentaPhoto { get; set; }

        /// <summary>
        /// Gets or sets the yellow toner.
        /// </summary>
        /// <value>
        /// The yellow toner.
        /// </value>
        [DataMember]
        public byte? YellowToner { get; set; }

        /// <summary>
        /// Gets or sets the yellow photo.
        /// </summary>
        /// <value>
        /// The yellow photo.
        /// </value>
        [DataMember]
        public byte? YellowPhoto { get; set; }

        /// <summary>
        /// Gets or sets the note.
        /// </summary>
        /// <value>
        /// The note.
        /// </value>
        [DataMember]
        public string Note { get; set; }


        /// <summary>
        /// Gets or sets a value indicating whether this instance is offline.
        /// </summary>
        /// <value>
        /// <c>true</c> if this instance is offline; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsOffline { get; set; }

        /// <summary>
        /// Gets or sets the uninstall.
        /// </summary>
        /// <value>
        /// The uninstall.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime? Uninstall { get; set; }

        /// <summary>
        /// Gets or sets the MonthlyMonoVolume
        /// </summary>
        /// <value>
        /// The counter MonthlyMonoVolume.
        /// </value>
        [DataMember]
        public virtual int? MonthlyMonoVolume { get; set; }

        /// <summary>
        /// Gets or sets the MonthlyColorVolume
        /// </summary>
        /// <value>
        /// The counter MonthlyColorVolume.
        /// </value>
        [DataMember]
        public virtual int? MonthlyColorVolume { get; set; }

        /// <summary>
        /// Gets or sets the firmware.
        /// </summary>
        /// <value>
        /// The firmware.
        /// </value>
        [DataMember]
        public string Firmware { get; set; }

        /// <summary>
        /// Gets or sets the counter mono.
        /// </summary>
        /// <value>
        /// The counter mono.
        /// </value>
        [DataMember]
        public virtual int? CounterMono { get; set; }

        /// <summary>
        /// Gets or sets the color of the counter.
        /// </summary>
        /// <value>
        /// The color of the counter.
        /// </value>
        [DataMember]
        public virtual int? CounterColor { get; set; }

        /// <summary>
        /// Gets or sets the install counter mono.
        /// </summary>
        /// <value>
        /// The counter mono.
        /// </value>
        [DataMember]
        public virtual int? InstallCounterMono { get; set; }

        /// <summary>
        /// Gets or sets the install color of the counter.
        /// </summary>
        /// <value>
        /// The color of the counter.
        /// </value>
        [DataMember]
        public virtual int? InstallCounterColor { get; set; }

        /// <summary>
        /// Gets or sets the mail delivery toner.
        /// </summary>
        /// <value>
        /// The mail delivery toner.
        /// </value>
        [DataMember]
        public virtual string MailDeliveryToner { get; set; }

        /// <summary>
        /// Gets or sets the hardware order number.
        /// </summary>
        /// <value>
        /// The hardware order number.
        /// </value>
        [DataMember]
        public virtual string HardwareOrderNumber { get; set; }

        /// <summary>
        /// Gets or sets the toner order number.
        /// </summary>
        /// <value>
        /// The toner order number.
        /// </value>
        [DataMember]
        public virtual string TonerOrderNumber { get; set; }

        /// <summary>
        /// Gets or sets the product.
        /// </summary>
        /// <value>
        /// The product.
        /// </value>
        [DataMember]
        public ProductDto Product { get; set; }


        /// <summary>
        /// Gets or sets the dealer product supply set.
        /// </summary>
        /// <value>
        /// The dealer product supply set.
        /// </value>
        [DataMember]
        public DealerSupplySetBaseDto DealerSupplySet { get; set; }

        /// <summary>
        /// Gets a value indicating whether [manage consumables].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [manage consumables]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool ManageConsumables
        {
            get { return ProjectDescription != ProjectDto.NotManageConsumables && ProjectDescription != ProjectDto.DefaultDescription; }
        }

        /// <summary>
        /// Gets the SdsDevice Data.
        /// </summary>
        /// <value>
        /// The SDS device data.
        /// </value>
        [DataMember]
        public SdsDeviceListDto SdsDevice { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [to work].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [to work]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool ToWork { get; set; }

        /// <summary>
        /// Gets or sets the Mono Coverage.
        /// </summary>
        /// <value>
        /// The mono coverage.
        /// </value>
        [DataMember]
        public double? MonoCoverage { get; set; }


        /// <summary>
        /// Gets or sets the Color Coverage.
        /// </summary>
        /// <value>
        /// The color coverage.
        /// </value>
        [DataMember]
        public double? ColorCoverage { get; set; }


        [DataMember]
        public string FirmawareVersionUpgradeAvailable { get; set; }


        [DataMember]
        public JamOperationResultEnum? LastAssessRemediateOperationResult { get; set; }
        [DataMember]
        public bool LastAssessRemediateOperationPending { get; set; }
        [DataMember]
        public string LastAssessRemediateOperationPolicyName { get; set; }
        [DataMember]
        public bool? LastAssessRemediateOperationAssessAndRemediate { get; set; }

        [DataMember]
        public JamOperationResultEnum? LastSetCredentialsOperationResult { get; set; }
        [DataMember]
        public bool LastSetCredentialsOperationResultPending { get; set; }

        [DataMember]
        public JamOperationResultEnum? LastRapaOperationResult { get; set; }
        [DataMember]
        public bool LastRapaOperationResultPending { get; set; }

        [DataMember]
        public JamOperationResultEnum? LastSetDeviceConfigResult { get; set; }
        [DataMember]
        public bool LastSetDeviceConfigResultPending { get; set; }

        [DataMember]
        public bool CanSetConfigItem { get; set; }
    }

    /// <summary>
    /// 
    /// </summary>
    public class DeviceBaseDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the serial number.
        /// </summary>
        /// <value>
        /// The serial number.
        /// </value>
        [DataMember]
        public string SerialNumber { get; set; }

        /// <summary>
        /// Gets or sets the mac address.
        /// </summary>
        /// <value>
        /// The mac address.
        /// </value>
        [DataMember]
        public string MacAddress { get; set; }

        /// <summary>
        /// Gets or sets the ip address.
        /// </summary>
        /// <value>
        /// The ip address.
        /// </value>
        [DataMember]
        public string IpAddress { get; set; }


        /// <summary>
        /// Gets or sets the name of the system.
        /// </summary>
        /// <value>
        /// The name of the system.
        /// </value>
        [DataMember]
        public string SystemName { get; set; }

        /// <summary>
        /// Gets or sets the asset number.
        /// </summary>
        /// <value>
        /// The asset number.
        /// </value>
        [DataMember]
        public string AssetNumber { get; set; }

        /// <summary>
        /// Gets or sets the external identifier.
        /// </summary>
        /// <value>
        /// The external identifier.
        /// </value>
        [DataMember]
        public string ExternalIdentifier { get; set; }
    }

    /// <summary>
    ///  Represent a device
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class DeviceDto : DeviceBaseDto
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="DeviceDto"/> class.
        /// </summary>
        public DeviceDto()
        {
            MaintenanceAlerts = new List<MaintenanceAlertDto>();
            CustomFieldValues = new List<CustomFieldValueDto>();
        }

        #region Parents


        /// <summary>
        /// Gets or sets the custom field values.
        /// </summary>
        /// <value>
        /// The custom field values.
        /// </value>
        [DataMember]
        public IEnumerable<CustomFieldValueDto> CustomFieldValues { get; set; }

        /// <summary>
        /// Gets or sets the dealer identifier.
        /// </summary>
        /// <value>
        /// The dealer identifier.
        /// </value>
        [DataMember]
        public string DealerId { get; set; }

        /// <summary>
        /// Gets or sets the dealer code.
        /// </summary>
        /// <value>
        /// The dealer code.
        /// </value>
        [DataMember]
        public string DealerCode { get; set; }


        /// <summary>
        /// Gets or sets the dealer description.
        /// </summary>
        /// <value>
        /// The dealer description.
        /// </value>
        [DataMember]
        public string DealerDescription { get; set; }

        /// <summary>
        /// Gets or sets the customer id.
        /// </summary>
        /// <value>
        /// The customer code.
        /// </value>
        [DataMember]
        public string CustomerId { get; set; }

        /// <summary>
        /// Gets or sets the customer code.
        /// </summary>
        /// <value>
        /// The customer code.
        /// </value>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Gets or sets the customer description.
        /// </summary>
        /// <value>
        /// The customer description.
        /// </value>
        [DataMember]
        public string CustomerDescription { get; set; }

        [DataMember]
        public string CustomerAssessAndRemediateTemplatedId { get; set; }

        /// <summary>
        /// Gets or sets the office identifier.
        /// </summary>
        /// <value>
        /// The office identifier.
        /// </value>
        [DataMember]
        public string OfficeId { get; set; }

        /// <summary>
        /// Gets or sets the office code.
        /// </summary>
        /// <value>
        /// The office code.
        /// </value>
        [DataMember]
        public string OfficeCode { get; set; }


        /// <summary>
        /// Gets or sets the office description.
        /// </summary>
        /// <value>
        /// The office description.
        /// </value>
        [DataMember]
        public string OfficeDescription { get; set; }

        /// <summary>
        /// Gets or sets the office address.
        /// </summary>
        /// <value>
        /// The office address.
        /// </value>
        [DataMember]
        public string OfficeAddress { get; set; }

        /// <summary>
        /// Gets or sets the office zip code.
        /// </summary>
        /// <value>
        /// The office zip code.
        /// </value>
        [DataMember]
        public string OfficeZipCode { get; set; }

        /// <summary>
        /// Gets or sets the office province.
        /// </summary>
        /// <value>
        /// The office province.
        /// </value>
        [DataMember]
        public string OfficeProvince { get; set; }

        /// <summary>
        /// Gets or sets the office city.
        /// </summary>
        /// <value>
        /// The office city.
        /// </value>
        [DataMember]
        public string OfficeCity { get; set; }

        /// <summary>
        /// Gets or sets the office identifier.
        /// </summary>
        /// <value>
        /// The office identifier.
        /// </value>
        [DataMember]
        public string ProjectId { get; set; }

        /// <summary>
        /// Gets or sets the project description.
        /// </summary>
        /// <value>
        /// The project description.
        /// </value>
        [DataMember]
        public string ProjectDescription { get; set; }

        /// <summary>
        /// Gets or sets the project alert information box.
        /// </summary>
        /// <value>
        /// The project alert information box.
        /// </value>
        [DataMember]
        public string ProjectAlertInformationBox { get; set; }

        /// <summary>
        /// Gets or sets the projects Supplies.
        /// </summary>
        /// <value>
        /// The the projects Supplies..
        /// </value>
        [DataMember]
        public List<ProjectVolumeDto> ProjectSupplies { get; set; }


        /// <summary>
        /// Gets or sets the costcenter id.
        /// </summary>
        /// <value>
        /// The cost center id.
        /// </value>
        [DataMember]
        public string CostCenterId { get; set; }


        /// <summary>
        /// Gets or sets the costcenter description.
        /// </summary>
        /// <value>
        /// The cost center description.
        /// </value>
        [DataMember]
        public string CostCenterDescription { get; set; }


        /// <summary>
        /// Gets or sets the product.
        /// </summary>
        /// <value>
        /// The product.
        /// </value>
        [DataMember]
        public ProductDto Product { get; set; }


        /// <summary>
        /// Gets or sets the dealer product supply set.
        /// </summary>
        /// <value>
        /// The dealer product supply set.
        /// </value>
        [DataMember]
        public DealerSupplySetBaseDto DealerSupplySet { get; set; }

        /// <summary>
        /// Gets a value indicating whether [manage consumables].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [manage consumables]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool ManageConsumables
        {
            get { return ProjectDescription != ProjectDto.NotManageConsumables && ProjectDescription != ProjectDto.DefaultDescription; }
        }

        #endregion

        /// <summary>
        /// Gets or sets the MailDeliveryToner.
        /// </summary>
        /// <value>
        /// The MailDeliveryToner.
        /// </value>
        [DataMember]
        public string MailDeliveryToner { get; set; }

        /// <summary>
        /// Gets or sets the use Esprinet logistic.
        /// </summary>
        /// <value>
        /// The esprinet logistc.
        /// </value>
        [DataMember]
        public bool IsEsprinetLogisticEnabled { get; set; }


        /// <summary>
        /// Gets or sets the firmware.
        /// </summary>
        /// <value>
        /// The firmware.
        /// </value>
        [DataMember]
        public string Firmware { get; set; }


        /// <summary>
        /// Department
        /// </summary>
        [DataMember]
        public string Department { get; set; }


        /// <summary>
        /// Gets or sets the contact.
        /// </summary>
        /// <value>
        /// The contact.
        /// </value>
        [DataMember]

        public string Contact { get; set; }


        /// <summary>
        /// Gets or sets the note.
        /// </summary>
        /// <value>
        /// The note.
        /// </value>
        [DataMember]
        public string Note { get; set; }

        /// <summary>
        /// Gets or sets the install.
        /// </summary>
        /// <value>
        /// The install.
        /// </value>
        [DataMember]
        public DateTime Install { get; set; }

        /// <summary>
        /// Gets or sets the uninstall.
        /// </summary>
        /// <value>
        /// The uninstall.
        /// </value>
        [DataMember]
        public DateTime? Uninstall { get; set; }

        /// <summary>
        /// Gets or sets the mono install counter.
        /// </summary>
        /// <value>
        /// The mono install counter.
        /// </value>
        [DataMember]
        public int? MonoInstallCounter { get; set; }

        /// <summary>
        /// Gets or sets the color install counter.
        /// </summary>
        /// <value>
        /// The color install counter.
        /// </value>
        [DataMember]
        public int? ColorInstallCounter { get; set; }

        /////// <summary>
        /////// Gets or sets the HardwareOrderNumber.
        /////// </summary>
        /////// <value>
        /////// The HardwareOrderNumber field.
        /////// </value>
        ////[DataMember]
        ////public string HardwareOrderNumber { get; set; }

        /// <summary>
        /// Gets or sets the TonerOrderNumber.
        /// </summary>
        /// <value>
        /// The TonerOrderNumber field.
        /// </value>
        [DataMember]
        public string TonerOrderNumber { get; set; }


        /// <summary>
        /// Gets or sets the ContractExpiration
        /// </summary>
        /// <value>
        /// The ContractExpiration field.
        /// </value>
        [DataMember]
        public DateTime? ContractExpiration { get; set; }

        /// <summary>
        /// Gets or sets the last update.
        /// </summary>
        /// <value>
        /// The last update.
        /// </value>
        [DataMember]
        public DateTime LastUpdate { get; set; }


        /// <summary>
        /// Gets or sets the black toner.
        /// </summary>
        /// <value>
        /// The black toner.
        /// </value>
        [DataMember]
        public byte? BlackToner { get; set; }

        /// <summary>
        /// Gets or sets the black toner1.
        /// </summary>
        /// <value>
        /// The black toner1.
        /// </value>
        [DataMember]
        public byte? BlackToner1 { get; set; }

        /// <summary>
        /// Gets or sets the black toner1 desc.
        /// </summary>
        /// <value>
        /// The black toner1 desc.
        /// </value>
        [DataMember]
        public string BlackToner1Desc { get; set; }

        /// <summary>
        /// Gets or sets the black toner2.
        /// </summary>
        /// <value>
        /// The black toner2.
        /// </value>
        [DataMember]
        public byte? BlackToner2 { get; set; }

        /// <summary>
        /// Gets or sets the black toner2 desc.
        /// </summary>
        /// <value>
        /// The black toner2 desc.
        /// </value>
        [DataMember]
        public string BlackToner2Desc { get; set; }

        /// <summary>
        /// Gets or sets the black toner3.
        /// </summary>
        /// <value>
        /// The black toner3.
        /// </value>
        [DataMember]
        public byte? BlackToner3 { get; set; }

        /// <summary>
        /// Gets or sets the black toner3 desc.
        /// </summary>
        /// <value>
        /// The black toner3 desc.
        /// </value>
        [DataMember]
        public string BlackToner3Desc { get; set; }


        /// <summary>
        /// Gets or sets the black photo.
        /// </summary>
        /// <value>
        /// The black photo.
        /// </value>
        [DataMember]
        public byte? BlackPhoto { get; set; }

        /// <summary>
        /// Gets or sets the cyan toner.
        /// </summary>
        /// <value>
        /// The cyan toner.
        /// </value>
        [DataMember]
        public byte? CyanToner { get; set; }

        /// <summary>
        /// Gets or sets the cyan photo.
        /// </summary>
        /// <value>
        /// The cyan photo.
        /// </value>
        [DataMember]
        public byte? CyanPhoto { get; set; }

        /// <summary>
        /// Gets or sets the magenta toner.
        /// </summary>
        /// <value>
        /// The magenta toner.
        /// </value>
        [DataMember]
        public byte? MagentaToner { get; set; }

        /// <summary>
        /// Gets or sets the magenta photo.
        /// </summary>
        /// <value>
        /// The magenta photo.
        /// </value>
        [DataMember]
        public byte? MagentaPhoto { get; set; }

        /// <summary>
        /// Gets or sets the yellow toner.
        /// </summary>
        /// <value>
        /// The yellow toner.
        /// </value>
        [DataMember]
        public byte? YellowToner { get; set; }

        /// <summary>
        /// Gets or sets the yellow photo.
        /// </summary>
        /// <value>
        /// The yellow photo.
        /// </value>
        [DataMember]
        public byte? YellowPhoto { get; set; }


        /// <summary>
        /// Gets or sets the is alert generator.
        /// </summary>
        /// <value>
        /// The is alert generator.
        /// </value>
        [DataMember]
        public bool IsAlertGenerator { get; set; }


        /// <summary>
        /// Gets or sets the IsInsideProject.
        /// </summary>
        /// <value>
        /// The IsInsideProject generator.
        /// </value>
        [DataMember]
        public bool IsInsideProject { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is manage supplies.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is manage supplies; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsClassified { get; set; }

        /// <summary>
        /// Calculated from.
        /// </summary>
        /// <value>
        /// The is manage supplies.
        /// </value>
        [DataMember]
        public bool? IsManageSupplies { get; set; }

        /// <summary>
        /// Gets or sets the Mono Coverage.
        /// </summary>
        /// <value>
        /// The mono coverage.
        /// </value>
        [DataMember]
        public double? MonoCoverage { get; set; }


        /// <summary>
        /// Gets or sets the Color Coverage.
        /// </summary>
        /// <value>
        /// The color coverage.
        /// </value>
        [DataMember]
        public double? ColorCoverage { get; set; }


        /// <summary>
        /// Gets or sets the MonthlyMonoVolume .
        /// </summary>
        /// <value>
        /// The Monthly Mono Volume.
        /// </value>
        [DataMember]
        public int? MonthlyMonoVolume { get; set; }

        /// <summary>
        /// Gets or sets the MonthlyColorVolume .
        /// </summary>
        /// <value>
        /// The MonthlyColorVolume.
        /// </value>
        [DataMember]
        public int? MonthlyColorVolume { get; set; }

        /// <summary>
        /// Gets or sets the MonthlyMonoVolumeByContract.
        /// </summary>
        /// <value>
        /// The MonthlyMonoVolumeByContract.
        /// </value>
        [DataMember]
        public int? MonthlyMonoVolumeByContract { get; set; }

        /// <summary>
        /// Gets or sets the MonthlyMonoVolumeByContract.
        /// </summary>
        /// <value>
        /// The MonthlyMonoVolumeByContract.
        /// </value>
        [DataMember]
        public int? MonthlyColorVolumeByContract { get; set; }


        /// <summary>
        /// Gets or sets the IsOffline .
        /// </summary>
        /// <value>
        /// The IsOffline.
        /// </value>
        [DataMember]
        public bool IsOffline { get; set; }

        /// <summary>
        /// Gets or sets the parked serial number, this field is set when the printer change its serial number
        /// </summary>
        /// <value>
        /// The parked serial number.
        /// </value>
        public string ParkedSerialNumber { get; set; }

        /// <summary>
        /// Gets or sets the counter.
        /// </summary>
        /// <value>
        /// The counter.
        /// </value>
        [DataMember]
        public CounterDto Counter { get; set; }

        /// <summary>
        /// Gets or sets the CountersDetailed.
        /// </summary>
        /// <value>
        /// The counter.
        /// </value>
        [DataMember]
        public IEnumerable<CounterDetailedDto> CountersDetailed { get; set; }

        /// <summary>
        /// Gets or sets the counter.
        /// </summary>
        /// <value>
        /// The counter.
        /// </value>
        [DataMember]
        public SdsDeviceDto SdsDevice { get; set; }

        ///// <summary>
        ///// Gets or sets the supply alerts.
        ///// </summary>
        ///// <value>
        ///// The supply alerts.
        ///// </value>
        //[DataMember]
        //public IList<SupplyAlertDto> SupplyAlerts { get; set; }

        /// <summary>
        /// Gets or sets the maintenance alerts.
        /// </summary>
        /// <value>
        /// The maintenance alerts.
        /// </value>
        [DataMember]
        public IEnumerable<MaintenanceAlertDto> MaintenanceAlerts { get; set; }

        /// <summary>
        /// Gets or sets the external identifier.
        /// </summary>
        /// <value>
        /// The external identifier.
        /// </value>
        [DataMember]
        public string ExternalIdentifier { get; set; }

        /// <summary>
        /// Gets or sets the mono partial counter.
        /// </summary>
        /// <value>
        /// The mono partial counter.
        /// </value>
        [DataMember]
        public int MonoPartialCounter { get; set; }

        /// <summary>
        /// Gets or sets the color partial counter.
        /// </summary>
        /// <value>
        /// The color partial counter.
        /// </value>
        [DataMember]
        public int ColorPartialCounter { get; set; }

        /// <summary>
        /// Get if the device manage repeated alerts
        /// </summary>
        [DataMember]
        public bool ManageRepeatedAlerts { get; set; }
    }

    /// <summary>
    /// 
    /// </summary>
    public class DeviceSuppliesDetailsDto
    {
        public IEnumerable<DeviceSupplyDetailDto> SupplyDetails { get; set; }
    }

    /// <summary>
    /// 
    /// </summary>
    public class DeviceSupplyDetailDto
    {
        public SupplyTypeEnum SupplyType { get; set; }
        public ColorTypeEnum ColorTypeEnum { get; set; }
        public string PartNumber { get; set; }
        public int? NominalDuration { get; set; }
        public int ShippedQuantity { get; set; }
        public int InstalledQuantity { get; set; }
        public float AverageDuration { get; set; }
        public double? AverageCoverage { get; set; }
        public byte? ResidualDuration { get; set; }
        public DateTime? DateExhaustion { get; set; }
    }


    /// <summary>
    /// Supplies related to Dealer choices
    /// </summary>
    public class DeviceSupplyDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the craetion
        /// </summary>
        /// <value>
        /// The creation date
        /// </value>
        [DataMember]
        public DateTime Creation { get; set; }


        /// <summary>
        /// Gets or sets the type of the maintenanceKit type.
        /// </summary>
        /// <value>
        /// The type of maintenance kit.
        /// </value>
        [DataMember]
        public EntityIdDescIntDto MaintenanceKitType { get; set; }

        /// <summary>
        /// Gets or sets the Description.
        /// </summary>
        /// <value>
        /// The Description.
        /// </value>
        [DataMember]
        public virtual string Description { get; set; }

        /// <summary>
        /// Gets if it is present now in the device or in the past
        /// </summary>
        /// <value>
        /// True or false.
        /// </value>
        [DataMember]
        public virtual bool CurrentlyInUse { get; set; }


    }
}
