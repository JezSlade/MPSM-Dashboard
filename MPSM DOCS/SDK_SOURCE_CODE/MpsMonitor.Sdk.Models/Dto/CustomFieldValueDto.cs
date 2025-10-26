using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    /// Represent a customer
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class CustomFieldValueDto
    {
        /// <summary>
        /// Represent a Label
        /// </summary>
        [DataMember]
        public string Label { get; set; }

        /// <summary>
        /// Represent a Label
        /// </summary>
        [DataMember]
        public string Placeholder { get; set; }

        /// <summary>
        /// Represent a Value
        /// </summary>
        [DataMember]
        public string Value { get; set; }
    }
}
