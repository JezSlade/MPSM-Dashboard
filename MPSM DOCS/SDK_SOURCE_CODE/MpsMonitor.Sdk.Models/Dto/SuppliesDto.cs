using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    class SuppliesDto
    {
    }

    /// <summary>
    /// Supplies related to Dealer choices
    /// </summary>
    public class DealerSupplyListDto : EntityDto
    {
        /// <summary>
        /// Gets the PartNumber.
        /// </summary>
        [DataMember]
        public virtual string PartNumber { get; set; }

        /// <summary>
        /// Gets or sets the Description.
        /// </summary>
        /// <value>
        /// The Description.
        /// </value>
        [DataMember]
        public virtual string Description { get; set; }

        /// <summary>
        /// Gets or sets the DescriptionLocalized.
        /// </summary>
        /// <value>
        /// The Description.
        /// </value>
        [DataMember]
        public virtual string DescriptionLocalized { get; set; }

        /// <summary>
        /// Gets or sets the Supply Type.
        /// </summary>
        /// <value>
        /// The Supply Type.
        /// </value>
        [DataMember]
        public virtual SupplyTypeEnum SupplyType { get; set; }

        /// <summary>
        /// Gets or sets the Color Type.
        /// </summary>
        /// <value>
        /// The Color Type.
        /// </value>
        [DataMember]
        public virtual ColorTypeEnum ColorType { get; set; }

        /// <summary>
        /// Gets or sets the MaintenanceKit Type.
        /// </summary>
        /// <value>
        /// The MaintenanceKit Type.
        /// </value>
        [DataMember]
        public virtual EntityIdDescIntDto MaintenanceKitType { get; set; }

        /// <summary>
        /// Gets or sets the MaintenanceKit Color.
        /// </summary>
        /// <value>
        /// The MaintenanceKit Color.
        /// </value>
        [DataMember]
        public virtual EntityIdDescIntDto MaintenanceKitColor { get; set; }


        /// <summary>
        /// Gets or sets the Duration.
        /// </summary>
        /// <value>
        /// The Duration.
        /// </value>
        [DataMember]
        public virtual int Duration { get; set; }

        /// <summary>
        /// Get if the supply is inherited from super dealer
        /// </summary>
        [DataMember]
        public virtual bool IsInherited { get; set; }
    }
}
