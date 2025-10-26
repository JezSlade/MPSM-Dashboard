using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    ///  Represent the Country dto
    /// </summary>
    [DataContract]
    public class CountryDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the code.
        /// </summary>
        /// <value>
        /// The code.
        /// </value>
        [DataMember]
        public string Code { get; set; }

        /// <summary>
        /// Gets or sets the name.
        /// </summary>
        /// <value>
        /// The name.
        /// </value>
        [DataMember]
        public string Name { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is eu.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is eu; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsEu { get; set; }

        /// <summary>
        /// Gets a value indicating whether this instance is italy.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is italy; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsItaly { get; set; }

        /// <summary>
        /// Get the region of a country.
        /// </summary>
        [DataMember]
        public CountryRegionDto CountryRegion { get; set; }
    }

    /// <summary>
    /// Defines the Country Region object
    /// </summary>
    [DataContract]
    public class CountryRegionDto : EntityDto
    {
        /// <summary>
        /// The region code
        /// </summary>
        [DataMember]
        public string Code { get; set; }

        /// <summary>
        /// The region name
        /// </summary>
        [DataMember]
        public string Description { get; set; }
    }
}
