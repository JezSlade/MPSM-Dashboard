using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{

    /// <summary>
    /// Represents a supply alert
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class SupplyAlertDto : EntityDto
    {
        /////// <summary>
        /////// Gets or sets the code.
        /////// </summary>
        /////// <value>
        /////// The code.
        /////// </value>
        ////[DataMember]
        ////public string Code { get; set; }

        /// <summary>
        /// Gets or sets the IdInstalledProduct.
        /// </summary>
        /// <value>
        /// The id.
        /// </value>
        [DataMember]
        public string InstalledProductId { get; set; }

        /// <summary>
        /// Gets or sets the IsShipped
        /// </summary>
        /// <value>
        /// The IsShipped.
        /// </value>
        [DataMember]
        public bool IsShipped { get; set; }

        /// <summary>
        /// Gets or sets the serial number
        /// </summary>
        /// <value>
        /// The SerialNumber.
        /// </value>
        [DataMember]
        public string SerialNumber { get; set; }

        /// <summary>
        /// Gets or sets the asset number
        /// </summary>
        /// <value>
        /// The AssetNumber.
        /// </value>
        [DataMember]
        public string AssetNumber { get; set; }

        /// <summary>
        /// Gets or sets the system name
        /// </summary>
        /// <value>
        /// The AssetNumber.
        /// </value>
        [DataMember]
        public string SystemName { get; set; }

        /// <summary>
        /// Gets or sets the Department
        /// </summary>
        /// <value>
        /// The AssetNumber.
        /// </value>
        [DataMember]
        public string Department { get; set; }



        /// <summary>
        /// Gets or sets the Product
        /// </summary>
        /// <value>
        /// The Product.
        /// </value>
        [DataMember]
        public ProductBaseDto Product { get; set; }


        /// <summary>
        /// Gets or sets the Project
        /// </summary>
        /// <value>
        /// The Project.
        /// </value>
        [DataMember]
        public ProjectDto Project { get; set; }

        /// <summary>
        /// Gets or sets the DealerSupplySetDescription
        /// </summary>
        /// <value>
        /// The dealer supply set description.
        /// </value>
        [DataMember]
        public string DealerSupplySetDescription { get; set; }


        /// <summary>
        /// Gets or sets the Office
        /// </summary>
        /// <value>
        /// The Project.
        /// </value>
        [DataMember]
        public OfficeDto Office { get; set; }

        /// <summary>
        /// Gets or sets the warning.
        /// </summary>
        /// <value>
        /// The warning.
        /// </value>
        [DataMember]
        public string Warning { get; set; }

        /// <summary>
        /// Gets or sets the initial date.
        /// </summary>
        /// <value>
        /// The initial date.
        /// </value>
        [DataMember]
        public DateTime? InitialDate { get; set; }

        /// <summary>
        /// Gets or sets the initial mono.
        /// </summary>
        /// <value>
        /// The initial mono.
        /// </value>
        [DataMember]
        public int InitialMono { get; set; }

        /// <summary>
        /// Gets or sets the initial color.
        /// </summary>
        /// <value>
        /// The initial color.
        /// </value>
        [DataMember]
        public int InitialColor { get; set; }

        /// <summary>
        /// Gets or sets the initial total.
        /// </summary>
        /// <value>
        /// The initial total.
        /// </value>
        [DataMember]
        public int InitialTotal { get; set; }

        /// <summary>
        /// Gets or sets the initial residual percentage.
        /// </summary>
        /// <value>
        /// The initial residual percentage.
        /// </value>
        [DataMember]
        public byte InitialResidualPercentage { get; set; }

        /// <summary>
        /// Gets or sets the hide alert limit.
        /// </summary>
        /// <value>
        /// The hide alert limit.
        /// </value>
        [DataMember]
        public int? HideAlertLimit { get; set; }

        /// <summary>
        /// Gets or sets the actual date.
        /// </summary>
        /// <value>
        /// The actual date.
        /// </value>
        [DataMember]
        public DateTime? ActualDate { get; set; }

        /// <summary>
        /// Gets or sets the actual mono.
        /// </summary>
        /// <value>
        /// The actual mono.
        /// </value>
        [DataMember]
        public int ActualMono { get; set; }

        /// <summary>
        /// Gets or sets the actual color.
        /// </summary>
        /// <value>
        /// The actual color.
        /// </value>
        [DataMember]
        public int ActualColor { get; set; }

        /// <summary>
        /// Gets or sets the actual total.
        /// </summary>
        /// <value>
        /// The actual total.
        /// </value>
        [DataMember]
        public int ActualTotal { get; set; }

        /// <summary>
        /// Gets or sets the actual residual percentage.
        /// </summary>
        /// <value>
        /// The actual residual percentage.
        /// </value>
        [DataMember]
        public int ActualResidualPercentage { get; set; }

        /// <summary>
        /// Gets or sets the canceled on.
        /// </summary>
        /// <value>
        /// The canceled on.
        /// </value>
        [DataMember]
        public DateTime? CanceledOn { get; set; }

        /// <summary>
        /// Gets or sets the toner delivered.
        /// </summary>
        /// <value>
        /// The toner delivered.
        /// </value>
        [DataMember]
        public DateTime? TonerDelivered { get; set; }

        /// <summary>
        /// Gets or sets the toner installed.
        /// </summary>
        /// <value>
        /// The toner installed.
        /// </value>
        [DataMember]
        public DateTime? TonerInstalled { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is hidden.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is hidden; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool? IsHidden { get; set; }

        /// <summary>
        /// Gets or sets the type of the color.
        /// </summary>
        /// <value>
        /// The type of the color.
        /// </value>
        [DataMember]
        public ColorTypeEnum ColorType { get; set; }

        /// <summary>
        /// Gets or sets the type of the supply.
        /// </summary>
        /// <value>
        /// The type of the supply.
        /// </value>
        [DataMember]
        public SupplyTypeEnum SupplyType { get; set; }

        /// <summary>
        /// Gets or sets the type of the maintenanceKit type.
        /// </summary>
        /// <value>
        /// The type of maintenance kit.
        /// </value>
        [DataMember]
        public EntityIdDescIntDto MaintenanceKitType { get; set; }

        /// <summary>
        /// Gets or sets the color of the maintenance kit.
        /// </summary>
        /// <value>
        /// The color of the maintenance kit
        /// </value>
        [DataMember]
        public EntityIdDescIntDto MaintenanceKitColor { get; set; }


        /// <summary>
        /// Gets or sets the type of the color.
        /// </summary>
        /// <value>
        /// The type of the color.
        /// </value>
        [DataMember]
        public DateTime? ExhaustedExpiration { get; set; }

        /// <summary>
        /// Gets or sets the time zone.
        /// </summary>
        /// <value>
        /// The time zone.
        /// </value>
        [DataMember]
        public string TimeZone { get; set; }

        /// <summary>
        /// Gets or sets the time zone.
        /// </summary>
        /// <value>
        /// The time zone.
        /// </value>
        [DataMember]
        public string TimeZoneIana { get; set; }
    }
    /// <summary>
    /// Supply AlertList
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class SupplyAlertListDto : EntityDto
    {
        /// <summary>Gets or sets the customer identifier.</summary>
        /// <value>The customer identifier.</value>
        [DataMember]
        public string CustomerId { get; set; }

        /// <summary>
        /// Gets or sets the customer code.
        /// </summary>
        /// <value>
        /// The customer code.
        /// </value>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Gets or sets the customer description.
        /// </summary>
        /// <value>
        /// The customer description.
        /// </value>
        [DataMember]
        public string CustomerDescription { get; set; }

        /// <summary>Gets or sets the dealer identifier.</summary>
        /// <value>The dealer identifier.</value>
        [DataMember]
        public string DealerId { get; set; }

        /// <summary>Gets or sets the dealer code.</summary>
        /// <value>The dealer code.</value>
        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>Gets or sets the dealer description.</summary>
        /// <value>The dealer description.</value>
        [DataMember]
        public string DealerDescription { get; set; }

        /// <summary>
        /// Gets or sets the IdInstalledProduct.
        /// </summary>
        /// <value>
        /// The id.
        /// </value>
        [DataMember]
        public string DeviceId { get; set; }

        /// <summary>
        /// Gets or sets the serial number
        /// </summary>
        /// <value>
        /// The SerialNumber.
        /// </value>
        [DataMember]
        public string SerialNumber { get; set; }

        /// <summary>
        /// Gets or sets the mac address.
        /// </summary>
        /// <value>
        /// The mac address.
        /// </value>
        [DataMember]
        public string MacAddress { get; set; }

        /// <summary>
        /// Gets or sets the ip address.
        /// </summary>
        /// <value>
        /// The ip address.
        /// </value>
        [DataMember]
        public string IpAddress { get; set; }

        /// <summary>
        /// Gets or sets the asset number
        /// </summary>
        /// <value>
        /// The AssetNumber.
        /// </value>
        [DataMember]
        public string AssetNumber { get; set; }

        /// <summary>
        /// Gets or sets the system name
        /// </summary>
        /// <value>
        /// The AssetNumber.
        /// </value>
        [DataMember]
        public string SystemName { get; set; }

        /// <summary>
        /// Gets or sets the Department
        /// </summary>
        /// <value>
        /// The AssetNumber.
        /// </value>
        [DataMember]
        public string Department { get; set; }

        /// <summary>
        /// Gets or sets the IsShipped
        /// </summary>
        /// <value>
        /// The IsShipped.
        /// </value>
        [DataMember]
        public bool IsShipped { get; set; }

        /// <summary>
        /// Gets or sets the Product
        /// </summary>
        /// <value>
        /// The Product.
        /// </value>
        [DataMember]
        public ProductBaseDto Product { get; set; }

        /// <summary>
        /// Gets or sets the Project
        /// </summary>
        /// <value>
        /// The Project.
        /// </value>
        [DataMember]
        [Obsolete]
        public ProjectDto Project { get; set; }


        /// <summary>
        /// Gets or sets the ProductBrand
        /// </summary>
        /// <value>
        /// The ProductBrand.
        /// </value>
        [DataMember]
        public string ProductBrand { get; set; }

        /// <summary>
        /// Gets or sets the ProductModel
        /// </summary>
        /// <value>
        /// The ProductModel.
        /// </value>
        [DataMember]
        public string ProductModel { get; set; }

        /// <summary>
        /// Gets or sets the DealerSupplySetDescription
        /// </summary>
        /// <value>
        /// The DealerSupplySetDescription.
        /// </value>
        [DataMember]
        public string DealerSupplySetDescription { get; set; }


        /// <summary>
        /// Gets or sets the Office
        /// </summary>
        /// <value>
        /// The Project.
        /// </value>
        [DataMember]
        public OfficeBaseDto Office { get; set; }

        /// <summary>
        /// Gets or sets the warning.
        /// </summary>
        /// <value>
        /// The warning.
        /// </value>
        [DataMember]
        public string Warning { get; set; }

        /// <summary>
        /// Gets or sets the initial date.
        /// </summary>
        /// <value>
        /// The initial date.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime? InitialDate { get; set; }

        /// <summary>
        /// Gets or sets the initial mono.
        /// </summary>
        /// <value>
        /// The initial mono.
        /// </value>
        [DataMember]
        public int InitialMono { get; set; }

        /// <summary>
        /// Gets or sets the initial color.
        /// </summary>
        /// <value>
        /// The initial color.
        /// </value>
        [DataMember]
        public int InitialColor { get; set; }

        /// <summary>
        /// Gets or sets the initial total.
        /// </summary>
        /// <value>
        /// The initial total.
        /// </value>
        [DataMember]
        public int InitialTotal { get; set; }

        /// <summary>
        /// Gets or sets the time zone.
        /// </summary>
        /// <value>
        /// The time zone.
        /// </value>
        [DataMember]
        public string TimeZone { get; set; }

        /// <summary>
        /// Gets or sets the time zone.
        /// </summary>
        /// <value>
        /// The time zone.
        /// </value>
        [DataMember]
        public string TimeZoneIana { get; set; }

        /// <summary>
        /// Gets or sets the initial residual percentage.
        /// </summary>
        /// <value>
        /// The initial residual percentage.
        /// </value>
        [DataMember]
        public byte InitialResidualPercentage { get; set; }

        /// <summary>
        /// Gets or sets the hide alert limit.
        /// </summary>
        /// <value>
        /// The hide alert limit.
        /// </value>
        [DataMember]
        public int? HideAlertLimit { get; set; }

        /// <summary>
        /// Gets or sets the actual date.
        /// </summary>
        /// <value>
        /// The actual date.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime? ActualDate { get; set; }

        /// <summary>
        /// Gets or sets the actual mono.
        /// </summary>
        /// <value>
        /// The actual mono.
        /// </value>
        [DataMember]
        public int ActualMono { get; set; }

        /// <summary>
        /// Gets or sets the actual color.
        /// </summary>
        /// <value>
        /// The actual color.
        /// </value>
        [DataMember]
        public int ActualColor { get; set; }

        /// <summary>
        /// Gets or sets the actual total.
        /// </summary>
        /// <value>
        /// The actual total.
        /// </value>
        [DataMember]
        public int ActualTotal { get; set; }

        /// <summary>
        /// Gets or sets the actual residual percentage.
        /// </summary>
        /// <value>
        /// The actual residual percentage.
        /// </value>
        [DataMember]
        public int ActualResidualPercentage { get; set; }

        /// <summary>
        /// Gets or sets the canceled on.
        /// </summary>
        /// <value>
        /// The canceled on.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime? CanceledOn { get; set; }

        /// <summary>
        /// Gets or sets the toner delivered.
        /// </summary>
        /// <value>
        /// The toner delivered.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime? TonerDelivered { get; set; }

        /// <summary>
        /// Gets or sets the toner installed.
        /// </summary>
        /// <value>
        /// The toner installed.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime? TonerInstalled { get; set; }


        /// <summary>
        /// Gets or sets a value indicating whether this instance is hidden.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is hidden; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool? IsHidden { get; set; }

        /// <summary>
        /// Gets or sets the type of the color.
        /// </summary>
        /// <value>
        /// The type of the color.
        /// </value>
        [DataMember]
        public ColorTypeEnum ColorType { get; set; }

        /// <summary>
        /// Gets or sets the type of the supply.
        /// </summary>
        /// <value>
        /// The type of the supply.
        /// </value>
        [DataMember]
        public SupplyTypeEnum SupplyType { get; set; }

        /// <summary>
        /// Gets or sets the type of the maintenanceKit type.
        /// </summary>
        /// <value>
        /// The type of maintenance kit.
        /// </value>
        [DataMember]
        public EntityIdDescIntDto MaintenanceKitType { get; set; }

        /// <summary>
        /// Gets or sets the color of the maintenance kit.
        /// </summary>
        /// <value>
        /// The color of the maintenance kit
        /// </value>
        [DataMember]
        public EntityIdDescIntDto MaintenanceKitColor { get; set; }


        /// <summary>
        /// Gets or sets the type of the color.
        /// </summary>
        /// <value>
        /// The type of the color.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime? ExhaustedExpiration { get; set; }

        /// <summary>
        /// Gets or sets the suggested part number.
        /// </summary>
        /// <value>
        /// The suggested part number.
        /// </value>
        [DataMember]
        public string SuggestedPartNumber { get; set; }

        /// <summary>
        /// Gets or sets the shipped supply Id
        /// </summary>
        /// <value>
        /// The value.
        /// </value>
        [DataMember]
        public string ShippedSupplyId { get; set; }


        /// <summary>
        /// Gets or sets the shipped supply quantity
        /// </summary>
        /// <value>
        /// The value.
        /// </value>
        [DataMember]
        public int? ShippedSupplyQuantity { get; set; }

        /// <summary>
        /// Gets or sets the shipped OrderNumber
        /// </summary>
        /// <value>
        /// The value.
        /// </value>
        [DataMember]
        public string ShippedSupplyOrderNumber { get; set; }

        /// <summary>
        /// Gets or sets the shipped DocumentNumber
        /// </summary>
        /// <value>
        /// The value.
        /// </value>
        [DataMember]
        public string ShippedSupplyDocumentNumber { get; set; }

        /// <summary>
        /// Gets or sets the shipped generation
        /// </summary>
        /// <value>
        /// The value.
        /// </value>
        [DataMember]
        public GenerationsEnum? ShippedSupplyGenerationType { get; set; }

        /// <summary>
        /// Gets or sets the shipped supply creation.
        /// </summary>
        /// <value>
        /// The shipped supply creation.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime? ShippedSupplyCreation { get; set; }
    }

    /// <summary>
    /// Supplies related to Dealer choices and device
    /// </summary>
    public class AvailableSuppliesDto
    {

        public AvailableSuppliesDto()
        {
            AvailableSupplies = new List<DealerSupplyListDto>();
            CurrentSupplies = new List<DeviceSupplyDto>();
            SuggestedSupplies = new List<DealerSupplyListDto>();
        }


        ///
        /// Available supplies from supply set or project volumes
        ///
        [DataMember]
        public IList<DealerSupplyListDto> AvailableSupplies { get; set; }

        ///
        /// Last shipped supply from same alert type and color 
        ///
        [DataMember]
        public LastShippedSupplyDto LastUsedSupply { get; set; }

        ///
        /// The supply inside the device (if this information are available )
        ///
        [DataMember]
        public IEnumerable<DeviceSupplyDto> CurrentSupplies { get; set; }

        ///
        /// Suggested supplies from some internal consideration
        ///
        [DataMember]
        public IList<DealerSupplyListDto> SuggestedSupplies { get; set; }



    }

    /// <summary>
    /// Represent the last shipped supply
    /// </summary>
    /// <seealso cref="MpsMonitor.Models.EntityDto" />
    [DataContract]
    public class LastShippedSupplyDto
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="LastShippedSupplyDto"/> class.
        /// </summary>
        public LastShippedSupplyDto()
        {

        }

        /// <summary>
        /// Gets or sets the supply.
        /// </summary>
        /// <value>
        /// The supply.
        /// </value>
        [DataMember]
        public DealerSupplyListDto Supply { get; set; }



        /// <summary>
        /// Gets or sets the creation.
        /// </summary>
        /// <value>
        /// The creation.
        /// </value>
        [DataMember]
        [DisplayFormat(DataFormatString = "{0:s}")]
        public DateTime Creation { get; set; }
    }
}
