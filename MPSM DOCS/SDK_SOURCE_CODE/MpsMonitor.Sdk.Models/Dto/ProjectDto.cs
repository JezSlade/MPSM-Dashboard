using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{

    /// <summary />
    [DataContract]
    public class ProjectDto : EntityDto
    {
        public static readonly string DefaultDescription = "NEW";
        public static readonly string NotManageConsumables = "No consumables";

        /// <summary />
        public ProjectDto()
        {
            CustomFieldValues = new List<CustomFieldValueDto>();
        }

        /// <summary>
        /// Gets or sets the description.
        /// </summary>
        /// <value>
        /// The description.
        /// </value>
        [DataMember]
        public string Description { get; set; }

        /// <summary>
        /// Gets or sets the start.
        /// </summary>
        /// <value>
        /// The start.
        /// </value>
        [DataMember]
        public DateTime Start { get; set; }

        /// <summary>
        /// Gets or sets the finish.
        /// </summary>
        /// <value>
        /// The finish.
        /// </value>
        [DataMember]
        public DateTime Finish { get; set; }

        /// <summary>
        /// Gets or sets the custom fields.
        /// </summary>
        /// <value>
        /// </value>

        [DataMember]
        public List<CustomFieldValueDto> CustomFieldValues { get; set; }

        ///// <summary>
        ///// Contract
        ///// </summary>
        ////[DataMember]
        ////public FileInfoDto Contract { get; set; }

        /// <summary>
        /// Contract file name
        /// </summary>
        [DataMember]
        public string ContractFileName { get; set; }


        /// <summary>
        /// Gets or sets the enable massive shipping.
        /// </summary>
        /// <value>
        /// The value.
        /// </value>
        [DataMember]
        public bool EnableMassiveShipping { get; set; }

        /// <summary>
        /// Gets or sets the enable massive shipping notification
        /// </summary>
        /// <value>
        /// The value.
        /// </value>
        [DataMember]
        public bool MassiveShippingSendNotification { get; set; }


        /// <summary>
        /// Gets or sets the enable massive shipping logistic notification
        /// </summary>
        /// <value>
        /// The value.
        /// </value>
        [DataMember]
        public bool MassiveShippingSendLogistic { get; set; }

        ///// <summary>
        ///// Gets or sets the AlertInformationBox
        ///// </summary>
        ///// <value>
        ///// The value.
        ///// </value>
        ////[DataMember]
        ////public string AlertInformationBox { get; set; }
    }



    /// <summary>
    ///  Represent a device
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class ProjectVolumeDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the Product
        /// </summary>
        /// <value>
        /// The Produt.
        /// </value>
        [DataMember]
        public ProductDto Product { get; set; }


        /// <summary>
        /// Gets or sets the SupplyType.
        /// </summary>
        /// <value>
        /// The SupplyType.
        /// </value>
        [DataMember]
        public string SupplyType { get; set; }

        /// <summary>
        /// Gets or sets the ColorType.
        /// </summary>
        /// <value>
        /// The ColorType.
        /// </value>
        [DataMember]
        public string ColorType { get; set; }

        /// <summary>
        /// Gets or sets the PartNumber.
        /// </summary>
        /// <value>
        /// The PartNumber.
        /// </value>
        [DataMember]
        public string PartNumber { get; set; }

        /// <summary>
        /// Gets or sets the PartNumber.
        /// </summary>
        /// <value>
        /// The PartNumber.
        /// </value>
        [DataMember]
        public string Description { get; set; }


    }
}
