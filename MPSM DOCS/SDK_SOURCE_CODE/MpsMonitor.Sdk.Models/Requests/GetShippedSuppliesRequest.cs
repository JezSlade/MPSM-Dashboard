using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{

    /// <summary>
    /// Represents a request to retrieve a list of archived shipped supplies
    /// </summary>
    /// <seealso cref="BaseRequest" />
    [DataContract]
    public class GetShippedSuppliesRequest : FilteredPagedRequest
    {
        /// <summary>
        /// Gets or sets the brand.
        /// </summary>
        /// <value>
        /// The brand.
        /// </value>
        [DataMember]
        public string Brand { get; set; }

        /// <summary>
        /// Gets or sets the model.
        /// </summary>
        /// <value>
        /// The model.
        /// </value>
        [DataMember]
        public string Model { get; set; }

        /// <summary>
        /// Gets or sets the customer code.
        /// </summary>
        /// <value>
        /// The customer code.
        /// </value>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Gets or sets the filter customer text.
        /// </summary>
        /// <value>
        /// The filter customer text.
        /// </value>
        [DataMember]
        public string FilterCustomerText { get; set; }

        /// <summary>
        /// Gets or sets the dealer code.
        /// </summary>
        /// <value>
        /// The dealer code.
        /// </value>
        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>
        /// Gets or sets the installed product identifier.
        /// </summary>
        /// <value>
        /// The installed product identifier.
        /// </value>
        [DataMember]
        ////[Required]
        public string InstalledProductId { get; set; }

        /// <summary>
        /// Gets or sets the InstalledProductIsManaged
        /// </summary>
        /// <value>
        /// The InstalledProductIsManaged.
        /// </value>
        [DataMember]
        public bool? InstalledProductIsManaged { get; set; }


        /// <summary>
        /// Gets or sets the generations.
        /// </summary>
        /// <value>
        /// The generations.
        /// </value>
        [DataMember]
        public GenerationsEnum? Generations { get; set; }

        /// <summary>
        /// Gets or sets the type of the supply.
        /// </summary>
        /// <value>
        /// The type of the supply.
        /// </value>
        [DataMember]
        public SupplyTypeEnum? SupplyType { get; set; }

        /// <summary>
        /// Gets or sets the type of the color.
        /// </summary>
        /// <value>
        /// The type of the color.
        /// </value>
        [DataMember]
        public ColorTypeEnum? ColorType { get; set; }

        /// <summary>
        /// Gets or sets the part number.
        /// </summary>
        /// <value>
        /// The part number.
        /// </value>
        [DataMember]
        public string PartNumber { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            ////if (string.IsNullOrWhiteSpace(this.InstalledProductId))
            ////{
            ////    errors.Add("InstalledProductId", "Installed product id field is required");
            ////}

            return errors;
        }
    }
}
