using System;
using System.Collections.Generic;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    /// 
    /// </summary>
    /// <typeparam name="T"></typeparam>
    public class RequestDto<T>
    {
        /// <summary>
        /// 
        /// </summary>
        public string Url { get; set; }

        /// <summary>
        /// 
        /// </summary>
        public T Request { get; set; }
       
        /// <summary>
        /// 
        /// </summary>
        public string Method { get; set; }
    }
}
