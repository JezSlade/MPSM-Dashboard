using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Get Explorer Configurations Request
    /// </summary>
    public class GetExplorerConfigurationRequest : BaseRequest
    {
        /// <summary>
        /// CustomerCode
        /// </summary>
        [DataMember]
        [Required]
        public string CustomerCode { get; set; }

        /// <summary>
        /// ConfigurationCode
        /// </summary>
        [DataMember]
        [Required]
        public string ConfigurationId { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrEmpty(this.CustomerCode))
            {
                errors.Add(new CodeDesc("CustomerCode", "CustomerCode field is required"));
            }

            if (string.IsNullOrEmpty(this.ConfigurationId))
            {
                errors.Add(new CodeDesc("ConfigurationId", "ConfigurationId field is required"));
            }

            return errors;

        }
    }
}
