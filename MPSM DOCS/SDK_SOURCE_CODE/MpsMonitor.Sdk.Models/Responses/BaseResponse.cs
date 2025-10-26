using MpsMonitor.Sdk.Models.Common;
using System.Collections.Generic;
using System.Runtime.Serialization;

namespace MpsMonitor.Sdk.Models.Responses
{
    /// <summary>
    ///  Represents the base class for all the responses
    /// </summary>
    [DataContract]
    public class BaseResponse
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="BaseResponse"/> class.
        /// </summary>
        public BaseResponse()
        {
            Errors = new List<CodeDesc>();
        }


        /// <summary>
        /// Returns true if the response is valid (No errors).
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is valid; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsValid { get; set; }

        /// <summary>
        /// Gets or sets the errors. The list is empty if the response is valid
        /// </summary>
        /// <value>
        /// The errors.
        /// </value>
        [DataMember]
        public IList<CodeDesc> Errors { get; set; }

        /// <summary>
        /// Gets or sets the generic string return value.
        /// </summary>
        /// <value>
        /// The return value.
        /// </value>
        [DataMember]
        public string ReturnValue { get; set; }
    }
}
