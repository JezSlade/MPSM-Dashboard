using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents a request to retrieve an alert
    /// </summary>
    /// <seealso cref="BaseRequest" />
    [DataContract]
    public class GetAvailableSuppliesForADeviceRequest : BaseRequest
    {
        /// <summary>
        /// Gets or sets the device Id
        /// </summary>
        /// <value>
        /// The Id.
        /// </value>
        [DataMember]
        [Required]
        public string DeviceId { get; set; }

        /// <summary>
        /// Gets or sets the SupplyType 
        /// </summary>
        /// <value>
        /// The SupplyType.
        /// </value>
        [DataMember]
        [Required]
        public SupplyTypeEnum? SupplyType { get; set; }

        /// <summary>
        /// Gets or sets the ColorType 
        /// </summary>
        /// <value>
        /// The ColorType.
        /// </value>
        [DataMember]
        [Required]
        public ColorTypeEnum? ColorType { get; set; }


        /// <summary>
        /// Gets or sets the MaintenanceKitType 
        /// </summary>
        /// <value>
        /// The MaintenanceKitType.
        /// </value>
        [DataMember]
        [Required]
        public int? MaintenanceKitTypeId { get; set; }

        /// <summary>
        /// Gets or sets the MaintenanceKitColor 
        /// </summary>
        /// <value>
        /// The MaintenanceKitColor.
        /// </value>
        [DataMember]
        [Required]
        public int? MaintenanceKitColorId { get; set; }

        /// <summary>
        /// Gets or sets the SupplyAlert warning
        /// </summary>
        /// <value>
        /// The warning SupplyAlert.
        /// </value>
        [DataMember]
        [Required]
        public string Warning { get; set; }

        /// <summary>
        /// Set the language to retrieve supplies localizated
        /// </summary>
        [DataMember]
        [Required]
        public LanguageEnum? Language { get; set; }

        /// <summary>
        /// Show only current supplies in use
        /// </summary>
        [DataMember]
        public bool ShowOnlyCurrentSuppliesInUse { get; set; }

        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrEmpty(DeviceId))
            {
                errors.Add(new CodeDesc("DeviceId", "Device is required"));
            }


            return errors;
        }
    }
}
