using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Responses
{
    /// <summary>
    /// Represent a response for customer creation
    /// </summary>
    /// <seealso cref="BaseResponse" />
    [DataContract]
    public class CreateCustomerResponse : BaseResponse
    {
        /// <summary>
        /// The code of the newly created Customer
        /// </summary>
        [DataMember]
        public string Id { get; set; }

        /// <summary>
        /// The code of the newly created Customer
        /// </summary>
        [DataMember]
        public string Code { get; set; }

        /// <summary>
        /// The description of the newly created Customer
        /// </summary>
        [DataMember]
        public string Description { get; set; }

        /// <summary>
        /// eXplorer download url
        /// </summary>
        [DataMember]
        public string ExplorerDownloadUrl { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [send explorer installation invitation failed].
        /// </summary>
        [DataMember]
        public bool SendExplorerInstallationInvitationFailed { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [enable SDS failed].
        /// </summary>
        [DataMember]
        public bool EnableSDSFailed { get; set; }
    }
}
