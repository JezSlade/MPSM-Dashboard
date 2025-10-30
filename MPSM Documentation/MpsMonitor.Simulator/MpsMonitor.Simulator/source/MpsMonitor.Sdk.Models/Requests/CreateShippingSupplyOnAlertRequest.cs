using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Dto;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Requests
{

    /// <summary />
    [DataContract]
    public class CreateShippingSupplyRequest : BaseRequest
    {
        /// <summary>
        /// Gets or sets the device identifier.
        /// </summary>
        /// <value>
        /// The device identifier.
        /// </value>
        [DataMember]
        public string DeviceId { get; set; }

        /// <summary>
        /// Gets or sets the price.
        /// </summary>
        /// <value>
        /// The price.
        /// </value>
        [DataMember]
        public float? Price { get; set; }

        /// <summary>
        /// Gets or sets the part number.
        /// </summary>
        /// <value>
        /// The part number.
        /// </value>
        [DataMember]
        public DealerSupplyDto Supply { get; set; }

        /// <summary>
        /// In case of Custom Supply Set associated to the device, the Supply specified will replace the same partnumber type.
        /// </summary>
        /// <value>
        ///   <c>true</c> if [replace supply in custom set]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool ReplaceSupplyInCustomSet { get; set; }

        /// <summary>
        /// Gets or sets the quantity.
        /// </summary>
        /// <value>
        /// The quantity.
        /// </value>
        [DataMember]
        public int Quantity { get; set; }

        /// <summary>
        /// Gets or sets the counter.
        /// </summary>
        /// <value>
        /// The counter.
        /// </value>
        [DataMember]
        public int Counter { get; set; }

        /// <summary>
        /// Gets or sets the creation.
        /// </summary>
        /// <value>
        /// The creation.
        /// </value>
        [DataMember]
        public DateTime? Creation { get; set; }

        /// <summary>
        /// Gets or sets the document number.
        /// </summary>
        /// <value>
        /// The document number.
        /// </value>
        [DataMember]
        public string DocumentNumber { get; set; }

        /// <summary>
        /// Gets or sets the order number.
        /// </summary>
        /// <value>
        /// The order number.
        /// </value>
        [DataMember]
        public string OrderNumber { get; set; }

        /// <summary>
        /// Gets or sets the department.
        /// </summary>
        /// <value>
        /// The department.
        /// </value>
        [DataMember]
        public string Department { get; set; }

        /// <summary>
        /// Gets or sets the contact.
        /// </summary>
        /// <value>
        /// The contact.
        /// </value>
        [DataMember]
        public string Contact { get; set; }

        /// <summary>
        /// Send notification email to customer if enabled
        /// </summary>
        [DataMember]
        public bool SendCustomerNotificationEmail { get; set; }

        /// <summary>
        /// Activate logistic notification process
        /// </summary>
        [DataMember]
        public bool ActivateLogisticNotification { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrWhiteSpace(this.DeviceId))
            {
                errors.Add(new CodeDesc("DeviceId", "Required"));
            }

            if (Supply == null || (string.IsNullOrEmpty(Supply.Id) && string.IsNullOrEmpty(Supply.PartNumber)))
            {
                errors.Add(new CodeDesc("Supply", "Supply Id or Supply Partnumber is required"));
            }

            if (this.Creation == DateTime.MinValue || !this.Creation.HasValue)
            {
                errors.Add(new CodeDesc("Creation", "Required"));
            }

            if (this.Quantity < 1 || this.Quantity > 10)
            {
                errors.Add(new CodeDesc("Quantity", "Value should be greather or equal then 1 and less than 10"));
            }

            return errors;
        }
    }

    /// <summary />
    [DataContract]
    public class CreateShippingSupplyOnAlertRequest : CreateShippingSupplyRequest
    {
        /// <summary>
        /// Gets or sets the supply alert identifier.
        /// </summary>
        /// <value>
        /// The supply alert identifier.
        /// </value>
        [DataMember]
        public string SupplyAlertId { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();

            if (string.IsNullOrWhiteSpace(this.SupplyAlertId))
            {
                errors.Add(new CodeDesc("SupplyAlertId", "Required"));
            }

            return errors;
        }
    }
}
