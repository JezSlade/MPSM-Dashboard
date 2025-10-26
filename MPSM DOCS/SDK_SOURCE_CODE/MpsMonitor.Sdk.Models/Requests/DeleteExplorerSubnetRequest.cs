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
    public class DeleteExplorerSubnetRequest : DeleteRequest
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

            return errors;
        }
    }
}
