using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Dto;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents a request to retrieve a list of counters
    /// </summary>
    /// <seealso cref="BaseRequest" />
    [DataContract]
    public class GetCountersRequest : BaseRequest
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
        /// Gets or sets the from date.
        /// </summary>
        /// <value>
        /// The from date.
        /// </value>
        [DataMember]
        [Required]
        public DateTime FromDate { get; set; }

        /// <summary>
        /// Gets or sets the to date.
        /// </summary>
        /// <value>
        /// The to date.
        /// </value>
        [DataMember]
        [Required]
        public DateTime ToDate { get; set; }


        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrEmpty(DealerCode))
            {
                errors.Add(new CodeDesc("DealerCode", "DealerCode required"));
            }
            if (string.IsNullOrEmpty(CustomerCode))
            {
                errors.Add(new CodeDesc("CustomerCode", "DealerCode required"));
            }

            if (FromDate.Subtract(ToDate).Days > 31)
            {
                errors.Add(new CodeDesc("Date period", "Date range cannot be grater than 1 month"));
            }

            return errors;
        }
    }
}
