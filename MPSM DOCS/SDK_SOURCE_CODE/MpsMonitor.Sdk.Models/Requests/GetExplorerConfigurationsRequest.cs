using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// 
    /// </summary>
    /// <seealso cref="BaseRequest" />
    public class GetExplorerConfigurationsRequest : PagedRequest
    {
        /// <summary>
        /// Gets or sets the customer code.
        /// </summary>
        /// <value>
        /// The customer code.
        /// </value>
        [DataMember]
        [Required]
        public string CustomerCode { get; set; }


        /// <summary>
        /// Gets or sets the explorer data identifier.
        /// </summary>
        /// <value>
        /// The explorer data identifier.
        /// </value>
        [DataMember]
        [Required]
        public string ExplorerDataIdentifier { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrEmpty(CustomerCode))
            {
                errors.Add(new CodeDesc("CustomerCode", "CustomerCode field is required"));
            }

            ////if (string.IsNullOrEmpty(ExplorerDataIdentifier))
            ////{
            ////    errors.Add(new CodeDesc("ExplorerDataIdentifier", "ExplorerDataIdentifier field is required"));
            ////}

            return errors;
        }
    }
}
