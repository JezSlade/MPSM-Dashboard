using System.Runtime.Serialization;

namespace MpsMonitor.Sdk.Models.Responses
{
    /// <summary>
    /// Represents a response that returns a single result
    /// </summary>
    /// <typeparam name="T"></typeparam>
    /// <seealso cref="BaseResponse" />
    [DataContract]
    public class SingleResultResponse<T> : BaseResponse
    {
        /// <summary>
        /// Gets or sets the result.
        /// </summary>
        /// <value>
        /// The result.
        /// </value>
        [DataMember]
        public T Result { get; set; }
    }
}
