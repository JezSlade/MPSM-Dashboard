using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents the generic request for updating an object
    /// </summary>
    [DataContract]
    public class UpdateRequest<T> : BaseRequest
    {
        /// <summary>
        /// Gets or sets the object to update.
        /// </summary>
        [DataMember]
        [Required]
        public T ObjectToUpdate { get; set; }


        /// <summary>
        /// Validates the request
        /// </summary>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (ObjectToUpdate == null)
            {
                errors.Add(new CodeDesc("ObjectToUpdate", "ObjectToUpdate field is required"));
            }

            return errors;
        }
    }
}
