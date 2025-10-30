using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    ///  Represent the Id Desc dto
    /// </summary>
    [DataContract]
    public class EntityIdDescIntDto

    {
        public EntityIdDescIntDto()
        {

        }


        public EntityIdDescIntDto(int id, string description)
        {
            this.Id = id;
            this.Description = description;
        }

        /// <summary>
        /// Gets or sets the Id
        /// </summary>
        /// <value>
        /// The description.
        /// </value>
        [DataMember]
        public int Id { get; set; }
        /// <summary>
        /// Gets or sets the description
        /// </summary>
        /// <value>
        /// The description.
        /// </value>
        [DataMember]
        public string Description { get; set; }
    }

}
