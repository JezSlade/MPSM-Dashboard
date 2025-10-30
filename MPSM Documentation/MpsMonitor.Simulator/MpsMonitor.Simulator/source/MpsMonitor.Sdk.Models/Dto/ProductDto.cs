using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    /// Product
    /// </summary>
    [DataContract]
    public class ProductDto : ProductBaseDto
    {
        /// <summary>
        /// Gets or sets the logo.
        /// </summary>
        /// <value>
        /// The logo.
        /// </value>
        [DataMember]
        public string Logo { get; set; }

        /// <summary>
        /// Gets or sets the color.
        /// </summary>
        /// <value>
        /// The color.
        /// </value>
        [DataMember]
        public short Color { get; set; }

        /// <summary>
        /// Gets or sets the type of the format.
        /// </summary>
        /// <value>
        /// The type of the format.
        /// </value>
        [DataMember]
        public short FormatType { get; set; }
    }

    /// <summary>
    /// ProductBase
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class ProductBaseDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the model.
        /// </summary>
        /// <value>
        /// The model.
        /// </value>
        [DataMember]
        public string Model { get; set; }

        /// <summary>
        /// Gets or sets the brand.
        /// </summary>
        /// <value>
        /// The brand.
        /// </value>
        [DataMember]
        public string Brand { get; set; }
    }
}
