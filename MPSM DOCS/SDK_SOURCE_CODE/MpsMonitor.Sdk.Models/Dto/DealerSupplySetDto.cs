using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    ///  Represent an supply
    /// </summary>
    [DataContract]
    public class DealerSupplySetBaseDto : EntityDto
    {

        /// <summary>
        /// Gets or sets the description
        /// </summary>
        /// <value>
        /// The dealer code
        /// </value>
        [DataMember]
        public string Description { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is for device only.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is for device only; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsForDeviceOnly { get; set; }
    }
}
