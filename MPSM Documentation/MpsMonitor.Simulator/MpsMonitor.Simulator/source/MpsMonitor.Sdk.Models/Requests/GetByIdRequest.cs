
using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents a request to retrieve an object by id
    /// </summary>
    /// <seealso cref="BaseRequest" />
    [DataContract]
    public class GetByIdRequest : BaseRequest
    {
        /// <summary>
        /// Gets or sets the identifier.
        /// </summary>
        /// <value>
        /// The identifier.
        /// </value>
        [DataMember]
        [Required]
        public string Id { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrWhiteSpace(this.Id))
            {
                errors.Add(new CodeDesc("Id", "Id field is required"));
            }

            return errors;
        }
    }
}
