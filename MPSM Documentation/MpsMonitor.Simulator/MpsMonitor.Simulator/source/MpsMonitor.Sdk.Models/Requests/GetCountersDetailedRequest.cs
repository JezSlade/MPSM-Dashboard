using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Dto;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents a request to retrieve a list of counters with details
    /// </summary>
    /// <seealso cref="BaseRequest" />
    public class GetCountersDetailedRequest : BaseRequest
    {
        /// <summary>
        /// Gets or sets the dealer code.
        /// </summary>
        /// <value>
        /// The dealer code.
        /// </value>
        [DataMember]
        [Required]
        public string DealerCode { get; set; }

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
        /// Gets or sets the serial number.
        /// </summary>
        /// <value>
        /// The serial number.
        /// </value>
        [DataMember]
        public string SerialNumber { get; set; }


        /// <summary>
        /// Gets or sets the asset number.
        /// </summary>
        /// <value>
        /// The asset number.
        /// </value>
        [DataMember]
        public string AssetNumber { get; set; }

        /// <summary>
        /// Gets or sets the counter detaild tags.
        /// </summary>
        /// <value>
        /// The counter detaild tags.
        /// </value>
        [DataMember]
        public string[] CounterDetaildTags { get; set; }


        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrWhiteSpace(DealerCode))
            {
                errors.Add(new CodeDesc("DealerCode", "c.notfound"));
            }
            if (string.IsNullOrWhiteSpace(CustomerCode))
            {
                errors.Add(new CodeDesc("CustomerCode", "c.notfound"));
            }

            return errors;
        }
    }
}
