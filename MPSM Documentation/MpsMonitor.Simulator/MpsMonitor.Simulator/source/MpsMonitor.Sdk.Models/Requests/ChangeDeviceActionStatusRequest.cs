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
    /// <seealso cref="GetByIdRequest" />
    [DataContract]
    public class ChangeDeviceActionStatusRequest : GetByIdRequest
    {
        /// <summary>
        /// Gets or sets the action jam id identifier.
        /// </summary>
        /// <value>
        /// The action id identifier.
        /// </value>
        [DataMember]
        public Guid ActionJamId { get; set; }

        /// <summary>
        /// Gets or sets the device id identifier.
        /// </summary>
        /// <value>
        /// The device id identifier.
        /// </value>
        [DataMember]
        public string DeviceId { get; set; }

        /// <summary>
        /// Gets or sets the state.
        /// </summary>
        /// <value>
        /// The state.
        /// </value>
        [DataMember]
        public SdsActionUpdateStateEnum State { get; set; }

        /// <summary>
        /// Gets or sets the comments.
        /// </summary>
        /// <value>
        /// The comments.
        /// </value>
        [DataMember]
        public string Comments { get; set; }

        /// <summary>
        /// Gets or sets the triage action.
        /// </summary>
        /// <value>
        /// The triage action.
        /// </value>
        [DataMember]
        public string TriageAction { get; set; }

        /// <summary>
        /// Gets or sets the parts.
        /// </summary>
        /// <value>
        /// The parts.
        /// </value>
        [DataMember]
        public string[] Parts { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrWhiteSpace(this.DeviceId))
            {
                errors.Add(new CodeDesc("DeviceId", "DeviceId field is required"));
            }

            ////if (this.ActionJamId == Guid.Empty)
            ////{
            ////    errors.Add(new CodeDesc("ActionJamId", "ActionJamId field is required"));
            ////}

            return errors;
        }
    }
}
