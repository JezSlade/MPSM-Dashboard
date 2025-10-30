using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Dto;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents a request to retrieve a pagedlist meter reads of devices
    /// </summary>
    [DataContract]
    public class UpdateDeviceRequest : BaseRequest
    {
        /// <summary>
        /// Gets or sets the device identifier.
        /// </summary>
        /// <value>
        /// The device identifier.
        /// </value>
        [DataMember]
        public string DeviceId { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is managed.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is managed; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsManaged { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is managed.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is managed; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsGenerateAlert { get; set; }

        /// <summary>
        /// Set if you want to manage repeted alerts on the device
        /// </summary>
        [DataMember]
        public bool ManageRepeatedAlerts { get; set; }

        /// <summary>
        /// Set a new product brand for this device
        /// </summary>
        [DataMember]
        public string NewProductBrand { get; set; }

        /// <summary>
        /// Set a new product model for this device
        /// </summary>
        [DataMember]
        public string NewProductModel { get; set; }

        /// <summary>Gets or sets the project identifier.</summary>
        /// <value>The project identifier.</value>
        [DataMember]
        public string ProjectId { get; set; }

        /// <summary>
        /// Gets or sets the office identifier.
        /// </summary>
        /// <value>
        /// The office identifier.
        /// </value>
        [DataMember]
        public string OfficeId { get; set; }

        /// <summary>
        /// Gets or sets the contact.
        /// </summary>
        /// <value>
        /// The contact.
        /// </value>
        [DataMember]
        public string Contact { get; set; }

        /// <summary>
        /// Gets or sets the mail delivery toner.
        /// </summary>
        /// <value>
        /// The mail delivery toner.
        /// </value>
        [DataMember]
        public string MailDeliveryToner { get; set; }

        /// <summary>
        /// Gets or sets the hardware order number.
        /// </summary>
        /// <value>
        /// The hardware order number.
        /// </value>
        [DataMember]
        public string HardwareOrderNumber { get; set; }

        /// <summary>
        /// Gets or sets the toner order number.
        /// </summary>
        /// <value>
        /// The toner order number.
        /// </value>
        [DataMember]
        public string TonerOrderNumber { get; set; }

        /// <summary>
        /// Gets or sets the note.
        /// </summary>
        /// <value>
        /// The note.
        /// </value>
        [DataMember]
        public string Note { get; set; }

        /// <summary>
        /// Gets or sets the ip address.
        /// </summary>
        /// <value>
        /// The ip address.
        /// </value>
        [DataMember]
        public string IpAddress { get; set; }

        /////// <summary>
        /////// Gets or sets the mac address.
        /////// </summary>
        /////// <value>
        /////// The mac address.
        /////// </value>
        ////[DataMember]
        ////public string MacAddress { get; set; }

        /////// <summary>
        /////// Gets or sets the name of the system.
        /////// </summary>
        /////// <value>
        /////// The name of the system.
        /////// </value>
        ////[DataMember]
        ////public string SystemName { get; set; }

        /// <summary>
        /// Gets or sets the asset number.
        /// </summary>
        /// <value>
        /// The asset number.
        /// </value>
        [DataMember]
        public string AssetNumber { get; set; }

        /// <summary>
        /// Gets or sets the install.
        /// </summary>
        /// <value>
        /// The install.
        /// </value>
        [DataMember]
        public DateTime? Install { get; set; }

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

        /// <summary>
        /// Gets or sets the external identifier.
        /// </summary>
        /// <value>
        /// The external identifier.
        /// </value>
        [DataMember]
        public string ExternalIdentifier { get; set; }

        /// <summary>
        /// Sets the custom fields
        /// </summary>
        /// <value>
        /// The subnets.
        /// </value>
        [DataMember]
        public List<CustomFieldValueDto> CustomFieldValues { get; set; }

        /// <summary />
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrEmpty(this.DeviceId))
            {
                errors.Add(new CodeDesc("DeviceId", "Required"));
            }

            if (IsGenerateAlert && !IsManaged)
            {
                errors.Add(new CodeDesc("IsGenerateAlertSupplies", "Cannot set IsGenerateAlertSupplies on not managed device"));
            }


            if (!string.IsNullOrWhiteSpace(this.NewProductBrand) && string.IsNullOrWhiteSpace(this.NewProductModel))
            {
                errors.Add(new CodeDesc("NewProductModel", "Model not indicated"));
            }
            if (!string.IsNullOrWhiteSpace(this.NewProductModel) && string.IsNullOrWhiteSpace(this.NewProductBrand))
            {
                errors.Add(new CodeDesc("NewProductBrand", "Brand not indicated"));
            }


            return errors;
        }
    }
}
