using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents a paged request with an additional filter text
    /// </summary>
    /// <seealso cref="PagedRequest" />
    [DataContract]
    public class FilteredPagedRequest : PagedRequest
    {
        /// <summary>
        /// Gets or sets the filter text.
        /// </summary>
        /// <value>
        /// The filter text.
        /// </value>
        [DataMember]
        public string FilterText { get; set; }
    }

    /// <summary>
    ///  Represents a request with an additional filter text
    /// </summary>
    /// <seealso cref="BaseRequest" />
    [DataContract]
    public class FilteredRequest : BaseRequest
    {
        /// <summary>
        /// Gets or sets the filter text.
        /// </summary>
        /// <value>
        /// The filter text.
        /// </value>
        [DataMember]
        public string FilterText { get; set; }
    }
}
