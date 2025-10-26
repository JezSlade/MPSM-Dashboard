using MpsMonitor.Sdk.Models.Dto;
using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    [DataContract]
    public class GetDevicesRequest : FilteredPagedRequest
    {
        public GetDevicesRequest()
        {
            this.Status = ManagedStatusEnum.Managed;
        }

        /// <summary>
        /// Gets or sets the filter dealer i.
        /// </summary>
        /// <value>
        /// The filter dealer codes.
        /// </value>
        [DataMember]
        public string FilterDealerId { get; set; }

        /// <summary>
        /// Gets or sets the filter customer codes.
        /// </summary>
        /// <value>
        /// The filter customer codes.
        /// </value>
        [DataMember]
        public string[] FilterCustomerCodes { get; set; }

        /// <summary>
        /// Gets or sets the product brand.
        /// </summary>
        /// <value>
        /// The product brand.
        /// </value>
        [DataMember]
        public string ProductBrand { get; set; }

        /// <summary>
        /// Gets or sets the product model.
        /// </summary>
        /// <value>
        /// The product model.
        /// </value>
        [DataMember]
        public string ProductModel { get; set; }

        /// <summary>
        /// Gets or sets the office Id.
        /// </summary>
        /// <value>
        /// Gets or sets the office Id.
        /// </value>
        [DataMember]
        public string OfficeId { get; set; }


        /// <summary>
        /// Gets or sets the status.
        /// </summary>
        /// <value>
        /// The status.
        /// </value>
        [DataMember]
        public ManagedStatusEnum? Status { get; set; }
    }
}
