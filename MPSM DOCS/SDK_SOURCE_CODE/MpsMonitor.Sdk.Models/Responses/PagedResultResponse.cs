using System;
using System.Collections.Generic;
using System.Linq;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Responses
{
    /// <summary>
    /// Represents a response that returns a pagedlist result
    /// </summary>
    /// <typeparam name="T"></typeparam>
    /// <seealso cref="ListResultResponse{T}" />
    [DataContract]
    public class PagedResultResponse<T> : ListResultResponse<T>
    {
        public PagedResultResponse()
        {
            Result = Enumerable.Empty<T>();
        }


        /// <summary>
        /// Gets or sets the total rows.
        /// </summary>
        /// <value>
        /// The total rows.
        /// </value>
        [DataMember]
        public int TotalRows { get; set; }
    }
}
