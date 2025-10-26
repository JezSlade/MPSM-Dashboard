using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents the generic request for creating an object
    /// </summary>
    /// <typeparam name="T"></typeparam>
    /// <seealso cref="BaseRequest" />
    [DataContract]
    public class CreateRequest<T> : BaseRequest
    {
        /// <summary>
        /// Gets or sets the object to create.
        /// </summary>
        /// <value>
        /// The object to create.
        /// </value>
        [DataMember]
        [Required]
        public T ObjectToCreate { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (this.ObjectToCreate == null)
            {
                errors.Add(new CodeDesc("ObjectToCreate", "ObjectToCreate field is required"));
            }


            return errors;
        }

    }
}
