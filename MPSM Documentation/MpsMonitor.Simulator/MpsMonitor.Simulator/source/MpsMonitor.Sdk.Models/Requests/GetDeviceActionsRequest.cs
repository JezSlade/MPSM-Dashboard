using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// GetDeviceActions Request
    /// </summary>
    /// <seealso cref="FilteredPagedRequest" />
    [DataContract]
    public class GetDeviceActionsRequest : FilteredPagedRequest
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
        /// Gets or sets the dealer identifier.
        /// </summary>
        /// <value>
        /// The dealer identifier.
        /// </value>
        [DataMember]
        [Obsolete("Use code")]
        public string DealerId { get; set; }

        /// <summary>
        /// Gets or sets the customer identifier.
        /// </summary>
        /// <value>
        /// The customer identifier.
        /// </value>
        [DataMember]
        [Obsolete("Use code")]
        public string CustomerId { get; set; }

        /// <summary>
        /// Gets or sets the dealer identifier.
        /// </summary>
        /// <value>
        /// The dealer identifier.
        /// </value>
        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>
        /// Gets or sets the customer identifier.
        /// </summary>
        /// <value>
        /// The customer identifier.
        /// </value>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Gets or sets the state.
        /// </summary>
        /// <value>
        /// The state.
        /// </value>
        [DataMember]
        public SdsActionUpdateStateEnum? State { get; set; }

        /// <summary>
        /// Gets or sets the severity.
        /// </summary>
        /// <value>
        /// The severity.
        /// </value>
        [DataMember]
        public JamActionServerityEnum? Severity { get; set; }

        /// <summary>
        /// Gets or sets the type of the action.
        /// </summary>
        /// <value>
        /// The type of the action.
        /// </value>
        [DataMember]
        public string ActionType { get; set; }

        /// <summary />
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrEmpty(this.DeviceId) && string.IsNullOrEmpty(this.DealerId) && string.IsNullOrEmpty(this.DealerCode))
            {
                errors.Add(new CodeDesc("DealerCode", "DeviceId and DealerCode are required"));
            }

            return errors;
        }
    }
}
