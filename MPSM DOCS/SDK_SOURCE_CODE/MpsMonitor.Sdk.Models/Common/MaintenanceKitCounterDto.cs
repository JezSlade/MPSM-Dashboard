using MpsMonitor.Sdk.Models.Dto;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Common
{
    /// <summary>
    /// Represent the level of a maintenance kit
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class MaintenanceKitCounterDto //: EntityDto
    {

        public MaintenanceKitCounterDto()
        {
        }

        public MaintenanceKitCounterDto(string key, string value, IList<KeyValuePair<int, string>> mkTypes, IList<KeyValuePair<int, string>> mkColors)
        {
            Consumable = key;
            ResidualLevelPercentage = value;

            Key = GetSafeMaintenanceKitDescription(key);
            Value = value;

            var splittedDescription = key.Split('&');
            KeyValuePair<int, string> found;
            if (splittedDescription.Length > 1 && !string.IsNullOrWhiteSpace(splittedDescription[1]))
            {
                found = mkTypes.FirstOrDefault(x => x.Value.ToLower() == splittedDescription[1].ToLower());
                this.MaintenanceKitType = found.Equals(default(KeyValuePair<int, string>)) ? null : new EntityIdDescIntDto(found.Key, found.Value);
            }

            if (this.MaintenanceKitType != null && splittedDescription.Length > 2 && !string.IsNullOrWhiteSpace(splittedDescription[2]))
            {
                string removeC = splittedDescription[2].Substring(1, splittedDescription[2].Length - 1);
                if (string.IsNullOrWhiteSpace(removeC))
                    found = mkColors.FirstOrDefault(x => x.Key == Constants.MAINTENANCEKIT_COLOR_ID_NOT_AVAILABLE);
                else
                    found = mkColors.FirstOrDefault(x => x.Value.ToLower() == removeC.ToLower());

                this.MaintenanceKitColor = found.Equals(default(KeyValuePair<int, string>)) ? null : new EntityIdDescIntDto(found.Key, found.Value);
            }

        }

        public string GetSafeMaintenanceKitDescription(string value)
        {
            if (string.IsNullOrEmpty(value))
                return null;

            var splitted = value.Split('&');
            if (splitted.Length == 0)
                return null;
            return splitted[0];
        }

        /// <summary>
        /// Gets or sets the key.
        /// </summary>
        /// <value>
        /// The name.
        /// </value>
        [DataMember]
        public string Key { get; set; }

        /// <summary>
        /// Gets or sets the value.
        /// </summary>
        /// <value>
        /// The name.
        /// </value>
        [DataMember]
        public string Value { get; set; }


        /// <summary>
        /// Gets or sets the maintenance kit Type.
        /// </summary>
        /// <value>
        /// The maintenance kit type.
        /// </value>
        [DataMember]
        public EntityIdDescIntDto MaintenanceKitType { get; set; }

        /// <summary>
        /// Gets or sets the maintenance kit Color.
        /// </summary>
        /// <value>
        /// The maintenance kit Color.
        /// </value>
        [DataMember]
        public EntityIdDescIntDto MaintenanceKitColor { get; set; }

        /// <summary>
        /// Gets or sets the consumable name
        /// </summary>
        /// <value>
        /// The consumable name
        /// </value>
        [DataMember]
        [Obsolete("Use Key field")]
        public string Consumable { get; set; }


        /// <summary>
        /// Gets or sets the residual level percentage
        /// </summary>
        /// <value>
        /// The residual level percentage
        /// </value>
        [DataMember]
        [Obsolete("Use Value Field")]
        public string ResidualLevelPercentage { get; set; }

    }
}
