using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    class AlertDto
    {
    }
    
    /// <summary>
    /// MaintenanceAlertDto
    /// </summary>
    [DataContract]
    public class MaintenanceAlertDto
    {
        /// <summary>
        /// Identifier
        /// </summary>
        [DataMember]
        public string Identifier { get; protected set; }
        /// <summary>
        /// Code
        /// </summary>
        [DataMember]
        public string Code { get; protected set; }

        /// <summary>
        /// Description
        /// </summary>
        [DataMember]
        public string Description { get; protected set; }

        /// <summary>
        /// Opened
        /// </summary>
        public DateTime Opened { get; protected set; }

        /// <summary>
        /// Closed
        /// </summary>
        [DataMember]
        public DateTime? Closed { get; set; }
    }


    /// <summary>
    /// Represents a dto for massive update  alerts
    /// </summary>
    [DataContract]
    public class PostponeAlertDto : EntityDto
    {
        /// <summary>
        /// Gets or sets percentage
        /// </summary>
        /// <value>
        /// The percentage.
        /// </value>
        [DataMember]
        public int Percentage { get; set; }

    }

    /// <summary>
    /// Represents a dto for massive update  alerts
    /// </summary>
    [DataContract]
    public class MassiveUpdateAlertDto
    {
        /// <summary>
        /// Gets or sets the DealerCode
        /// </summary>
        /// <value>
        /// The dealer code.
        /// </value>
        [DataMember]
        public string DealerCode { get; set; }


        /// <summary>
        /// Gets or sets the ids
        /// </summary>
        /// <value>
        /// The id.
        /// </value>
        [DataMember]
        public string[] Id { get; set; }


        /// <summary>
        /// Gets or sets a value indicating set to canceled or not canceled.
        /// </summary>
        /// <value>
        ///   True or false
        /// </value>
        [DataMember]
        public bool? Cancel { get; set; }

        /// <summary>
        /// Gets or sets a value indicating set to hidden or not.
        /// </summary>
        /// <value>
        ///   True or false
        /// </value>
        [DataMember]
        public bool? Hidden { get; set; }

        ///// <summary>
        ///// Gets or sets a value indicating set to installed or not.
        ///// </summary>
        ///// <value>
        /////   True or false
        ///// </value>
        [DataMember]
        public bool? Install { get; set; }
    }
}
