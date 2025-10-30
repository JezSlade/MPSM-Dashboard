using System;
using System.Collections.Generic;
using System.Linq;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Responses
{
    /// <summary>
    /// Represents a response that returns a list result
    /// </summary>
    /// <typeparam name="T"></typeparam>
    /// <seealso cref="SingleResultResponse{T}" />
    [DataContract]
    public class ListResultResponse<T> : SingleResultResponse<IEnumerable<T>>
    {
        public ListResultResponse()
        {
            Result = Enumerable.Empty<T>();
        }
    }
}
