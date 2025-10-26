using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    /// Supplies related to Dealer choices
    /// </summary>
    public class DealerSupplyDto : EntityDto
    {
        public DealerSupplyDto()
        {
            Translations = new List<SupplyTranslationDto>();
        }

        /// <summary>
        /// Gets or sets the PartNumber.
        /// </summary>
        /// <value>
        /// The PartNumber.
        /// </value>
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
        /// Gets or sets the Maintenance Kit Type.
        /// </summary>
        /// <value>
        /// The MaintenanceKit Type.
        /// </value>
        [DataMember]
        public virtual EntityIdDescIntDto MaintenanceKitType { get; set; }

        /// <summary>
        /// Gets or sets the Maintenance Kit Color
        /// </summary>
        /// <value>
        /// The MaintenanceKitColor color.
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
        /// Gets or sets the Translations.
        /// </summary>
        /// <value>
        /// The Translations.
        /// </value>
        [DataMember]
        public virtual IList<SupplyTranslationDto> Translations { get; set; }

        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>
        /// Get if the supply is inherited from super dealer
        /// </summary>
        [DataMember]
        public bool IsInherited { get; set; }

        public bool AllLanguageEnumAreValid()
        {
            return this.Translations == null || this.Translations.All(x => Enum.IsDefined(typeof(LanguageEnum), x.Language));
        }
    }

    /// <summary>
    /// Supplies related to Dealer choices
    /// </summary>
    public class SupplyTranslationDto
    {
        /// <summary>
        /// Gets or sets the Language.
        /// </summary>
        /// <value>
        /// The Language.
        /// </value>
        [DataMember]
        public virtual LanguageEnum Language { get; set; }

        /// <summary>
        /// Gets or sets the Description.
        /// </summary>
        /// <value>
        /// The Description.
        /// </value>
        [DataMember]
        public virtual string Description { get; set; }


    }
}
