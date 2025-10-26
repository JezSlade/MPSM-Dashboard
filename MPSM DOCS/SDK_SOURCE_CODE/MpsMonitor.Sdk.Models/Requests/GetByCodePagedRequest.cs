using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents a request to retrieve an object by code
    /// </summary>
    /// <seealso cref="BaseRequest" />
    [DataContract]
    public class GetByCodePagedRequest : FilteredPagedRequest
    {
        /// <summary>
        /// Gets or sets the code.
        /// </summary>
        /// <value>
        /// The code.
        /// </value>
        [DataMember]
        public string Code { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrWhiteSpace(Code))
            {
                errors.Add(new CodeDesc("Code", "Code field is required"));
            }

            return errors;
        }
    }
}
