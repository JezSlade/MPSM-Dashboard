using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    public class CreateExplorerScheduleRequest : BaseRequest
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
        [DataMember(IsRequired = true)]
        public string Days { get; set; }

        /// <summary>
        /// SubnetIpEnd
        /// </summary>
        [DataMember]
        public string StartAtHours { get; set; }

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

            if (string.IsNullOrEmpty(this.Days))
            {
                errors.Add(new CodeDesc("Days", "Days field is required"));
            }

            if (string.IsNullOrEmpty(this.StartAtHours))
            {
                errors.Add(new CodeDesc("StartAtHours", "Days field is required"));
            }

            return errors;
        }
    }
}
