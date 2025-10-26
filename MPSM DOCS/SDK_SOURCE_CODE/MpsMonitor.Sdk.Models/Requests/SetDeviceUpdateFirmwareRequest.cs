using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    [DataContract]
    public class SetDeviceUpdateFirmwareRequest : GetByIdRequest
    {
        [DataMember]
        public string FwVersion { get; set; }

        [DataMember]
        public DateTime? OperationFirmwareUpdateAtUtc { get; set; }

        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrEmpty(this.FwVersion))
            {
                errors.Add(new CodeDesc("FwVersion", "FwVersion is required"));
            }
            
            return errors;
        }
    }
}
