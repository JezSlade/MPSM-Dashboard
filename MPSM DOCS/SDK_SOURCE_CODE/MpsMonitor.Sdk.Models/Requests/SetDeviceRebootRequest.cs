using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    [DataContract]
    public class SetDeviceRebootRequest : GetByIdRequest
    {
        [DataMember]
        public DateTime? OperationRebootAtUtc { get; set; }
    }
}
