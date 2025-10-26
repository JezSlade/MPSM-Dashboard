using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents a request to retrieve a pagedlist of offices by CustomerId
    /// </summary>
    /// <seealso cref="FilteredPagedRequest" />
    [DataContract]
    public class GetOfficesRequest : FilteredPagedRequest
    {
        /// <summary>
        /// Gets or sets the customer identifier.
        /// </summary>
        public string CustomerId { get; set; }
        /// <summary>
        /// Gets or sets the customer code.
        /// </summary>
        /// <value>
        /// The customer code.
        /// </value>
        [DataMember]
        public string CustomerCode { get; set; }

        [DataMember]
        public bool? HasSubnets { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrWhiteSpace(this.CustomerCode))
            {
                errors.Add(new CodeDesc("CustomerCode", "CustomerCode field is required"));
            }

            return errors;
        }
    }
}
