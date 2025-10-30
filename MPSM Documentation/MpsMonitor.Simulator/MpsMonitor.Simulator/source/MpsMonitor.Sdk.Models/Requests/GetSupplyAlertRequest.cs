using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// GetSupplyAlert Request
    /// </summary>
    [DataContract]
    public class GetSupplyAlertRequest : FilteredPagedRequest
    {

        /// <summary>
        /// Gets or sets the dealer code
        /// </summary>
        /// <value>
        /// The dealer code
        /// </value>
        [DataMember]
        public string DealerCode { get; set; }


        /// <summary>
        /// Gets or sets the installed product identifier.
        /// </summary>
        /// <value>
        /// The installed product identifier.
        /// </value>
        [DataMember]
        public string DeviceId { get; set; }

        /// <summary>
        /// Gets or sets the serialnumber
        /// </summary>
        /// <value>
        /// The serialnumber
        /// </value>
        [DataMember]
        public string SerialNumber { get; set; }

        /// <summary>
        /// Gets or sets the asset number
        /// </summary>
        /// <value>
        /// The asset number
        /// </value>
        [DataMember]
        public string AssetNumber { get; set; }


        /// <summary>
        /// Gets or sets the initial From date
        /// </summary>
        /// <value>
        /// The date
        /// </value>
        [DataMember]
        public DateTime? InitialFrom { get; set; }

        /// <summary>
        /// Gets or sets the initial To date
        /// </summary>
        /// <value>
        /// The date
        /// </value>
        [DataMember]
        public DateTime? InitialTo { get; set; }

        /// <summary>
        /// Gets or sets the Exhausted From date
        /// </summary>
        /// <value>
        /// The date
        /// </value>
        [DataMember]
        public DateTime? ExhaustedFrom { get; set; }

        /// <summary>
        /// Gets or sets the Exhausted To date
        /// </summary>
        /// <value>
        /// The date
        /// </value>
        [DataMember]
        public DateTime? ExhaustedTo { get; set; }

        /// <summary>
        /// Gets or sets the brand 
        /// </summary>
        /// <value>
        /// The Brand .
        /// </value>
        [DataMember]
        public string Brand { get; set; }

        /// <summary>
        /// Gets or sets the ModelId
        /// </summary>
        /// <value>
        /// The Model Id.
        /// </value>
        [DataMember]
        public string Model { get; set; }


        /// <summary>
        /// Gets or sets the OfficeDescription
        /// </summary>
        /// <value>
        /// The OfficeDescription.
        /// </value>
        [DataMember]
        public string OfficeDescription { get; set; }


        /// <summary>
        /// Gets or sets the SupplySet Description
        /// </summary>
        /// <value>
        /// The description
        /// </value>
        [DataMember]
        public string SupplySetDescription { get; set; }

        /// <summary>
        /// Gets or sets the customer identifier.
        /// </summary>
        /// <value>
        /// The customer identifier.
        /// </value>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Gets or sets the filter customer text.
        /// </summary>
        /// <value>
        /// The filter customer text.
        /// </value>
        [DataMember]
        public string FilterCustomerText { get; set; }

        /// <summary>
        /// Gets or sets the ManageOption
        /// </summary>
        /// <value>
        /// The ManageOption.
        /// </value>
        [DataMember]
        public SupplyAlertManageOptionEnum? ManageOption { get; set; }

        /// <summary>
        /// Gets or sets the InstallationOption
        /// </summary>
        /// <value>
        /// The InstallationOption.
        /// </value>
        [DataMember]
        public SupplyAlertInstallationOptionEnum? InstallationOption { get; set; }

        /// <summary>
        /// Gets or sets the InstallationOption
        /// </summary>
        /// <value>
        /// The InstallationOption.
        /// </value>
        [DataMember]
        public SupplyAlertCancelOptionEnum? CancelOption { get; set; }

        /// <summary>
        /// Gets or sets the HiddenOption
        /// </summary>
        /// <value>
        /// The InstallationOption.
        /// </value>
        [DataMember]
        public SupplyAlertHiddenOptionEnum? HiddenOption { get; set; }

        /// <summary>
        /// Gets or sets the type of the supply.
        /// </summary>
        /// <value>
        /// The type of the supply.
        /// </value>
        [DataMember]
        public SupplyTypeEnum? SupplyType { get; set; }

        /// <summary>
        /// Gets or sets the type of the color.
        /// </summary>
        /// <value>
        /// The type of the color.
        /// </value>
        [DataMember]
        public ColorTypeEnum? ColorType { get; set; }

        /// <summary>
        /// Exclude alerts opened internally for manage ForStock Shipped Supplies
        /// </summary>
        [DataMember]
        public bool ExcludeForStockShippedSupplies { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (this.DealerCode == null)
            {
                errors.Add(new CodeDesc("DealerCode", "DealerCode field is required"));
            }

            return errors;
        }
    }
}
