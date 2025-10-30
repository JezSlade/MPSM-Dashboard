using System.Runtime.Serialization;

namespace MpsMonitor.Sdk.Models.Requests
{
    public class GetCustomersRequest : FilteredPagedRequest
    {
        /// <summary>
        /// Gets or sets the dealer code.
        /// </summary>
        /// <value>
        /// The dealer code.
        /// </value>
        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>
        /// Gets or sets the dealer code.
        /// </summary>
        /// <value>
        /// The code.
        /// </value>
        [DataMember]
        public string Code { get; set; }

        /// <summary>
        /// Gets or sets the has hp SDS.
        /// </summary>
        /// <value>
        /// The has hp SDS.
        /// </value>
        [DataMember]
        public bool? HasHpSds { get; set; }
    }
}
