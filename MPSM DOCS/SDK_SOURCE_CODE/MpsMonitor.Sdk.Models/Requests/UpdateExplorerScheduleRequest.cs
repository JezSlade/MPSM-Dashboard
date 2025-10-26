using MpsMonitor.Sdk.Models.Common;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{
    public class UpdateExplorerScheduleRequest : CreateExplorerScheduleRequest
    {
        /// <summary>
        /// Id
        /// </summary>
        [DataMember(IsRequired = true)]
        public string Id { get; set; }


        /// <summary />
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrEmpty(this.Id))
            {
                errors.Add(new CodeDesc("Id", "Id field is required"));
            }

            return errors;
        }
    }
}
