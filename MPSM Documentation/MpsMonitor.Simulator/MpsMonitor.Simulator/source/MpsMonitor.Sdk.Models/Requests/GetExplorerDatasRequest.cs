using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Request for eXplorer data
    /// </summary>
    [DataContract]
    public class GetExplorerDatasRequest : PagedRequest
    {
        public const string LIST = "list";
        public const string TOBEUPDATED = "tobeupdated";
        public const string UPDATABLE = "updatable";
        public const string WITHPROBLEMS = "withproblems";


        /// <summary>
        /// Gets or sets the type of the list.
        /// </summary>
        /// <value>
        /// The type of the list.
        /// </value>
        [DataMember]
        public string ListType { get; set; }

        /// <summary>
        /// Gets or sets the filter dealer codes.
        /// </summary>
        /// <value>
        /// The filter dealer codes.
        /// </value>
        [DataMember]
        public string FilterDealerCodes { get; set; }

        /// <summary>
        /// Gets or sets the filter customer codes.
        /// </summary>
        /// <value>
        /// The filter customer codes.
        /// </value>
        [DataMember]
        public string FilterCustomerCodes { get; set; }

        /// <summary>
        /// Gets or sets the search key.
        /// </summary>
        /// <value>
        /// The search key.
        /// </value>
        [DataMember]
        public string SearchKey { get; set; }

        /// <summary>
        /// Gets or sets the filter jam.
        /// </summary>
        /// <value>
        /// The filter jam.
        /// </value>
        [DataMember]
        public bool? SDSOnly { get; set; }

        /// <summary>
        /// True to return only clustered eXplorer datas,
        /// False to return only unclustered eXplorer datas
        /// </summary>
        /// <value>
        /// IsClustered filter.
        /// </value>
        [DataMember]
        public bool? IsClustered { get; set; }

        /// <summary>
        /// Gets or sets the communication status.
        /// </summary>
        /// <value>
        /// The communication status.
        /// </value>
        [DataMember]
        public CommunicationStatusEnum CommunicationStatus { get; set; }

        /// <summary>
        /// Gets or sets the filter customer text.
        /// </summary>
        /// <value>
        /// The filter customer text.
        /// </value>
        [DataMember]
        public string FilterCustomerText { get; set; }
        /// <summary>
        /// Gets or sets the filter dealer id.
        /// </summary>
        /// <value>
        /// The filter dealer id.
        /// </value>
        [DataMember]
        public string FilterDealerId { get; set; }

        /// <summary>
        /// Gets or sets the filter customer id.
        /// </summary>
        /// <value>
        /// The filter customer id.
        /// </value>
        [DataMember]
        public string FilterCustomerId { get; set; }

        /// <summary>
        /// Gets or sets the filter customer id.
        /// </summary>
        /// <value>
        /// The filter customer id.
        /// </value>
        [DataMember]
        public bool? HasConfiguration { get; set; }
    }
}
