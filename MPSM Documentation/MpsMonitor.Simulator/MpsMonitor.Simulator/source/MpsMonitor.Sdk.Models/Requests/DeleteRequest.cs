using MpsMonitor.Sdk.Models.Common;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents the generic request for deleting an object by id
    /// </summary>
    /// <seealso cref="BaseRequest" />
    [DataContract]
    public class DeleteRequest : BaseRequest
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

            if (this.Id == null)
            {
                errors.Add(new CodeDesc("Id", "Id field is required"));
            }

            return errors;
        }
    }

    /// <summary>
    /// Represents the generic request for creating an object
    /// </summary>
    /// <typeparam name="T"></typeparam>
    /// <seealso cref="BaseRequest" />
    [DataContract]
    public class DeleteRequest<T> : BaseRequest
    {
        /// <summary>
        /// Gets or sets the object to delete.
        /// </summary>
        /// <value>
        /// The object to delete.
        /// </value>
        [DataMember]
        [Required]
        public T ObjectToDelete { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (this.ObjectToDelete == null)
            {
                errors.Add(new CodeDesc("ObjectToDelete", "ObjectToDelete field is required"));
            }

            return errors;
        }
    }
}