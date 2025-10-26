using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// CreateExplorerSubnetRequest
    /// </summary>
    [DataContract]
    public class CreateExplorerSubnetRequest : BaseRequest
    {
        /// <summary>
        /// CustomerCode
        /// </summary>
        [DataMember(IsRequired = true)]
        public string CustomerId { get; set; }

        /// <summary>
        /// ConfigurationCode
        /// </summary>
        [DataMember(IsRequired = true)]
        public string ExplorerConfigurationId { get; set; }

        /// <summary>
        /// SubnetIpStart
        /// </summary>
        [DataMember]
        public string IpStart { get; set; }

        /// <summary>
        /// SubnetIpEnd
        /// </summary>
        [DataMember]
        public string IpEnd { get; set; }

        /// <summary>
        /// Gets or sets the office identifier.
        /// </summary>
        /// <value>
        /// The office identifier.
        /// </value>
        [DataMember]
        public string OfficeId { get; set; }

        [DataMember]
        public string IpFreeText { get; set; }

        [DataMember]
        public bool IpFreeTextIgnoreBroadcast { get; set; }

        /// <summary />
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrEmpty(this.CustomerId))
            {
                errors.Add(new CodeDesc("CustomerId", "CustomerId field is required"));
            }

            if (string.IsNullOrEmpty(this.ExplorerConfigurationId))
            {
                errors.Add(new CodeDesc("ExplorerConfigurationId", "ExplorerConfigurationId field is required"));
            }

            if (string.IsNullOrEmpty(this.IpStart) && string.IsNullOrWhiteSpace(this.OfficeId) && string.IsNullOrWhiteSpace(this.IpFreeText))
            {
                errors.Add(new CodeDesc("IpStart", "IpStart field is required"));
            }

            return errors;
        }
    }
}
