using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class GetDealerSuppliesRequest : GetByCodePagedRequest
    {
        [DataMember]
        public ColorEnum? ColorType { get; set; }

        [DataMember]
        public LanguageEnum? Language { get; set; }

        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();
            return errors;
        }
    }
}
